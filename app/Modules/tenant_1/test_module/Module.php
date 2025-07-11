<?php

declare(strict_types=1);

namespace Modules\Test_module;

use App\Modules\BaseModule;

/**
 * Testovací modul pro ověření funkčnosti systému modulů
 * Umožňuje uživatelům vyzkoušet si, jak jednoduché je instalovat a spravovat moduly
 */
class Module extends BaseModule
{
    /**
     * Verze modulu
     */
    const VERSION = '1.4.2';
    
    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        $this->log('Inicializace testovacího modulu v' . self::VERSION);
        
        // Vytvoření potřebných adresářů pro assets
        $this->ensureAssetsDirectory();
    }
    
    /**
     * {@inheritdoc}
     */
    public function activate(): void
    {
        $this->log('Aktivace testovacího modulu');
        
        // Při aktivaci můžeme spustit nějaké úkoly
        $this->runActivationTasks();
    }
    
    /**
     * {@inheritdoc}
     */
    public function deactivate(): void
    {
        $this->log('Deaktivace testovacího modulu');
        
        // Cleanup při deaktivaci, ale zachováme data
    }
    
    /**
     * {@inheritdoc}
     */
    public function uninstall(): void
    {
        $this->log('Odinstalace testovacího modulu');
        
        // Kompletní cleanup při odinstalaci
        $this->cleanupModuleData();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMenuItems(): array
    {
        return [
            [
                'presenter' => 'ModuleAdmin',
                'action' => 'detail',
                'params' => ['id' => 'test_module'],
                'label' => 'Test modul',
                'icon' => 'bi bi-star-fill'
            ]
        ];
    }
    
    /**
     * Získání verze modulu
     */
    public function getVersion(): string
    {
        return self::VERSION;
    }
    
    /**
     * Získání ID modulu (povinné z IModule)
     */
    public function getId(): string
    {
        return 'test_module';
    }
    
    /**
     * Získání ikony modulu (povinné z IModule)
     */
    public function getIcon(): string
    {
        return 'bi bi-star-fill';
    }
    
    /**
     * Získání cesty k dashboard template (povinné z IModule)
     */
    public function getDashboardTemplate(): ?string
    {
        return __DIR__ . '/templates/dashboard.latte';
    }
    
    /**
     * Zpracování AJAX požadavků (povinné z IModule)
     */
    public function handleAjaxRequest(string $action, array $parameters = [], array $dependencies = [])
    {
        $this->log("AJAX požadavek: $action");
        
        switch ($action) {
            case 'runTest':
                return $this->runModuleTest();
            
            case 'getTestData':
                return $this->getTestData();
                
            default:
                throw new \Exception("Nepodporovaná AJAX akce: $action");
        }
    }
    
    /**
     * Získání názvu modulu
     */
    public function getName(): string
    {
        return 'Testovací modul';
    }
    
    /**
     * Získání popisu modulu
     */
    public function getDescription(): string
    {
        return 'Modul pro ukázku jednoduché instalace a správy modulů v systému';
    }
    
    /**
     * Získání autora modulu
     */
    public function getAuthor(): string
    {
        return 'QRdoklad System';
    }
    
    /**
     * Získání testovacích dat pro dashboard
     */
    public function getTestData(): array
    {
        return [
            'message' => 'Testovací modul funguje správně!',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => $this->getVersion(),
            'author' => $this->getAuthor(),
            'installation_time' => $this->getInstallationTime(),
            'features' => [
                'Vlastní dashboard template',
                'CSS styly s animacemi',
                'JavaScript funkcionalita',
                'Integrace do hlavního menu',
                'Ukázka správy modulů',
                'Demo testovací funkce'
            ],
            'stats' => [
                'files_count' => $this->countModuleFiles(),
                'size' => $this->getModuleSize(),
                'active_since' => $this->getActiveSince()
            ]
        ];
    }
    
    /**
     * Spuštění testu modulu
     */
    public function runModuleTest(): array
    {
        $testResults = [
            'success' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'tests' => []
        ];
        
        // Test 1: Kontrola souborů
        $testResults['tests']['files'] = $this->testModuleFiles();
        
        // Test 2: Kontrola menu integrace
        $testResults['tests']['menu'] = $this->testMenuIntegration();
        
        // Test 3: Kontrola CSS a JS
        $testResults['tests']['assets'] = $this->testAssets();
        
        // Test 4: Kontrola funkcionalit
        $testResults['tests']['functionality'] = $this->testFunctionality();
        
        // Celkový výsledek
        $testResults['success'] = array_reduce($testResults['tests'], function($carry, $test) {
            return $carry && $test['success'];
        }, true);
        
        $this->log('Test modulu dokončen: ' . ($testResults['success'] ? 'ÚSPĚCH' : 'CHYBA'));
        
        return $testResults;
    }
    
    /**
     * Zajištění existence adresáře pro assets
     */
    private function ensureAssetsDirectory(): void
    {
        $assetsDir = dirname(__DIR__, 3) . '/www/Modules/test_module/assets';
        
        if (!is_dir($assetsDir)) {
            mkdir($assetsDir, 0755, true);
            $this->log('Vytvořen adresář pro assets: ' . $assetsDir);
        }
    }
    
    /**
     * Spuštění úkolů při aktivaci
     */
    private function runActivationTasks(): void
    {
        // Příprava demonstračních dat
        $demoData = [
            'activated_at' => date('Y-m-d H:i:s'),
            'version' => $this->getVersion(),
            'demo_mode' => true
        ];
        
        // Uložení do souboru pro účely demo
        $configFile = __DIR__ . '/config/demo_config.json';
        $configDir = dirname($configFile);
        
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }
        
        file_put_contents($configFile, json_encode($demoData, JSON_PRETTY_PRINT));
        
        $this->log('Demo konfigurace vytvořena');
    }
    
    /**
     * Cleanup dat modulu
     */
    private function cleanupModuleData(): void
    {
        $configFile = __DIR__ . '/config/demo_config.json';
        
        if (file_exists($configFile)) {
            unlink($configFile);
            $this->log('Demo konfigurace smazána');
        }
        
        // Smažeme i celý config adresář pokud je prázdný
        $configDir = dirname($configFile);
        if (is_dir($configDir) && count(scandir($configDir)) == 2) {
            rmdir($configDir);
        }
    }
    
    /**
     * Test souborů modulu
     */
    private function testModuleFiles(): array
    {
        $requiredFiles = [
            'Module.php',
            'module.json',
            'templates/dashboard.latte'
        ];
        
        $result = ['success' => true, 'details' => []];
        
        foreach ($requiredFiles as $file) {
            $filePath = __DIR__ . '/' . $file;
            $exists = file_exists($filePath);
            
            $result['details'][$file] = [
                'exists' => $exists,
                'size' => $exists ? filesize($filePath) : 0
            ];
            
            if (!$exists) {
                $result['success'] = false;
            }
        }
        
        return $result;
    }
    
    /**
     * Test menu integrace
     */
    private function testMenuIntegration(): array
    {
        $menuItems = $this->getMenuItems();
        
        return [
            'success' => !empty($menuItems),
            'count' => count($menuItems),
            'items' => $menuItems
        ];
    }
    
    /**
     * Test assets (CSS a JS)
     */
    private function testAssets(): array
    {
        $assetsPath = dirname(__DIR__, 3) . '/www/Modules/test_module/assets';
        
        $cssExists = file_exists($assetsPath . '/css/style.css');
        $jsExists = file_exists($assetsPath . '/js/script.js');
        
        return [
            'success' => $cssExists && $jsExists,
            'css' => $cssExists,
            'js' => $jsExists,
            'path' => $assetsPath
        ];
    }
    
    /**
     * Test základní funkcionality
     */
    private function testFunctionality(): array
    {
        $tests = [
            'version' => !empty($this->getVersion()),
            'name' => !empty($this->getName()),
            'description' => !empty($this->getDescription()),
            'test_data' => !empty($this->getTestData())
        ];
        
        return [
            'success' => !in_array(false, $tests),
            'details' => $tests
        ];
    }
    
    /**
     * Počet souborů modulu
     */
    private function countModuleFiles(): int
    {
        $count = 0;
        $directory = new \RecursiveDirectoryIterator(__DIR__);
        $iterator = new \RecursiveIteratorIterator($directory);
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Velikost modulu
     */
    private function getModuleSize(): string
    {
        $size = 0;
        $directory = new \RecursiveDirectoryIterator(__DIR__);
        $iterator = new \RecursiveIteratorIterator($directory);
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        // Konverze na čitelný formát
        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1024 * 1024) {
            return round($size / 1024, 1) . ' KB';
        } else {
            return round($size / (1024 * 1024), 1) . ' MB';
        }
    }
    
    /**
     * Čas instalace
     */
    private function getInstallationTime(): string
    {
        $configFile = __DIR__ . '/config/demo_config.json';
        
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            return $config['activated_at'] ?? 'Neznámé';
        }
        
        return 'Neznámé';
    }
    
    /**
     * Doba od aktivace
     */
    private function getActiveSince(): string
    {
        $installTime = $this->getInstallationTime();
        
        if ($installTime === 'Neznámé') {
            return 'Neznámé';
        }
        
        $diff = time() - strtotime($installTime);
        
        if ($diff < 60) {
            return $diff . ' sekund';
        } elseif ($diff < 3600) {
            return round($diff / 60) . ' minut';
        } elseif ($diff < 86400) {
            return round($diff / 3600) . ' hodin';
        } else {
            return round($diff / 86400) . ' dní';
        }
    }
    
    /**
     * Logování událostí modulu
     */
    protected function log(string $message, string $level = 'INFO'): void
    {
        error_log('[TestModule] ' . $message);
    }
}