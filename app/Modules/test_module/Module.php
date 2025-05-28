<?php

declare(strict_types=1);

namespace Modules\Test_module;

use App\Modules\BaseModule;

/**
 * Testovací modul
 */
class Module extends BaseModule
{
    /**
     * {@inheritdoc}
     */
    public function getMenuItems(): array
    {
        return [
            [
                'presenter' => 'ModuleAdmin',
                'action' => 'detail',
                'params' => ['id' => 'test_module'],
                'label' => 'Testovací dashboard',
                'icon' => 'bi bi-speedometer2'
            ],
            [
                'link' => 'javascript:void(0)',
                'onclick' => 'alert("Test akce 1 byla spuštěna!")',
                'label' => 'Test akce 1',
                'icon' => 'bi bi-gear'
            ],
            [
                'link' => 'javascript:void(0)',
                'onclick' => 'alert("Test akce 2 byla spuštěna!")',
                'label' => 'Test akce 2',
                'icon' => 'bi bi-graph-up'
            ]
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        // Testovací inicializace
    }
    
    /**
     * {@inheritdoc}
     */
    public function activate(): void
    {
        // Testovací aktivace
    }
    
    /**
     * {@inheritdoc}
     */
    public function deactivate(): void
    {
        // Testovací deaktivace
    }
}