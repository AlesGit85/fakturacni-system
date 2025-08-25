<?php

declare(strict_types=1);

namespace App\Presentation\Migration;

use App\Presentation\BasePresenter;
use App\Model\MigrationService;

/**
 * Migration Presenter pro nástroje migrace dat
 * Obsahuje nástroj pro zašifrování starých dat v databázi
 */
class MigrationPresenter extends BasePresenter
{
    /** @var array Povolené role pro přístup k migračním nástrojům */
    protected array $requiredRoles = ['admin'];

    /** @var MigrationService */
    private $migrationService;

    public function injectMigrationService(MigrationService $migrationService): void
    {
        $this->migrationService = $migrationService;
    }

    /**
     * Hlavní stránka migračních nástrojů
     */
    public function actionDefault(): void
    {
        if (!$this->isAdmin() && !$this->isSuperAdmin()) {
            $this->error('Nemáte oprávnění pro přístup k migračním nástrojům', 403);
        }

        $this->securityLogger->logSecurityEvent(
            'migration_tools_access',
            "Uživatel {$this->getUser()->getIdentity()->username} přistoupil k migračním nástrojům",
            ['user_id' => $this->getUser()->getId()]
        );
    }

    public function renderDefault(): void
    {
        $this->template->pageTitle = 'Migration Utility';
        $this->template->isAdmin = $this->isAdmin();
        $this->template->isSuperAdmin = $this->isSuperAdmin();
    }

    /**
     * Stránka pro šifrování starých dat
     */
    public function actionEncryptOldData(): void
    {
        if (!$this->isAdmin() && !$this->isSuperAdmin()) {
            $this->error('Nemáte oprávnění pro spuštění šifrování dat', 403);
        }

        $this->securityLogger->logSecurityEvent(
            'encryption_migration_access',
            "Uživatel {$this->getUser()->getIdentity()->username} přistoupil k nástroji šifrování starých dat",
            ['user_id' => $this->getUser()->getId()]
        );
    }

    public function renderEncryptOldData(): void
    {
        $this->template->pageTitle = 'Šifrování starých dat';
        $this->template->isAdmin = $this->isAdmin();
        $this->template->isSuperAdmin = $this->isSuperAdmin();
    }

    /**
     * AJAX akce pro analýzu dat k šifrování
     */
    public function handleAnalyzeData(): void
    {
        if (!$this->isAjax()) {
            $this->error('Tato akce je dostupná pouze přes AJAX');
        }

        if (!$this->isAdmin() && !$this->isSuperAdmin()) {
            $this->sendJson([
                'success' => false,
                'message' => 'Nemáte oprávnění pro analýzu dat'
            ]);
            return;
        }

        if (!$this->migrationService) {
            $this->sendJson([
                'success' => false,
                'message' => 'MigrationService není dostupný'
            ]);
            return;
        }

        // Použijeme tenant ID 1 (můžeme později změnit na dynamické)
        $tenantId = 1;

        // Skutečná analýza
        $analysisResult = $this->migrationService->analyzeDataForEncryption($tenantId);

        $this->sendJson([
            'success' => true,
            'message' => 'Analýza dokončena',
            'data' => $analysisResult
        ]);
    }

    /**
     * AJAX akce pro spuštění šifrování (batch processing)
     */
    public function handleStartEncryption(): void
    {
        if (!$this->isAjax()) {
            $this->error('Tato akce je dostupná pouze přes AJAX');
        }

        if (!$this->isAdmin() && !$this->isSuperAdmin()) {
            $this->sendJson([
                'success' => false,
                'message' => 'Nemáte oprávnění pro spuštění šifrování'
            ]);
            return;
        }

        try {
            $tenantId = $this->getCurrentTenantId();
            $session = $this->getSession('migration');

            if (!isset($session->encryption_progress)) {
                $analysisResult = $this->migrationService->analyzeDataForEncryption($tenantId);

                $session->encryption_progress = [
                    'total' => $analysisResult['total_records'],
                    'processed' => 0,
                    'current_batch' => 0,
                    'errors' => 0,
                    'completed' => false,
                    'current_operation' => 'Inicializace...',
                    'clients_completed' => false,
                    'companies_completed' => false
                ];
            }

            $progress = $session->encryption_progress;
            $batchSize = 10;

            if (!$progress['clients_completed']) {
                $result = $this->migrationService->encryptClientsBatch($tenantId, $batchSize);
                $progress['processed'] += $result['processed'];
                $progress['errors'] += $result['errors'];

                if ($result['completed']) {
                    $progress['clients_completed'] = true;
                    $progress['current_operation'] = 'Klienti dokončeni, zpracovávám firemní údaje...';
                } else {
                    $progress['current_operation'] = "Šifruji klienty: batch {$progress['current_batch']}";
                }
            } elseif (!$progress['companies_completed']) {
                $result = $this->migrationService->encryptCompaniesBatch($tenantId, $batchSize);
                $progress['processed'] += $result['processed'];
                $progress['errors'] += $result['errors'];

                if ($result['completed']) {
                    $progress['companies_completed'] = true;
                    $progress['current_operation'] = 'Šifrování dokončeno';
                    $progress['completed'] = true;
                } else {
                    $progress['current_operation'] = "Šifruji firemní údaje: batch {$progress['current_batch']}";
                }
            }

            $progress['current_batch']++;
            $session->encryption_progress = $progress;

            if ($progress['completed']) {
                $this->securityLogger->logSecurityEvent(
                    'encryption_migration_completed',
                    "Šifrování starých dat dokončeno: {$progress['processed']} záznamů, {$progress['errors']} chyb",
                    [
                        'user_id' => $this->getUser()->getId(),
                        'tenant_id' => $tenantId,
                        'total_processed' => $progress['processed'],
                        'total_errors' => $progress['errors']
                    ]
                );

                unset($session->encryption_progress);
            }

            $this->sendJson([
                'success' => true,
                'message' => $progress['completed'] ? 'Šifrování dokončeno' : 'Batch zpracován',
                'data' => $progress
            ]);
        } catch (\Exception $e) {
            $this->securityLogger->logSecurityEvent(
                'encryption_migration_error',
                "Chyba při šifrování starých dat: " . $e->getMessage(),
                ['user_id' => $this->getUser()->getId()]
            );

            $this->sendJson([
                'success' => false,
                'message' => 'Chyba při šifrování: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX akce pro testovací kontrolu připojení
     */
    public function handleTestConnection(): void
    {
        if (!$this->isAjax()) {
            $this->error('Tato akce je dostupná pouze přes AJAX');
        }

        try {
            $result = $this->database->query("SELECT 1 as test")->fetch();

            $this->sendJson([
                'success' => true,
                'message' => 'Připojení k databázi je funkční',
                'data' => ['test_result' => $result->test ?? 0]
            ]);
        } catch (\Exception $e) {
            $this->sendJson([
                'success' => false,
                'message' => 'Chyba připojení k databázi: ' . $e->getMessage()
            ]);
        }
    }
}
