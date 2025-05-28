<?php

declare(strict_types=1);

namespace Modules\Financial_reports;

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
        
        $totalCount = $allInvoices->count();
        $paidCount = $allInvoices->where('status', 'paid')->count();
        $unpaidCount = $allInvoices->where('status != ?', 'paid')->count();
        $overdueCount = $allInvoices->where('status', 'overdue')->count();
        
        // Celkový obrat (všechny vystavené faktury)
        $totalTurnover = $allInvoices->sum('total') ?: 0;
        
        // Zaplacené částky
        $paidAmount = $allInvoices->where('status', 'paid')->sum('total') ?: 0;
        
        // Nezaplacené částky
        $unpaidAmount = $allInvoices->where('status != ?', 'paid')->sum('total') ?: 0;

        return [
            'totalCount' => $totalCount,
            'paidCount' => $paidCount,
            'unpaidCount' => $unpaidCount,
            'overdueCount' => $overdueCount,
            'totalTurnover' => $totalTurnover,
            'paidAmount' => $paidAmount,
            'unpaidAmount' => $unpaidAmount,
            'year' => $currentYear
        ];
    }

    /**
     * Získá statistiky pro konkrétní měsíc
     */
    public function getMonthlyStats(int $year, int $month): array
    {
        $invoices = $this->database->table('invoices')
            ->where('YEAR(issue_date) = ? AND MONTH(issue_date) = ?', $year, $month);
        
        $totalCount = $invoices->count();
        $paidCount = $invoices->where('status', 'paid')->count();
        $unpaidCount = $invoices->where('status != ?', 'paid')->count();
        $overdueCount = $invoices->where('status', 'overdue')->count();
        
        $totalTurnover = $invoices->sum('total') ?: 0;
        $paidAmount = $invoices->where('status', 'paid')->sum('total') ?: 0;
        $unpaidAmount = $invoices->where('status != ?', 'paid')->sum('total') ?: 0;

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
        
        // Celkový obrat za aktuální rok
        $yearlyTurnover = $this->database->table('invoices')
            ->where('YEAR(issue_date) = ?', $currentYear)
            ->sum('total') ?: 0;

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

        return [
            'currentTurnover' => $yearlyTurnover,
            'alerts' => $alerts,
            'nextLimit' => $yearlyTurnover < 2000000 ? 2000000 : 2536500,
            'progressToNextLimit' => $yearlyTurnover < 2000000 
                ? ($yearlyTurnover / 2000000) * 100 
                : (($yearlyTurnover - 2000000) / 536500) * 100
        ];
    }

    /**
     * Získá data pro graf příjmů po měsících
     */
    public function getMonthlyIncomeData(int $year): array
    {
        $monthlyData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthlyIncome = $this->database->table('invoices')
                ->where('YEAR(issue_date) = ? AND MONTH(issue_date) = ?', $year, $month)
                ->where('status', 'paid')
                ->sum('total') ?: 0;
            
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