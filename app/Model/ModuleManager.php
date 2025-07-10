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
        $this->baseWwwModulesDir = dirname(__DIR__, 2) . '/www/Modules';
        
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
                            
                            $key = "tenant_{$tenantId}_{$moduleInfo['id']}";
                            $allModules[$key] = $moduleInfo;
                        }
                    }
                }
            }
        }
        
        return $allModules;
    }

    /**
     * Získání aktivních modulů pro aktuálního uživatele
     */
    public function getActiveModules(): array
    {
        // Pokud není nastaven user context, vrátíme prázdné pole
        if ($this->currentUserId === null) {
            $this->logger->log("ModuleManager: Nebyl nastaven user context pro getActiveModules()", ILogger::WARNING);
            return [];
        }

        // Super admin vidí všechny moduly ze všech tenantů (upraveno)
        if ($this->isSuperAdmin) {
            return $this->getAllModulesForSuperAdmin();
        }

        // Normální uživatelé vidí pouze své aktivní moduly
        return $this->getActiveModulesForUser($this->currentUserId);
    }

    /**
     * Získá aktivní moduly pro konkrétního uživatele
     */
    public function getActiveModulesForUser(int $userId): array
    {
        $userModules = $this->database->table('user_modules')
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->fetchAll();

        $activeModules = [];

        foreach ($userModules as $userModule) {
            // Načteme informace o modulu ze souboru (z tenant-specific adresáře)
            $moduleInfo = $this->getModuleInfoFromFile($userModule->module_id, $userModule->tenant_id);
            
            if ($moduleInfo) {
                // Kombinujeme data z databáze a ze souboru
                $moduleInfo['user_module_id'] = $userModule->id;
                $moduleInfo['installed_at'] = $userModule->installed_at;
                $moduleInfo['last_used'] = $userModule->last_used;
                $moduleInfo['config_data'] = $userModule->config_data ? json_decode($userModule->config_data, true) : null;
                
                $activeModules[$userModule->module_id] = $moduleInfo;
            }
        }

        $this->logger->log("Načteno " . count($activeModules) . " aktivních modulů pro uživatele $userId", ILogger::INFO);
        return $activeModules;
    }

    /**
     * Získá všechny moduly pro super admina (kombinuje databázi + filesystem)
     */
    private function getAllModulesForSuperAdmin(): array
    {
        // Kombinujeme moduly z databáze se všemi dostupnými moduly
        $allModules = $this->getAllModules(); // Z filesystému (všechny tenanty)
        $userModules = $this->database->table('user_modules')->fetchAll();

        // Přidáme informace o tom, kdo má jaké moduly nainstalované
        foreach ($userModules as $userModule) {
            $moduleKey = "tenant_{$userModule->tenant_id}_{$userModule->module_id}";
            
            if (isset($allModules[$moduleKey])) {
                // Přidáme informace o instalacích
                if (!isset($allModules[$moduleKey]['installations'])) {
                    $allModules[$moduleKey]['installations'] = [];
                }
                
                $allModules[$moduleKey]['installations'][] = [
                    'user_id' => $userModule->user_id,
                    'tenant_id' => $userModule->tenant_id,
                    'is_active' => $userModule->is_active,
                    'installed_at' => $userModule->installed_at,
                    'last_used' => $userModule->last_used
                ];
            }
        }

        return $allModules;
    }

    // =====================================================
    // METODY PRO SPRÁVU UŽIVATELSKÝCH MODULŮ (AKTUALIZOVANÉ)
    // =====================================================

    /**
     * Nainstaluje modul pro konkrétního uživatele (z nahraného ZIP)
     */
    public function installModuleForUser(Nette\Http\FileUpload $file, int $userId, ?int $installedBy = null): array
    {
        try {
            // Získáme informace o uživateli pro tenant_id
            $user = $this->database->table('users')->get($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Uživatel neexistuje'
                ];
            }

            $tenantId = $user->tenant_id;
            if (!$tenantId) {
                return [
                    'success' => false,
                    'message' => 'Uživatel nemá přiřazen tenant'
                ];
            }

            // Zajistíme existenci tenant adresářů
            $this->ensureTenantDirectories($tenantId);

            // Rozbalení a instalace modulu (do tenant-specific adresáře)
            $installResult = $this->extractAndInstallModule($file, $tenantId);
            
            if (!$installResult['success']) {
                return $installResult;
            }

            $moduleInfo = $installResult['module_info'];
            $moduleId = $moduleInfo['id'];

            // Zkontrolujeme, zda už uživatel modul nemá
            $existingModule = $this->database->table('user_modules')
                ->where('user_id', $userId)
                ->where('module_id', $moduleId)
                ->fetch();

            if ($existingModule) {
                // Smažeme fyzické soubory, které jsme právě nainstalovali
                $this->removeModuleFiles($moduleId, $tenantId);
                
                return [
                    'success' => false,
                    'message' => 'Už máte tento modul nainstalovaný'
                ];
            }

            // Vložíme záznam do databáze
            $this->database->table('user_modules')->insert([
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'module_id' => $moduleId,
                'module_name' => $moduleInfo['name'] ?? $moduleId,
                'module_version' => $moduleInfo['version'] ?? '1.0.0',
                'module_path' => "Modules/tenant_{$tenantId}/{$moduleId}",
                'is_active' => 1,
                'installed_at' => new \DateTime(),
                'installed_by' => $installedBy,
                'config_data' => null,
                'last_used' => null
            ]);

            $this->logger->log("Modul '$moduleId' byl nainstalován pro uživatele $userId v tenant $tenantId", ILogger::INFO);
            
            return [
                'success' => true,
                'message' => "Modul '{$moduleInfo['name']}' byl úspěšně nainstalován",
                'module_id' => $moduleId
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
            $this->logger->log("Začíná instalace modulu ze souboru: " . $file->getName() . " pro tenant $tenantId", ILogger::INFO);
            
            // Kontrola souboru
            if (!$file->isOk()) {
                return ['success' => false, 'message' => 'Chyba při nahrávání souboru'];
            }
            
            if ($file->getContentType() !== 'application/zip' && !str_ends_with($file->getName(), '.zip')) {
                return ['success' => false, 'message' => 'Podporovány jsou pouze ZIP soubory'];
            }
            
            // Dočasný adresář
            $tempDir = $this->uploadsDir . '/' . uniqid('module_');
            mkdir($tempDir, 0755, true);
            
            // Přesun a rozbalení
            $zipPath = $tempDir . '/' . $file->getName();
            $file->move($zipPath);
            
            $zip = new ZipArchive;
            if ($zip->open($zipPath) !== TRUE) {
                $this->cleanup($tempDir);
                return ['success' => false, 'message' => 'Nepodařilo se otevřít ZIP soubor'];
            }
            
            $zip->extractTo($tempDir);
            $zip->close();
            
            // Hledání module.json
            $moduleJsonFile = $this->findModuleJson($tempDir);
            if (!$moduleJsonFile) {
                $this->cleanup($tempDir);
                return ['success' => false, 'message' => 'V modulu nebyl nalezen soubor module.json'];
            }
            
            // Načtení konfigurace
            $moduleConfig = json_decode(file_get_contents($moduleJsonFile), true);
            if (!$moduleConfig || !isset($moduleConfig['id'])) {
                $this->cleanup($tempDir);
                return ['success' => false, 'message' => 'Neplatný soubor module.json'];
            }
            
            $moduleId = $moduleConfig['id'];
            $tenantModulesDir = $this->getTenantModulesDir($tenantId);
            $finalModuleDir = $tenantModulesDir . '/' . $moduleId;
            
            // Kontrola, zda modul v tenant adresáři už neexistuje
            if (is_dir($finalModuleDir)) {
                $this->cleanup($tempDir);
                return [
                    'success' => false,
                    'message' => 'Modul s ID "' . $moduleId . '" již existuje v tomto tenant'
                ];
            }
            
            // Přesun modulu do finálního umístění
            $moduleRootDir = dirname($moduleJsonFile);
            $this->moveDirectory($moduleRootDir, $finalModuleDir);
            
            // Nastavení modulu jako aktivní
            $moduleConfig['active'] = true;
            file_put_contents($finalModuleDir . '/module.json', json_encode($moduleConfig, JSON_PRETTY_PRINT));
            
            // Vytvoření assets v www adresáři
            $this->setupModuleAssets($moduleId, $tenantId);
            
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
                'message' => "Modul '{$userModule->module_name}' byl $status"
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

            $tenantId = $userModule->tenant_id;

            // Smazání záznamu z databáze
            $this->database->table('user_modules')
                ->where('id', $userModule->id)
                ->delete();

            // Smazání fyzických souborů modulu
            $this->removeModuleFiles($moduleId, $tenantId);

            $this->logger->log("Modul '$moduleId' byl odinstalován pro uživatele $userId", ILogger::INFO);
            
            return [
                'success' => true,
                'message' => "Modul '{$userModule->module_name}' byl odinstalován"
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
    // POMOCNÉ METODY (AKTUALIZOVANÉ)
    // =====================================================

    /**
     * Načte informace o modulu ze souboru module.json (tenant-specific)
     */
    private function getModuleInfoFromFile(string $moduleId, int $tenantId): ?array
    {
        $tenantModulesDir = $this->getTenantModulesDir($tenantId);
        $moduleInfoFile = $tenantModulesDir . '/' . $moduleId . '/module.json';
        
        if (!file_exists($moduleInfoFile)) {
            return null;
        }
        
        $moduleInfo = json_decode(file_get_contents($moduleInfoFile), true);
        
        return $moduleInfo && isset($moduleInfo['id']) ? $moduleInfo : null;
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
    // ZACHOVANÉ PŮVODNÍ METODY PRO ZPĚTNOU KOMPATIBILITU
    // =====================================================

    /**
     * Starý způsob instalace (pro zpětnou kompatibilitu)
     * POZOR: Tato metoda je deprecated!
     */
    public function installModule(Nette\Http\FileUpload $file): array
    {
        if ($this->currentUserId && $this->currentTenantId) {
            return $this->installModuleForUser($file, $this->currentUserId, null);
        }
        
        return [
            'success' => false,
            'message' => 'Není nastaven kontext uživatele pro instalaci modulu'
        ];
    }

    // =====================================================
    // POMOCNÉ METODY (ZACHOVANÉ)
    // =====================================================

    private function findModuleJson(string $directory): ?string
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === 'module.json') {
                return $file->getPathname();
            }
        }
        
        return null;
    }

    private function moveDirectory(string $source, string $destination): void
    {
        if (!is_dir($source)) {
            throw new \Exception("Zdrojový adresář neexistuje: $source");
        }
        
        if (is_dir($destination)) {
            $this->rrmdir($destination);
        }
        
        if (!rename($source, $destination)) {
            throw new \Exception("Nepodařilo se přesunout adresář z $source do $destination");
        }
    }

    private function copyDirectory(string $source, string $destination): void
    {
        if (!is_dir($source)) {
            return;
        }
        
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($files as $file) {
            $relativePath = str_replace($source . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $targetPath = $destination . DIRECTORY_SEPARATOR . $relativePath;
            
            if ($file->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                $targetDir = dirname($targetPath);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                copy($file->getPathname(), $targetPath);
            }
        }
    }

    private function rrmdir(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        
        rmdir($directory);
    }

    private function cleanup(string $directory): void
    {
        if (is_dir($directory)) {
            $this->rrmdir($directory);
        }
    }
}