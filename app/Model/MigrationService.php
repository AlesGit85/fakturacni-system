<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use App\Security\EncryptionService;
use App\Security\SecurityLogger;

/**
 * Služba pro migraci a šifrování starých dat
 * Zajišťuje bezpečné batch processing s auditováním
 */
class MigrationService
{
    use Nette\SmartObject;

    /** @var Nette\Database\Explorer */
    private $database;

    /** @var EncryptionService */
    private $encryptionService;

    /** @var SecurityLogger */
    private $securityLogger;

    /** @var ClientsManager */
    private $clientsManager;

    /** @var CompanyManager */
    private $companyManager;

    /** @var array Konfigurace batch processing */
    private const BATCH_CONFIG = [
        'default_batch_size' => 10,
        'max_batch_size' => 50,
        'transaction_timeout' => 30 // sekundy
    ];

    public function __construct(
        Nette\Database\Explorer $database,
        EncryptionService $encryptionService,
        SecurityLogger $securityLogger,
        ClientsManager $clientsManager,
        CompanyManager $companyManager
    ) {
        $this->database = $database;
        $this->encryptionService = $encryptionService;
        $this->securityLogger = $securityLogger;
        $this->clientsManager = $clientsManager;
        $this->companyManager = $companyManager;
    }

    // =====================================================
    // ANALÝZA DAT PRO ŠIFROVÁNÍ
    // =====================================================

    /**
     * Provede kompletní analýzu dat vyžadujících šifrování
     */
    public function analyzeDataForEncryption(?int $tenantId = null): array
    {
        try {
            $clientsCount = $this->analyzeClientsForEncryption($tenantId);
            $companiesCount = $this->analyzeCompaniesForEncryption($tenantId);

            $result = [
                'clients_to_encrypt' => $clientsCount,
                'companies_to_encrypt' => $companiesCount,
                'total_records' => $clientsCount + $companiesCount,
                'encryption_enabled' => $this->encryptionService->isEncryptionEnabled(),
                'analysis_timestamp' => time()
            ];

            $this->securityLogger->logSecurityEvent(
                'migration_analysis_completed',
                "Analýza dat pro šifrování: {$clientsCount} klientů, {$companiesCount} firemních údajů",
                array_merge($result, ['tenant_id' => $tenantId])
            );

            return $result;
        } catch (\Exception $e) {
            $this->securityLogger->logSecurityEvent(
                'migration_analysis_error',
                "Chyba při analýze dat pro šifrování: " . $e->getMessage(),
                ['tenant_id' => $tenantId, 'error' => $e->getMessage()]
            );

            throw $e;
        }
    }

    /**
     * Analyzuje klienty vyžadující šifrování
     */
    public function analyzeClientsForEncryption(?int $tenantId = null): int
    {
        $query = $this->database->table('clients');

        // Aplikujeme tenant filtr
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        // Hledáme pravděpodobně nezašifrovaná data
        // Šifrovaná data jsou base64 encoded, takže mají specifické charakteristiky
        $query->where('(
            (email IS NOT NULL AND email != "" AND email NOT LIKE "%==" AND email NOT LIKE "Ly/%" AND email LIKE "%@%") OR
            (phone IS NOT NULL AND phone != "" AND phone NOT LIKE "%==" AND phone NOT LIKE "Ly/%" AND (phone LIKE "+%" OR phone LIKE "0%")) OR
            (ic IS NOT NULL AND ic != "" AND ic NOT LIKE "%==" AND ic NOT LIKE "Ly/%" AND ic REGEXP "^[0-9]+$")
        )');

        return $query->count();
    }

    /**
     * Analyzuje firemní údaje vyžadující šifrování
     */
    public function analyzeCompaniesForEncryption(?int $tenantId = null): int
    {
        $query = $this->database->table('company_info');

        // Aplikujeme tenant filtr
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        // Podobná logika jako u klientů
        $query->where('(
            (email IS NOT NULL AND email != "" AND email NOT LIKE "%==" AND email NOT LIKE "Ly/%" AND email LIKE "%@%") OR
            (phone IS NOT NULL AND phone != "" AND phone NOT LIKE "%==" AND phone NOT LIKE "Ly/%" AND (phone LIKE "+%" OR phone LIKE "0%")) OR
            (ic IS NOT NULL AND ic != "" AND ic NOT LIKE "%==" AND ic NOT LIKE "Ly/%" AND ic REGEXP "^[0-9]+$")
        )');

        return $query->count();
    }

    // =====================================================
    // BATCH ŠIFROVÁNÍ DAT
    // =====================================================

    /**
     * Provede batch šifrování klientů - OPRAVENÁ VERZE
     */
    public function encryptClientsBatch(?int $tenantId = null, int $batchSize = null): array
    {
        $batchSize = $batchSize ?? self::BATCH_CONFIG['default_batch_size'];
        $batchSize = min($batchSize, self::BATCH_CONFIG['max_batch_size']);

        $processed = 0;
        $errors = 0;
        $errorDetails = [];

        try {
            // Najdeme batch nezašifrovaných klientů
            $query = $this->database->table('clients');

            if ($tenantId !== null) {
                $query->where('tenant_id', $tenantId);
            }

            $query->where('(
            (email IS NOT NULL AND email != "" AND email NOT LIKE "%==" AND email NOT LIKE "Ly/%" AND email LIKE "%@%") OR
            (phone IS NOT NULL AND phone != "" AND phone NOT LIKE "%==" AND phone NOT LIKE "Ly/%" AND (phone LIKE "+%" OR phone LIKE "0%")) OR
            (ic IS NOT NULL AND ic != "" AND ic NOT LIKE "%==" AND ic NOT LIKE "Ly/%" AND ic REGEXP "^[0-9]+$")
        )')
                ->limit($batchSize);

            $clients = $query->fetchAll();

            // Nastavíme tenant kontext pro ClientsManager
            $this->clientsManager->setTenantContext($tenantId, true); // super admin mode pro migraci

            foreach ($clients as $client) {
                try {
                    // ✅ OPRAVA: Použijeme ClientsManager->save() který automaticky šifruje!
                    $clientData = $client->toArray();
                    unset($clientData['id']); // Odstranit ID pro update
                    unset($clientData['tenant_id']); // Tenant se nesmí měnit

                    // Toto volání automaticky zašifruje data pomocí EncryptionService
                    $this->clientsManager->save($clientData, $client->id);

                    $processed++;

                    // Logování jednotlivých úspěšných šifrování
                    $this->securityLogger->logSecurityEvent(
                        'client_encrypted',
                        "Klient ID {$client->id} byl úspěšně zašifrován",
                        ['client_id' => $client->id, 'tenant_id' => $tenantId]
                    );
                } catch (\Exception $e) {
                    $errors++;
                    $errorDetails[] = [
                        'client_id' => $client->id,
                        'error' => $e->getMessage()
                    ];

                    $this->securityLogger->logSecurityEvent(
                        'client_encryption_error',
                        "Chyba při šifrování klienta ID {$client->id}: " . $e->getMessage(),
                        ['client_id' => $client->id, 'error' => $e->getMessage(), 'tenant_id' => $tenantId]
                    );
                }
            }

            $result = [
                'processed' => $processed,
                'errors' => $errors,
                'error_details' => $errorDetails,
                'completed' => count($clients) < $batchSize,
                'batch_size' => $batchSize,
                'remaining_estimated' => max(0, $this->analyzeClientsForEncryption($tenantId) - $processed)
            ];

            // Logování batch rezultátů
            $this->securityLogger->logSecurityEvent(
                'clients_batch_encrypted',
                "Batch šifrování klientů: {$processed} úspěšných, {$errors} chyb",
                array_merge($result, ['tenant_id' => $tenantId])
            );

            return $result;
        } catch (\Exception $e) {
            $this->securityLogger->logSecurityEvent(
                'clients_batch_error',
                "Kritická chyba při batch šifrování klientů: " . $e->getMessage(),
                ['tenant_id' => $tenantId, 'error' => $e->getMessage()]
            );

            throw $e;
        }
    }

    /**
     * Provede batch šifrování firemních údajů
     */
    public function encryptCompaniesBatch(?int $tenantId = null, int $batchSize = null): array
    {
        $batchSize = $batchSize ?? self::BATCH_CONFIG['default_batch_size'];
        $batchSize = min($batchSize, self::BATCH_CONFIG['max_batch_size']);

        $processed = 0;
        $errors = 0;
        $errorDetails = [];

        try {
            // Najdeme batch nezašifrovaných firemních údajů
            $query = $this->database->table('company_info');

            if ($tenantId !== null) {
                $query->where('tenant_id', $tenantId);
            }

            $query->where('(
            (email IS NOT NULL AND email != "" AND email NOT LIKE "%==" AND email NOT LIKE "Ly/%" AND email LIKE "%@%") OR
            (phone IS NOT NULL AND phone != "" AND phone NOT LIKE "%==" AND phone NOT LIKE "Ly/%" AND (phone LIKE "+%" OR phone LIKE "0%")) OR
            (ic IS NOT NULL AND ic != "" AND ic NOT LIKE "%==" AND ic NOT LIKE "Ly/%" AND ic REGEXP "^[0-9]+$")
        )')
                ->limit($batchSize);

            $companies = $query->fetchAll();

            foreach ($companies as $company) {
                try {
                    $this->database->beginTransaction();

                    // Připravíme data pro šifrování
                    $companyData = $company->toArray();
                    unset($companyData['id']); // Odstranit ID pro update
                    unset($companyData['tenant_id']); // Tenant se nesmí měnit

                    // Ručně zašifrujeme citlivá pole pomocí EncryptionService
                    $encryptedData = $this->encryptionService->encryptFields($companyData, ['ic', 'dic', 'email', 'phone']);

                    // Přímý update v databázi
                    $this->database->table('company_info')
                        ->where('id', $company->id)
                        ->update($encryptedData);

                    $this->database->commit();
                    $processed++;

                    // Logování jednotlivých úspěšných šifrování
                    $this->securityLogger->logSecurityEvent(
                        'company_encrypted',
                        "Firemní údaje ID {$company->id} byly úspěšně zašifrovány",
                        ['company_id' => $company->id, 'tenant_id' => $tenantId]
                    );
                } catch (\Exception $e) {
                    $this->database->rollback();
                    $errors++;
                    $errorDetails[] = [
                        'company_id' => $company->id,
                        'error' => $e->getMessage()
                    ];

                    $this->securityLogger->logSecurityEvent(
                        'company_encryption_error',
                        "Chyba při šifrování firemních údajů ID {$company->id}: " . $e->getMessage(),
                        ['company_id' => $company->id, 'error' => $e->getMessage(), 'tenant_id' => $tenantId]
                    );
                }
            }

            $result = [
                'processed' => $processed,
                'errors' => $errors,
                'error_details' => $errorDetails,
                'completed' => count($companies) < $batchSize,
                'batch_size' => $batchSize,
                'remaining_estimated' => max(0, $this->analyzeCompaniesForEncryption($tenantId) - $processed)
            ];

            // Logování batch rezultátů
            $this->securityLogger->logSecurityEvent(
                'companies_batch_encrypted',
                "Batch šifrování firemních údajů: {$processed} úspěšných, {$errors} chyb",
                array_merge($result, ['tenant_id' => $tenantId])
            );

            return $result;
        } catch (\Exception $e) {
            $this->securityLogger->logSecurityEvent(
                'companies_batch_error',
                "Kritická chyba při batch šifrování firemních údajů: " . $e->getMessage(),
                ['tenant_id' => $tenantId, 'error' => $e->getMessage()]
            );

            throw $e;
        }
    }

    // =====================================================
    // UTILITY METODY
    // =====================================================

    /**
     * Kontrola stavu šifrovací služby
     */
    public function getEncryptionServiceStatus(): array
    {
        return [
            'encryption_enabled' => $this->encryptionService->isEncryptionEnabled(),
            'encryption_service_available' => true,
            'database_connection' => $this->testDatabaseConnection(),
            'managers_available' => [
                'clients_manager' => $this->clientsManager !== null,
                'company_manager' => $this->companyManager !== null
            ]
        ];
    }

    /**
     * Test databázového připojení
     */
    public function testDatabaseConnection(): bool
    {
        try {
            $result = $this->database->query("SELECT 1 as test")->fetch();
            return $result && $result->test === 1;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Odhad doby trvání migrace
     */
    public function estimateMigrationDuration(int $totalRecords, int $batchSize = null): array
    {
        $batchSize = $batchSize ?? self::BATCH_CONFIG['default_batch_size'];

        // Odhad na základě průměrné doby zpracování jednoho záznamu (0.1s)
        $avgTimePerRecord = 0.1;
        $totalBatches = ceil($totalRecords / $batchSize);
        $estimatedSeconds = $totalRecords * $avgTimePerRecord;

        return [
            'total_records' => $totalRecords,
            'total_batches' => $totalBatches,
            'batch_size' => $batchSize,
            'estimated_duration_seconds' => $estimatedSeconds,
            'estimated_duration_formatted' => $this->formatDuration($estimatedSeconds)
        ];
    }

    /**
     * Formátování doby trvání
     */
    private function formatDuration(float $seconds): string
    {
        if ($seconds < 60) {
            return round($seconds) . 's';
        } elseif ($seconds < 3600) {
            return round($seconds / 60) . 'min';
        } else {
            return round($seconds / 3600, 1) . 'h';
        }
    }
}
