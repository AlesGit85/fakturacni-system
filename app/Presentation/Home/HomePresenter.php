<?php

declare(strict_types=1);

namespace App\Presentation\Home;

use Nette;
use App\Model\InvoicesManager;
use App\Model\ClientsManager;
use App\Model\CompanyManager;
use App\Model\UserManager;
use App\Presentation\BasePresenter;

final class HomePresenter extends BasePresenter
{
    /** @var InvoicesManager */
    private $invoicesManager;

    /** @var ClientsManager */
    private $clientsManager;

    /** @var CompanyManager */
    private $companyManager;

    /** @var UserManager */
    private $userManager;

    protected array $requiredRoles = ['readonly', 'accountant', 'admin'];

    public function __construct(
        InvoicesManager $invoicesManager,
        ClientsManager $clientsManager,
        CompanyManager $companyManager,
        UserManager $userManager
    ) {
        $this->invoicesManager = $invoicesManager;
        $this->clientsManager = $clientsManager;
        $this->companyManager = $companyManager;
        $this->userManager = $userManager;
    }

    /**
     * MULTI-TENANCY: Nastavení tenant kontextu po spuštění presenteru
     */
    public function startup(): void
    {
        parent::startup();
        
        // Nastavíme tenant kontext v manažerech
        $this->clientsManager->setTenantContext(
            $this->getCurrentTenantId(),
            $this->isSuperAdmin()
        );
        
        // AKTUALIZOVÁNO: InvoicesManager má nyní multi-tenancy
        $this->invoicesManager->setTenantContext(
            $this->getCurrentTenantId(),
            $this->isSuperAdmin()
        );
        
        // AKTUALIZOVÁNO: CompanyManager má nyní multi-tenancy
        $this->companyManager->setTenantContext(
            $this->getCurrentTenantId(),
            $this->isSuperAdmin()
        );
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
            
            // Statistiky klientů
            $clientsCount = $this->clientsManager->getAll()->count();
            
            // Informace o společnosti
            $company = $this->companyManager->getCompanyInfo();

            // Informace o aktuálním uživateli
            $currentUser = $this->getUser()->getIdentity();
            $userDisplayName = '';
            $userFullName = '';

            if ($currentUser) {
                // Získání informací o uživateli z databáze pro zobrazení celého jména
                $userData = $this->userManager->getById($currentUser->id);
                
                if ($userData) {
                    $userDisplayName = $userData->username;
                    $userFullName = trim($userData->first_name . ' ' . $userData->last_name);
                    
                    // Pokud je celé jméno prázdné, použijeme username
                    if (empty($userFullName)) {
                        $userFullName = $userData->username;
                    }
                }
            }

            // Příprava dat pro dashboard
            $this->template->dashboardStats = [
                'clients' => $clientsCount,
                'invoices' => [
                    'total' => $invoiceStats['total'] ?? 0,
                    'paid' => $invoiceStats['paid'] ?? 0,
                    'overdue' => $invoiceStats['overdue'] ?? 0,
                    'unpaidAmount' => $invoiceStats['unpaid_amount'] ?? 0
                ]
            ];

            // Logika pro "Začínáme" sekci (pouze pro admin)
            if ($this->isAdmin()) {
                $setupSteps = $this->getSetupSteps($company, $clientsCount, $invoiceStats['total'] ?? 0);
                $this->template->setupSteps = $setupSteps;
                $this->template->isSetupComplete = empty($setupSteps);
            } else {
                $this->template->setupSteps = [];
                $this->template->isSetupComplete = true;
            }

            // Blížící se splatnosti (faktury splatné do 7 dnů) - pro všechny přihlášené uživatele
            if ($this->isReadonly()) {
                $upcomingInvoices = $this->getUpcomingDueInvoices();
                $this->template->upcomingInvoices = $upcomingInvoices;

                // Nedávné faktury (posledních 5) - pro všechny přihlášené uživatele
                $recentInvoices = $this->invoicesManager->getAll()->limit(5);
                $this->template->recentInvoices = $recentInvoices;
            } else {
                // Fallback pro nepřihlášené uživatele (nemělo by se stát)
                $this->template->upcomingInvoices = [];
                $this->template->recentInvoices = [];
            }

            // Předání dat o uživateli do šablony
            $this->template->company = $company;
            $this->template->currentUserData = $currentUser;
            $this->template->userDisplayName = $userDisplayName;
            $this->template->userFullName = $userFullName;
            
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
            $this->template->currentUserData = null;
            $this->template->userDisplayName = '';
            $this->template->userFullName = '';
            
            // Zobrazíme chybovou hlášku
            $this->flashMessage('Došlo k chybě při načítání dashboardu. Zkuste to prosím znovu.', 'warning');
        }
    }

    /**
     * Získá kroky pro dokončení nastavení systému
     */
    private function getSetupSteps($company, int $clientsCount, int $invoicesCount): array
    {
        $steps = [];

        // Krok 1: Nastavení údajů společnosti
        if (!$company || empty($company->name) || empty($company->address) || empty($company->ic)) {
            $steps[] = [
                'title' => 'Doplňte údaje vaší společnosti',
                'description' => 'Nastavte název, adresu, IČO a další informace o vaší firmě.',
                'link' => $this->link('Settings:default'),
                'linkText' => 'Nastavit údaje',
                'icon' => 'bi-building',
                'priority' => 1
            ];
        }

        // Krok 2: Nastavení bankovního účtu
        if (!$company || empty($company->bank_account)) {
            $steps[] = [
                'title' => 'Nastavte bankovní účet',
                'description' => 'Přidejte číslo bankovního účtu pro platby faktur.',
                'link' => $this->link('Settings:default'),
                'linkText' => 'Přidat účet',
                'icon' => 'bi-bank',
                'priority' => 2
            ];
        }

        // Krok 3: Přidání prvního klienta
        if ($clientsCount === 0) {
            $steps[] = [
                'title' => 'Přidejte prvního klienta',
                'description' => 'Vytvořte záznam o vašem prvním klientovi.',
                'link' => $this->link('Clients:add'),
                'linkText' => 'Přidat klienta',
                'icon' => 'bi-person-plus',
                'priority' => 3
            ];
        }

        // Krok 4: Vytvoření první faktury
        if ($invoicesCount === 0) {
            $steps[] = [
                'title' => 'Vytvořte první fakturu',
                'description' => 'Vystavte svou první fakturu a vyzkoušejte si systém.',
                'link' => $this->link('Invoices:add'),
                'linkText' => 'Vytvořit fakturu',
                'icon' => 'bi-file-earmark-plus',
                'priority' => 4
            ];
        }

        // Seřadíme podle priority
        usort($steps, function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $steps;
    }

    /**
     * Získá faktury s blížící se splatností (do 7 dnů)
     */
    private function getUpcomingDueInvoices()
    {
        $sevenDaysFromNow = new \DateTime('+7 days');
        $today = new \DateTime();

        return $this->invoicesManager->getAll()
            ->where('status', 'created')
            ->where('due_date >= ?', $today->format('Y-m-d'))
            ->where('due_date <= ?', $sevenDaysFromNow->format('Y-m-d'))
            ->order('due_date ASC')
            ->limit(5);
    }
}