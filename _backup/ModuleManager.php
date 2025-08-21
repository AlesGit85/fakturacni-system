<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Tracy\ILogger;
use ZipArchive;

/**
 * Třída pro správu modulů s tenant-specific adresáři
 */
class ModuleManager
{
    use Nette\SmartObject;

    /** @var ILogger */
    private $logger;
    
    /** @var Nette\Database\Explorer */
    private $database;
    
    /** @var string Základní cesta k adresáři s moduly */
    private $baseModulesDir;
    
    /** @var string Cesta k adresáři pro dočasné nahrávání souborů */
    private $uploadsDir;
    
    /** @var string Základní cesta k adresáři assets v WWW */
    private $baseWwwModulesDir;

    // =====================================================
    // MULTI-TENANCY CONTEXT
    // =====================================================

    /** @var int|null Current user ID pro filtrování modulů */
    private $currentUserId = null;

    /** @var int|null Current tenant ID pro filtrování */
    private $currentTenantId = null;

    /** @var bool Je uživatel super admin? */
    private $isSuperAdmin = false;

    /**
     * Konstruktor třídy
     */
    public function __construct(ILogger $logger, Nette\Database\Explorer $database)
    {
        $this->logger = $logger;
        $this->database = $database;
        $this->baseModulesDir = dirname(__DIR__) . '/Modules';
        $this->uploadsDir = dirname(__DIR__, 2) . '/temp/module_uploads';
        $this->baseWwwModulesDir = dirname(__DIR__, 2) . '/web/Modules';
        
        // Vytvoření základních adresářů
        if (!is_dir($this->baseModulesDir)) {
            mkdir($this->baseModulesDir, 0755, true);
        }
        
        if (!is_dir($this->uploadsDir)) {
            mkdir($this->uploadsDir, 0755, true);
        }
        
        if (!is_dir($this->baseWwwModulesDir)) {
            mkdir($this->baseWwwModulesDir, 0755, true);
        }
        
        $this->logger->log("ModuleManager byl inicializován s tenant-specific adresáři", ILogger::INFO);
    }

    // =====================================================
    // TENANT-SPECIFIC CESTY (NOVÉ)
    // =====================================================

    /**
     * Získá cestu k modulům pro konkrétní tenant
     */
    private function getTenantModulesDir(int $tenantId): string
    {
        return $this->baseModulesDir . '/tenant_' . $tenantId;
    }

    /**
     * Získá cestu k WWW modulům pro konkrétní tenant
     */
    private function getTenantWwwModulesDir(int $tenantId): string
    {
        return $this->baseWwwModulesDir . '/tenant_' . $tenantId;
    }

    /**
     * Zajistí existenci tenant adresáře
     */
    private function ensureTenantDirectories(int $tenantId): void
    {
        $tenantModulesDir = $this->getTenantModulesDir($tenantId);
        $tenantWwwDir = $this->getTenantWwwModulesDir($tenantId);
        
        if (!is_dir($tenantModulesDir)) {
            mkdir($tenantModulesDir, 0755, true);
            $this->logger->log("Vytvořen tenant adresář: $tenantModulesDir", ILogger::INFO);
        }
        
        if (!is_dir($tenantWwwDir)) {
            mkdir($tenantWwwDir, 0755, true);
            $this->logger->log("Vytvořen tenant WWW adresář: $tenantWwwDir", ILogger::INFO);
        }
    }

    // =====================================================
    // MULTI-TENANCY NASTAVENÍ
    // =====================================================

    /**
     * Nastaví kontext aktuálního uživatele pro filtrování modulů
     */
    public function setUserContext(?int $userId, ?int $tenantId, bool $isSuperAdmin = false): void
    {
        $this->currentUserId = $userId;
        $this->currentTenantId = $tenantId;
        $this->isSuperAdmin = $isSuperAdmin;
        
        // Zajistíme existenci tenant adresářů
        if ($tenantId && !$isSuperAdmin) {
            $this->ensureTenantDirectories($tenantId);
        }
        
        $this->logger->log("ModuleManager: Nastaven user context - User ID: $userId, Tenant ID: $tenantId, Super Admin: " . ($isSuperAdmin ? 'yes' : 'no'), ILogger::INFO);
    }

    // =====================================================
    // HLAVNÍ METODY PRO ZÍSKÁVÁNÍ MODULŮ (AKTUALIZOVANÉ)
    // =====================================================

    /**
     * Získání všech dostupných modulů pro super admina (ze všech tenantů)
     */
    public function getAllModules(): array
    {
        if (!$this->isSuperAdmin) {
            return [];
        }

        $allModules = [];
        
        // Projdeme všechny tenant adresáře
        if (is_dir($this->baseModulesDir)) {
            $tenantDirectories = array_diff(scandir($this->baseModulesDir), ['.', '..']);
            
            foreach ($tenantDirectories as $tenantDir) {
                if (!preg_match('/^tenant_(\d+)$/', $tenantDir, $matches)) {
                    continue; // Přeskočíme adresáře, které nejsou tenant_X
                }
                
                $tenantId = (int)$matches[1];
                $tenantModulesDir = $this->baseModulesDir . '/' . $tenantDir;
                
                if (!is_dir($tenantModulesDir)) {
                    continue;
                }
                
                $moduleDirectories = array_diff(scandir($tenantModulesDir), ['.', '..']);
                
                foreach ($moduleDirectories as $moduleDir) {
                    $moduleInfoFile = $tenantModulesDir . '/' . $moduleDir . '/module.json';
                    
                    if (file_exists($moduleInfoFile)) {
                        $moduleInfo = json_decode(file_get_contents($moduleInfoFile), true);
                        
                        if ($moduleInfo && isset($moduleInfo['id'])) {
                            // Přidáme informaci o tenant
                            $moduleInfo['tenant_id'] = $tenantId;
                            $moduleInfo['tenant_path'] = $tenantDir . '/' . $moduleDir;
                            $moduleInfo['physical_path'] = $tenantModulesDir . '/' . $moduleDir;
                            
                            // Klíč bude jedinečný pro kombinaci tenant + modul
                            $key = "tenant_{$tenantId}_{$moduleInfo['id']}";
                            $allModules[$key] = $moduleInfo;
                        }
                    }
                }
            }
        }
        
        $this->logger->log("Super admin: Načteno " . count($allModules) . " modulů ze všech tenantů", ILogger::INFO);
        
        return $allModules;
    }

    /**
     * OPRAVENÁ METODA: Získání všech nainstalovaných modulů (aktivních i neaktivních) pro aktuálního uživatele
     * Změna: Přejmenování z getActiveModules() na getAllInstalledModules() pro lepší pochopení
     */
    public function getAllInstalledModules(): array
    {
        // Pokud není nastaven user context, vrátíme prázdné pole
        if ($this->currentUserId === null) {
            $this->logger->log("ModuleManager: Nebyl nastaven user context pro getAllInstalledModules()", ILogger::WARNING);
            return [];
        }

        // Všichni uživatelé (i super admin) vidí pouze své nainstalované moduly (aktivní i neaktivní)
        return $this->getAllInstalledModulesForUser($this->currentUserId);
    }

    /**
     * ZACHOVÁNÍ PŮVODNÍ METODY: Získání pouze aktivních modulů pro zpětnou kompatibilitu
     */
    public function getActiveModules(): array
    {
        // Získáme všechny nainstalované moduly
        $allModules = $this->getAllInstalledModules();
        
        // Filtrujeme pouze aktivní
        $activeModules = array_filter($allModules, function($module) {
            return $module['is_active'] ?? false;
        });
        
        $this->logger->log("Filtrováno " . count($activeModules) . " aktivních modulů z " . count($allModules) . " celkem", ILogger::DEBUG);
        
        return $activeModules;
    }

    /**
     * NOVÁ METODA: Získá všechny nainstalované moduly (aktivní i neaktivní) pro konkrétního uživatele
     */
    public function getAllInstalledModulesForUser(int $userId): array
    {
        $this->logger->log("=== NAČÍTÁNÍ VŠECH NAINSTALOVANÝCH MODULŮ PRO UŽIVATELE $userId ===", ILogger::DEBUG);
        
        // ZMĚNA: Odstraněno where('is_active', 1) - načteme všechny nainstalované moduly
        $userModules = $this->database->table('user_modules')
            ->where('user_id', $userId)
            ->fetchAll();

        $this->logger->log("Nalezeno " . count($userModules) . " nainstalovaných modulů (aktivních i neaktivních) pro uživatele $userId", ILogger::DEBUG);

        $installedModules = [];

        foreach ($userModules as $userModule) {
            $this->logger->log("Zpracovávám modul: ID={$userModule->module_id}, tenant_id={$userModule->tenant_id}, name={$userModule->module_name}, aktivní={$userModule->is_active}", ILogger::DEBUG);
            
            // Načteme informace o modulu ze souboru (z tenant-specific adresáře)
            $moduleInfo = $this->getModuleInfoFromFile($userModule->module_id, $userModule->tenant_id);
            
            if ($moduleInfo) {
                $this->logger->log("Module info úspěšně načteno pro {$userModule->module_id}", ILogger::DEBUG);
                
                // Kombinujeme data z databáze a ze souboru
                $moduleInfo['user_module_id'] = $userModule->id;
                $moduleInfo['installed_at'] = $userModule->installed_at;
                $moduleInfo['last_used'] = $userModule->last_used;
                $moduleInfo['config_data'] = $userModule->config_data ? json_decode($userModule->config_data, true) : null;
                $moduleInfo['tenant_id'] = $userModule->tenant_id;
                $moduleInfo['physical_path'] = $this->getTenantModulesDir($userModule->tenant_id) . '/' . $userModule->module_id;
                
                // KLÍČOVÁ ZMĚNA: Přidáme stav aktivní/neaktivní z databáze
                $moduleInfo['is_active'] = (bool)$userModule->is_active;
                $moduleInfo['module_status'] = $userModule->is_active ? 'active' : 'inactive';
                
                $installedModules[$userModule->module_id] = $moduleInfo;
                $this->logger->log("Modul {$userModule->module_id} přidán do výsledků (stav: " . ($userModule->is_active ? 'aktivní' : 'neaktivní') . ")", ILogger::DEBUG);
            } else {
                $this->logger->log("CHYBA: Module info se nepodařilo načíst pro {$userModule->module_id} z tenant {$userModule->tenant_id}", ILogger::WARNING);
                
                // NOVÉ: I když se nepodařilo načíst module.json, přidáme základní informace z databáze
                $installedModules[$userModule->module_id] = [
                    'id' => $userModule->module_id,
                    'name' => $userModule->module_name,
                    'version' => $userModule->module_version,
                    'description' => 'Modul bez detailních informací (chybí module.json)',
                    'user_module_id' => $userModule->id,
                    'installed_at' => $userModule->installed_at,
                    'last_used' => $userModule->last_used,
                    'tenant_id' => $userModule->tenant_id,
                    'is_active' => (bool)$userModule->is_active,
                    'module_status' => $userModule->is_active ? 'active' : 'inactive',
                    'has_module_json' => false, // Indikátor problému
                ];
                
                $this->logger->log("Modul {$userModule->module_id} přidán s minimálními informacemi", ILogger::DEBUG);
            }
        }

        $this->logger->log("=== KONEC: Uživatel $userId má " . count($installedModules) . " nainstalovaných modulů ===", ILogger::DEBUG);

        return $installedModules;
    }

    /**
     * ZACHOVÁNO: Získá aktivní moduly pro konkrétního uživatele (pro zpětnou kompatibilitu)
     */
    public function getActiveModulesForUser(int $userId): array
    {
        $allModules = $this->getAllInstalledModulesForUser($userId);
        
        // Filtrujeme pouze aktivní
        return array_filter($allModules, function($module) {
            return $module['is_active'] ?? false;
        });
    }

    // =====================================================
    // POMOCNÉ METODY (AKTUALIZOVANÉ)
    // =====================================================

    /**
     * Načte informace o modulu ze souboru module.json (tenant-specific) - DOKONČENÁ METODA
     */
    private function getModuleInfoFromFile(string $moduleId, int $tenantId): ?array
    {
        $tenantModulesDir = $this->getTenantModulesDir($tenantId);
        $moduleInfoFile = $tenantModulesDir . '/' . $moduleId . '/module.json';
        
        $this->logger->log("Načítám modul info ze souboru: $moduleInfoFile", ILogger::DEBUG);
        
        if (!file_exists($moduleInfoFile)) {
            $this->logger->log("Soubor module.json nenalezen: $moduleInfoFile", ILogger::WARNING);
            return null;
        }
        
        $moduleInfo = json_decode(file_get_contents($moduleInfoFile), true);
        
        if (!$moduleInfo || !isset($moduleInfo['id'])) {
            $this->logger->log("Neplatný module.json soubor: $moduleInfoFile", ILogger::WARNING);
            return null;
        }
        
        $this->logger->log("Úspěšně načten modul {$moduleInfo['id']} z tenant $tenantId", ILogger::DEBUG);
        
        return $moduleInfo;
    }

    /**
     * Aktualizuje čas posledního použití modulu
     */
    public function updateLastUsed(string $moduleId, int $userId): void
    {
        $this->database->table('user_modules')
            ->where('user_id', $userId)
            ->where('module_id', $moduleId)
            ->update(['last_used' => new \DateTime()]);
    }

    // =====================================================
    // INSTALACE MODULŮ (ZACHOVANÉ PŮVODNÍ METODY)
    // =====================================================

    /**
     * Nainstaluje modul pro uživatele z nahraného souboru
     */
    public function installModuleForUser(Nette\Http\FileUpload $file, int $userId, ?int $tenantId = null, ?int $installedBy = null): array
    {
        try {
            // Pokud není zadán tenant ID, použijeme aktuální tenant z kontextu
            if ($tenantId === null) {
                $tenantId = $this->currentTenantId;
            }

            if ($tenantId === null) {
                return [
                    'success' => false,
                    'message' => 'Není možné určit tenant pro instalaci modulu'
                ];
            }

            // Zajistíme existenci tenant adresářů
            $this->ensureTenantDirectories($tenantId);

            // Rozbalení a instalace modulu (do tenant-specific adresáře)
            $installResult = $this->extractAndInstallModule($file, $tenantId);
            
            if (!$installResult['success']) {
                return $installResult;
            }
            
            $moduleConfig = $installResult['module_info'];
            
            // Kontrola, zda uživatel již nemá tento modul nainstalovaný
            $existingModule = $this->database->table('user_modules')
                ->where('user_id', $userId)
                ->where('module_id', $moduleConfig['id'])
                ->fetch();
            
            if ($existingModule) {
                // Modul již existuje, aktualizujeme jej
                $this->database->table('user_modules')
                    ->where('id', $existingModule->id)
                    ->update([
                        'module_name' => $moduleConfig['name'],
                        'module_version' => $moduleConfig['version'],
                        'module_path' => 'tenant_' . $tenantId . '/' . $moduleConfig['id'],
                        'is_active' => true, // Při reinstalaci aktivujeme
                        'installed_at' => new \DateTime(),
                        'installed_by' => $installedBy,
                        'tenant_id' => $tenantId
                    ]);
                
                $action = 'přeinstalován';
            } else {
                // Nový modul, vložíme záznam do databáze
                $this->database->table('user_modules')->insert([
                    'user_id' => $userId,
                    'module_id' => $moduleConfig['id'],
                    'module_name' => $moduleConfig['name'],
                    'module_version' => $moduleConfig['version'],
                    'module_path' => 'tenant_' . $tenantId . '/' . $moduleConfig['id'],
                    'is_active' => true,
                    'installed_at' => new \DateTime(),
                    'installed_by' => $installedBy,
                    'tenant_id' => $tenantId
                ]);
                
                $action = 'nainstalován';
            }
            
            $this->logger->log("Modul '{$moduleConfig['name']}' byl $action pro uživatele $userId v tenant $tenantId", ILogger::INFO);
            
            return [
                'success' => true,
                'message' => "Modul '{$moduleConfig['name']}' byl úspěšně $action",
                'module_info' => $moduleConfig
            ];
            
        } catch (\Exception $e) {
            $this->logger->log("Chyba při instalaci modulu pro uživatele: " . $e->getMessage(), ILogger::ERROR);
            
            return [
                'success' => false,
                'message' => 'Chyba při instalaci modulu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Rozbalí a nainstaluje modul do tenant-specific adresáře
     */
    private function extractAndInstallModule(Nette\Http\FileUpload $file, int $tenantId): array
    {
        try {
            // Kontrola souboru
            if (!$file->isOk()) {
                throw new \Exception('Chyba při nahrávání souboru');
            }
            
            if ($file->getContentType() !== 'application/zip') {
                throw new \Exception('Neplatný typ souboru. Povoleny jsou pouze ZIP soubory.');
            }
            
            // Vytvoření dočasného adresáře
            $tempDir = $this->uploadsDir . '/' . uniqid('module_', true);
            mkdir($tempDir, 0755, true);
            
            // Uložení nahraného souboru
            $zipPath = $tempDir . '/module.zip';
            $file->move($zipPath);
            
            // Rozbalení ZIP souboru
            $zip = new ZipArchive;
            if ($zip->open($zipPath) !== TRUE) {
                throw new \Exception('Nepodařilo se otevřít ZIP soubor');
            }
            
            $zip->extractTo($tempDir);
            $zip->close();
            
            // Nalezení module.json souboru
            $moduleJsonFile = $this->findModuleJsonRecursively($tempDir);
            if (!$moduleJsonFile) {
                throw new \Exception('V ZIP souboru nebyl nalezen soubor module.json');
            }
            
            // Validace module.json
            $moduleConfig = json_decode(file_get_contents($moduleJsonFile), true);
            if (!$moduleConfig || !isset($moduleConfig['id'], $moduleConfig['name'], $moduleConfig['version'])) {
                throw new \Exception('Neplatný soubor module.json');
            }
            
            // Cílový adresář v tenant-specific umístění
            $tenantModulesDir = $this->getTenantModulesDir($tenantId);
            $finalModuleDir = $tenantModulesDir . '/' . $moduleConfig['id'];
            
            // Kontrola, zda modul již neexistuje
            if (is_dir($finalModuleDir)) {
                // Smazání starého modulu
                $this->rrmdir($finalModuleDir);
            }
            
            // Přesun modulu do finálního umístění
            $moduleRootDir = dirname($moduleJsonFile);
            $this->moveDirectory($moduleRootDir, $finalModuleDir);
            
            // Nastavení modulu jako aktivní
            $moduleConfig['active'] = true;
            file_put_contents($finalModuleDir . '/module.json', json_encode($moduleConfig, JSON_PRETTY_PRINT));
            
            // Vytvoření assets v www adresáři
            $this->setupModuleAssets($moduleConfig['id'], $tenantId);
            
            // Úklid
            $this->cleanup($tempDir);
            
            $this->logger->log("Modul '{$moduleConfig['name']}' byl úspěšně nainstalován do tenant $tenantId", ILogger::INFO);
            
            return [
                'success' => true,
                'module_info' => $moduleConfig
            ];
            
        } catch (\Exception $e) {
            $this->logger->log("Chyba při rozbalení modulu: " . $e->getMessage(), ILogger::ERROR);
            
            if (isset($tempDir) && is_dir($tempDir)) {
                $this->cleanup($tempDir);
            }
            
            return [
                'success' => false,
                'message' => 'Chyba při instalaci modulu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Nastaví assets modulu pro tenant
     */
    private function setupModuleAssets(string $moduleId, int $tenantId): void
    {
        $tenantModulesDir = $this->getTenantModulesDir($tenantId);
        $tenantWwwDir = $this->getTenantWwwModulesDir($tenantId);
        
        $moduleAssetsDir = $tenantModulesDir . '/' . $moduleId . '/assets';
        $wwwModuleDir = $tenantWwwDir . '/' . $moduleId;
        
        if (is_dir($moduleAssetsDir)) {
            if (is_dir($wwwModuleDir)) {
                $this->rrmdir($wwwModuleDir);
            }
            
            $this->copyDirectory($moduleAssetsDir, $wwwModuleDir);
            $this->logger->log("Assets zkopírovány pro modul $moduleId v tenant $tenantId", ILogger::INFO);
        }
    }

    /**
     * Aktivuje/deaktivuje modul pro uživatele
     */
    public function toggleModuleForUser(string $moduleId, int $userId): array
    {
        try {
            $userModule = $this->database->table('user_modules')
                ->where('user_id', $userId)
                ->where('module_id', $moduleId)
                ->fetch();

            if (!$userModule) {
                return [
                    'success' => false,
                    'message' => 'Nemáte tento modul nainstalovaný'
                ];
            }

            // Přepneme stav
            $newStatus = !$userModule->is_active;
            $this->database->table('user_modules')
                ->where('id', $userModule->id)
                ->update(['is_active' => $newStatus]);

            $status = $newStatus ? 'aktivován' : 'deaktivován';
            
            $this->logger->log("Modul '$moduleId' byl $status pro uživatele $userId", ILogger::INFO);
            
            return [
                'success' => true,
                'message' => "Modul '{$userModule->module_name}' byl $status",
                'new_status' => $newStatus
            ];

        } catch (\Exception $e) {
            $this->logger->log("Chyba při přepínání modulu: " . $e->getMessage(), ILogger::ERROR);
            
            return [
                'success' => false,
                'message' => 'Chyba při přepínání modulu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Odinstaluje modul pro uživatele
     */
    public function uninstallModuleForUser(string $moduleId, int $userId): array
    {
        try {
            $userModule = $this->database->table('user_modules')
                ->where('user_id', $userId)
                ->where('module_id', $moduleId)
                ->fetch();

            if (!$userModule) {
                return [
                    'success' => false,
                    'message' => 'Nemáte tento modul nainstalovaný'
                ];
            }

            // Ověření, zda jiný uživatel nemá stejný modul ve stejném tenantu
            $otherUsersWithModule = $this->database->table('user_modules')
                ->where('module_id', $moduleId)
                ->where('tenant_id', $userModule->tenant_id)
                ->where('user_id != ?', $userId)
                ->count();

            // Smazání záznamu z databáze
            $this->database->table('user_modules')
                ->where('id', $userModule->id)
                ->delete();

            // Pokud žádný jiný uživatel v tomto tenantu nemá tento modul, smažeme i fyzické soubory
            if ($otherUsersWithModule === 0) {
                $this->removeModuleFiles($moduleId, $userModule->tenant_id);
                $this->logger->log("Fyzické soubory modulu '$moduleId' byly smazány z tenant {$userModule->tenant_id}", ILogger::INFO);
            } else {
                $this->logger->log("Fyzické soubory modulu '$moduleId' ponechány - používá je $otherUsersWithModule dalších uživatelů", ILogger::INFO);
            }
            
            $this->logger->log("Modul '$moduleId' byl odinstalován pro uživatele $userId", ILogger::INFO);
            
            return [
                'success' => true,
                'message' => "Modul '{$userModule->module_name}' byl úspěšně odinstalován"
            ];

        } catch (\Exception $e) {
            $this->logger->log("Chyba při odinstalaci modulu: " . $e->getMessage(), ILogger::ERROR);
            
            return [
                'success' => false,
                'message' => 'Chyba při odinstalaci modulu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Odstraní fyzické soubory modulu
     */
    private function removeModuleFiles(string $moduleId, int $tenantId): void
    {
        $tenantModulesDir = $this->getTenantModulesDir($tenantId);
        $tenantWwwDir = $this->getTenantWwwModulesDir($tenantId);
        
        $moduleDir = $tenantModulesDir . '/' . $moduleId;
        $wwwModuleDir = $tenantWwwDir . '/' . $moduleId;
        
        if (is_dir($moduleDir)) {
            $this->rrmdir($moduleDir);
        }
        
        if (is_dir($wwwModuleDir)) {
            $this->rrmdir($wwwModuleDir);
        }
    }

    // =====================================================
    // POMOCNÉ UTILITY METODY
    // =====================================================

    /**
     * Rekurzivně najde module.json soubor
     */
    private function findModuleJsonRecursively(string $dir): ?string
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->getFilename() === 'module.json') {
                return $file->getPathname();
            }
        }
        
        return null;
    }

    /**
     * Přesune adresář
     */
    private function moveDirectory(string $source, string $destination): bool
    {
        if (!is_dir($source)) {
            return false;
        }
        
        if (!is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0755, true);
        }
        
        return rename($source, $destination);
    }

    /**
     * Vyčistí dočasný adresář
     */
    private function cleanup(string $dir): void
    {
        if (is_dir($dir)) {
            $this->rrmdir($dir);
        }
    }

    /**
     * Rekurzivně smaže adresář
     */
    private function rrmdir(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->rrmdir($path);
            } else {
                unlink($path);
            }
        }
        
        return rmdir($dir);
    }

    /**
     * Kopíruje adresář rekurzivně
     */
    private function copyDirectory(string $source, string $destination): bool
    {
        if (!is_dir($source)) {
            return false;
        }
        
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $files = array_diff(scandir($source), ['.', '..']);
        foreach ($files as $file) {
            $sourcePath = $source . '/' . $file;
            $destPath = $destination . '/' . $file;
            
            if (is_dir($sourcePath)) {
                $this->copyDirectory($sourcePath, $destPath);
            } else {
                copy($sourcePath, $destPath);
            }
        }
        
        return true;
    }
}