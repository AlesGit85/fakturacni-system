<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Tracy\ILogger;
use ZipArchive;

/**
 * Třída pro správu modulů
 */
class ModuleManager
{
    use Nette\SmartObject;

    /** @var ILogger */
    private $logger;
    
    /** @var string Cesta k adresáři s moduly */
    private $modulesDir;
    
    /** @var string Cesta k adresáři pro dočasné nahrávání souborů */
    private $uploadsDir;
    
    /** @var string Cesta k adresáři assets v WWW */
    private $wwwModulesDir;

    /**
     * Konstruktor třídy
     */
    public function __construct(ILogger $logger)
    {
        $this->logger = $logger;
        $this->modulesDir = dirname(__DIR__) . '/Modules';
        $this->uploadsDir = dirname(__DIR__, 2) . '/temp/module_uploads';
        $this->wwwModulesDir = dirname(__DIR__, 2) . '/www/Modules';
        
        // Vytvoření adresářů, pokud neexistují
        if (!is_dir($this->modulesDir)) {
            mkdir($this->modulesDir, 0755, true);
        }
        
        if (!is_dir($this->uploadsDir)) {
            mkdir($this->uploadsDir, 0755, true);
        }
        
        if (!is_dir($this->wwwModulesDir)) {
            mkdir($this->wwwModulesDir, 0755, true);
        }
        
        $this->logger->log("ModuleManager byl inicializován", ILogger::INFO);
    }

    /**
     * Získání všech dostupných modulů
     */
    public function getAllModules(): array
    {
        $modules = [];
        
        // Nejprve přidáme testovací modul
        $modules['test_module'] = [
            'id' => 'test_module',
            'name' => 'Testovací modul',
            'version' => '1.0.0',
            'description' => 'Základní test',
            'author' => 'System',
            'active' => true,
            'icon' => 'bi bi-star-fill'
        ];
        
        // Načtení všech modulů z adresáře
        if (is_dir($this->modulesDir)) {
            $moduleDirectories = array_diff(scandir($this->modulesDir), ['.', '..', 'test_module']);
            
            foreach ($moduleDirectories as $moduleDir) {
                $moduleInfoFile = $this->modulesDir . '/' . $moduleDir . '/module.json';
                
                if (file_exists($moduleInfoFile)) {
                    $moduleInfo = json_decode(file_get_contents($moduleInfoFile), true);
                    
                    if ($moduleInfo && isset($moduleInfo['id'])) {
                        $modules[$moduleInfo['id']] = $moduleInfo;
                    }
                }
            }
        }
        
        return $modules;
    }

    /**
     * Získání aktivních modulů
     */
    public function getActiveModules(): array
    {
        $allModules = $this->getAllModules();
        $activeModules = [];
        
        foreach ($allModules as $id => $module) {
            if (isset($module['active']) && $module['active']) {
                $activeModules[$id] = $module;
            }
        }
        
        return $activeModules;
    }
    
    /**
     * Instalace nového modulu
     * 
     * @param Nette\Http\FileUpload $file Nahraný ZIP soubor
     * @return array Výsledek operace
     */
    public function installModule(Nette\Http\FileUpload $file): array
    {
        try {
            $this->logger->log("Začíná instalace modulu ze souboru: " . $file->getName(), ILogger::INFO);
            
            // Kontrola, zda je soubor platný ZIP
            if (!$file->isOk() || $file->getContentType() !== 'application/zip') {
                return [
                    'success' => false,
                    'message' => 'Nahraný soubor není platný ZIP archiv'
                ];
            }
            
            // Vytvoření dočasného adresáře pro extrakci
            $tempDir = $this->uploadsDir . '/' . uniqid('module_');
            mkdir($tempDir, 0755, true);
            
            // Dočasné uložení ZIP souboru
            $zipFile = $tempDir . '/' . $file->getSanitizedName();
            $file->move($zipFile);
            
            // Rozbalení archivu
            $zip = new ZipArchive();
            if ($zip->open($zipFile) !== true) {
                $this->cleanup($tempDir);
                return [
                    'success' => false,
                    'message' => 'Nelze otevřít ZIP archiv'
                ];
            }
            
            $extractDir = $tempDir . '/extracted';
            mkdir($extractDir, 0755);
            $zip->extractTo($extractDir);
            $zip->close();
            
            // Hledání module.json
            $moduleJsonFile = $this->findModuleJson($extractDir);
            
            if (!$moduleJsonFile) {
                $this->cleanup($tempDir);
                return [
                    'success' => false,
                    'message' => 'Archiv neobsahuje platný soubor module.json'
                ];
            }
            
            // Načtení konfigurace modulu
            $moduleConfig = json_decode(file_get_contents($moduleJsonFile), true);
            
            if (!$moduleConfig || !isset($moduleConfig['id']) || !isset($moduleConfig['name'])) {
                $this->cleanup($tempDir);
                return [
                    'success' => false,
                    'message' => 'Soubor module.json neobsahuje platnou konfiguraci'
                ];
            }
            
            $moduleId = $moduleConfig['id'];
            
            // Kontrola, zda modul již existuje
            if (is_dir($this->modulesDir . '/' . $moduleId)) {
                $this->cleanup($tempDir);
                return [
                    'success' => false,
                    'message' => 'Modul s ID "' . $moduleId . '" již existuje'
                ];
            }
            
            // Získání složky, ve které je module.json
            $moduleRootDir = dirname($moduleJsonFile);
            
            // Přesun modulu do finálního umístění
            $this->moveDirectory($moduleRootDir, $this->modulesDir . '/' . $moduleId);
            
            // Nastavení modulu jako aktivní
            $moduleConfig['active'] = true;
            file_put_contents($this->modulesDir . '/' . $moduleId . '/module.json', json_encode($moduleConfig, JSON_PRETTY_PRINT));
            
            // Vytvoření symlinku pro assets v www adresáři
            $moduleAssetsDir = $this->modulesDir . '/' . $moduleId . '/assets';
            $wwwModuleDir = $this->wwwModulesDir . '/' . $moduleId;
            
            if (is_dir($moduleAssetsDir)) {
                if (is_dir($wwwModuleDir)) {
                    $this->rrmdir($wwwModuleDir); // Odstranění existujícího adresáře
                }
                
                // Kopírování assets místo vytváření symlinku (kvůli kompatibilitě s Windows)
                $this->copyDirectory($moduleAssetsDir, $wwwModuleDir);
            }
            
            // Úklid dočasných souborů
            $this->cleanup($tempDir);
            
            $this->logger->log("Modul '{$moduleConfig['name']}' byl úspěšně nainstalován", ILogger::INFO);
            return [
                'success' => true,
                'message' => "Modul '{$moduleConfig['name']}' byl úspěšně nainstalován"
            ];
            
        } catch (\Exception $e) {
            $this->logger->log("Chyba při instalaci modulu: " . $e->getMessage(), ILogger::ERROR);
            
            // Úklid v případě chyby
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
     * Aktivace/deaktivace modulu
     * 
     * @param string $moduleId ID modulu
     * @return array Výsledek operace
     */
    public function toggleModule(string $moduleId): array
    {
        try {
            $moduleDir = $this->modulesDir . '/' . $moduleId;
            $moduleJsonFile = $moduleDir . '/module.json';
            
            if (!is_dir($moduleDir) || !file_exists($moduleJsonFile)) {
                return [
                    'success' => false,
                    'message' => 'Modul s ID "' . $moduleId . '" neexistuje'
                ];
            }
            
            $moduleConfig = json_decode(file_get_contents($moduleJsonFile), true);
            
            if (!$moduleConfig) {
                return [
                    'success' => false,
                    'message' => 'Neplatná konfigurace modulu'
                ];
            }
            
            // Přepnutí stavu modulu
            $moduleConfig['active'] = !($moduleConfig['active'] ?? false);
            
            // Uložení změn
            file_put_contents($moduleJsonFile, json_encode($moduleConfig, JSON_PRETTY_PRINT));
            
            $status = $moduleConfig['active'] ? 'aktivován' : 'deaktivován';
            
            $this->logger->log("Modul '{$moduleConfig['name']}' byl $status", ILogger::INFO);
            return [
                'success' => true,
                'message' => "Modul '{$moduleConfig['name']}' byl $status"
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
     * Odinstalace modulu
     * 
     * @param string $moduleId ID modulu
     * @return array Výsledek operace
     */
    public function uninstallModule(string $moduleId): array
    {
        try {
            $moduleDir = $this->modulesDir . '/' . $moduleId;
            $moduleJsonFile = $moduleDir . '/module.json';
            
            if (!is_dir($moduleDir) || !file_exists($moduleJsonFile)) {
                return [
                    'success' => false,
                    'message' => 'Modul s ID "' . $moduleId . '" neexistuje'
                ];
            }
            
            $moduleConfig = json_decode(file_get_contents($moduleJsonFile), true);
            $moduleName = $moduleConfig['name'] ?? $moduleId;
            
            // Odstranění adresáře modulu
            $this->rrmdir($moduleDir);
            
            // Odstranění symlinku pro assets
            $wwwModuleDir = $this->wwwModulesDir . '/' . $moduleId;
            if (is_dir($wwwModuleDir)) {
                $this->rrmdir($wwwModuleDir);
            }
            
            $this->logger->log("Modul '$moduleName' byl odinstalován", ILogger::INFO);
            return [
                'success' => true,
                'message' => "Modul '$moduleName' byl odinstalován"
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
     * Rekurzivně hledá soubor module.json v adresáři
     * 
     * @param string $dir Adresář pro hledání
     * @return string|null Cesta k souboru module.json nebo null
     */
    private function findModuleJson(string $dir): ?string
    {
        $files = scandir($dir);
        
        // Nejprve hledáme přímo v aktuálním adresáři
        if (in_array('module.json', $files)) {
            return $dir . '/module.json';
        }
        
        // Potom hledáme v podadresářích
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                $result = $this->findModuleJson($path);
                if ($result) {
                    return $result;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Přesune adresář z jednoho umístění do druhého
     * 
     * @param string $source Zdrojový adresář
     * @param string $dest Cílový adresář
     */
    private function moveDirectory(string $source, string $dest): void
    {
        // Vytvoření cílového adresáře, pokud neexistuje
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        
        // Implementace bez použití getSubPathname()
        $this->recursiveCopy($source, $dest);
    }
    
    /**
     * Kopíruje adresář z jednoho umístění do druhého
     * 
     * @param string $source Zdrojový adresář
     * @param string $dest Cílový adresář
     */
    private function copyDirectory(string $source, string $dest): void
    {
        // Vytvoření cílového adresáře, pokud neexistuje
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        
        // Implementace bez použití getSubPathname()
        $this->recursiveCopy($source, $dest);
    }
    
    /**
     * Rekurzivně kopíruje obsah adresáře
     * 
     * @param string $src Zdrojový adresář
     * @param string $dst Cílový adresář
     */
    private function recursiveCopy(string $src, string $dst): void
    {
        $dir = opendir($src);
        
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;
            
            if (is_dir($srcFile)) {
                if (!is_dir($dstFile)) {
                    mkdir($dstFile, 0755, true);
                }
                $this->recursiveCopy($srcFile, $dstFile);
            } else {
                copy($srcFile, $dstFile);
            }
        }
        
        closedir($dir);
    }
    
    /**
     * Rekurzivně odstraní adresář a jeho obsah
     * 
     * @param string $dir Adresář k odstranění
     */
    private function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object === '.' || $object === '..') {
                continue;
            }
            
            $path = $dir . '/' . $object;
            
            if (is_dir($path)) {
                $this->rrmdir($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }
    
    /**
     * Odstraní dočasné soubory
     * 
     * @param string $dir Adresář k odstranění
     */
    private function cleanup(string $dir): void
    {
        $this->rrmdir($dir);
    }
}