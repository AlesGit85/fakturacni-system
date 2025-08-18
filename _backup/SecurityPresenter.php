<?php

declare(strict_types=1);

namespace App\Presentation\Security;

use App\Presentation\BasePresenter;
use App\Security\SQLSecurityAudit;

/**
 * Security Presenter pro bezpečnostní nástroje
 * Obsahuje SQL Audit Tool a další bezpečnostní nástroje
 * 
 * Barvy projektu: primární #B1D235, sekundární #95B11F, šedá #6c757d, černá #212529
 */

class SecurityPresenter extends BasePresenter
{
    private SQLSecurityAudit $sqlAudit;

    /** @var array ✅ OPRAVENO: Povolené role pro přístup k bezpečnostním nástrojům */
    protected array $requiredRoles = ['admin'];

    public function injectSqlAudit(SQLSecurityAudit $sqlAudit): void
    {
        $this->sqlAudit = $sqlAudit;
    }

    public function actionTest(): void
    {
        // Test action
    }

    /**
     * Hlavní stránka bezpečnostních nástrojů
     */
    public function actionDefault(): void
    {
        // Kontrola oprávnění
        if (!$this->isAdmin() && !$this->isSuperAdmin()) {
            $this->error('Nemáte oprávnění pro přístup k bezpečnostním nástrojům', 403);
        }

        // Logování přístupu k bezpečnostním nástrojům
        $this->securityLogger->logSecurityEvent(
            'security_tools_access',
            "Uživatel {$this->getUser()->getIdentity()->username} přistoupil k bezpečnostním nástrojům",
            ['user_id' => $this->getUser()->getId()]
        );
    }

    public function renderDefault(): void
    {
        // Žádná speciální logika pro default view
    }

    /**
     * SQL Security Audit stránka
     */
    public function actionSqlAudit(): void
    {
        // Kontrola oprávnění
        if (!$this->isAdmin() && !$this->isSuperAdmin()) {
            $this->error('Nemáte oprávnění pro přístup k SQL auditu', 403);
        }

        // Logování spuštění SQL auditu
        $this->securityLogger->logSecurityEvent(
            'sql_audit_started',
            "Uživatel {$this->getUser()->getIdentity()->username} spustil SQL audit",
            ['user_id' => $this->getUser()->getId()]
        );
    }

    public function renderSqlAudit(): void
    {
        $this->template->pageTitle = 'SQL Security Audit';
    }

    /**
     * ✅ UPRAVENO: Rate limiting statistiky - nyní přístupné i pro normální admina
     */
    public function actionRateLimitStats(): void
    {
        // ✅ ZMĚNA: Povoleno i pro normální admina
        if (!$this->isAdmin() && !$this->isSuperAdmin()) {
            $this->error('Nemáte oprávnění pro přístup k rate limiting statistikám', 403);
        }
    }

    public function renderRateLimitStats(): void
    {
        $this->template->pageTitle = 'Rate Limiting Statistiky';

        // ✅ Informace o typu uživatele pro šablonu
        $this->template->isSuperAdmin = $this->isSuperAdmin();
        $this->template->isAdmin = $this->isAdmin();

        // ✅ Základní statistiky
        $this->template->statistics = $this->getBasicRateLimitStatistics();

        // ✅ Současné IP adresy s blokováním
        $blockedIPs = $this->getBasicBlockedIPs();
        $this->template->blockedIPs = $blockedIPs;

        // ✅ Nejčastější typy blokování
        $blockTypes = $this->getBasicBlockTypes();
        $this->template->blockTypes = $blockTypes;

        // ✅ Aktuální IP uživatele
        $this->template->currentIP = $this->getRateLimiter()->getClientIP();
    }

    /**
     * ✅ AKTUALIZACE: getBasicRateLimitStatistics() - s tenant podporou
     */
    private function getBasicRateLimitStatistics(): array
    {
        try {
            if ($this->isSuperAdmin()) {
                // Super admin vidí všechno
                return $this->getRateLimiter()->getStatistics(null, false);
            } else {
                // Normální admin vidí své + obecné záznamy
                $tenantId = $this->getCurrentTenantId();
                return $this->getRateLimiter()->getStatistics($tenantId, true);
            }
        } catch (\Exception $e) {
            // Pokud tabulky neexistují, vrátíme výchozí hodnoty
            return [
                'currently_blocked_ips' => 0,
                'attempts_last_24h' => 0,
                'failed_attempts_last_24h' => 0,
                'success_rate' => 100,
                'top_attacking_ips' => []
            ];
        }
    }

    /**
     * ✅ AKTUALIZACE: getBasicBlockedIPs() - s tenant filtrováním
     */
    private function getBasicBlockedIPs(): array
    {
        try {
            $query = $this->database->table('rate_limit_blocks')
                ->where('blocked_until > ?', new \DateTime())
                ->order('blocked_until DESC');

            // ✅ OPRAVENO: Super admin vidí všechno, normální admin vidí svoje + obecné (NULL)
            if (!$this->isSuperAdmin()) {
                $tenantId = $this->getCurrentTenantId();
                if ($tenantId) {
                    // Normální admin vidí záznamy svého tenanta NEBO obecné záznamy (tenant_id IS NULL)
                    $query->where('tenant_id = ? OR tenant_id IS NULL', $tenantId);
                } else {
                    // Pokud admin nemá tenant_id, vidí jen obecné záznamy
                    $query->where('tenant_id IS NULL');
                }
            }
            // Super admin bez dodatečného filtrování - vidí všechno

            return iterator_to_array($query);
        } catch (\Exception $e) {
            // Pokud tabulka neexistuje, vrátíme prázdné pole
            return [];
        }
    }

    /**
     * ✅ AKTUALIZACE: getBasicBlockTypes() - s tenant filtrováním
     */
        private function getBasicBlockTypes(): array
    {
        try {
            $query = "SELECT action, COUNT(*) as total_blocks 
                 FROM rate_limit_blocks 
                 WHERE created_at > ? AND blocked_until > ?";
            $params = [new \DateTime('-24 hours'), new \DateTime()];

            // ✅ OPRAVENO: Správné filtrování podle typu uživatele
            if (!$this->isSuperAdmin()) {
                $tenantId = $this->getCurrentTenantId();
                if ($tenantId) {
                    // Normální admin vidí své + obecné záznamy
                    $query .= " AND (tenant_id = ? OR tenant_id IS NULL)";
                    $params[] = $tenantId;
                } else {
                    // Admin bez tenant_id vidí jen obecné
                    $query .= " AND tenant_id IS NULL";
                }
            }
            // Super admin bez dodatečného filtrování

            $query .= " GROUP BY action ORDER BY total_blocks DESC LIMIT 10";

            $result = $this->database->query($query, ...$params)->fetchAll();
            return iterator_to_array($result);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Security Dashboard s přehledem
     */
    public function actionDashboard(): void
    {
        // Kontrola oprávnění
        if (!$this->isAdmin() && !$this->isSuperAdmin()) {
            $this->error('Nemáte oprávnění pro přístup k security dashboard', 403);
        }
    }

    public function renderDashboard(): void
    {
        // Zkontrolujeme, zda tabulka security_logs existuje
        $securityStats = $this->getSafeSecurityStats();
        $this->template->securityStats = $securityStats;

        // Poslední bezpečnostní události (pouze pokud tabulka existuje)
        $recentEvents = $this->getSafeRecentEvents();
        $this->template->recentEvents = $recentEvents;
    }

    /**
     * Bezpečné načtení statistik (funguje i bez security_logs tabulky)
     */
    private function getSafeSecurityStats(): array
    {
        try {
            // Zkusíme načíst z security_logs tabulky
            return [
                'login_attempts_today' => $this->database->table('security_logs')
                    ->where('event_type', 'login_attempt')
                    ->where('created_at > ?', new \DateTime('today'))
                    ->count(),

                'failed_logins_today' => $this->database->table('security_logs')
                    ->where('event_type', 'login_failure')
                    ->where('created_at > ?', new \DateTime('today'))
                    ->count(),

                'xss_attempts_today' => $this->database->table('security_logs')
                    ->where('event_type', 'xss_attempt')
                    ->where('created_at > ?', new \DateTime('today'))
                    ->count(),

                'rate_limit_blocks_today' => $this->getSafeRateLimitCount(),
            ];
        } catch (\Exception $e) {
            // Pokud tabulka neexistuje, vrátíme nulové hodnoty
            return [
                'login_attempts_today' => 0,
                'failed_logins_today' => 0,
                'xss_attempts_today' => 0,
                'rate_limit_blocks_today' => 0,
            ];
        }
    }

    /**
     * Bezpečné načtení posledních událostí
     */
    private function getSafeRecentEvents(): array
    {
        try {
            $events = $this->database->table('security_logs')
                ->order('created_at DESC')
                ->limit(10)
                ->fetchAll();
            return iterator_to_array($events);
        } catch (\Exception $e) {
            // Pokud tabulka neexistuje, vrátíme prázdné pole
            return [];
        }
    }

    /**
     * Bezpečné načtení rate limit statistik
     */
    private function getSafeRateLimitCount(): int
    {
        try {
            // Zkusíme načíst z rate_limit_blocks tabulky
            return $this->database->table('rate_limit_blocks')
                ->where('created_at > ?', new \DateTime('today'))
                ->count();
        } catch (\Exception $e) {
            // Pokud tabulka neexistuje, vrátíme 0
            return 0;
        }
    }

    /**
     * ✅ AKTUALIZACE: handleClearRateLimit() - s tenant podporou
     */
    public function handleClearRateLimit(): void
    {
        // Kontrola oprávnění
        if (!$this->isAdmin() && !$this->isSuperAdmin()) {
            if ($this->isAjax()) {
                $this->sendError('Nemáte oprávnění k této akci.');
            }
            $this->flashMessage('Nemáte oprávnění k této akci.', 'danger');
            $this->redirect('this');
            return;
        }

        try {
            $ip = $this->getParameter('ip');
            $adminName = $this->getUser()->getIdentity()->username;
            $tenantId = $this->isSuperAdmin() ? null : $this->getCurrentTenantId();

            if ($ip) {
                $adminNote = "Vymazáno administrátorem {$adminName}";

                if ($this->getRateLimiter()->clearBlocking($ip, $adminNote, $tenantId)) {
                    $tenantInfo = $tenantId ? " (tenant {$tenantId})" : " (všechny tenants)";

                    $this->securityLogger->logSecurityEvent(
                        'rate_limit_ip_cleared',
                        "IP {$ip} odblokována administrátorem {$adminName}{$tenantInfo}"
                    );

                    $message = "Rate limiting úspěšně vymazán pro IP: {$ip}";

                    if ($this->isAjax()) {
                        $this->sendSuccess($message);
                    }

                    $this->flashMessage($message, 'success');
                    $this->redirect('this');
                    return;
                } else {
                    $errorMessage = "Rate limiting se nepodařilo vymazat pro IP: {$ip}";

                    if ($this->isAjax()) {
                        $this->sendError($errorMessage);
                    }

                    $this->flashMessage($errorMessage, 'warning');
                    $this->redirect('this');
                    return;
                }
            }
        } catch (\Exception $e) {
            $errorMessage = 'Chyba při mazání rate limitingu: ' . $e->getMessage();

            if ($this->isAjax()) {
                $this->sendError($errorMessage);
            }

            $this->flashMessage($errorMessage, 'danger');
            $this->redirect('this');
            return;
        }
    }

    /**
     * ✅ NOVÁ: handleClearAllRateLimits() - kompletně vymaže všechny rate limity
     */
    public function handleClearAllRateLimits(): void
    {
        // Kontrola oprávnění - pouze super admin
        if (!$this->isSuperAdmin()) {
            if ($this->isAjax()) {
                $this->sendJson([
                    'success' => false,
                    'error' => 'Nemáte oprávnění k této akci. Vyžaduje se Super Admin.'
                ]);
                return;
            }

            $this->flashMessage('Nemáte oprávnění k této akci. Vyžaduje se Super Admin.', 'danger');
            $this->redirect('this');
        }

        try {
            // Vymazání všech rate limit záznamů
            $deletedBlocks = $this->database->table('rate_limit_blocks')->delete();
            $deletedAttempts = $this->database->table('rate_limits')->delete();

            // Vymazání přihlašovacích pokusů pro důkladné vyčištění
            $deletedLoginAttempts = $this->database->table('login_attempts')->delete();

            $adminName = $this->getUser()->getIdentity()->username;
            $this->securityLogger->logSecurityEvent(
                'all_rate_limits_cleared',
                "Všechny rate limity vymazány super adminem {$adminName}. Bloky: {$deletedBlocks}, Pokusy: {$deletedAttempts}, Login pokusy: {$deletedLoginAttempts}"
            );

            $message = "⚠️ Všechny rate limity vymazány! Bloky: {$deletedBlocks}, Pokusy: {$deletedAttempts}, Login pokusy: {$deletedLoginAttempts}";

            if ($this->isAjax()) {
                $this->sendJson([
                    'success' => true,
                    'message' => $message
                ]);
                return;
            }

            $this->flashMessage($message, 'warning');
        } catch (\Exception $e) {
            $errorMessage = 'Chyba při mazání všech rate limitů: ' . $e->getMessage();

            // Logování chyby
            $this->securityLogger->logSecurityEvent(
                'all_rate_limits_clear_error',
                "Chyba při mazání všech rate limitů: " . $e->getMessage(),
                [
                    'admin_id' => $this->getUser()->getId(),
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            );

            if ($this->isAjax()) {
                $this->sendJson([
                    'success' => false,
                    'error' => $errorMessage
                ]);
                return;
            }

            $this->flashMessage($errorMessage, 'danger');
        }

        // Pokud není AJAX, přesměruj
        if (!$this->isAjax()) {
            $this->redirect('this');
        }
    }

    /**
     * ✅ OPRAVENO: Kompletní AJAX handler pro spuštění SQL auditu
     */
    public function handleRunSqlAudit(): void
    {
        // Kontrola oprávnění
        if (!$this->isAdmin() && !$this->isSuperAdmin()) {
            $this->sendJson([
                'success' => false,
                'error' => 'Nemáte oprávnění k této akci.'
            ]);
            return;
        }

        try {
            // Logování spuštění auditu
            $this->securityLogger->logSecurityEvent(
                'sql_audit_run',
                "Uživatel {$this->getUser()->getIdentity()->username} spustil SQL audit",
                ['user_id' => $this->getUser()->getId()]
            );

            // Spuštění SQL auditu
            $auditResults = $this->sqlAudit->runFullAudit();
            $processedResults = $this->processAuditResults($auditResults);

            $this->sendJson([
                'success' => true,
                'results' => $processedResults
            ]);
        } catch (\Nette\Application\AbortException $e) {
            // ✅ AbortException je NORMÁLNÍ - jen ji přehodíme
            throw $e;
        } catch (\Exception $e) {
            // ✅ Zachycujeme jen skutečné chyby
            $this->securityLogger->logSecurityEvent(
                'sql_audit_error',
                "Chyba při SQL auditu: " . $e->getMessage(),
                [
                    'user_id' => $this->getUser()->getId(),
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            );

            $this->sendJson([
                'success' => false,
                'error' => 'Chyba auditu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ NOVÉ: Zpracuje výsledky auditu pro frontend
     */
    private function processAuditResults(array $results): array
    {
        $totalQueries = count($results['safe_queries']) + count($results['potential_issues']);
        $safeCount = count($results['safe_queries']);
        $issueCount = count($results['potential_issues']);

        $safetyPercentage = $totalQueries > 0 ? round(($safeCount / $totalQueries) * 100, 1) : 100;

        // Určení celkového statusu
        $overallStatus = 'EXCELLENT';
        if ($safetyPercentage < 100) {
            $overallStatus = 'GOOD';
        }
        if ($safetyPercentage < 90) {
            $overallStatus = 'NEEDS_ATTENTION';
        }
        if ($safetyPercentage < 70) {
            $overallStatus = 'CRITICAL';
        }

        // Generování doporučení
        $recommendations = $this->generateRecommendations($results);

        return [
            'timestamp' => $results['timestamp']->format('Y-m-d H:i:s'),
            'summary' => [
                'files_scanned' => $results['files_scanned'],
                'total_queries' => $totalQueries,
                'safe_queries' => $safeCount,
                'potential_issues' => $issueCount,
                'safety_percentage' => $safetyPercentage,
                'overall_status' => $overallStatus,
                'priority_issues' => isset($results['summary']['priority_issues']) ? $results['summary']['priority_issues'] : [],
                'recommendations' => isset($results['summary']['recommendations']) ? $results['summary']['recommendations'] : []
            ],
            'potential_issues' => array_slice($results['potential_issues'], 0, 20), // Limit pro UI
            'safe_queries' => array_slice($results['safe_queries'], 0, 10), // Limit pro UI
            'recommendations' => $recommendations
        ];
    }

    /**
     * ✅ NOVÉ: Generuje doporučení na základě výsledků auditu
     */
    private function generateRecommendations(array $results): array
    {
        $recommendations = [];

        $issueCount = count($results['potential_issues']);
        $totalQueries = count($results['safe_queries']) + $issueCount;

        if ($issueCount === 0) {
            $recommendations[] = 'Výborná bezpečnost! Nebyly nalezeny žádné potenciální bezpečnostní problémy v SQL dotazech.';
        } else {
            $recommendations[] = "Nalezeno {$issueCount} potenciálních problémů. Doporučujeme prozkoumat a opravit označené SQL dotazy.";
        }

        if ($totalQueries > 0) {
            $rawQueryCount = 0;
            foreach (array_merge($results['safe_queries'], $results['potential_issues']) as $query) {
                // ✅ OPRAVENO: Zpracování obou formátů dat
                $queryText = $query['query'] ?? $query['matched_text'] ?? '';
                if (strpos($queryText, '->query(') !== false) {
                    $rawQueryCount++;
                }
            }

            if ($rawQueryCount > 0) {
                $recommendations[] = "Nalezeno {$rawQueryCount} raw SQL dotazů. Zvažte migraci na Nette Database Selection API pro lepší bezpečnost.";
            }
        }

        $recommendations[] = 'Doporučujeme spouštět SQL audit alespoň jednou měsíčně.';

        return $recommendations;
    }

    /**
     * ✅ TESTOVACÍ: Simple AJAX test pro ověření funkcionality
     */
    public function handleSimpleTest(): void
    {
        $this->flashMessage('AJAX test funguje!', 'success');
        $this->redirect('this');
    }
}
