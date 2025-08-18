<?php

declare(strict_types=1);

namespace App\Security;

use Nette;
use App\Security\SecurityLogger;

/**
 * Rate Limiter pro ochranu proti brute force útokům a spam
 * Implementuje různé druhy limitů podle typu akce s multi-tenancy podporou
 */
class RateLimiter
{
    use Nette\SmartObject;

    /** @var Nette\Database\Explorer */
    private $database;

    /** @var SecurityLogger */
    private $securityLogger;

    /** @var array Konfigurace limitů pro různé typy akcí */
    private $limits = [
        'login' => [
            'attempts' => 5,        // 5 pokusů
            'window' => 900,        // za 15 minut
            'lockout' => 3600,      // blokování na 1 hodinu
        ],
        'form_submit' => [
            'attempts' => 10,       // 10 odeslání formulářů
            'window' => 300,        // za 5 minut
            'lockout' => 900,       // blokování na 15 minut
        ],
        'password_reset' => [
            'attempts' => 3,        // 3 požadavky na reset hesla
            'window' => 3600,       // za 1 hodinu
            'lockout' => 7200,      // blokování na 2 hodiny
        ],
        'api_request' => [
            'attempts' => 100,      // 100 API požadavků
            'window' => 3600,       // za 1 hodinu
            'lockout' => 3600,      // blokování na 1 hodinu
        ],
        'user_creation' => [
            'attempts' => 5,        // 5 pokusů o vytvoření uživatele
            'window' => 1800,       // za 30 minut
            'lockout' => 3600,      // blokování na 1 hodinu
        ]
    ];

    public function __construct(
        Nette\Database\Explorer $database,
        SecurityLogger $securityLogger
    ) {
        $this->database = $database;
        $this->securityLogger = $securityLogger;

        // Zajistíme, že tabulky rate_limits existují
        $this->ensureTablesExist();
    }

    /**
     * ✅ ROZŠÍŘENO: Kontroluje, zda IP adresa není zablokována pro daný typ akce
     */
    public function isAllowed(string $action, string $ipAddress, ?int $tenantId = null): bool
    {
        if (!isset($this->limits[$action])) {
            // Neznámá akce - povolíme, ale zalogujeme
            $this->securityLogger->logSecurityEvent(
                'unknown_rate_limit_action',
                "Neznámá akce pro rate limiting: {$action} z IP: {$ipAddress}, tenant: " . ($tenantId ?? 'NULL')
            );
            return true;
        }

        $limit = $this->limits[$action];

        // Vyčistíme staré záznamy
        $this->cleanupOldRecords($action, $ipAddress, $limit['window'], $tenantId);

        // Zkontrolujeme aktivní blokování
        if ($this->isBlocked($action, $ipAddress, $tenantId)) {
            return false;
        }

        // Spočítáme aktuální počet pokusů v časovém okně
        $currentAttempts = $this->getAttemptCount($action, $ipAddress, $limit['window'], $tenantId);

        return $currentAttempts < $limit['attempts'];
    }

    /**
     * ✅ ROZŠÍŘENO: Zaznamenává pokus o akci s tenant informacemi
     */
    public function recordAttempt(string $action, string $ipAddress, bool $successful = false, ?int $tenantId = null, ?int $userId = null): void
    {
        if (!isset($this->limits[$action])) {
            return;
        }

        $limit = $this->limits[$action];

        // Zaznamenáme pokus s tenant informacemi
        $this->database->table('rate_limits')->insert([
            'ip_address' => $ipAddress,
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action' => $action,
            'successful' => $successful,
            'created_at' => new \DateTime(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);

        // ✅ OPRAVENO: Různé akce mají různé pravidla blokování
        $shouldCheckLimits = false;

        // Akce, které se blokují pouze po neúspěšných pokusech
        $failureOnlyActions = ['login', 'api_request'];

        // Akce, které se blokují po jakýxkoliv pokusech (i úspěšných)
        $allAttemptsActions = ['password_reset', 'form_submit', 'user_creation'];

        if (in_array($action, $failureOnlyActions)) {
            $shouldCheckLimits = !$successful; // Pouze neúspěšné
        } elseif (in_array($action, $allAttemptsActions)) {
            $shouldCheckLimits = true; // Všechny pokusy
        } else {
            $shouldCheckLimits = !$successful; // Výchozí: pouze neúspěšné
        }

        if ($shouldCheckLimits) {
            $currentAttempts = $this->getAttemptCount($action, $ipAddress, $limit['window'], $tenantId);

            if ($currentAttempts >= $limit['attempts']) {
                // Překročen limit - aktivujeme blokování
                $this->activateBlocking($action, $ipAddress, $limit['lockout'], $tenantId, $userId);

                $this->securityLogger->logSecurityEvent(
                    'rate_limit_exceeded',
                    "Rate limit překročen pro akci '{$action}' z IP: {$ipAddress}, tenant: " . ($tenantId ?? 'NULL') . ". Pokusů: {$currentAttempts}/{$limit['attempts']}"
                );
            }
        }
    }

    /**
     * ✅ ROZŠÍŘENO: Zkontroluje, zda je IP adresa zablokována pro daný tenant
     */
    public function isBlocked(string $action, string $ipAddress, ?int $tenantId = null): bool
    {
        $query = $this->database->table('rate_limit_blocks')
            ->where('ip_address', $ipAddress)
            ->where('action', $action)
            ->where('blocked_until > ?', new \DateTime());

        // Filtrujeme podle tenanta pouze pokud je zadán
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $blocking = $query->fetch();
        return $blocking !== null;
    }

    /**
     * ✅ ROZŠÍŘENO: Aktivuje blokování IP adresy pro danou akci s tenant informacemi
     */
    private function activateBlocking(string $action, string $ipAddress, int $lockoutSeconds, ?int $tenantId = null, ?int $userId = null): void
    {
        $blockedUntil = new \DateTime();
        $blockedUntil->add(new \DateInterval('PT' . $lockoutSeconds . 'S'));

        // Zkusíme najít existující blokování pro tento tenant/IP/action
        $existing = $this->database->table('rate_limit_blocks')
            ->where('ip_address', $ipAddress)
            ->where('action', $action)
            ->where('tenant_id', $tenantId)
            ->fetch();

        if ($existing) {
            // Aktualizujeme existující blokování
            $existing->update([
                'blocked_until' => $blockedUntil,
                'block_count' => $existing->block_count + 1,
                'updated_at' => new \DateTime(),
                'user_id' => $userId, // Aktualizujeme i user_id
            ]);
        } else {
            // Vytvoříme nové blokování
            $this->database->table('rate_limit_blocks')->insert([
                'ip_address' => $ipAddress,
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'action' => $action,
                'blocked_until' => $blockedUntil,
                'block_count' => 1,
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            ]);
        }
    }

    /**
     * ✅ ROZŠÍŘENO: Spočítá počet pokusů v časovém okně pro daný tenant
     */
    private function getAttemptCount(string $action, string $ipAddress, int $windowSeconds, ?int $tenantId = null): int
    {
        $windowStart = new \DateTime();
        $windowStart->sub(new \DateInterval('PT' . $windowSeconds . 'S'));

        $query = $this->database->table('rate_limits')
            ->where('ip_address', $ipAddress)
            ->where('action', $action)
            ->where('created_at > ?', $windowStart);

        // Filtrujeme podle tenanta pouze pokud je zadán
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->count();
    }

    /**
     * ✅ ROZŠÍŘENO: Vyčistí staré záznamy s tenant podporou
     */
    private function cleanupOldRecords(string $action, string $ipAddress, int $windowSeconds, ?int $tenantId = null): void
    {
        $cutoff = new \DateTime();
        $cutoff->sub(new \DateInterval('PT' . ($windowSeconds * 2) . 'S')); // 2x časové okno

        $query = $this->database->table('rate_limits')
            ->where('ip_address', $ipAddress)
            ->where('action', $action)
            ->where('created_at < ?', $cutoff);

        // Filtrujeme podle tenanta pouze pokud je zadán
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $query->delete();
    }

    /**
     * ✅ ROZŠÍŘENO: Získá informace o aktuálním stavu limitů pro IP adresu s tenant podporou
     */
    public function getLimitStatus(string $action, string $ipAddress, ?int $tenantId = null): array
    {
        if (!isset($this->limits[$action])) {
            return [
                'allowed' => true,
                'attempts_remaining' => 999,
                'window_seconds' => 0,
                'blocked_until' => null,
            ];
        }

        $limit = $this->limits[$action];
        $currentAttempts = $this->getAttemptCount($action, $ipAddress, $limit['window'], $tenantId);
        $isBlocked = $this->isBlocked($action, $ipAddress, $tenantId);

        $blockedUntil = null;
        if ($isBlocked) {
            $query = $this->database->table('rate_limit_blocks')
                ->where('ip_address', $ipAddress)
                ->where('action', $action)
                ->where('blocked_until > ?', new \DateTime());

            if ($tenantId !== null) {
                $query->where('tenant_id', $tenantId);
            }

            $blocking = $query->fetch();

            if ($blocking) {
                $blockedUntil = $blocking->blocked_until;
            }
        }

        return [
            'allowed' => !$isBlocked && $currentAttempts < $limit['attempts'],
            'attempts_remaining' => max(0, $limit['attempts'] - $currentAttempts),
            'attempts_used' => $currentAttempts,
            'attempts_max' => $limit['attempts'],
            'window_seconds' => $limit['window'],
            'blocked_until' => $blockedUntil,
            'is_blocked' => $isBlocked,
        ];
    }

    /**
     * ✅ ROZŠÍŘENO: Vymaže všechna blokování pro IP adresu s tenant podporou
     */
    public function clearBlocking(string $ipAddress, string $adminNote = '', ?int $tenantId = null): bool
    {
        try {
            $blocksQuery = $this->database->table('rate_limit_blocks')
                ->where('ip_address', $ipAddress);

            // ✅ OPRAVENO: Mazání VŠECH záznamů pro IP, ne jen neúspěšných
            $attemptsQuery = $this->database->table('rate_limits')
                ->where('ip_address', $ipAddress);

            // Filtrujeme podle tenanta pokud je zadán
            if ($tenantId !== null) {
                $blocksQuery->where('tenant_id', $tenantId);
                $attemptsQuery->where('tenant_id', $tenantId);
            }

            $deletedBlocks = $blocksQuery->delete();
            $deletedAttempts = $attemptsQuery->delete();

            if ($deletedBlocks > 0 || $deletedAttempts > 0) {
                $tenantInfo = $tenantId ? " (tenant: {$tenantId})" : " (všichni tenanti)";
                $this->securityLogger->logSecurityEvent(
                    'rate_limit_cleared',
                    "Rate limit vymazán pro IP: {$ipAddress}{$tenantInfo}. Bloky: {$deletedBlocks}, Pokusy: {$deletedAttempts}. Poznámka: {$adminNote}"
                );
            }

            return true;
        } catch (\Exception $e) {
            $this->securityLogger->logSecurityEvent(
                'rate_limit_clear_error',
                "Chyba při mazání rate limitů pro IP: {$ipAddress}. Chyba: " . $e->getMessage()
            );
            return false;
        }
    }

    /**
     * ✅ ROZŠÍŘENO: Získá statistiky rate limitingu pro dashboard s tenant podporou
     */
    public function getStatistics(?int $tenantId = null): array
    {
        try {
            // Aktuálně zablokované IP adresy
            $blocksQuery = $this->database->table('rate_limit_blocks')
                ->where('blocked_until > ?', new \DateTime());

            if ($tenantId !== null) {
                $blocksQuery->where('tenant_id', $tenantId);
            }

            $currentlyBlocked = $blocksQuery->count();

            // Pokusy za posledních 24 hodin
            $last24h = new \DateTime('-24 hours');
            $attemptsQuery = $this->database->table('rate_limits')
                ->where('created_at > ?', $last24h);

            if ($tenantId !== null) {
                $attemptsQuery->where('tenant_id', $tenantId);
            }

            $attemptsLast24h = $attemptsQuery->count();

            // Neúspěšné pokusy za posledních 24 hodin
            $failedAttemptsQuery = $this->database->table('rate_limits')
                ->where('created_at > ?', $last24h)
                ->where('successful', false);

            if ($tenantId !== null) {
                $failedAttemptsQuery->where('tenant_id', $tenantId);
            }

            $failedAttemptsLast24h = $failedAttemptsQuery->count();

            // Top 5 IP adres s nejvíce pokusy
            $topIPsQuery = 'SELECT ip_address, COUNT(*) as attempt_count 
                FROM rate_limits 
                WHERE created_at > ? AND successful = 0';
            $params = [$last24h];

            if ($tenantId !== null) {
                $topIPsQuery .= ' AND tenant_id = ?';
                $params[] = $tenantId;
            }

            $topIPsQuery .= ' GROUP BY ip_address ORDER BY attempt_count DESC LIMIT 5';

            $topIPs = $this->database->query($topIPsQuery, ...$params)->fetchAll();

            return [
                'currently_blocked_ips' => $currentlyBlocked,
                'attempts_last_24h' => $attemptsLast24h,
                'failed_attempts_last_24h' => $failedAttemptsLast24h,
                'success_rate' => $attemptsLast24h > 0 ?
                    round((($attemptsLast24h - $failedAttemptsLast24h) / $attemptsLast24h) * 100, 1) : 100,
                'top_attacking_ips' => $topIPs,
            ];
        } catch (\Exception $e) {
            // V případě chyby vrátíme výchozí hodnoty
            return [
                'currently_blocked_ips' => 0,
                'attempts_last_24h' => 0,
                'failed_attempts_last_24h' => 0,
                'success_rate' => 100,
                'top_attacking_ips' => [],
            ];
        }
    }

    /**
     * Získá IP adresu klienta (včetně proxy a CloudFlare)
     */
    public function getClientIP(): string
    {
        // Kontrola různých headerů pro skutečnou IP
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED'];

        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Zajistí, že potřebné databázové tabulky existují
     */
    private function ensureTablesExist(): void
    {
        try {
            // Kontrola existence tabulky rate_limits
            $this->database->query('SELECT 1 FROM rate_limits LIMIT 1');
        } catch (\Exception $e) {
            // Tabulka neexistuje, vytvoříme ji
            $this->createRateLimitTables();
        }

        try {
            // Kontrola existence tabulky rate_limit_blocks
            $this->database->query('SELECT 1 FROM rate_limit_blocks LIMIT 1');
        } catch (\Exception $e) {
            // Tabulka neexistuje, vytvoříme ji
            $this->createRateLimitTables();
        }
    }

    /**
     * ✅ AKTUALIZOVÁNO: Vytvoří tabulky pro rate limiting s tenant podporou
     */
    private function createRateLimitTables(): void
    {
        try {
            // Tabulka pro záznamy pokusů s tenant podporou
            $this->database->query('
                CREATE TABLE IF NOT EXISTS rate_limits (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    ip_address VARCHAR(45) NOT NULL,
                    tenant_id INT(11) NULL DEFAULT NULL,
                    user_id INT(11) NULL DEFAULT NULL,
                    action VARCHAR(50) NOT NULL,
                    successful BOOLEAN DEFAULT FALSE,
                    user_agent TEXT,
                    created_at DATETIME NOT NULL,
                    INDEX idx_ip_action_time (ip_address, action, created_at),
                    INDEX idx_tenant_ip_action (tenant_id, ip_address, action),
                    INDEX idx_cleanup (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');

            // Tabulka pro aktivní blokování s tenant podporou
            $this->database->query('
                CREATE TABLE IF NOT EXISTS rate_limit_blocks (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    ip_address VARCHAR(45) NOT NULL,
                    tenant_id INT(11) NULL DEFAULT NULL,
                    user_id INT(11) NULL DEFAULT NULL,
                    action VARCHAR(50) NOT NULL,
                    blocked_until DATETIME NOT NULL,
                    block_count INT DEFAULT 1,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL,
                    UNIQUE KEY unique_tenant_ip_action (tenant_id, ip_address, action),
                    INDEX idx_blocked_until (blocked_until),
                    INDEX idx_tenant_id (tenant_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');

            $this->securityLogger->logSecurityEvent(
                'rate_limit_tables_created',
                'Rate limiting tabulky byly automaticky vytvořeny s multi-tenancy podporou'
            );
        } catch (\Exception $e) {
            error_log('Chyba při vytváření rate limiting tabulek: ' . $e->getMessage());
        }
    }
}
