<?php

declare(strict_types=1);

namespace Modules\Tenant12\Financial_reports;

use App\Modules\BaseModule;
use App\Model\InvoicesManager;
use App\Model\CompanyManager;
use Nette\Database\Explorer;

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
     * {@inheritdoc}
     * 
     * NOVÁ IMPLEMENTACE: Zpracování AJAX požadavků přímo v modulu
     */
    public function handleAjaxRequest(string $action, array $parameters = [], array $dependencies = [])
    {
        $this->log("Zpracovávám AJAX akci: $action");
        
        try {
            // Získáme potřebné závislosti
            $invoicesManager = $this->getDependency($dependencies, InvoicesManager::class);
            $companyManager = $this->getDependency($dependencies, CompanyManager::class);
            $database = $this->getDependency($dependencies, Explorer::class);
            
            if (!$invoicesManager || !$companyManager || !$database) {
                throw new \Exception('Chybí potřebné závislosti pro modul Financial Reports');
            }
            
            $this->log("Závislosti úspěšně získány");
            
            // Vytvoříme instanci služby
            $service = $this->getFinancialReportsService($invoicesManager, $companyManager, $database);
            
            // Zpracujeme akci
            switch ($action) {
                case 'getBasicStats':
                    $this->log("Volám getBasicStats");
                    $result = $service->getBasicStats();
                    $this->log("getBasicStats výsledek: " . json_encode($result));
                    return $result;

                case 'getVatLimits':
                    $this->log("Volám getVatLimits");
                    $result = $service->checkVatLimits();
                    $this->log("getVatLimits výsledek: " . json_encode($result));
                    return $result;

                case 'getAllData':
                    $this->log("Volám getAllData (getBasicStats + getVatLimits)");
                    
                    $this->log("Načítám základní statistiky...");
                    $stats = $service->getBasicStats();
                    $this->log("Základní statistiky: " . json_encode($stats));
                    
                    $this->log("Načítám DPH limity...");
                    $vatLimits = $service->checkVatLimits();
                    $this->log("DPH limity: " . json_encode($vatLimits));
                    
                    $result = [
                        'stats' => $stats,
                        'vatLimits' => $vatLimits
                    ];
                    
                    $this->log("getAllData kompletní výsledek: " . json_encode($result));
                    return $result;
                    
                case 'getMonthlyStats':
                    $year = (int)($parameters['year'] ?? date('Y'));
                    $month = (int)($parameters['month'] ?? date('n'));
                    
                    $this->log("Volám getMonthlyStats pro rok $year, měsíc $month");
                    $result = $service->getMonthlyStats($year, $month);
                    $this->log("getMonthlyStats výsledek: " . json_encode($result));
                    return $result;
                    
                case 'getMonthlyIncomeData':
                    $year = (int)($parameters['year'] ?? date('Y'));
                    
                    $this->log("Volám getMonthlyIncomeData pro rok $year");
                    $result = $service->getMonthlyIncomeData($year);
                    $this->log("getMonthlyIncomeData výsledek: " . json_encode($result));
                    return $result;
                    
                case 'getInvoicesOverview':
                    $year = isset($parameters['year']) ? (int)$parameters['year'] : null;
                    $month = isset($parameters['month']) ? (int)$parameters['month'] : null;
                    $limit = (int)($parameters['limit'] ?? 50);
                    
                    $this->log("Volám getInvoicesOverview - rok: $year, měsíc: $month, limit: $limit");
                    $result = $service->getInvoicesOverview($year, $month, $limit);
                    
                    // Převedeme na pole pro JSON
                    $invoicesArray = [];
                    foreach ($result as $invoice) {
                        $invoicesArray[] = $invoice->toArray();
                    }
                    
                    $this->log("getInvoicesOverview výsledek: " . count($invoicesArray) . " faktur");
                    return $invoicesArray;

                default:
                    throw new \Exception("Nepodporovaná akce: $action");
            }
            
        } catch (\Throwable $e) {
            $this->log("Chyba při zpracování AJAX akce '$action': " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Získá instanci služby pro finanční přehledy
     */
    private function getFinancialReportsService(
        InvoicesManager $invoicesManager,
        CompanyManager $companyManager,
        Explorer $database
    ): FinancialReportsService {
        
        $this->log("Vytvářím instanci FinancialReportsService");
        
        // Načteme službu pokud ještě není načtená
        $serviceFile = $this->modulePath . '/FinancialReportsService.php';
        
        if (!file_exists($serviceFile)) {
            throw new \Exception("Soubor služby FinancialReportsService nebyl nalezen: $serviceFile");
        }
        
        if (!class_exists(FinancialReportsService::class)) {
            require_once $serviceFile;
        }
        
        if (!class_exists(FinancialReportsService::class)) {
            throw new \Exception("Třída FinancialReportsService nebyla nalezena");
        }
        
        $this->log("FinancialReportsService úspěšně vytvořena");
        
        return new FinancialReportsService($invoicesManager, $companyManager, $database);
    }
    
    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        $this->log("Inicializace modulu Financial Reports");
    }
    
    /**
     * {@inheritdoc}
     */
    public function activate(): void
    {
        $this->log("Aktivace modulu Financial Reports");
    }
    
    /**
     * {@inheritdoc}
     */
    public function deactivate(): void
    {
        $this->log("Deaktivace modulu Financial Reports");
    }
}