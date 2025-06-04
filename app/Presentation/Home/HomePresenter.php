<?php

declare(strict_types=1);

namespace App\Presentation\Home;

use Nette;
use App\Model\InvoicesManager;
use App\Model\ClientsManager;
use App\Model\CompanyManager;
use App\Presentation\BasePresenter;

final class HomePresenter extends BasePresenter
{
    /** @var InvoicesManager */
    private $invoicesManager;

    /** @var ClientsManager */
    private $clientsManager;

    /** @var CompanyManager */
    private $companyManager;

    protected array $requiredRoles = ['readonly', 'accountant', 'admin'];

    public function __construct(
        InvoicesManager $invoicesManager,
        ClientsManager $clientsManager,
        CompanyManager $companyManager
    ) {
        $this->invoicesManager = $invoicesManager;
        $this->clientsManager = $clientsManager;
        $this->companyManager = $companyManager;
    }

    public function renderDefault(): void
    {
        try {
            // Kontrola faktur po splatnosti (pouze pro účetní a admin)
            if ($this->isAccountant()) {
                $this->invoicesManager->checkOverdueDates();
            }

            // Dashboard statistiky s bezpečnou kontrolou
            $invoiceStats = $this->invoicesManager->getStatistics();
            
            // Bezpečné získání počtu klientů
            $clientsSelection = $this->clientsManager->getAll();
            $clientsCount = 0;
            if ($clientsSelection && method_exists($clientsSelection, 'count')) {
                $clientsCount = $clientsSelection->count();
            } elseif (is_array($clientsSelection)) {
                $clientsCount = count($clientsSelection);
            }
            
            $company = $this->companyManager->getCompanyInfo();

            // Připravíme data pro dashboard
            $this->template->dashboardStats = [
                'clients' => $clientsCount,
                'invoices' => [
                    'total' => $invoiceStats['totalCount'],
                    'paid' => $invoiceStats['paidCount'],
                    'overdue' => $invoiceStats['overdueCount'],
                    'unpaidAmount' => $invoiceStats['unpaidAmount']
                ]
            ];

            // Logika pro "Začínáme" sekci (pouze pro admin)
            if ($this->isAdmin()) {
                $setupSteps = $this->getSetupSteps($company, $clientsCount, $invoiceStats['totalCount']);
                $this->template->setupSteps = $setupSteps;
                $this->template->isSetupComplete = empty($setupSteps);
            } else {
                $this->template->setupSteps = [];
                $this->template->isSetupComplete = true;
            }

            // Blížící se splatnosti (faktury splatné do 7 dnů)
            if ($this->isAccountant()) {
                $upcomingInvoices = $this->getUpcomingDueInvoices();
                $this->template->upcomingInvoices = $upcomingInvoices;

                // Nedávné faktury (posledních 5)
                $recentInvoices = $this->invoicesManager->getAll()->limit(5);
                $this->template->recentInvoices = $recentInvoices;
            } else {
                // Pro readonly uživatele zobrazíme méně informací
                $this->template->upcomingInvoices = [];
                $this->template->recentInvoices = [];
            }

            $this->template->company = $company;
            
        } catch (\Exception $e) {
            // Logování chyby pro debug
            error_log('Chyba v HomePresenter::renderDefault(): ' . $e->getMessage());
            
            // Fallback hodnoty
            $this->template->dashboardStats = [
                'clients' => 0,
                'invoices' => [
                    'total' => 0,
                    'paid' => 0,
                    'overdue' => 0,
                    'unpaidAmount' => 0
                ]
            ];
            $this->template->setupSteps = [];
            $this->template->isSetupComplete = true;
            $this->template->upcomingInvoices = [];
            $this->template->recentInvoices = [];
            $this->template->company = null;
            
            // Zobrazíme chybovou hlášku
            $this->flashMessage('Došlo k chybě při načítání dashboardu. Zkuste to prosím znovu.', 'warning');
        }
    }

    /**
     * Určí, které kroky nastavení ještě zbývají
     */
    private function getSetupSteps(?object $company, int $clientsCount, int $invoicesCount): array
    {
        $steps = [];

        // Krok 1: Nastavení firemních údajů
        if (!$company || empty($company->name) || empty($company->ic) || empty($company->bank_account)) {
            $steps[] = [
                'title' => 'Nastavte firemní údaje',
                'description' => 'Vyplňte základní informace o vaší společnosti',
                'icon' => 'bi-gear',
                'link' => $this->link('Settings:default'),
                'linkText' => 'Upravit nastavení',
                'priority' => 1
            ];
        }

        // Krok 2: Přidání klientů
        if ($clientsCount === 0) {
            $steps[] = [
                'title' => 'Přidejte prvního klienta',
                'description' => 'Vytvořte databázi svých klientů pro rychlejší fakturaci',
                'icon' => 'bi-people',
                'link' => $this->link('Clients:add'),
                'linkText' => 'Přidat klienta',
                'priority' => 2
            ];
        }

        // Krok 3: Vystavení první faktury
        if ($invoicesCount === 0) {
            $steps[] = [
                'title' => 'Vystavte první fakturu',
                'description' => 'Začněte fakturovat a využívejte plný potenciál systému',
                'icon' => 'bi-file-earmark-text',
                'link' => $this->link('Invoices:add'),
                'linkText' => 'Vytvořit fakturu',
                'priority' => 3
            ];
        }

        // Seřadíme podle priority
        usort($steps, function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $steps;
    }

    /**
     * Získá faktury se splatností do 7 dnů
     */
    private function getUpcomingDueInvoices()
    {
        $sevenDaysFromNow = new \DateTime('+7 days');
        
        return $this->invoicesManager->getAll()
            ->where('status', 'created')
            ->where('due_date BETWEEN ? AND ?', date('Y-m-d'), $sevenDaysFromNow->format('Y-m-d'))
            ->order('due_date ASC')
            ->limit(5);
    }
}