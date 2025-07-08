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
        
        // TODO: Přidat i pro InvoicesManager a CompanyManager až budou mít multi-tenancy
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
            
            // OPRAVENO: Bezpečné získání počtu klientů s tenant filtrováním
            $clientsSelection = $this->clientsManager->getAll();
            $clientsCount = 0;
            if ($clientsSelection && method_exists($clientsSelection, 'count')) {
                $clientsCount = $clientsSelection->count();
            } elseif (is_array($clientsSelection)) {
                $clientsCount = count($clientsSelection);
            }
            
            $company = $this->companyManager->getCompanyInfo();

            // Získání informací o přihlášeném uživateli
            $currentUser = null;
            $userDisplayName = '';
            $userFullName = '';
            
            if ($this->getUser()->isLoggedIn()) {
                $userId = $this->getUser()->getId();
                $currentUser = $this->userManager->getById($userId);
                
                if ($currentUser) {
                    // Místo getUserDisplayName() používáme vokativ pro křestní jména
                    if (!empty($currentUser->first_name)) {
                        // Pokud má křestní jméno, použijeme ho v 5. pádě (vokativ)
                        $userDisplayName = $this->getVocativeName($currentUser->first_name);
                    } else {
                        // Jinak použijeme username (bez vokativu, protože to není křestní jméno)
                        $userDisplayName = $currentUser->username;
                    }
                    
                    $userFullName = $this->userManager->getUserFullName($currentUser);
                }
            }

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
            $this->flashMessage('Došlo k chybě při načítání dashboardu. Zkuste to prosím znovu.', 'danger');
        }
    }

    /**
     * Vytváří seznam kroků pro dokončení nastavení systému
     */
    private function getSetupSteps($company, int $clientsCount, int $invoicesCount): array
    {
        $steps = [];

        // Kontrola firemních údajů
        if (!$company || empty($company->name) || empty($company->address)) {
            $steps[] = [
                'title' => 'Nastavte firemní údaje',
                'description' => 'Zadejte základní informace o vaší společnosti.',
                'icon' => 'bi-building',
                'link' => $this->link('Settings:default'),
                'action' => 'Nastavit údaje'
            ];
        }

        // Kontrola klientů
        if ($clientsCount === 0) {
            $steps[] = [
                'title' => 'Přidejte prvního klienta',
                'description' => 'Začněte přidáním klienta, kterému budete fakturovat.',
                'icon' => 'bi-people',
                'link' => $this->link('Clients:add'),
                'action' => 'Přidat klienta'
            ];
        }

        // Kontrola faktur (pouze pokud už má klienty)
        if ($clientsCount > 0 && $invoicesCount === 0) {
            $steps[] = [
                'title' => 'Vytvořte první fakturu',
                'description' => 'Vystavte první fakturu a začněte fakturovat.',
                'icon' => 'bi-file-earmark-text',
                'link' => $this->link('Invoices:add'),
                'action' => 'Vytvořit fakturu'
            ];
        }

        return $steps;
    }

    /**
     * Získá faktury blížící se splatnosti (do 7 dnů)
     */
    private function getUpcomingDueInvoices(): array
    {
        $upcomingDate = new \DateTime('+7 days');
        $today = new \DateTime();
        
        $upcomingInvoices = $this->invoicesManager->getAll()
            ->where('status', 'created')
            ->where('due_date >= ?', $today->format('Y-m-d'))
            ->where('due_date <= ?', $upcomingDate->format('Y-m-d'))
            ->order('due_date ASC')
            ->limit(5);

        return iterator_to_array($upcomingInvoices);
    }
}