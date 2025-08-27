<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

/**
 * Správce nastavení session timeoutů
 * Umožňuje adminům konfigurovat různé timeouty pro jednotlivé tenancy
 */
final class SessionSettingsManager
{
    use Nette\SmartObject;

    /** @var Nette\Database\Explorer */
    private $database;

    /** @var int|null Aktuální tenant ID */
    private $currentTenantId = null;

    /** @var bool Je uživatel super admin? */
    private $isSuperAdmin = false;

    /** @var array Výchozí hodnoty timeoutů (v sekundách) */
    private const DEFAULT_SETTINGS = [
        'grace_period' => 120,          // 2 minuty
        'inactivity_timeout' => 14400,  // 4 hodiny  
        'max_lifetime' => 43200,        // 12 hodin
        'regeneration_interval' => 1800  // 30 minut
    ];

    /** @var array Cache pro nastavení */
    private $settingsCache = [];

    public function __construct(Nette\Database\Explorer $database)
    {
        $this->database = $database;
        $this->ensureTableExists();
    }

    /**
     * Nastaví tenant kontext
     */
    public function setTenantContext(?int $tenantId, bool $isSuperAdmin = false): void
    {
        $this->currentTenantId = $tenantId;
        $this->isSuperAdmin = $isSuperAdmin;
        
        // Vyčistit cache při změně kontextu
        $this->settingsCache = [];
    }

    /**
     * Získá session nastavení pro aktuální tenant
     */
    public function getSessionSettings(): array
    {
        $tenantId = $this->currentTenantId ?? 1;
        
        // Kontrola cache
        if (isset($this->settingsCache[$tenantId])) {
            return $this->settingsCache[$tenantId];
        }

        // Načtení z databáze
        $settings = $this->database->table('session_settings')
            ->where('tenant_id', $tenantId)
            ->fetch();

        if ($settings) {
            $result = [
                'grace_period' => (int)$settings->grace_period,
                'inactivity_timeout' => (int)$settings->inactivity_timeout,
                'max_lifetime' => (int)$settings->max_lifetime,
                'regeneration_interval' => (int)$settings->regeneration_interval,
                'updated_at' => $settings->updated_at,
                'updated_by' => $settings->updated_by
            ];
        } else {
            // Použít výchozí hodnoty
            $result = self::DEFAULT_SETTINGS;
        }

        // Uložit do cache
        $this->settingsCache[$tenantId] = $result;
        
        return $result;
    }

    /**
     * Uloží session nastavení pro aktuální tenant
     */
    public function saveSessionSettings(array $settings, int $userId): bool
    {
        $tenantId = $this->currentTenantId ?? 1;

        // Validace hodnot
        $validatedSettings = $this->validateSettings($settings);
        if (!$validatedSettings) {
            return false;
        }

        try {
            // Kontrola, zda už nastavení existuje
            $existing = $this->database->table('session_settings')
                ->where('tenant_id', $tenantId)
                ->fetch();

            $data = [
                'grace_period' => $validatedSettings['grace_period'],
                'inactivity_timeout' => $validatedSettings['inactivity_timeout'], 
                'max_lifetime' => $validatedSettings['max_lifetime'],
                'regeneration_interval' => $validatedSettings['regeneration_interval'],
                'updated_at' => new \DateTime(),
                'updated_by' => $userId
            ];

            if ($existing) {
                // Update existujícího záznamu
                $existing->update($data);
            } else {
                // Insert nového záznamu
                $data['tenant_id'] = $tenantId;
                $data['created_at'] = new \DateTime();
                $this->database->table('session_settings')->insert($data);
            }

            // Vyčistit cache
            unset($this->settingsCache[$tenantId]);

            return true;

        } catch (\Exception $e) {
            \Tracy\Debugger::log("Chyba při ukládání session nastavení: " . $e->getMessage(), \Tracy\ILogger::ERROR);
            return false;
        }
    }

    /**
     * Validuje nastavení timeoutů
     */
    private function validateSettings(array $settings): ?array
    {
        $validated = [];

        // Grace period (30 sekund - 10 minut)
        $gracePeriod = (int)($settings['grace_period'] ?? self::DEFAULT_SETTINGS['grace_period']);
        if ($gracePeriod < 30 || $gracePeriod > 600) {
            return null; // Neplatná hodnota
        }
        $validated['grace_period'] = $gracePeriod;

        // Inactivity timeout (5 minut - 24 hodin)
        $inactivityTimeout = (int)($settings['inactivity_timeout'] ?? self::DEFAULT_SETTINGS['inactivity_timeout']);
        if ($inactivityTimeout < 300 || $inactivityTimeout > 86400) {
            return null; // Neplatná hodnota
        }
        $validated['inactivity_timeout'] = $inactivityTimeout;

        // Max lifetime (1 hodina - 7 dní)
        $maxLifetime = (int)($settings['max_lifetime'] ?? self::DEFAULT_SETTINGS['max_lifetime']);
        if ($maxLifetime < 3600 || $maxLifetime > 604800) {
            return null; // Neplatná hodnota
        }
        $validated['max_lifetime'] = $maxLifetime;

        // Regeneration interval (5 minut - 2 hodiny)
        $regenerationInterval = (int)($settings['regeneration_interval'] ?? self::DEFAULT_SETTINGS['regeneration_interval']);
        if ($regenerationInterval < 300 || $regenerationInterval > 7200) {
            return null; // Neplatná hodnota
        }
        $validated['regeneration_interval'] = $regenerationInterval;

        // Logická kontrola - max_lifetime musí být větší než inactivity_timeout
        if ($validated['max_lifetime'] <= $validated['inactivity_timeout']) {
            return null;
        }

        return $validated;
    }

    /**
     * Převede sekundy na lidsky čitelný formát
     */
    public function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' sekund';
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            return $minutes . ' minut';
        } elseif ($seconds < 86400) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return $hours . ' hodin' . ($minutes > 0 ? ' ' . $minutes . ' minut' : '');
        } else {
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            return $days . ' dní' . ($hours > 0 ? ' ' . $hours . ' hodin' : '');
        }
    }

    /**
     * Zajistí, že tabulka session_settings existuje
     */
    private function ensureTableExists(): void
    {
        try {
            // Test existence tabulky
            $this->database->query('SELECT 1 FROM session_settings LIMIT 1');
        } catch (\Exception $e) {
            // Tabulka neexistuje, vytvoříme ji
            $this->createSessionSettingsTable();
        }
    }

    /**
     * Vytvoří tabulku session_settings
     */
    private function createSessionSettingsTable(): void
    {
        try {
            $this->database->query('
                CREATE TABLE IF NOT EXISTS session_settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    tenant_id INT NOT NULL,
                    grace_period INT NOT NULL DEFAULT 120 COMMENT "Grace period po přihlášení (sekundy)",
                    inactivity_timeout INT NOT NULL DEFAULT 14400 COMMENT "Timeout neaktivity (sekundy)",
                    max_lifetime INT NOT NULL DEFAULT 43200 COMMENT "Maximální doba života session (sekundy)",
                    regeneration_interval INT NOT NULL DEFAULT 1800 COMMENT "Interval regenerace session ID (sekundy)",
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL,
                    updated_by INT NOT NULL COMMENT "ID uživatele, který provedl změnu",
                    UNIQUE KEY unique_tenant (tenant_id),
                    INDEX idx_tenant_id (tenant_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');

            \Tracy\Debugger::log("Session settings tabulka byla automaticky vytvořena", \Tracy\ILogger::INFO);

        } catch (\Exception $e) {
            \Tracy\Debugger::log("Chyba při vytváření session_settings tabulky: " . $e->getMessage(), \Tracy\ILogger::ERROR);
            throw $e;
        }
    }
}