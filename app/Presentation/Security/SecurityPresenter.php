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
     * Rate limiting statistiky (rozšíření)
     */
    public function actionRateLimitStats(): void
    {
        // Kontrola oprávnění - pouze super admin
        if (!$this->isSuperAdmin()) {
            $this->error('Nemáte oprávnění pro přístup k rate limiting statistikám', 403);
        }
    }

    public function renderRateLimitStats(): void
    {
        $this->template->pageTitle = 'Rate Limiting Statistiky';
        
        // Získání statistik
        $this->template->statistics = $this->getRateLimiter()->getStatistics();

        // Současné IP adresy s blokováním
        $blockedIPs = $this->database->table('rate_limit_blocks')
            ->where('blocked_until > ?', new \DateTime())
            ->order('blocked_until DESC')
            ->limit(50);

        $this->template->blockedIPs = $blockedIPs;

        // Nejčastější typy blokování
        $blockTypes = $this->database->query('
            SELECT action_type, COUNT(*) as count 
            FROM rate_limit_blocks 
            WHERE created_at > ? 
            GROUP BY action_type 
            ORDER BY count DESC
        ', new \DateTime('-7 days'))->fetchAll();

        $this->template->blockTypes = $blockTypes;
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
     * AJAX: Vyčištění rate limit blokování
     */
    public function handleClearRateLimit(): void
    {
        // Kontrola oprávnění - pouze super admin
        if (!$this->isSuperAdmin()) {
            $this->sendJson(['success' => false, 'error' => 'Nedostatečná oprávnění']);
            return;
        }

        $ip = $this->getParameter('ip');
        
        try {
            if ($ip) {
                // Vyčištění pro konkrétní IP
                $deleted = $this->database->table('rate_limit_blocks')
                    ->where('ip_address', $ip)
                    ->delete();
                    
                $this->securityLogger->logSecurityEvent(
                    'rate_limit_cleared',
                    "Rate limit vyčištěn pro IP: {$ip} (administrátorem)",
                    ['ip_address' => $ip, 'admin_user_id' => $this->getUser()->getId()]
                );
                
                $this->sendJson([
                    'success' => true,
                    'message' => "Rate limit pro IP {$ip} byl vyčištěn ({$deleted} záznamů)"
                ]);
            } else {
                // Vyčištění všech starých záznamů
                $deleted = $this->database->table('rate_limit_blocks')
                    ->where('blocked_until < ?', new \DateTime())
                    ->delete();
                    
                $this->securityLogger->logSecurityEvent(
                    'rate_limit_cleanup',
                    "Vyčištěny staré rate limit záznamy (administrátorem)",
                    ['deleted_count' => $deleted, 'admin_user_id' => $this->getUser()->getId()]
                );
                
                $this->sendJson([
                    'success' => true,
                    'message' => "Vyčištěno {$deleted} starých rate limit záznamů"
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