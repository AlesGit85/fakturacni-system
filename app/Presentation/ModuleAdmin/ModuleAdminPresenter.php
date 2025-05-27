<?php

declare(strict_types=1);

namespace App\Presentation\ModuleAdmin;

use Nette;
use Nette\Application\UI\Form;
use App\Model\ModuleManager;
use App\Presentation\BasePresenter;
use Tracy\ILogger;

final class ModuleAdminPresenter extends BasePresenter
{
    /** @var ModuleManager */
    private $moduleManager;
    
    /** @var ILogger */
    private $logger;

    protected array $requiredRoles = ['admin'];

    public function __construct(ModuleManager $moduleManager, ILogger $logger)
    {
        $this->moduleManager = $moduleManager;
        $this->logger = $logger;
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

    public function renderDefault(): void
    {
        $this->template->title = "Správa modulů";
        
        // Načtení všech modulů
        $this->template->modules = $this->moduleManager->getAllModules();
        
        // Získání maximální velikosti souboru pro nahrávání
        $maxUploadSize = $this->getMaxUploadSize();
        $this->template->maxUploadSize = $maxUploadSize;
        $this->template->maxUploadSizeFormatted = $this->formatBytes($maxUploadSize);
    }
    
    public function renderDetail(string $id): void
    {
        $allModules = $this->moduleManager->getAllModules();
        if (!isset($allModules[$id])) {
            $this->flashMessage('Modul nebyl nalezen.', 'danger');
            $this->redirect('default');
        }
        
        $this->template->moduleInfo = $allModules[$id];
        $this->template->moduleId = $id;
        
        // Přidání stylu modulu, pokud existuje
        $cssPath = dirname(__DIR__, 2) . '/Modules/' . $id . '/assets/css/style.css';
        if (file_exists($cssPath)) {
            $this->template->moduleCss = '/Modules/' . $id . '/assets/css/style.css';
        }
        
        // Načtení šablony modulu
        $templatePath = dirname(__DIR__, 2) . '/Modules/' . $id . '/templates/dashboard.latte';
        if (file_exists($templatePath)) {
            $this->template->moduleTemplatePath = $templatePath;
        }
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
     * Získá maximální velikost souboru pro nahrávání
     */
    private function getMaxUploadSize(): int
    {
        // Získá maximální velikost souboru z php.ini
        $uploadMaxFilesize = $this->parseSize(ini_get('upload_max_filesize'));
        $postMaxSize = $this->parseSize(ini_get('post_max_size'));
        
        // Použije menší z hodnot (obvykle je to omezující faktor)
        $serverLimit = min($uploadMaxFilesize, $postMaxSize);
        
        // Nastavíme limit 20 MB, ale respektujeme serverové omezení, pokud je nižší
        $desiredLimit = 20 * 1024 * 1024; // 20 MB
        
        return min($serverLimit, $desiredLimit);
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