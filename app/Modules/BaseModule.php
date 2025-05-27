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
}