<?php

declare(strict_types=1);

namespace App\Modules;

/**
 * Rozhraní pro moduly QRdoklad
 */
interface IModule
{
    /**
     * Vrátí ID modulu (musí být shodné s ID v module.json)
     */
    public function getId(): string;
    
    /**
     * Vrátí název modulu
     */
    public function getName(): string;
    
    /**
     * Vrátí verzi modulu
     */
    public function getVersion(): string;
    
    /**
     * Vrátí autora modulu
     */
    public function getAuthor(): string;
    
    /**
     * Vrátí popis modulu
     */
    public function getDescription(): string;
    
    /**
     * Vrátí ikonu modulu (ve formátu Bootstrap Icons, např. 'bi bi-star')
     */
    public function getIcon(): string;
    
    /**
     * Vrátí cestu k šabloně dashboardu modulu, pokud existuje
     */
    public function getDashboardTemplate(): ?string;
    
    /**
     * Metoda volaná při inicializaci modulu
     */
    public function initialize(): void;
    
    /**
     * Metoda volaná při aktivaci modulu
     */
    public function activate(): void;
    
    /**
     * Metoda volaná při deaktivaci modulu
     */
    public function deactivate(): void;
    
    /**
     * Metoda volaná při odinstalaci modulu
     */
    public function uninstall(): void;
    
    /**
     * Vrátí položky, které mají být přidány do menu
     * @return array ve formátu [['link' => 'Presenter:action', 'label' => 'Název', 'icon' => 'bi bi-xyz'], ...]
     */
    public function getMenuItems(): array;
}