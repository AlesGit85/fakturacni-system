<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Tracy\ILogger;

/**
 * Třída pro správu modulů
 */
class ModuleManager
{
    use Nette\SmartObject;

    /** @var ILogger */
    private $logger;

    /**
     * Konstruktor třídy
     */
    public function __construct(ILogger $logger)
    {
        $this->logger = $logger;
        $this->logger->log("ModuleManager byl inicializován", ILogger::INFO);
    }

    /**
     * Získání všech dostupných modulů
     */
    public function getAllModules(): array
    {
        return [
            'test_module' => [
                'id' => 'test_module',
                'name' => 'Testovací modul',
                'version' => '1.0.0',
                'description' => 'Základní test',
                'author' => 'System',
                'active' => true,
                'icon' => 'bi bi-star-fill'
            ]
        ];
    }

    /**
     * Získání aktivních modulů
     */
    public function getActiveModules(): array
    {
        return $this->getAllModules();
    }
}