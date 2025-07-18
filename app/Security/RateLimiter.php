<?php

declare(strict_types=1);

namespace App\Security;

use Nette;
use App\Security\SecurityLogger;

/**
 * Rate Limiter pro ochranu proti brute force útokům a spam
 * Implementuje různé druhy limitů podle typu akce
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
            'attempts' => 3,        // 3 pokusy o vytvoření uživatele
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
        
        // Zajistíme, že tabulka rate_limits existuje
        $this->ensureTableExists();
    }

    /**
     * Kontroluje, zda IP adresa není zablokována pro daný typ akce
     */
    public function isAllowed(string $action, string $ipAddress): bool
    {
        if (!isset($this->limits[$action])) {
            // Neznámá akce - povolíme, ale zalogujeme
            $this->securityLogger->logSecurityEvent(
                'unknown_rate_limit_action',
                "Neznámá akce pro rate limiting: {$action} z IP: {$ipAddress}"
            );
            return true;
        }

        $limit = $this->limits[$action];
        
        // Vyčistíme staré záznamy
        $this->cleanupOldRecords($action, $ipAddress, $limit['window']);
        
        // Zkontrolujeme aktivní blokování
        if ($this->isBlocked($action, $ipAddress)) {
            return false;
        }
        
        // Spočítáme aktuální počet pokusů v časovém okně
        $currentAttempts = $this->getAttemptCount($action, $ipAddress, $limit['window']);
        
        return $currentAttempts < $limit['attempts'];
    }

    /**
     * Zaznamenává pokus o akci
     */
    public function recordAttempt(string $action, string $ipAddress, bool $successful = false): void
    {
        if (!isset($this->limits[$action])) {
            return;
        }

        $limit = $this->limits[$action];
        
        // Zaznamenáme pokus
        $this->database->table('rate_limits')->insert([
            'ip_address' => $ipAddress,
            'action' => $action,
            'successful' => $successful,
            'created_at' => new \DateTime(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);

        // Pokud nebyl úspěšný, zkontrolujme limity
        if (!$successful) {
            $currentAttempts = $this->getAttemptCount($action, $ipAddress, $limit['window']);
            
            if ($currentAttempts >= $limit['attempts']) {
                // Překročen limit - aktivujeme blokování
                $this->activateBlocking($action, $ipAddress, $limit['lockout']);
                
                $this->securityLogger->logSecurityEvent(
                    'rate_limit_exceeded',
                    "Rate limit překročen pro akci '{$action}' z IP: {$ipAddress}. Pokusů: {$currentAttempts}/{$limit['attempts']}"
                );
            }
        }
    }

    /**
     * Zkontroluje, zda je IP adresa zablokována
     */
    public function isBlocked(string $action, string $ipAddress): bool
    {
        $blocking = $this->database->table('rate_limit_blocks')
            ->where('ip_address', $ipAddress)
            ->where('action', $action)
            ->where('blocked_until > ?', new \DateTime())
            ->fetch();

        return $blocking !== null;
    }

    /**
     * Aktivuje blokování IP adresy pro danou akci
     */
    private function activateBlocking(string $action, string $ipAddress, int $lockoutSeconds): void
    {
        $blockedUntil = new \DateTime();
        $blockedUntil->add(new \DateInterval('PT' . $lockoutSeconds . 'S'));

        // Aktualizujeme nebo vytvoříme blokování
        $existing = $this->database->table('rate_limit_blocks')
            ->where('ip_address', $ipAddress)
            ->where('action', $action)
            ->fetch();

        if ($existing) {
            $this->database->table('rate_limit_blocks')
                ->where('id', $existing->id)
                ->update([
                    'blocked_until' => $blockedUntil,
                    'block_count' => $existing->block_count + 1,
                    'updated_at' => new \DateTime(),
                ]);
        } else {
            $this->database->table('rate_limit_blocks')->insert([
                'ip_address' => $ipAddress,
                'action' => $action,
                'blocked_until' => $blockedUntil,
                'block_count' => 1,
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            ]);
        }
    }

    /**
     * Spočítá počet pokusů v daném časovém okně
     */
    private function getAttemptCount(string $action, string $ipAddress, int $windowSeconds): int
    {
        $windowStart = new \DateTime();
        $windowStart->sub(new \DateInterval('PT' . $windowSeconds . 'S'));

        return $this->database->table('rate_limits')
            ->where('ip_address', $ipAddress)
            ->where('action', $action)
            ->where('successful', false)
            ->where('created_at > ?', $windowStart)
            ->count();
    }

    /**
     * Vyčistí staré záznamy
     */
    private function cleanupOldRecords(string $action, string $ipAddress, int $windowSeconds): void
    {
        $cutoff = new \DateTime();
        $cutoff->sub(new \DateInterval('PT' . ($windowSeconds * 2) . 'S')); // 2x časové okno

        $this->database->table('rate_limits')
            ->where('ip_address', $ipAddress)
            ->where('action', $action)
            ->where('created_at < ?', $cutoff)
            ->delete();
    }

    /**
     * Získá informace o aktuálním stavu limitů pro IP adresu
     */
    public function getLimitStatus(string $action, string $ipAddress): array
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
        $currentAttempts = $this->getAttemptCount($action, $ipAddress, $limit['window']);
        $isBlocked = $this->isBlocked($action, $ipAddress);
        
        $blockedUntil = null;
        if ($isBlocked) {
            $blocking = $this->database->table('rate_limit_blocks')
                ->where('ip_address', $ipAddress)
                ->where('action', $action)
                ->where('blocked_until > ?', new \DateTime())
                ->fetch();
            
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
     * Vymaže všechna blokování pro IP adresu (pouze pro administrátory)
     */
    public function clearBlocking(string $ipAddress, string $adminNote = ''): bool
    {
        try {
            $deletedBlocks = $this->database->table('rate_limit_blocks')
                ->where('ip_address', $ipAddress)
                ->delete();

            $deletedAttempts = $this->database->table('rate_limits')
                ->where('ip_address', $ipAddress)
                ->where('successful', false)
                ->delete();

            if ($deletedBlocks > 0 || $deletedAttempts > 0) {
                $this->securityLogger->logSecurityEvent(
                    'rate_limit_cleared',
                    "Rate limit vymazán pro IP: {$ipAddress}. Bloky: {$deletedBlocks}, Pokusy: {$deletedAttempts}. Poznámka: {$adminNote}"
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
     * Získá statistiky rate limitingu pro dashboard
     */
    public function getStatistics(): array
    {
        try {
            // Aktuálně zablokované IP adresy
            $currentlyBlocked = $this->database->table('rate_limit_blocks')
                ->where('blocked_until > ?', new \DateTime())
                ->count();

            // Pokusy za posledních 24 hodin
            $last24h = new \DateTime('-24 hours');
            $attemptsLast24h = $this->database->table('rate_limits')
                ->where('created_at > ?', $last24h)
                ->count();

            // Neúspěšné pokusy za posledních 24 hodin
            $failedAttemptsLast24h = $this->database->table('rate_limits')
                ->where('created_at > ?', $last24h)
                ->where('successful', false)
                ->count();

            // Top 5 IP adres s nejvíce pokusy
            $topIPs = $this->database->query('
                SELECT ip_address, COUNT(*) as attempt_count 
                FROM rate_limits 
                WHERE created_at > ? AND successful = 0
                GROUP BY ip_address 
                ORDER BY attempt_count DESC 
                LIMIT 5
            ', $last24h)->fetchAll();

            return [
                'currently_blocked_ips' => $currentlyBlocked,
                'attempts_last_24h' => $attemptsLast24h,
                'failed_attempts_last_24h' => $failedAttemptsLast24h,
                'success_rate' => $attemptsLast24h > 0 ? 
                    round((($attemptsLast24h - $failedAttemptsLast24h) / $attemptsLast24h) * 100, 1) : 100,
                'top_ips' => $topIPs,
            ];
        } catch (\Exception $e) {
            return [
                'currently_blocked_ips' => 0,
                'attempts_last_24h' => 0,
                'failed_attempts_last_24h' => 0,
                'success_rate' => 100,
                'top_ips' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Získá aktuální IP adresu uživatele
     */
    public static function getClientIP(): string
    {
        // Kontrola různých možností získání IP adresy
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_FORWARDED_FOR',      // Proxy/Load balancer
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standardní
        ];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                // Validace IP adresy
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Fallback - i privátní IP adresy
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Zajistí, že potřebné databázové tabulky existují
     */
    private function ensureTableExists(): void
    {
        try {
            // Kontrola existence tabulky rate_limits
            $this->database->query('SELECT 1 FROM rate_limits LIMIT 1');
        } catch (\Exception $e) {
            // Tabulka neexistuje, vytvoříme ji
            $this->createRateLimitTables();
        }
    }

    /**
     * Vytvoří tabulky pro rate limiting (pokud neexistují)
     */
    private function createRateLimitTables(): void
    {
        try {
            // Tabulka pro záznamy pokusů
            $this->database->query('
                CREATE TABLE IF NOT EXISTS rate_limits (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    ip_address VARCHAR(45) NOT NULL,
                    action VARCHAR(50) NOT NULL,
                    successful BOOLEAN DEFAULT FALSE,
                    user_agent TEXT,
                    created_at DATETIME NOT NULL,
                    INDEX idx_ip_action_time (ip_address, action, created_at),
                    INDEX idx_cleanup (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');

            // Tabulka pro aktivní blokování
            $this->database->query('
                CREATE TABLE IF NOT EXISTS rate_limit_blocks (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    ip_address VARCHAR(45) NOT NULL,
                    action VARCHAR(50) NOT NULL,
                    blocked_until DATETIME NOT NULL,
                    block_count INT DEFAULT 1,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL,
                    UNIQUE KEY unique_ip_action (ip_address, action),
                    INDEX idx_blocked_until (blocked_until)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');

            $this->securityLogger->logSecurityEvent(
                'rate_limit_tables_created',
                'Rate limiting tabulky byly automaticky vytvořeny'
            );

        } catch (\Exception $e) {
            error_log('Chyba při vytváření rate limiting tabulek: ' . $e->getMessage());
        }
    }
}