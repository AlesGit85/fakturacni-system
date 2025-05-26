<?php

declare(strict_types=1);

namespace App\Presentation\ModuleAdmin;

use App\Presentation\BasePresenter;

final class ModuleAdminPresenter extends BasePresenter
{
    protected array $requiredRoles = ['admin'];

    public function renderDefault(): void
    {
        $this->template->title = "Správa modulů";
        
        // Minimální statická data pro šablonu
        $this->template->modules = [
            'test_module' => [
                'id' => 'test_module',
                'name' => 'Testovací modul',
                'version' => '1.0.0',
                'description' => 'Testovací modul pro ověření funkčnosti',
                'author' => 'Systém',
                'active' => true,
                'icon' => 'bi bi-puzzle-fill'
            ]
        ];
    }
}