<?php

declare(strict_types=1);

namespace App\Presentation\ModuleAdmin;

use Nette;
use Nette\Application\UI\Form;
use App\Model\ModuleManager;
use App\Model\InvoicesManager;
use App\Model\CompanyManager;
use App\Presentation\BasePresenter;
use Tracy\ILogger;

final class ModuleAdminPresenter extends BasePresenter
{
    /** @var ModuleManager */
    private $moduleManager;

    /** @var ILogger */
    private $logger;

    /** @var InvoicesManager */
    private $invoicesManager;

    /** @var CompanyManager */
    private $companyManager;

    /** @var Nette\Database\Explorer */
    private $database;

    // Základní přístup k modulům mají všichni přihlášení uživatelé
    protected array $requiredRoles = ['readonly', 'accountant', 'admin'];

    // Konkrétní role pro jednotlivé akce
    protected array $actionRoles = [
        'detail' => ['readonly', 'accountant', 'admin'], // Zobrazení detailu modulu - všichni
        'default' => ['admin'], // Správa modulů - pouze admin
        'toggleModule' => ['admin'], // Aktivace/deaktivace - pouze admin
        'uninstallModule' => ['admin'], // Odinstalace - pouze admin
        'moduleData' => ['readonly', 'accountant', 'admin'], // AJAX data z modulů - všichni
    ];

    public function __construct(
        ModuleManager $moduleManager, 
        ILogger $logger,
        InvoicesManager $invoicesManager,
        CompanyManager $companyManager,
        Nette\Database\Explorer $database
    ) {
        $this->moduleManager = $moduleManager;
        $this->logger = $logger;
        $this->invoicesManager = $invoicesManager;
        $this->companyManager = $companyManager;
        $this->database = $database;
    }

    /**
     * Nastavení vlastních cest k šablonám
     */
    public function formatTemplateFiles(): array
    {
        return [
            __DIR__ . "/templates/{$this->getAction()}.latte",
        ];
    }

    /**
     * Nastavení cesty k layoutu
     */
    public function formatLayoutTemplateFiles(): array
    {
        return [
            __DIR__ . '/../@layout.latte',
        ];
    }

    /**
     * AJAX action pro načítání dat z modulů - NOVÁ OBECNÁ IMPLEMENTACE
     */
    public function handleModuleData(): void
    {
        try {
            $this->logger->log("=== ZAČÁTEK OBECNÉHO AJAX VOLÁNÍ ===", ILogger::INFO);
            
            // Čteme parametry přímo z HTTP požadavku
            $moduleId = $this->getHttpRequest()->getQuery('moduleId');
            $action = $this->getHttpRequest()->getQuery('action') ?: 'getAllData';
            
            $this->logger->log("AJAX parametry - moduleId: '$moduleId', action: '$action'", ILogger::INFO);

            if (!$moduleId) {
                $this->logger->log("CHYBA: Nebyl zadán moduleId", ILogger::ERROR);
                $this->sendJson([
                    'success' => false,
                    'error' => 'Nebyl zadán moduleId'
                ]);
                return;
            }

            // Kontrola, zda je modul aktivní
            $this->logger->log("Kontroluji aktivní moduly...", ILogger::INFO);
            $activeModules = $this->moduleManager->getActiveModules();
            $this->logger->log("Aktivní moduly: " . implode(', ', array_keys($activeModules)), ILogger::INFO);
            
            if (!isset($activeModules[$moduleId])) {
                $this->logger->log("CHYBA: Modul '$moduleId' není aktivní nebo neexistuje", ILogger::ERROR);
                $this->sendJson([
                    'success' => false,
                    'error' => 'Modul není aktivní nebo neexistuje'
                ]);
                return;
            }

            $this->logger->log("Modul '$moduleId' je aktivní, načítám instanci modulu", ILogger::INFO);

            // NOVÁ OBECNÁ LOGIKA: Vytvoření instance modulu
            $moduleInstance = $this->createModuleInstance($moduleId);
            
            if (!$moduleInstance) {
                $this->logger->log("CHYBA: Nepodařilo se vytvořit instanci modulu '$moduleId'", ILogger::ERROR);
                $this->sendJson([
                    'success' => false,
                    'error' => 'Nepodařilo se načíst modul'
                ]);
                return;
            }

            $this->logger->log("Instance modulu '$moduleId' úspěšně vytvořena", ILogger::INFO);

            // Příprava závislostí pro modul
            $dependencies = $this->prepareDependencies();
            
            // Příprava parametrů z HTTP požadavku
            $parameters = $this->prepareParameters();

            $this->logger->log("Volám handleAjaxRequest na modulu '$moduleId' s akcí '$action'", ILogger::INFO);

            // Zavolání AJAX metody na modulu
            $result = $moduleInstance->handleAjaxRequest($action, $parameters, $dependencies);

            $this->logger->log("AJAX akce úspěšně zpracována, odesílám výsledek", ILogger::INFO);
            $this->sendJson([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (Nette\Application\AbortException $e) {
            // AbortException je normální ukončení po sendJson() - nelogujeme jako chybu
            $this->logger->log("AJAX volání úspěšně ukončeno (AbortException je normální)", ILogger::INFO);
            throw $e; // Necháme ji projít, je to očekávané chování
            
        } catch (\Throwable $e) {
            $this->logger->log("=== SKUTEČNÁ CHYBA V OBECNÉM AJAX VOLÁNÍ ===", ILogger::ERROR);
            $this->logger->log("Exception: " . get_class($e), ILogger::ERROR);
            $this->logger->log("Message: " . $e->getMessage(), ILogger::ERROR);
            $this->logger->log("File: " . $e->getFile() . " (line " . $e->getLine() . ")", ILogger::ERROR);
            $this->logger->log("Stack trace: " . $e->getTraceAsString(), ILogger::ERROR);
            
            $this->sendJson([
                'success' => false,
                'error' => 'Chyba při načítání dat: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * NOVÁ METODA: Vytvoří instanci modulu
     */
    private function createModuleInstance(string $moduleId): ?\App\Modules\IModule
    {
        try {
            $this->logger->log("Vytvářím instanci modulu '$moduleId'", ILogger::INFO);
            
            // Cesta k hlavnímu souboru modulu
            $modulePath = dirname(__DIR__, 2) . '/Modules/' . $moduleId;
            $moduleFile = $modulePath . '/Module.php';
            
            $this->logger->log("Hledám soubor modulu: $moduleFile", ILogger::INFO);
            
            if (!file_exists($moduleFile)) {
                $this->logger->log("CHYBA: Soubor modulu neexistuje: $moduleFile", ILogger::ERROR);
                return null;
            }
            
            $this->logger->log("Soubor modulu nalezen, načítám...", ILogger::INFO);

            // Načtení souboru modulu
            require_once $moduleFile;
            
            // Vytvoření názvu třídy (namespace podle ID modulu)
            $moduleClassName = 'Modules\\' . ucfirst($moduleId) . '\\Module';
            
            $this->logger->log("Kontroluji existenci třídy: $moduleClassName", ILogger::INFO);
            
            if (!class_exists($moduleClassName)) {
                $this->logger->log("CHYBA: Třída modulu neexistuje: $moduleClassName", ILogger::ERROR);
                return null;
            }
            
            $this->logger->log("Třída nalezena, vytvářím instanci...", ILogger::INFO);

            // Vytvoření instance modulu
            $moduleInstance = new $moduleClassName();
            
            if (!$moduleInstance instanceof \App\Modules\IModule) {
                $this->logger->log("CHYBA: Třída modulu neimplementuje IModule: $moduleClassName", ILogger::ERROR);
                return null;
            }
            
            $this->logger->log("Instance modulu '$moduleId' úspěšně vytvořena", ILogger::INFO);
            return $moduleInstance;
            
        } catch (\Throwable $e) {
            $this->logger->log("CHYBA při vytváření instance modulu '$moduleId': " . $e->getMessage(), ILogger::ERROR);
            return null;
        }
    }

    /**
     * NOVÁ METODA: Připraví závislosti pro moduly
     */
    private function prepareDependencies(): array
    {
        $this->logger->log("Připravuji závislosti pro modul", ILogger::INFO);
        
        return [
            $this->invoicesManager,
            $this->companyManager,
            $this->database,
            $this->logger
        ];
    }

    /**
     * NOVÁ METODA: Připraví parametry z HTTP požadavku
     */
    private function prepareParameters(): array
    {
        $httpRequest = $this->getHttpRequest();
        
        // Získáme všechny query parametry kromě základních
        $parameters = [];
        foreach ($httpRequest->getQuery() as $key => $value) {
            if (!in_array($key, ['do', 'moduleId', 'action'])) {
                $parameters[$key] = $value;
            }
        }
        
        // Přidáme také POST parametry pokud existují
        foreach ($httpRequest->getPost() as $key => $value) {
            $parameters[$key] = $value;
        }
        
        $this->logger->log("Připravené parametry: " . json_encode($parameters), ILogger::INFO);
        
        return $parameters;
    }

    public function renderDefault(): void
    {
        $this->template->title = "Správa modulů";

        // Načtení všech modulů
        $this->template->modules = $this->moduleManager->getAllModules();

        // Získání maximální velikosti souboru pro nahrávání
        $maxUploadSize = $this->getMaxUploadSize();
        $this->template->maxUploadSize = $maxUploadSize;
        $this->template->maxUploadSizeFormatted = $this->formatBytes($maxUploadSize);

        // DEBUG: Přidáme informace o PHP limitech pro debugging
        $this->template->debugInfo = $this->getPhpUploadDebugInfo();
    }

    public function renderDetail(string $id): void
    {
        $allModules = $this->moduleManager->getActiveModules();
        if (!isset($allModules[$id])) {
            $this->flashMessage('Modul nebyl nalezen.', 'danger');
            $this->redirect('Home:default');
        }

        $this->template->moduleInfo = $allModules[$id];
        $this->template->moduleId = $id;

        // Zkopírování/aktualizace assets při každém zobrazení detailu
        $this->updateModuleAssets($id);

        // Přidání CSS stylu modulu, pokud existuje
        $cssPath = '/Modules/' . $id . '/assets/css/style.css';
        $cssFullPath = WWW_DIR . $cssPath;
        if (file_exists($cssFullPath)) {
            $this->template->moduleCss = $cssPath;
        }

        // Přidání JS scriptu modulu, pokud existuje
        $jsPath = '/Modules/' . $id . '/assets/js/script.js';
        $jsFullPath = WWW_DIR . $jsPath;
        if (file_exists($jsFullPath)) {
            $this->template->moduleJs = $jsPath;
        }

        // Načtení šablony modulu
        $templatePath = dirname(__DIR__, 2) . '/Modules/' . $id . '/templates/dashboard.latte';
        if (file_exists($templatePath)) {
            $this->template->moduleTemplatePath = $templatePath;
        }

        // OBECNÉ: AJAX URL pro všechny moduly
        $this->template->ajaxUrl = $this->link('moduleData!', [
            'moduleId' => $id, 
            'action' => 'getAllData'
        ]);
        $this->template->moduleId = $id;
    }

    /**
     * Aktualizuje assets modulu - vždy překopíruje nejnovější verzi
     */
    private function updateModuleAssets(string $moduleId): void
    {
        $moduleAssetsDir = dirname(__DIR__, 2) . '/Modules/' . $moduleId . '/assets';
        $wwwModuleDir = WWW_DIR . '/Modules/' . $moduleId;

        // Pokud zdrojové assets neexistují, nic neděláme
        if (!is_dir($moduleAssetsDir)) {
            $this->logger->log("Assets modulu '$moduleId' neexistují v: $moduleAssetsDir", ILogger::INFO);
            return;
        }

        try {
            // Pokud už www adresář existuje, smažeme ho
            if (is_dir($wwwModuleDir)) {
                $this->logger->log("Mažu existující assets modulu '$moduleId' z: $wwwModuleDir", ILogger::INFO);
                $this->removeDirectory($wwwModuleDir);
            }

            // Vytvoření základního adresáře
            if (!is_dir(dirname($wwwModuleDir))) {
                mkdir(dirname($wwwModuleDir), 0755, true);
            }

            // Zkopírování nových assets
            $this->logger->log("Kopíruji nové assets modulu '$moduleId' z: $moduleAssetsDir do: $wwwModuleDir", ILogger::INFO);
            $this->copyDirectory($moduleAssetsDir, $wwwModuleDir . '/assets');

            $this->logger->log("Assets modulu '$moduleId' byly úspěšně aktualizovány", ILogger::INFO);
            
        } catch (\Exception $e) {
            $this->logger->log("Chyba při aktualizaci assets modulu '$moduleId': " . $e->getMessage(), ILogger::ERROR);
        }
    }

    /**
     * Rekurzivně odstraní adresář a jeho obsah
     */
    private function removeDirectory(string $dir): void
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
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    /**
     * Rekurzivně kopíruje adresář
     */
    private function copyDirectory(string $source, string $dest): void
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $srcFile = $source . '/' . $file;
            $destFile = $dest . '/' . $file;

            if (is_dir($srcFile)) {
                $this->copyDirectory($srcFile, $destFile);
            } else {
                copy($srcFile, $destFile);
            }
        }
        closedir($dir);
    }

    /**
     * Formulář pro nahrání nového modulu
     */
    protected function createComponentUploadForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        // Získání maximální velikosti souboru pro nahrávání
        $maxUploadSize = $this->getMaxUploadSize();

        $form->addUpload('moduleZip', 'ZIP soubor s modulem:')
            ->setRequired('Vyberte ZIP soubor s modulem')
            ->addRule(Form::MIME_TYPE, 'Soubor musí být ve formátu ZIP', 'application/zip')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost souboru je ' . $this->formatBytes($maxUploadSize), $maxUploadSize);

        $form->addSubmit('upload', 'Nahrát modul');

        $form->onSuccess[] = [$this, 'uploadFormSucceeded'];

        return $form;
    }

    /**
     * Získá maximální velikost souboru pro nahrávání s lepším debugováním
     */
    private function getMaxUploadSize(): int
    {
        // Získá maximální velikost souboru z php.ini
        $uploadMaxFilesize = $this->parseSize(ini_get('upload_max_filesize'));
        $postMaxSize = $this->parseSize(ini_get('post_max_size'));
        $memoryLimit = $this->parseSize(ini_get('memory_limit'));

        // Logujeme hodnoty pro debugging
        $this->logger->log("PHP Upload limits - upload_max_filesize: " . $this->formatBytes($uploadMaxFilesize) .
            ", post_max_size: " . $this->formatBytes($postMaxSize) .
            ", memory_limit: " . $this->formatBytes($memoryLimit), ILogger::INFO);

        // Použije menší z upload a post hodnot
        $serverLimit = min($uploadMaxFilesize, $postMaxSize);

        // Pro lokální development nastavíme vyšší limit
        // V produkci můžeme snížit podle potřeby
        $desiredLimit = 50 * 1024 * 1024; // 50 MB pro development

        $finalLimit = min($serverLimit, $desiredLimit);

        $this->logger->log("Final upload limit: " . $this->formatBytes($finalLimit), ILogger::INFO);

        return $finalLimit;
    }

    /**
     * Získá debug informace o PHP upload limitech
     */
    private function getPhpUploadDebugInfo(): array
    {
        return [
            'upload_max_filesize_raw' => ini_get('upload_max_filesize'),
            'upload_max_filesize_bytes' => $this->parseSize(ini_get('upload_max_filesize')),
            'upload_max_filesize_formatted' => $this->formatBytes($this->parseSize(ini_get('upload_max_filesize'))),

            'post_max_size_raw' => ini_get('post_max_size'),
            'post_max_size_bytes' => $this->parseSize(ini_get('post_max_size')),
            'post_max_size_formatted' => $this->formatBytes($this->parseSize(ini_get('post_max_size'))),

            'memory_limit_raw' => ini_get('memory_limit'),
            'memory_limit_bytes' => $this->parseSize(ini_get('memory_limit')),
            'memory_limit_formatted' => $this->formatBytes($this->parseSize(ini_get('memory_limit'))),

            'max_execution_time' => ini_get('max_execution_time'),
            'max_input_time' => ini_get('max_input_time'),

            'final_limit' => $this->getMaxUploadSize(),
            'final_limit_formatted' => $this->formatBytes($this->getMaxUploadSize())
        ];
    }

    /**
     * Převede řetězec s velikostí (např. "2M") na integer (bytes)
     */
    private function parseSize(string $size): int
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);

        if ($unit) {
            $size *= pow(1024, stripos('bkmgtpezy', $unit[0]));
        }

        return (int) $size;
    }

    /**
     * Formátuje velikost v bytech na čitelný formát
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Zpracování formuláře pro nahrání modulu
     */
    public function uploadFormSucceeded(Form $form, \stdClass $values): void
    {
        $file = $values->moduleZip;

        if ($file->isOk() && $file->getSize() > 0) {
            try {
                // Zavolání metody pro instalaci modulu
                $result = $this->moduleManager->installModule($file);

                if ($result['success']) {
                    $this->flashMessage('Modul byl úspěšně nainstalován: ' . $result['message'], 'success');
                } else {
                    $this->flashMessage('Chyba při instalaci modulu: ' . $result['message'], 'danger');
                }
            } catch (\Exception $e) {
                $this->logger->log('Chyba při instalaci modulu: ' . $e->getMessage(), ILogger::ERROR);
                $this->flashMessage('Nastala chyba při instalaci modulu: ' . $e->getMessage(), 'danger');
            }
        } else {
            $this->flashMessage('Neplatný soubor nebo chyba při nahrávání.', 'danger');
        }

        $this->redirect('default');
    }

    /**
     * Akce pro aktivaci/deaktivaci modulu
     */
    public function handleToggleModule(string $id): void
    {
        try {
            $result = $this->moduleManager->toggleModule($id);
            if ($result['success']) {
                $this->flashMessage($result['message'], 'success');
            } else {
                $this->flashMessage($result['message'], 'danger');
            }
        } catch (\Exception $e) {
            $this->logger->log('Chyba při přepínání modulu: ' . $e->getMessage(), ILogger::ERROR);
            $this->flashMessage('Nastala chyba při přepínání modulu: ' . $e->getMessage(), 'danger');
        }

        $this->redirect('default');
    }

    /**
     * Akce pro odstranění modulu
     */
    public function handleUninstallModule(string $id): void
    {
        try {
            $result = $this->moduleManager->uninstallModule($id);
            if ($result['success']) {
                $this->flashMessage($result['message'], 'success');
            } else {
                $this->flashMessage($result['message'], 'danger');
            }
        } catch (\Exception $e) {
            $this->logger->log('Chyba při odinstalaci modulu: ' . $e->getMessage(), ILogger::ERROR);
            $this->flashMessage('Nastala chyba při odinstalaci modulu: ' . $e->getMessage(), 'danger');
        }

        $this->redirect('default');
    }
}