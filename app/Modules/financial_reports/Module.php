<?php

declare(strict_types=1);

namespace Modules\Financial_reports;

use App\Modules\BaseModule;

/**
 * Modul Finanční přehledy
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
                'params' => ['id' => 'financial_reports'],
                'label' => 'Finanční přehledy',
                'icon' => 'bi bi-graph-up-arrow'
            ],
            [
                'presenter' => 'ModuleAdmin',
                'action' => 'detail',
                'params' => ['id' => 'financial_reports', 'view' => 'monthly'],
                'label' => 'Měsíční přehledy',
                'icon' => 'bi bi-calendar-month'
            ],
            [
                'presenter' => 'ModuleAdmin',
                'action' => 'detail',
                'params' => ['id' => 'financial_reports', 'view' => 'vat_status'],
                'label' => 'DPH status',
                'icon' => 'bi bi-percent'
            ]
        ];
    }
    
    /**
     * Získá instanci služby pro finanční přehledy
     */
    public function getFinancialReportsService(): FinancialReportsService
    {
        // V produkční verzi by toto bylo lépe vyřešeno přes DI kontejner
        global $container;
        
        if (isset($container)) {
            $invoicesManager = $container->getByType(\App\Model\InvoicesManager::class);
            $companyManager = $container->getByType(\App\Model\CompanyManager::class);
            $database = $container->getByType(\Nette\Database\Explorer::class);
            
            return new FinancialReportsService($invoicesManager, $companyManager, $database);
        }
        
        throw new \RuntimeException('Nelze získat závislosti pro FinancialReportsService');
    }
    
    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        // Inicializace modulu
    }
    
    /**
     * {@inheritdoc}
     */
    public function activate(): void
    {
        // Aktivace modulu
    }
    
    /**
     * {@inheritdoc}
     */
    public function deactivate(): void
    {
        // Deaktivace modulu
    }
}