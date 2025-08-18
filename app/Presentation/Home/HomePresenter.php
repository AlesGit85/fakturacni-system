<?php

declare(strict_types=1);

namespace App\Presentation\Home;

use Nette;
use App\Model\InvoicesManager;
use App\Model\ClientsManager;
use App\Model\CompanyManager;
use App\Model\UserManager;
use App\Model\ModuleManager;
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

    /** @var ModuleManager */
    private $moduleManager;

    protected array $requiredRoles = ['readonly', 'accountant', 'admin'];

    public function __construct(
        InvoicesManager $invoicesManager,
        ClientsManager $clientsManager,
        CompanyManager $companyManager,
        UserManager $userManager,
        ModuleManager $moduleManager
    ) {
        $this->invoicesManager = $invoicesManager;
        $this->clientsManager = $clientsManager;
        $this->companyManager = $companyManager;
        $this->userManager = $userManager;
        $this->moduleManager = $moduleManager;
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

        // AKTUALIZOVÁNO: UserManager má nyní multi-tenancy
        $this->userManager->setTenantContext(
            $this->getCurrentTenantId(),
            $this->isSuperAdmin()
        );
    }

    public function renderDefault(): void
    {
        try {
            // NOVÉ: Pokud je uživatel super admin, zobrazíme jiný dashboard
            if ($this->isSuperAdmin()) {
                $this->renderSuperAdminDashboard();
                return;
            }

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

            // OPRAVENO: Informace o aktuálním uživateli s pátým pádem
            $currentUser = $this->getUser()->getIdentity();
            $userDisplayName = '';
            $userFullName = '';

            if ($currentUser) {
                // OPRAVA: Použijeme přímý DB dotaz místo UserManager (který má problém s tenant filtrováním)
                $userData = $this->database->query('SELECT * FROM users WHERE id = ?', $currentUser->getId())->fetch();
                
                if ($userData) {
                    // Pátý pád (vokativ) pro oslovení
                    $userDisplayName = $this->getVocativeName($userData->first_name);
                    $userFullName = trim($userData->first_name . ' ' . $userData->last_name);
                }
            }

            // Předání dat do šablony
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
            $this->flashMessage('Došlo k chybě při načítání dashboardu. Zkuste to prosím znovu.', 'danger');
        }
    }

    /**
     * NOVÁ METODA: Vykreslení super admin dashboardu
     */
    public function renderSuperAdminDashboard(): void
    {
        try {
            // Získání super admin statistik
            $superAdminStats = $this->getSuperAdminStatistics();
            
            // Informace o aktuálním uživateli (i pro super admina)
            $currentUser = $this->getUser()->getIdentity();
            $userDisplayName = '';
            $userFullName = '';

            if ($currentUser) {
                $userData = $this->database->query('SELECT * FROM users WHERE id = ?', $currentUser->getId())->fetch();
                
                if ($userData) {
                    $userDisplayName = $this->getVocativeName($userData->first_name);
                    $userFullName = trim($userData->first_name . ' ' . $userData->last_name);
                }
            }
            
            // Předání dat do šablony
            $this->template->superAdminStats = $superAdminStats;
            $this->template->isSuperAdmin = true;
            $this->template->currentUserData = $currentUser;
            $this->template->userDisplayName = $userDisplayName;
            $this->template->userFullName = $userFullName;
            
            // Super admin nepotřebuje tyto sekce, ale nastavíme je pro kompatibilitu šablony
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
            
        } catch (\Exception $e) {
            error_log('Chyba v super admin dashboardu: ' . $e->getMessage());
            
            // Fallback hodnoty pro super admin
            $this->template->superAdminStats = [
                'total_tenants' => 0,
                'total_users' => 0,
                'total_clients' => 0,
                'total_active_modules' => 0,
                'total_invoices' => 0,
                'latest_tenant_registration' => null,
                'blocked_ips_count' => 0,
                'failed_attempts_24h' => 0
            ];
            $this->template->isSuperAdmin = true;
            $this->template->currentUserData = null;
            $this->template->userDisplayName = '';
            $this->template->userFullName = '';
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
            
            $this->flashMessage('Došlo k chybě při načítání super admin dashboardu.', 'danger');
        }
    }

    /**
     * NOVÁ METODA: Získání statistik pro super admin dashboard
     */
    private function getSuperAdminStatistics(): array
    {
        try {
            // Počet tenantů
            $totalTenants = $this->database->table('tenants')->count();
            
            // Počet všech uživatelů v systému
            $totalUsers = $this->database->table('users')
                ->where('tenant_id IS NOT NULL')
                ->count();
            
            // Počet všech klientů v systému
            $totalClients = $this->database->table('clients')->count();
            
            // Celkový počet aktivních modulů
            $totalActiveModules = $this->database->table('user_modules')
                ->where('is_active', 1)
                ->count();
            
            // Celkový počet faktur v systému
            $totalInvoices = $this->database->table('invoices')->count();
            
            // Datum registrace posledního tenanta
            $latestTenant = $this->database->table('tenants')
                ->order('created_at DESC')
                ->limit(1)
                ->fetch();
            $latestTenantRegistration = $latestTenant ? $latestTenant->created_at : null;
            
            // Počet aktuálně blokovaných IP adres
            $blockedIpsCount = $this->getSafeBlockedIpsCount();
            
            // Počet neúspěšných pokusů za 24h
            $failedAttempts24h = $this->getSafeFailedAttempts24h();
            
            return [
                'total_tenants' => $totalTenants,
                'total_users' => $totalUsers,
                'total_clients' => $totalClients,
                'total_active_modules' => $totalActiveModules,
                'total_invoices' => $totalInvoices,
                'latest_tenant_registration' => $latestTenantRegistration,
                'blocked_ips_count' => $blockedIpsCount,
                'failed_attempts_24h' => $failedAttempts24h
            ];
            
        } catch (\Exception $e) {
            error_log('Chyba při načítání super admin statistik: ' . $e->getMessage());
            
            return [
                'total_tenants' => 0,
                'total_users' => 0,
                'total_clients' => 0,
                'total_active_modules' => 0,
                'total_invoices' => 0,
                'latest_tenant_registration' => null,
                'blocked_ips_count' => 0,
                'failed_attempts_24h' => 0
            ];
        }
    }

    /**
     * POMOCNÁ METODA: Bezpečné načtení počtu blokovaných IP adres
     */
    private function getSafeBlockedIpsCount(): int
    {
        try {
            $now = new \DateTime();
            return $this->database->table('rate_limit_blocks')
                ->where('blocked_until > ?', $now)
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * POMOCNÁ METODA: Bezpečné načtení počtu neúspěšných pokusů za 24h
     */
    private function getSafeFailedAttempts24h(): int
    {
        try {
            $yesterday = new \DateTime('-24 hours');
            return $this->database->table('rate_limits')
                ->where('successful', 0)
                ->where('created_at > ?', $yesterday)
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }



    /**
     * Získá kroky pro dokončení nastavení systému
     */
    private function getSetupSteps($company, int $clientsCount, int $invoicesCount): array
    {
        $steps = [];

        // Kontrola údajů o společnosti
        if (!$company || empty($company->name) || empty($company->address)) {
            $steps[] = [
                'title' => 'Dokončete údaje o společnosti',
                'description' => 'Vyplňte název, adresu a další kontaktní údaje vaší společnosti.',
                'link' => $this->link('Settings:default'),
                'icon' => 'bi-building',
                'priority' => 1
            ];
        }

        // Kontrola klientů
        if ($clientsCount === 0) {
            $steps[] = [
                'title' => 'Přidejte prvního klienta',
                'description' => 'Vytvořte záznam o vašem prvním klientovi pro snadnější fakturaci.',
                'link' => $this->link('Clients:add'),
                'icon' => 'bi-person-plus',
                'priority' => 2
            ];
        }

        // Kontrola faktur
        if ($invoicesCount === 0 && $clientsCount > 0) {
            $steps[] = [
                'title' => 'Vytvořte první fakturu',
                'description' => 'Zkuste si vytvořit vaši první fakturu v systému.',
                'link' => $this->link('Invoices:add'),
                'icon' => 'bi-file-earmark-plus',
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
     * Získá faktury splatné do 7 dnů
     */
    private function getUpcomingDueInvoices()
    {
        $sevenDaysFromNow = new \DateTime('+7 days');
        
        return $this->invoicesManager->getAll()
            ->where('status', ['created', 'overdue'])
            ->where('due_date <= ?', $sevenDaysFromNow)
            ->order('due_date ASC')
            ->limit(5);
    }
}