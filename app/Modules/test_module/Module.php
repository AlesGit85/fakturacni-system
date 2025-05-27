<?php

declare(strict_types=1);

namespace App\Modules\test_module;

use App\Modules\BaseModule;

/**
 * Testovací modul
 */
class Module extends BaseModule
{
    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        // Zde by mohl být kód pro inicializaci modulu
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMenuItems(): array
    {
        return [
            [
                'link' => 'TestModule:default',
                'label' => 'Testovací modul',
                'icon' => 'bi bi-stars'
            ]
        ];
    }
}