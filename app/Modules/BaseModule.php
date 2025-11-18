<?php

declare(strict_types=1);

namespace App\Modules;

/**
 * Základní třída pro moduly QRdoklad
 */
abstract class BaseModule implements IModule
{
    /** @var array */
    protected $moduleInfo;

    /** @var string */
    protected $modulePath;

    public function __construct()
    {
        // Zjistíme cestu k adresáři modulu
        $reflector = new \ReflectionClass($this);
        $this->modulePath = dirname($reflector->getFileName());

        // Načteme module.json, pokud existuje
        $moduleJsonPath = $this->modulePath . '/module.json';
        if (file_exists($moduleJsonPath)) {
            $json = file_get_contents($moduleJsonPath);
            $this->moduleInfo = json_decode($json, true) ?: [];
        } else {
            $this->moduleInfo = [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return $this->moduleInfo['id'] ?? basename($this->modulePath);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->moduleInfo['name'] ?? 'Unnamed Module';
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): string
    {
        return $this->moduleInfo['version'] ?? '1.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthor(): string
    {
        return $this->moduleInfo['author'] ?? 'Unknown';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return $this->moduleInfo['description'] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        return $this->moduleInfo['icon'] ?? 'bi bi-puzzle-fill';
    }

    /**
     * {@inheritdoc}
     */
    public function getDashboardTemplate(): ?string
    {
        $templatePath = $this->modulePath . '/templates/dashboard.latte';
        return file_exists($templatePath) ? $templatePath : null;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        // Prázdná implementace
    }

    /**
     * {@inheritdoc}
     */
    public function activate(): void
    {
        // Prázdná implementace
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(): void
    {
        // Prázdná implementace
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(): void
    {
        // Prázdná implementace
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuItems(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     * 
     * NOVÁ METODA: Základní implementace zpracování AJAX požadavků
     * Potomci tuto metodu mohou přepsat pro vlastní logiku
     */
    public function handleAjaxRequest(string $action, array $parameters = [], array $dependencies = [])
    {
        // Základní implementace - žádné AJAX akce nejsou podporovány
        throw new \Exception("Modul '{$this->getName()}' nepodporuje AJAX akci: $action");
    }

    /**
     * Pomocná metoda pro získání závislosti z pole dependencies
     * 
     * @param array $dependencies Pole závislostí
     * @param string $className Název třídy kterou hledáme
     * @return mixed|null Nalezená závislost nebo null
     */
    protected function getDependency(array $dependencies, string $className)
    {
        foreach ($dependencies as $dependency) {
            if ($dependency instanceof $className) {
                return $dependency;
            }
        }
        return null;
    }

    /**
     * Pomocná metoda pro logování z modulu
     * 
     * @param string $message Zpráva k zalogování
     * @param string $level Úroveň loga (INFO, ERROR, WARNING)
     */
    protected function log(string $message, string $level = 'INFO'): void
    {
        $moduleId = $this->getId();
        $logMessage = "[MODULE: $moduleId] $message";

        // V produkční verzi by zde mohlo být lepší řešení logování
        error_log("[$level] $logMessage");
    }

    /**
     * Nastaví tenant kontext pro modul (volitelné pro moduly)
     */
    public function setTenantContext(int $tenantId, bool $isSuperAdmin = false): void
    {
        // Default implementace - moduly můžou override
        // Některé moduly nemusí tenant kontext potřebovat
    }
}
