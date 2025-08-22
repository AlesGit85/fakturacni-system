<?php

declare(strict_types=1);

namespace Modules\Tenant12\Financial_reports;

use Nette;
use App\Model\InvoicesManager;
use App\Model\CompanyManager;

/**
 * Služba pro finanční přehledy
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
     * Získá základní finanční statistiky
     */
    public function getBasicStats(): array
    {
        $currentYear = date('Y');
        
        // Všechny faktury v aktuálním roce
        $allInvoices = $this->database->table('invoices')
            ->where('YEAR(issue_date) = ?', $currentYear);
        
        // Debug: Zjistíme si všechny faktury pro kontrolu
        $allInvoicesArray = $allInvoices->fetchAll();
        $debugInfo = [];
        foreach ($allInvoicesArray as $invoice) {
            $debugInfo[] = [
                'number' => $invoice->number,
                'status' => $invoice->status,
                'total' => $invoice->total,
                'issue_date' => $invoice->issue_date
            ];
        }
        error_log("DEBUG - Všechny faktury pro rok $currentYear: " . json_encode($debugInfo));
        
        // Znovu vytvoříme query pro počítání
        $baseQuery = $this->database->table('invoices')
            ->where('YEAR(issue_date) = ?', $currentYear);
        
        $totalCount = $baseQuery->count();
        
        // Zaplacené faktury
        $paidQuery = $this->database->table('invoices')
            ->where('YEAR(issue_date) = ?', $currentYear)
            ->where('status', 'paid');
        $paidCount = $paidQuery->count();
        
        // Nezaplacené faktury (created + overdue)
        $unpaidQuery = $this->database->table('invoices')
            ->where('YEAR(issue_date) = ?', $currentYear)
            ->where('status != ?', 'paid');
        $unpaidCount = $unpaidQuery->count();
        
        // Po splatnosti
        $overdueQuery = $this->database->table('invoices')
            ->where('YEAR(issue_date) = ?', $currentYear)
            ->where('status', 'overdue');
        $overdueCount = $overdueQuery->count();
        
        // Celkový obrat (všechny vystavené faktury)
        $totalTurnoverQuery = $this->database->table('invoices')
            ->where('YEAR(issue_date) = ?', $currentYear);
        $totalTurnover = 0;
        foreach ($totalTurnoverQuery as $invoice) {
            $totalTurnover += (float)$invoice->total;
        }
        
        // Zaplacené částky
        $paidAmountQuery = $this->database->table('invoices')
            ->where('YEAR(issue_date) = ?', $currentYear)
            ->where('status', 'paid');
        $paidAmount = 0;
        foreach ($paidAmountQuery as $invoice) {
            $paidAmount += (float)$invoice->total;
        }
        
        // Nezaplacené částky
        $unpaidAmountQuery = $this->database->table('invoices')
            ->where('YEAR(issue_date) = ?', $currentYear)
            ->where('status != ?', 'paid');
        $unpaidAmount = 0;
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
            'year' => $currentYear
        ];
        
        error_log("DEBUG - Výsledné statistiky: " . json_encode($result));
        
        return $result;
    }

    /**
     * Získá statistiky pro konkrétní měsíc
     */
    public function getMonthlyStats(int $year, int $month): array
    {
        $invoices = $this->database->table('invoices')
            ->where('YEAR(issue_date) = ? AND MONTH(issue_date) = ?', $year, $month);
        
        $totalCount = $invoices->count();
        
        // Znovu vytvoříme queries pro jednotlivé kategorie
        $paidCount = $this->database->table('invoices')
            ->where('YEAR(issue_date) = ? AND MONTH(issue_date) = ?', $year, $month)
            ->where('status', 'paid')
            ->count();
            
        $unpaidCount = $this->database->table('invoices')
            ->where('YEAR(issue_date) = ? AND MONTH(issue_date) = ?', $year, $month)
            ->where('status != ?', 'paid')
            ->count();
            
        $overdueCount = $this->database->table('invoices')
            ->where('YEAR(issue_date) = ? AND MONTH(issue_date) = ?', $year, $month)
            ->where('status', 'overdue')
            ->count();
        
        // Ruční sčítání pro jistotu
        $totalTurnover = 0;
        $paidAmount = 0;
        $unpaidAmount = 0;
        
        $allMonthInvoices = $this->database->table('invoices')
            ->where('YEAR(issue_date) = ? AND MONTH(issue_date) = ?', $year, $month);
            
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
            'monthName' => $this->getMonthName($month)
        ];
    }

    /**
     * Kontrola DPH limitů
     */
    public function checkVatLimits(): array
    {
        $currentYear = date('Y');
        
        // Celkový obrat za aktuální rok - ruční sčítání
        $yearlyTurnover = 0;
        $yearInvoices = $this->database->table('invoices')
            ->where('YEAR(issue_date) = ?', $currentYear);
            
        foreach ($yearInvoices as $invoice) {
            $yearlyTurnover += (float)$invoice->total;
        }
        
        error_log("DEBUG - DPH obrat za rok $currentYear: $yearlyTurnover");

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
            'progressToNextLimit' => $progressToNextLimit
        ];
    }

    /**
     * Získá data pro graf příjmů po měsících
     */
    public function getMonthlyIncomeData(int $year): array
    {
        $monthlyData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthlyIncome = 0;
            
            $monthInvoices = $this->database->table('invoices')
                ->where('YEAR(issue_date) = ? AND MONTH(issue_date) = ?', $year, $month)
                ->where('status', 'paid');
                
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
     * Získá seznam faktur pro přehled
     */
    public function getInvoicesOverview(?int $year = null, ?int $month = null, int $limit = 50): array
    {
        $query = $this->database->table('invoices');
        
        if ($year) {
            $query->where('YEAR(issue_date) = ?', $year);
        }
        
        if ($month) {
            $query->where('MONTH(issue_date) = ?', $month);
        }
        
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