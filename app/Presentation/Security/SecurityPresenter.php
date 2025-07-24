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
        
        // ✅ ZMĚNA: Informace o typu uživatele pro šablonu
        $this->template->isSuperAdmin = $this->isSuperAdmin();
        $this->template->isAdmin = $this->isAdmin();
        
        // ✅ ZÁKLADNÍ: Statistiky bez tenant funkcí (dokud nepřidáme sloupce)
        $this->template->statistics = $this->getBasicRateLimitStatistics();

        // ✅ ZÁKLADNÍ: Současné IP adresy s blokováním (bez tenant informací)
        $blockedIPs = $this->getBasicBlockedIPs();
        $this->template->blockedIPs = $blockedIPs;

        // ✅ ZÁKLADNÍ: Nejčastější typy blokování (bez tenant statistik)
        $blockTypes = $this->getBasicBlockTypes();
        $this->template->blockTypes = $blockTypes;

        // ✅ PŘIPRAVENO: Prázdné pole pro tenant data (implementujeme v příštím kroku)
        $this->template->tenantStats = [];
        $this->template->topAttackingIPs = [];
    }

    /**
     * ✅ AKTUALIZACE: getBasicRateLimitStatistics() - s tenant podporou
     */
    private function getBasicRateLimitStatistics(): array
    {
        try {
            // ✅ NOVÉ: Určení tenant kontextu
            $tenantId = $this->isSuperAdmin() ? null : $this->getCurrentTenantId();
            
            // ✅ NOVÉ: Použití RateLimiter metody s tenant podporou
            return $this->getRateLimiter()->getStatistics($tenantId);

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
                ->order('blocked_until DESC')
                ->limit(50);

            // ✅ NOVÉ: Filtrování podle tenanta pro normální adminy
            if (!$this->isSuperAdmin()) {
                $tenantId = $this->getCurrentTenantId();
                if ($tenantId) {
                    $query->where('tenant_id', $tenantId);
                }
            }

            return $query->fetchAll();

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * ✅ AKTUALIZACE: getBasicBlockTypes() - s tenant filtrováním
     */
    private function getBasicBlockTypes(): array
    {
        try {
            $query = 'SELECT 
                        action as action,
                        COUNT(*) as count,
                        COUNT(DISTINCT ip_address) as unique_ips
                      FROM rate_limit_blocks 
                      WHERE created_at > ?';
            
            $params = [new \DateTime('-7 days')];

            // ✅ NOVÉ: Filtrování podle tenanta pro normální adminy
            if (!$this->isSuperAdmin()) {
                $tenantId = $this->getCurrentTenantId();
                if ($tenantId) {
                    $query .= ' AND tenant_id = ?';
                    $params[] = $tenantId;
                }
            }

            $query .= ' GROUP BY action ORDER BY count DESC';

            return $this->database->query($query, ...$params)->fetchAll();

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
        if (!$this->isAdmin() && !$this->isSuperAdmin()) {
            $this->sendJson(['success' => false, 'error' => 'Nedostatečná oprávnění']);
            return;
        }

        $ip = $this->getParameter('ip');
        
        try {
            if ($ip) {
                // ✅ NOVÉ: Určení tenant kontextu
                $tenantId = $this->isSuperAdmin() ? null : $this->getCurrentTenantId();
                
                // ✅ NOVÉ: Použití RateLimiter metody s tenant podporou
                $adminName = $this->getUser()->getIdentity()->username;
                $adminNote = "Vymazáno administrátorem {$adminName}";
                
                if ($this->getRateLimiter()->clearBlocking($ip, $adminNote, $tenantId)) {
                    $tenantInfo = $tenantId ? " (tenant: {$tenantId})" : " (všichni tenanti)";
                    $this->sendJson([
                        'success' => true,
                        'message' => "Rate limit pro IP {$ip} byl vyčištěn{$tenantInfo}"
                    ]);
                } else {
                    $this->sendJson([
                        'success' => false,
                        'error' => 'Chyba při mazání rate limitingu'
                    ]);
                }
            } else {
                // ✅ OMEZENÍ: Vyčištění všech starých záznamů pouze pro super admina
                if (!$this->isSuperAdmin()) {
                    $this->sendJson([
                        'success' => false, 
                        'error' => 'Vyčištění všech záznamů je povoleno pouze super adminovi'
                    ]);
                    return;
                }

                // ✅ NOVÉ: Vyčištění všech expirovaných záznamů
                $deletedBlocks = $this->database->table('rate_limit_blocks')
                    ->where('blocked_until < ?', new \DateTime())
                    ->delete();

                $deletedAttempts = $this->database->table('rate_limits')
                    ->where('created_at < ?', new \DateTime('-24 hours'))
                    ->delete();
                    
                $this->securityLogger->logSecurityEvent(
                    'rate_limit_cleanup',
                    "Vyčištěny staré rate limit záznamy (super adminem)",
                    [
                        'deleted_blocks' => $deletedBlocks,
                        'deleted_attempts' => $deletedAttempts,
                        'admin_user_id' => $this->getUser()->getId()
                    ]
                );
                
                $this->sendJson([
                    'success' => true,
                    'message' => "Vyčištěno {$deletedBlocks} bloků a {$deletedAttempts} pokusů"
                ]);
            }

        } catch (\Exception $e) {
            $this->sendJson([
                'success' => false,
                'error' => 'Chyba při čištění: ' . $e->getMessage()
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

    public function handleSimpleTest(): void
{
    $this->flashMessage('AJAX test funguje!', 'success');
    $this->redirect('this');
}

}