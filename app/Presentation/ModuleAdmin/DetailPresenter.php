<?php

declare(strict_types=1);

namespace App\Presentation\Modules;

use Nette;
use App\Model\ModuleManager;
use App\Presentation\BasePresenter;

final class DetailPresenter extends BasePresenter
{
    /** @var ModuleManager */
    private $moduleManager;
    
    /** @var string */
    private $moduleId;
    
    /** @var object */
    private $moduleInstance;

    // Admin může přistupovat ke všem modulům
    protected array $requiredRoles = ['admin'];

    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    public function startup(): void
    {
        parent::startup();
        
        // Získání ID modulu z parametru
        $this->moduleId = $this->getParameter('id');
        if (!$this->moduleId) {
            $this->flashMessage('Nebyl zadán modul.', 'danger');
            $this->redirect('Home:default');
        }
        
        // Kontrola, zda modul existuje a je aktivní
        $activeModules = $this->moduleManager->getActiveModules();
        if (!isset($activeModules[$this->moduleId])) {
            $this->flashMessage('Modul není aktivní nebo neexistuje.', 'danger');
            $this->redirect('Home:default');
        }
        
        // Načtení instance modulu
        $modulePath = dirname(__DIR__, 2) . '/Modules/' . $this->moduleId;
        $moduleFile = $modulePath . '/Module.php';
        
        if (file_exists($moduleFile)) {
            require_once $moduleFile;
            $moduleClassName = 'Modules\\' . ucfirst($this->moduleId) . '\\Module';
            if (class_exists($moduleClassName)) {
                $this->moduleInstance = new $moduleClassName();
            } else {
                $this->flashMessage('Třída modulu nebyla nalezena.', 'danger');
                $this->redirect('Home:default');
            }
        } else {
            $this->flashMessage('Soubor modulu nebyl nalezen.', 'danger');
            $this->redirect('Home:default');
        }
    }

    public function renderDefault(): void
    {
        $activeModules = $this->moduleManager->getActiveModules();
        $this->template->moduleInfo = $activeModules[$this->moduleId];
        $this->template->moduleId = $this->moduleId;
        
        // Přidání stylu modulu, pokud existuje
        $cssPath = dirname(__DIR__, 2) . '/Modules/' . $this->moduleId . '/assets/css/style.css';
        if (file_exists($cssPath)) {
            $this->template->moduleCss = '/Modules/' . $this->moduleId . '/assets/css/style.css';
        }
        
        // Načtení šablony modulu
        if (method_exists($this->moduleInstance, 'getDashboardTemplate')) {
            $templatePath = $this->moduleInstance->getDashboardTemplate();
            if (file_exists($templatePath)) {
                $this->template->moduleTemplatePath = $templatePath;
            }
        }
    }
}