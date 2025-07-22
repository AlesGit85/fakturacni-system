<?php

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
    /** @var SQLSecurityAudit */
    private $sqlAudit;

    /** @var array Povolené role pro přístup k bezpečnostním nástrojům */
    protected array $requiredRoles = ['admin', 'super_admin'];

    public function __construct(SQLSecurityAudit $sqlAudit)
    {
        parent::__construct();
        $this->sqlAudit = $sqlAudit;
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
     * AJAX: Spuštění SQL auditu
     */
    public function handleRunSqlAudit(): void
    {
        // Kontrola oprávnění
        if (!$this->isAdmin() && !$this->isSuperAdmin()) {
            $this->sendJson(['success' => false, 'error' => 'Nedostatečná oprávnění']);
            return;
        }

        try {
            // Spuštění auditu
            $results = $this->sqlAudit->runFullAudit();

            // Logování dokončení auditu
            $this->securityLogger->logSecurityEvent(
                'sql_audit_completed',
                "SQL audit dokončen - nalezeno {$results['summary']['potential_issues']} problémů",
                [
                    'user_id' => $this->getUser()->getId(),
                    'files_scanned' => $results['summary']['files_scanned'],
                    'queries_found' => $results['summary']['total_queries'],
                    'issues_found' => $results['summary']['potential_issues'],
                    'overall_status' => $results['summary']['overall_status']
                ]
            );

            // Odeslání výsledků
            $this->sendJson([
                'success' => true,
                'results' => $results,
                'message' => 'SQL audit byl úspěšně dokončen'
            ]);

        } catch (\Exception $e) {
            // Logování chyby
            $this->securityLogger->logSecurityEvent(
                'sql_audit_error',
                "Chyba při spuštění SQL auditu: " . $e->getMessage(),
                ['user_id' => $this->getUser()->getId()]
            );

            $this->sendJson([
                'success' => false,
                'error' => 'Chyba při spuštění auditu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX: Získání detailů problému
     */
    public function handleGetIssueDetails(): void
    {
        $issueIndex = (int)$this->getParameter('index');
        
        // Kontrola oprávnění
        if (!$this->isAdmin() && !$this->isSuperAdmin()) {
            $this->sendJson(['success' => false, 'error' => 'Nedostatečná oprávnění']);
            return;
        }

        try {
            // Získání aktuálních výsledků auditu
            $results = $this->sqlAudit->runFullAudit();
            
            if (isset($results['potential_issues'][$issueIndex])) {
                $issue = $results['potential_issues'][$issueIndex];
                
                $this->sendJson([
                    'success' => true,
                    'issue' => $issue
                ]);
            } else {
                $this->sendJson([
                    'success' => false,
                    'error' => 'Problém nebyl nalezen'
                ]);
            }

        } catch (\Exception $e) {
            $this->sendJson([
                'success' => false,
                'error' => 'Chyba při získávání detailů: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Rate limiting statistiky pro tenant-specific monitoring
     * POUZE pro běžné adminy - vidí jen data svého tenantu
     */
    public function actionRateLimitStats(): void
    {
        // Kontrola oprávnění - pro všechny adminy
        if (!$this->isAdmin() && !$this->isSuperAdmin()) {
            $this->error('Nemáte oprávnění pro přístup k rate limiting statistikám', 403);
        }
    }

    public function renderRateLimitStats(): void
    {
        $this->template->pageTitle = 'Rate Limiting Monitoring';
        
        // TENANT-SPECIFIC: Získání statistik pro aktuální tenant
        $currentTenantId = $this->getCurrentTenantId();
        $this->template->statistics = $this->getTenantRateLimitStatistics($currentTenantId);

        // TENANT-SPECIFIC: Blokované IP pro aktuální tenant
        $blockedIPs = $this->getTenantBlockedIPs($currentTenantId);
        $this->template->blockedIPs = $blockedIPs;

        // TENANT-SPECIFIC: Typy blokování pro tenant
        $blockTypes = $this->getTenantBlockTypes($currentTenantId);
        $this->template->blockTypes = $blockTypes;

        // Informace o tenantu
        $this->template->currentTenant = $this->getCurrentTenant();
        $this->template->isTenantSpecific = !$this->isSuperAdmin();
    }

    /**
     * NOVÉ: Tenant-specific rate limit statistiky
     */
    private function getTenantRateLimitStatistics(int $tenantId): array
    {
        try {
            // Získáme uživatele z aktuálního tenantu
            $tenantUserIds = $this->database->table('users')
                ->where('tenant_id', $tenantId)
                ->fetchPairs('id', 'id');

            if (empty($tenantUserIds)) {
                return [
                    'currently_blocked_ips' => 0,
                    'attempts_last_24h' => 0,
                    'failed_attempts_last_24h' => 0,
                    'success_rate' => 100,
                    'top_attacking_ips' => []
                ];
            }

            $last24h = new \DateTime('-24 hours');

            // Aktuálně blokované IP adresy pro tento tenant
            $currentlyBlocked = $this->database->table('rate_limit_blocks')
                ->where('blocked_until > ?', new \DateTime())
                ->count();

            // Pokusy za posledních 24 hodin pro uživatele z tohoto tenantu
            // Poznámka: rate_limits neobsahuje tenant_id, takže nemůžeme přímo filtrovat
            // Použijeme IP adresy z security_logs pro tento tenant
            $tenantIPs = $this->getTenantIPAddresses($tenantId);
            
            $attemptsLast24h = 0;
            $failedAttemptsLast24h = 0;
            
            if (!empty($tenantIPs)) {
                $attemptsLast24h = $this->database->table('rate_limits')
                    ->where('created_at > ?', $last24h)
                    ->where('ip_address', $tenantIPs)
                    ->count();

                $failedAttemptsLast24h = $this->database->table('rate_limits')
                    ->where('created_at > ?', $last24h)
                    ->where('ip_address', $tenantIPs)
                    ->where('successful', false)
                    ->count();
            }

            // TOP IP adresy pro tento tenant
            $topIPs = [];
            if (!empty($tenantIPs)) {
                $topIPs = $this->database->query('
                    SELECT ip_address, COUNT(*) as attempt_count 
                    FROM rate_limits 
                    WHERE created_at > ? AND successful = 0 AND ip_address IN (?)
                    GROUP BY ip_address 
                    ORDER BY attempt_count DESC 
                    LIMIT 5
                ', $last24h, $tenantIPs)->fetchAll();
            }

            return [
                'currently_blocked_ips' => $currentlyBlocked,
                'attempts_last_24h' => $attemptsLast24h,
                'failed_attempts_last_24h' => $failedAttemptsLast24h,
                'success_rate' => $attemptsLast24h > 0 ? 
                    round((($attemptsLast24h - $failedAttemptsLast24h) / $attemptsLast24h) * 100, 1) : 100,
                'top_attacking_ips' => $topIPs,
                'tenant_name' => $this->getCurrentTenant()['name'] ?? 'Neznámý tenant'
            ];
        } catch (\Exception $e) {
            return [
                'currently_blocked_ips' => 0,
                'attempts_last_24h' => 0,
                'failed_attempts_last_24h' => 0,
                'success_rate' => 100,
                'top_attacking_ips' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * NOVÉ: Získá IP adresy používané tímto tenantem z security_logs
     */
    private function getTenantIPAddresses(int $tenantId): array
    {
        try {
            // Získáme uživatele z tenantu
            $tenantUserIds = $this->database->table('users')
                ->where('tenant_id', $tenantId)
                ->fetchPairs('id', 'id');

            if (empty($tenantUserIds)) {
                return [];
            }

            // Získáme IP adresy z security_logs pro tyto uživatele za posledních 30 dní
            $ips = $this->database->table('security_logs')
                ->where('user_id', array_keys($tenantUserIds))
                ->where('created_at > ?', new \DateTime('-30 days'))
                ->select('DISTINCT ip_address')
                ->fetchPairs('ip_address', 'ip_address');

            return array_filter($ips); // Odstraníme null hodnoty
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * NOVÉ: Tenant-specific blokované IP
     */
    private function getTenantBlockedIPs(int $tenantId): array
    {
        try {
            $tenantIPs = $this->getTenantIPAddresses($tenantId);
            
            if (empty($tenantIPs)) {
                return [];
            }

            return $this->database->table('rate_limit_blocks')
                ->where('blocked_until > ?', new \DateTime())
                ->where('ip_address', $tenantIPs)
                ->order('blocked_until DESC')
                ->limit(50)
                ->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * NOVÉ: Tenant-specific typy blokování
     */
    private function getTenantBlockTypes(int $tenantId): array
    {
        try {
            $tenantIPs = $this->getTenantIPAddresses($tenantId);
            
            if (empty($tenantIPs)) {
                return [];
            }

            return $this->database->query('
                SELECT action, COUNT(*) as count 
                FROM rate_limit_blocks 
                WHERE created_at > ? AND ip_address IN (?)
                GROUP BY action 
                ORDER BY count DESC
            ', new \DateTime('-7 days'), $tenantIPs)->fetchAll();
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
     * AJAX: Vyčištění rate limit blokování (pouze pro konkrétní tenant)
     */
    public function handleClearRateLimit(): void
    {
        // Kontrola oprávnění
        if (!$this->isAdmin() && !$this->isSuperAdmin()) {
            $this->sendJson(['success' => false, 'error' => 'Nedostatečná oprávnění']);
            return;
        }

        $ip = $this->getParameter('ip');
        $currentTenantId = $this->getCurrentTenantId();
        
        try {
            if ($ip) {
                // Ověříme, že IP patří k tomuto tenantu
                $tenantIPs = $this->getTenantIPAddresses($currentTenantId);
                
                if (!in_array($ip, $tenantIPs)) {
                    $this->sendJson([
                        'success' => false, 
                        'error' => 'Nemáte oprávnění vyčistit tuto IP adresu'
                    ]);
                    return;
                }

                // Vyčištění pro konkrétní IP
                $deleted = $this->database->table('rate_limit_blocks')
                    ->where('ip_address', $ip)
                    ->delete();
                    
                $this->securityLogger->logSecurityEvent(
                    'rate_limit_cleared',
                    "Rate limit vyčištěn pro IP: {$ip} (administrátorem tenantu {$currentTenantId})",
                    ['ip_address' => $ip, 'admin_user_id' => $this->getUser()->getId(), 'tenant_id' => $currentTenantId]
                );
                
                $this->sendJson([
                    'success' => true,
                    'message' => "Rate limit pro IP {$ip} byl vyčištěn ({$deleted} záznamů)"
                ]);
            } else {
                // Vyčištění expirovaných záznamů pro tenant
                $tenantIPs = $this->getTenantIPAddresses($currentTenantId);
                
                if (empty($tenantIPs)) {
                    $this->sendJson([
                        'success' => true,
                        'message' => 'Žádné záznamy k vyčištění pro váš tenant'
                    ]);
                    return;
                }

                $deleted = $this->database->table('rate_limit_blocks')
                    ->where('blocked_until < ?', new \DateTime())
                    ->where('ip_address', $tenantIPs)
                    ->delete();
                    
                $this->securityLogger->logSecurityEvent(
                    'rate_limit_cleanup',
                    "Vyčištěny expirované rate limit záznamy pro tenant {$currentTenantId}",
                    ['deleted_count' => $deleted, 'admin_user_id' => $this->getUser()->getId(), 'tenant_id' => $currentTenantId]
                );
                
                $this->sendJson([
                    'success' => true,
                    'message' => "Vyčištěno {$deleted} expirovaných záznamů pro váš tenant"
                ]);
            }

        } catch (\Exception $e) {
            $this->sendJson([
                'success' => false,
                'error' => 'Chyba při čištění: ' . $e->getMessage()
            ]);
        }
    }
}