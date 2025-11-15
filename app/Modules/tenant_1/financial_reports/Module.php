<?php

declare(strict_types=1);

namespace Modules\Tenant1\Financial_reports;

use App\Modules\BaseModule;
use App\Model\InvoicesManager;
use App\Model\CompanyManager;
use Nette\Database\Explorer;

/**
 * Modul Finanční přehledy - aktualizovaná verze pro multitenancy
 */
class Module extends BaseModule
{
    /** @var int|null ID aktuálního tenanta */
    private $currentTenantId;

    /** @var bool Zda je uživatel super admin */
    private $isSuperAdmin = false;

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
     * Zpracování AJAX požadavků s tenant kontextem
     */
    public function handleAjaxRequest(string $action, array $parameters = [], array $dependencies = [])
    {
        $this->log("Zpracovávám AJAX akci: $action pro tenant: " . ($this->currentTenantId ?? 'neznámý'));
        
        try {
            // Nastavíme tenant kontext z dependencies pokud není nastaven
            if (isset($dependencies['tenantId'])) {
                $this->currentTenantId = (int)$dependencies['tenantId'];
            }
            
            if (isset($dependencies['isSuperAdmin'])) {
                $this->isSuperAdmin = (bool)$dependencies['isSuperAdmin'];
            }

            // Získáme potřebné závislosti
            $invoicesManager = $this->getDependency($dependencies, InvoicesManager::class);
            $companyManager = $this->getDependency($dependencies, CompanyManager::class);
            $database = $this->getDependency($dependencies, Explorer::class);
            
            if (!$invoicesManager || !$companyManager || !$database) {
                throw new \Exception('Chybí potřebné závislosti pro modul Financial Reports');
            }
            
            $this->log("Závislosti úspěšně získány, tenant_id: " . ($this->currentTenantId ?? 'null'));
            
            // Vytvoříme instanci služby s tenant kontextem
            $service = $this->getFinancialReportsService($invoicesManager, $companyManager, $database);
            
            // Zpracujeme akci
            switch ($action) {
                case 'getBasicStats':
                    $this->log("Volám getBasicStats pro tenant: " . ($this->currentTenantId ?? 'all'));
                    $result = $service->getBasicStats();
                    $this->log("getBasicStats výsledek: " . json_encode($result));
                    return $result;

                case 'getVatLimits':
                    $this->log("Volám getVatLimits pro tenant: " . ($this->currentTenantId ?? 'all'));
                    $result = $service->checkVatLimits();
                    $this->log("getVatLimits výsledek: " . json_encode($result));
                    return $result;

                case 'getAllData':
                    $this->log("Volám getAllData (getBasicStats + getVatLimits) pro tenant: " . ($this->currentTenantId ?? 'all'));
                    
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
                    
                    $this->log("Volám getMonthlyStats pro rok $year, měsíc $month, tenant: " . ($this->currentTenantId ?? 'all'));
                    $result = $service->getMonthlyStats($year, $month);
                    $this->log("getMonthlyStats výsledek: " . json_encode($result));
                    return $result;
                    
                case 'getMonthlyIncomeData':
                    $year = (int)($parameters['year'] ?? date('Y'));
                    
                    $this->log("Volám getMonthlyIncomeData pro rok $year, tenant: " . ($this->currentTenantId ?? 'all'));
                    $result = $service->getMonthlyIncomeData($year);
                    $this->log("getMonthlyIncomeData výsledek: " . json_encode($result));
                    return $result;
                    
                case 'getInvoicesOverview':
                    $year = isset($parameters['year']) ? (int)$parameters['year'] : null;
                    $month = isset($parameters['month']) ? (int)$parameters['month'] : null;
                    $limit = (int)($parameters['limit'] ?? 50);
                    
                    $this->log("Volám getInvoicesOverview - rok: $year, měsíc: $month, limit: $limit, tenant: " . ($this->currentTenantId ?? 'all'));
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
     * Nastaví tenant kontext pro modul
     */
    public function setTenantContext(int $tenantId, bool $isSuperAdmin = false): void
    {
        $this->currentTenantId = $tenantId;
        $this->isSuperAdmin = $isSuperAdmin;
        $this->log("Nastaven tenant kontext: tenant_id=$tenantId, is_super_admin=" . ($isSuperAdmin ? 'true' : 'false'));
    }
    
    /**
     * Získá instanci služby pro finanční přehledy
     */
    private function getFinancialReportsService(
        InvoicesManager $invoicesManager,
        CompanyManager $companyManager,
        Explorer $database
    ) {
        
        $this->log("Vytvářím instanci FinancialReportsService");
        
        // Načteme službu pokud ještě není načtená
        $serviceFile = $this->modulePath . '/FinancialReportsService.php';
        
        if (!file_exists($serviceFile)) {
            throw new \Exception("Soubor služby FinancialReportsService nebyl nalezen: $serviceFile");
        }
        
        // Dynamicky určíme název třídy podle aktuálního namespace
        $currentNamespace = __NAMESPACE__;
        $serviceClassName = $currentNamespace . '\\FinancialReportsService';
        
        if (!class_exists($serviceClassName)) {
            require_once $serviceFile;
        }
        
        if (!class_exists($serviceClassName)) {
            throw new \Exception("Třída $serviceClassName nebyla nalezena");
        }
        
        $this->log("FinancialReportsService úspěšně vytvořena jako: $serviceClassName");
        
        // Vytvoříme službu s tenant kontextem
        $service = new $serviceClassName($invoicesManager, $companyManager, $database);
        
        // Nastavíme tenant kontext do služby pokud metoda existuje
        if (method_exists($service, 'setTenantContext') && $this->currentTenantId !== null) {
            $service->setTenantContext($this->currentTenantId, $this->isSuperAdmin);
        }
        
        return $service;
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
