<?php

declare(strict_types=1);

namespace Modules\TestModule;

use App\Modules\BaseModule;

/**
 * Testovací modul pro ověření funkčnosti systému modulů
 */
class Module extends BaseModule
{
    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        // Inicializace testovacího modulu
    }
    
    /**
     * {@inheritdoc}
     */
    public function activate(): void
    {
        // Logika při aktivaci modulu
    }
    
    /**
     * {@inheritdoc}
     */
    public function deactivate(): void
    {
        // Logika při deaktivaci modulu
    }
    
    /**
     * {@inheritdoc}
     */
    public function uninstall(): void
    {
        // Cleanup při odinstalaci modulu
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMenuItems(): array
    {
        return [
            [
                'link' => 'Modules:detail',
                'params' => ['id' => 'test_module'],
                'label' => 'Test Dashboard',
                'icon' => 'bi bi-star'
            ]
        ];
    }
    
    /**
     * Vlastní metoda pro testování
     */
    public function getTestData(): array
    {
        return [
            'message' => 'Testovací modul funguje správně!',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => $this->getVersion(),
            'features' => [
                'Základní funkcionalita',
                'Dashboard template',
                'CSS styly',
                'JavaScript funkce'
            ]
        ];
    }
}