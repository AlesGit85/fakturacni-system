<?php

declare(strict_types=1);

namespace App\Security;

use Nette;

/**
 * Service pro automatické čištění starých rate limiting záznamů
 * Měl by být spuštěn přes cron nebo periodicky
 */
class RateLimiterCleaner
{
    use Nette\SmartObject;

    /** @var Nette\Database\Explorer */
    private $database;

    /** @var SecurityLogger */
    private $securityLogger;

    public function __construct(
        Nette\Database\Explorer $database,
        SecurityLogger $securityLogger
    ) {
        $this->database = $database;
        $this->securityLogger = $securityLogger;
    }

    /**
     * Vyčistí všechny staré záznamy starší než zadaný počet dní
     */
    public function cleanOldRecords(int $olderThanDays = 7): array
    {
        $result = [
            'rate_limits_deleted' => 0,
            'expired_blocks_deleted' => 0,
            'old_blocks_deleted' => 0,
            'errors' => []
        ];

        try {
            $cutoffDate = new \DateTime("-{$olderThanDays} days");
            $now = new \DateTime();

            // 1. Vymazání starých pokusů z rate_limits
            $rateLimitsDeleted = $this->database->table('rate_limits')
                ->where('created_at < ?', $cutoffDate)
                ->delete();
            
            $result['rate_limits_deleted'] = $rateLimitsDeleted;

            // 2. Vymazání expirovaných blokování
            $expiredBlocksDeleted = $this->database->table('rate_limit_blocks')
                ->where('blocked_until < ?', $now)
                ->delete();
            
            $result['expired_blocks_deleted'] = $expiredBlocksDeleted;

            // 3. Vymazání starých blokování (i těch, co už expirovali dříve)
            $oldBlocksDeleted = $this->database->table('rate_limit_blocks')
                ->where('updated_at < ?', $cutoffDate)
                ->delete();
            
            $result['old_blocks_deleted'] = $oldBlocksDeleted;

            // Logování úklidu
            if ($rateLimitsDeleted > 0 || $expiredBlocksDeleted > 0 || $oldBlocksDeleted > 0) {
                $this->securityLogger->logSecurityEvent(
                    'rate_limit_cleanup',
                    "Rate limit cleanup dokončen. Pokusů: {$rateLimitsDeleted}, Exp. bloků: {$expiredBlocksDeleted}, Starých bloků: {$oldBlocksDeleted}"
                );
            }

        } catch (\Exception $e) {
            $error = 'Chyba při čištění rate limitů: ' . $e->getMessage();
            $result['errors'][] = $error;
            
            $this->securityLogger->logSecurityEvent(
                'rate_limit_cleanup_error',
                $error
            );
        }

        return $result;
    }

    /**
     * Optimalizuje databázové tabulky (pouze pro MySQL)
     */
    public function optimizeTables(): bool
    {
        try {
            // Pouze pro MySQL/MariaDB
            $this->database->query('OPTIMIZE TABLE rate_limits');
            $this->database->query('OPTIMIZE TABLE rate_limit_blocks');
            
            $this->securityLogger->logSecurityEvent(
                'rate_limit_tables_optimized',
                'Rate limit tabulky byly optimalizovány'
            );
            
            return true;
        } catch (\Exception $e) {
            $this->securityLogger->logSecurityEvent(
                'rate_limit_optimize_error',
                'Chyba při optimalizaci tabulek: ' . $e->getMessage()
            );
            return false;
        }
    }

    /**
     * Získá statistiky velikosti tabulek
     */
    public function getTableStats(): array
    {
        try {
            $rateLimitsCount = $this->database->table('rate_limits')->count();
            $blocksCount = $this->database->table('rate_limit_blocks')->count();
            
            // Velikost dat (pouze pro MySQL)
            $tableSize = null;
            try {
                $sizeQuery = $this->database->query("
                    SELECT 
                        table_name,
                        ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                    FROM information_schema.TABLES 
                    WHERE table_schema = DATABASE() 
                    AND table_name IN ('rate_limits', 'rate_limit_blocks')
                ");
                
                $sizes = [];
                foreach ($sizeQuery as $row) {
                    $sizes[$row->table_name] = $row->size_mb;
                }
                $tableSize = $sizes;
            } catch (\Exception $e) {
                // Ignorujeme chyby při získávání velikosti
            }

            return [
                'rate_limits_count' => $rateLimitsCount,
                'blocks_count' => $blocksCount,
                'table_sizes_mb' => $tableSize,
                'last_cleanup' => $this->getLastCleanupTime(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Získá čas posledního úklidu
     */
    private function getLastCleanupTime(): ?\DateTime
    {
        try {
            // Hledáme v security_logs
            $lastCleanup = $this->database->table('security_logs')
                ->where('event_type', 'rate_limit_cleanup')
                ->order('created_at DESC')
                ->fetch();
            
            return $lastCleanup ? $lastCleanup->created_at : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Kontroluje, zda je potřeba vyčištění
     */
    public function needsCleaning(int $maxRecords = 10000): bool
    {
        try {
            $rateLimitsCount = $this->database->table('rate_limits')->count();
            return $rateLimitsCount > $maxRecords;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Automatické čištění pokud je to potřeba
     */
    public function autoCleanIfNeeded(int $maxRecords = 10000, int $olderThanDays = 7): ?array
    {
        if ($this->needsCleaning($maxRecords)) {
            return $this->cleanOldRecords($olderThanDays);
        }
        
        return null;
    }
}