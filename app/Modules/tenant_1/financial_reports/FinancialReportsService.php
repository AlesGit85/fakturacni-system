<?php

declare(strict_types=1);

namespace Modules\Tenant1\Financial_reports;

use Nette;
use App\Model\InvoicesManager;
use App\Model\CompanyManager;

/**
 * Služba pro finanční přehledy s podporou multitenancy
 */
class FinancialReportsService
{
    use Nette\SmartObject;

    /** @var InvoicesManager */
    private $invoicesManager;
    
    /** @var CompanyManager */
    private $companyManager;
    
    /** @var Nette\Database\Explorer */
    private $database;

    /** @var int|null ID aktuálního tenanta */
    private $currentTenantId;

    /** @var bool Zda je uživatel super admin */
    private $isSuperAdmin = false;

    public function __construct(
        InvoicesManager $invoicesManager,
        CompanyManager $companyManager,
        Nette\Database\Explorer $database
    ) {
        $this->invoicesManager = $invoicesManager;
        $this->companyManager = $companyManager;
        $this->database = $database;
    }

    /**
     * Nastaví tenant kontext pro filtrování dat
     */
    public function setTenantContext(int $tenantId, bool $isSuperAdmin = false): void
    {
        $this->currentTenantId = $tenantId;
        $this->isSuperAdmin = $isSuperAdmin;
        
        error_log("FinancialReportsService: Nastaven tenant kontext - tenant_id: $tenantId, is_super_admin: " . ($isSuperAdmin ? 'yes' : 'no'));
    }

    /**
     * Aplikuje tenant filtr na databázový dotaz
     */
    private function applyTenantFilter(Nette\Database\Table\Selection $selection): Nette\Database\Table\Selection
    {
        // Super admin vidí data ze všech tenantů
        if ($this->isSuperAdmin) {
            return $selection;
        }

        // Ostatní uživatelé vidí pouze data svého tenanta
        if ($this->currentTenantId !== null) {
            return $selection->where('tenant_id', $this->currentTenantId);
        }

        // Pokud není nastaven tenant, vrátíme prázdný výsledek
        return $selection->where('1 = 0');
    }

    /**
     * Získá základní finanční statistiky s tenant filtrováním
     */
    public function getBasicStats(): array
    {
        $currentYear = date('Y');
        
        error_log("FinancialReportsService: Načítám statistiky pro rok $currentYear, tenant: " . ($this->currentTenantId ?? 'všechny'));
        
        // Základní dotaz na faktury aktuálního roku s tenant filtrem
        $baseQuery = $this->database->table('invoices')
            ->where('YEAR(issue_date) = ?', $currentYear);
        
        // Aplikujeme tenant filtr
        $baseQuery = $this->applyTenantFilter($baseQuery);
        
        // Debug: Zjistíme si všechny faktury pro kontrolu
        $allInvoicesArray = $baseQuery->fetchAll();
        $debugInfo = [];
        /** @var Nette\Database\Table\ActiveRow $invoice */
        foreach ($allInvoicesArray as $invoice) {
            $debugInfo[] = [
                'number' => $invoice->number,
                'status' => $invoice->status,
                'total' => $invoice->total,
                'issue_date' => $invoice->issue_date,
                'tenant_id' => $invoice->tenant_id ?? 'NULL'
            ];
        }
        error_log("DEBUG - Filtrované faktury pro rok $currentYear: " . json_encode($debugInfo));
        
        // Celkový počet faktur
        $totalCount = $this->applyTenantFilter(
            $this->database->table('invoices')
                ->where('YEAR(issue_date) = ?', $currentYear)
        )->count();
        
        // Zaplacené faktury
        $paidCount = $this->applyTenantFilter(
            $this->database->table('invoices')
                ->where('YEAR(issue_date) = ?', $currentYear)
                ->where('status', 'paid')
        )->count();
        
        // Nezaplacené faktury (created + overdue)
        $unpaidCount = $this->applyTenantFilter(
            $this->database->table('invoices')
                ->where('YEAR(issue_date) = ?', $currentYear)
                ->where('status != ?', 'paid')
        )->count();
        
        // Po splatnosti
        $overdueCount = $this->applyTenantFilter(
            $this->database->table('invoices')
                ->where('YEAR(issue_date) = ?', $currentYear)
                ->where('status', 'overdue')
        )->count();
        
        // Celkový obrat (všechny vystavené faktury)
        $totalTurnoverQuery = $this->applyTenantFilter(
            $this->database->table('invoices')
                ->where('YEAR(issue_date) = ?', $currentYear)
        );
        $totalTurnover = 0;
        /** @var Nette\Database\Table\ActiveRow $invoice */
        foreach ($totalTurnoverQuery as $invoice) {
            $totalTurnover += (float)$invoice->total;
        }
        
        // Zaplacené částky
        $paidAmountQuery = $this->applyTenantFilter(
            $this->database->table('invoices')
                ->where('YEAR(issue_date) = ?', $currentYear)
                ->where('status', 'paid')
        );
        $paidAmount = 0;
        /** @var Nette\Database\Table\ActiveRow $invoice */
        foreach ($paidAmountQuery as $invoice) {
            $paidAmount += (float)$invoice->total;
        }
        
        // Nezaplacené částky
        $unpaidAmountQuery = $this->applyTenantFilter(
            $this->database->table('invoices')
                ->where('YEAR(issue_date) = ?', $currentYear)
                ->where('status != ?', 'paid')
        );
        $unpaidAmount = 0;
        /** @var Nette\Database\Table\ActiveRow $invoice */
        foreach ($unpaidAmountQuery as $invoice) {
            $unpaidAmount += (float)$invoice->total;
        }

        $result = [
            'totalCount' => $totalCount,
            'paidCount' => $paidCount,
            'unpaidCount' => $unpaidCount,
            'overdueCount' => $overdueCount,
            'totalTurnover' => $totalTurnover,
            'paidAmount' => $paidAmount,
            'unpaidAmount' => $unpaidAmount,
            'year' => $currentYear,
            'tenant_id' => $this->currentTenantId,
            'is_super_admin' => $this->isSuperAdmin
        ];
        
        error_log("DEBUG - Výsledné statistiky s tenant filtrem: " . json_encode($result));
        
        return $result;
    }

    /**
     * Získá statistiky pro konkrétní měsíc s tenant filtrováním
     */
    public function getMonthlyStats(int $year, int $month): array
    {
        error_log("FinancialReportsService: Načítám měsíční statistiky pro $year/$month, tenant: " . ($this->currentTenantId ?? 'všechny'));

        // Celkový počet faktur
        $totalCount = $this->applyTenantFilter(
            $this->database->table('invoices')
                ->where('YEAR(issue_date) = ? AND MONTH(issue_date) = ?', $year, $month)
        )->count();
        
        // Zaplacené faktury
        $paidCount = $this->applyTenantFilter(
            $this->database->table('invoices')
                ->where('YEAR(issue_date) = ? AND MONTH(issue_date) = ?', $year, $month)
                ->where('status', 'paid')
        )->count();
            
        // Nezaplacené faktury
        $unpaidCount = $this->applyTenantFilter(
            $this->database->table('invoices')
                ->where('YEAR(issue_date) = ? AND MONTH(issue_date) = ?', $year, $month)
                ->where('status != ?', 'paid')
        )->count();
            
        // Po splatnosti
        $overdueCount = $this->applyTenantFilter(
            $this->database->table('invoices')
                ->where('YEAR(issue_date) = ? AND MONTH(issue_date) = ?', $year, $month)
                ->where('status', 'overdue')
        )->count();
        
        // Ruční sčítání pro jistotu s tenant filtrem
        $totalTurnover = 0;
        $paidAmount = 0;
        $unpaidAmount = 0;
        
        $allMonthInvoices = $this->applyTenantFilter(
            $this->database->table('invoices')
                ->where('YEAR(issue_date) = ? AND MONTH(issue_date) = ?', $year, $month)
        );
        
        /** @var Nette\Database\Table\ActiveRow $invoice */
        foreach ($allMonthInvoices as $invoice) {
            $amount = (float)$invoice->total;
            $totalTurnover += $amount;
            
            if ($invoice->status === 'paid') {
                $paidAmount += $amount;
            } else {
                $unpaidAmount += $amount;
            }
        }

        return [
            'totalCount' => $totalCount,
            'paidCount' => $paidCount,
            'unpaidCount' => $unpaidCount,
            'overdueCount' => $overdueCount,
            'totalTurnover' => $totalTurnover,
            'paidAmount' => $paidAmount,
            'unpaidAmount' => $unpaidAmount,
            'year' => $year,
            'month' => $month,
            'monthName' => $this->getMonthName($month),
            'tenant_id' => $this->currentTenantId,
            'is_super_admin' => $this->isSuperAdmin
        ];
    }

    /**
     * Kontrola DPH limitů s tenant filtrováním
     */
    public function checkVatLimits(): array
    {
        $currentYear = date('Y');
        
        error_log("FinancialReportsService: Kontrolujem DPH limity pro rok $currentYear, tenant: " . ($this->currentTenantId ?? 'všechny'));
        
        // Celkový obrat za aktuální rok s tenant filtrem - ruční sčítání
        $yearlyTurnover = 0;
        $yearInvoices = $this->applyTenantFilter(
            $this->database->table('invoices')
                ->where('YEAR(issue_date) = ?', $currentYear)
        );
        
        /** @var Nette\Database\Table\ActiveRow $invoice */
        foreach ($yearInvoices as $invoice) {
            $yearlyTurnover += (float)$invoice->total;
        }
        
        error_log("DEBUG - DPH obrat za rok $currentYear (tenant filtrované): $yearlyTurnover");

        $alerts = [];
        
        // První limit: 2 000 000 Kč
        if ($yearlyTurnover >= 2000000 && $yearlyTurnover < 2536500) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Stanete se plátcem DPH od 1. ledna následujícího roku',
                'message' => 'Registrovat k DPH se musíte do 10 dnů.',
                'amount' => $yearlyTurnover,
                'limit' => 2000000
            ];
        }
        
        // Druhý limit: 2 536 500 Kč
        if ($yearlyTurnover >= 2536500) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Stáváte se ihned plátcem DPH',
                'message' => 'Registrovat k DPH se musíte do 10 dnů.',
                'amount' => $yearlyTurnover,
                'limit' => 2536500
            ];
        }

        // Pokrok k dalšímu limitu
        $nextLimit = $yearlyTurnover < 2000000 ? 2000000 : 2536500;
        $progressToNextLimit = $yearlyTurnover < 2000000 
            ? ($yearlyTurnover / 2000000) * 100 
            : (($yearlyTurnover - 2000000) / 536500) * 100;

        return [
            'currentTurnover' => $yearlyTurnover,
            'alerts' => $alerts,
            'nextLimit' => $nextLimit,
            'progressToNextLimit' => $progressToNextLimit,
            'tenant_id' => $this->currentTenantId,
            'is_super_admin' => $this->isSuperAdmin
        ];
    }

    /**
     * Získá data pro graf příjmů po měsících s tenant filtrováním
     */
    public function getMonthlyIncomeData(int $year): array
    {
        error_log("FinancialReportsService: Načítám měsíční příjmy pro rok $year, tenant: " . ($this->currentTenantId ?? 'všechny'));

        $monthlyData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthlyIncome = 0;
            
            $monthInvoices = $this->applyTenantFilter(
                $this->database->table('invoices')
                    ->where('YEAR(issue_date) = ? AND MONTH(issue_date) = ?', $year, $month)
                    ->where('status', 'paid')
            );
            
            /** @var Nette\Database\Table\ActiveRow $invoice */
            foreach ($monthInvoices as $invoice) {
                $monthlyIncome += (float)$invoice->total;
            }
            
            $monthlyData[] = [
                'month' => $month,
                'monthName' => $this->getMonthName($month),
                'income' => $monthlyIncome
            ];
        }

        return $monthlyData;
    }

    /**
     * Získá seznam faktur pro přehled s tenant filtrováním
     */
    public function getInvoicesOverview(?int $year = null, ?int $month = null, int $limit = 50): array
    {
        error_log("FinancialReportsService: Načítám přehled faktur - rok: " . ($year ?? 'všechny') . ", měsíc: " . ($month ?? 'všechny') . ", tenant: " . ($this->currentTenantId ?? 'všechny'));

        $query = $this->database->table('invoices');
        
        if ($year) {
            $query->where('YEAR(issue_date) = ?', $year);
        }
        
        if ($month) {
            $query->where('MONTH(issue_date) = ?', $month);
        }

        // Aplikujeme tenant filtr
        $query = $this->applyTenantFilter($query);
        
        return $query->order('issue_date DESC, number DESC')
            ->limit($limit)
            ->fetchAll();
    }

    /**
     * Pomocná metoda pro získání názvu měsíce
     */
    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'Leden', 2 => 'Únor', 3 => 'Březen', 4 => 'Duben',
            5 => 'Květen', 6 => 'Červen', 7 => 'Červenec', 8 => 'Srpen',
            9 => 'Září', 10 => 'Říjen', 11 => 'Listopad', 12 => 'Prosinec'
        ];
        
        return $months[$month] ?? 'Neznámý';
    }

    /**
     * Formátuje částku do českého formátu
     */
    public function formatAmount(float $amount): string
    {
        return number_format($amount, 0, ',', ' ') . ' Kč';
    }
}
