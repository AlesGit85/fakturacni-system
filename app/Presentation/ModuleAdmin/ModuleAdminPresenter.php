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

    protected array $requiredRoles = ['admin'];

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
     * AJAX action pro načítání dat z modulů
     */
    public function handleModuleData(): void
    {
        try {
            // Čteme parametry přímo z HTTP požadavku
            $moduleId = $this->getHttpRequest()->getQuery('moduleId');
            $action = $this->getHttpRequest()->getQuery('action') ?: 'getAllData';
            
            $this->logger->log("AJAX volání pro modul: $moduleId, action: $action", ILogger::INFO);

            if (!$moduleId) {
                $this->sendJson([
                    'success' => false,
                    'error' => 'Nebyl zadán moduleId'
                ]);
                return;
            }

            // Kontrola, zda je modul aktivní
            $activeModules = $this->moduleManager->getActiveModules();
            if (!isset($activeModules[$moduleId])) {
                $this->sendJson([
                    'success' => false,
                    'error' => 'Modul není aktivní nebo neexistuje'
                ]);
                return;
            }

            // Zpracování podle modulu
            $result = $this->processModuleAction($moduleId, $action);

            $this->sendJson([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Throwable $e) {
            $this->logger->log("Chyba při AJAX volání modulu: " . $e->getMessage(), ILogger::ERROR);
            $this->logger->log("Stack trace: " . $e->getTraceAsString(), ILogger::ERROR);
            $this->sendJson([
                'success' => false,
                'error' => 'Chyba při načítání dat: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Volá specifickou akci na modulu
     */
    private function processModuleAction(string $moduleId, string $action)
    {
        switch ($moduleId) {
            case 'financial_reports':
                return $this->processFinancialReportsAction($action);

            default:
                throw new \Exception("Nepodporovaný modul: $moduleId");
        }
    }

    /**
     * Obsluha akcí pro modul Financial Reports
     */
    private function processFinancialReportsAction(string $action)
    {
        try {
            // Ověříme, že soubor služby existuje
            $serviceFile = dirname(__DIR__, 2) . '/Modules/financial_reports/FinancialReportsService.php';
            if (!file_exists($serviceFile)) {
                throw new \Exception("Soubor služby FinancialReportsService nebyl nalezen: $serviceFile");
            }

            // Načteme službu
            require_once $serviceFile;
            
            // Ověříme, že třída existuje
            if (!class_exists('\Modules\Financial_reports\FinancialReportsService')) {
                throw new \Exception("Třída FinancialReportsService nebyla nalezena");
            }

            // Vytvoříme instanci služby s reálnými závislostmi
            $service = new \Modules\Financial_reports\FinancialReportsService(
                $this->invoicesManager,
                $this->companyManager,
                $this->database
            );

            switch ($action) {
                case 'getBasicStats':
                    return $service->getBasicStats();

                case 'getVatLimits':
                    return $service->checkVatLimits();

                case 'getAllData':
                    $stats = $service->getBasicStats();
                    $vatLimits = $service->checkVatLimits();
                    
                    return [
                        'stats' => $stats,
                        'vatLimits' => $vatLimits
                    ];

                default:
                    throw new \Exception("Nepodporovaná akce: $action");
            }
        } catch (\Throwable $e) {
            $this->logger->log("Chyba ve FinancialReportsAction: " . $e->getMessage(), ILogger::ERROR);
            throw $e;
        }
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
            $this->redirect('default');
        }

        $this->template->moduleInfo = $allModules[$id];
        $this->template->moduleId = $id;

        // Zkopírování assets do www adresáře, pokud ještě nebyly zkopírovány
        $this->ensureModuleAssets($id);

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

        // Pro financial_reports přidáme AJAX URL (jen pro kompatibilitu)
        if ($id === 'financial_reports') {
            $this->template->ajaxUrl = $this->link('moduleData!', [
                'moduleId' => $id, 
                'action' => 'getAllData'
            ]);
            $this->template->moduleId = $id;
        }
    }

    /**
     * Zajistí, že assets modulu jsou zkopírovány do www adresáře
     */
    private function ensureModuleAssets(string $moduleId): void
    {
        $moduleAssetsDir = dirname(__DIR__, 2) . '/Modules/' . $moduleId . '/assets';
        $wwwModuleDir = WWW_DIR . '/Modules/' . $moduleId;

        // Pokud assets existují a ještě nejsou zkopírovány
        if (is_dir($moduleAssetsDir) && !is_dir($wwwModuleDir)) {
            // Vytvoření adresáře
            if (!is_dir(dirname($wwwModuleDir))) {
                mkdir(dirname($wwwModuleDir), 0755, true);
            }

            // Zkopírování assets
            $this->copyDirectory($moduleAssetsDir, $wwwModuleDir . '/assets');

            $this->logger->log("Assets modulu '$moduleId' byly zkopírovány do www adresáře", ILogger::INFO);
        }
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