<?php

declare(strict_types=1);

namespace App\Presentation;

use Nette;
use Nette\Application\UI\Presenter;
use App\Security\SecurityLogger;
use App\Security\RateLimiter;
use App\Model\ModuleManager;

abstract class BasePresenter extends Presenter
{
    /** @var array Definice požadovaných rolí pro jednotlivé presentery */
    protected array $requiredRoles = [];

    /** @var array Definice požadovaných rolí pro jednotlivé akce */
    protected array $actionRoles = [];

    /** @var bool Zda presenter vyžaduje přihlášení */
    protected bool $requiresLogin = true;

    /** @var bool Zda má presenter vypnuté rate limiting (pro SignPresenter) */
    protected bool $disableRateLimit = false;

    /** @var SecurityLogger */
    private $securityLogger;

    /** @var RateLimiter */
    private $rateLimiter;

    /** @var ModuleManager */
    private $moduleManager;

    /** @var Nette\Database\Explorer Databáze pro multi-tenancy dotazy */
    protected $database;

    public function injectSecurityLogger(SecurityLogger $securityLogger): void
    {
        $this->securityLogger = $securityLogger;
    }

    public function injectRateLimiter(RateLimiter $rateLimiter): void
    {
        $this->rateLimiter = $rateLimiter;
    }

    public function injectModuleManager(ModuleManager $moduleManager): void
    {
        $this->moduleManager = $moduleManager;
    }

    public function injectDatabase(Nette\Database\Explorer $database): void
    {
        $this->database = $database;
    }

    public function startup(): void
    {
        parent::startup();

        // ✅ NOVÉ: Rate Limiting kontrola PŘED všemi ostatními kontrolami
        if (!$this->disableRateLimit && $this->requiresLogin) {
            $this->checkRateLimit();
        }

        // Kontrola přihlášení
        if ($this->requiresLogin && !$this->getUser()->isLoggedIn()) {
            if ($this->getUser()->getLogoutReason() === Nette\Security\UserStorage::LOGOUT_INACTIVITY) {
                $this->flashMessage('Byli jste odhlášeni z důvodu neaktivity. Přihlaste se prosím znovu.', 'warning');
            } else {
                $this->flashMessage('Pro přístup k této stránce se musíte přihlásit.', 'info');
            }
            $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
        }

        // NOVÉ: Kontrola statusu tenanta pro přihlášené uživatele
        if ($this->requiresLogin && $this->getUser()->isLoggedIn()) {
            $this->checkTenantStatus();
        }

        // =====================================================
        // NASTAVENÍ MODULU KONTEXTU (NOVÉ!)
        // =====================================================
        if ($this->requiresLogin && $this->getUser()->isLoggedIn()) {
            $this->setupModuleContext();
        }

        // Kontrola rolí na úrovni presenteru
        if ($this->requiresLogin && !empty($this->requiredRoles)) {
            $identity = $this->getUser()->getIdentity();
            if ($identity && isset($identity->role)) {
                $userRole = $identity->role;
                if (!in_array($userRole, $this->requiredRoles)) {
                    // Logování pokusu o neoprávněný přístup
                    $resource = $this->getName() . ':' . $this->getAction();
                    $this->securityLogger->logUnauthorizedAccess($resource, $identity->id, $identity->username);

                    $this->flashMessage('Nemáte oprávnění pro přístup k této stránce.', 'danger');
                    $this->redirect('Home:default');
                }
            }
        }

        // Kontrola rolí na úrovni akce
        $action = $this->getAction();
        if ($this->requiresLogin && isset($this->actionRoles[$action]) && !empty($this->actionRoles[$action])) {
            $identity = $this->getUser()->getIdentity();
            if ($identity && isset($identity->role)) {
                $userRole = $identity->role;
                if (!$this->hasRequiredRoleForAction($action, $userRole)) {
                    // Logování pokusu o neoprávněný přístup k akci
                    $resource = $this->getName() . ':' . $action;
                    $this->securityLogger->logUnauthorizedAccess($resource, $identity->id, $identity->username);

                    $this->flashMessage('Nemáte oprávnění pro provedení této akce.', 'danger');
                    $this->redirect('Home:default');
                }
            }
        }
    }

    /**
     * ✅ NOVÉ: Globální Rate Limiting kontrola
     */
    private function checkRateLimit(): void
    {
        $clientIP = $this->rateLimiter->getClientIP();
        $action = 'form_submit'; // Obecné rate limiting pro všechny formuláře

        // Kontrola pouze pro POST požadavky (odesílání formulářů)
        if ($this->getHttpRequest()->isMethod('POST')) {
            if (!$this->rateLimiter->isAllowed($action, $clientIP)) {
                $status = $this->rateLimiter->getLimitStatus($action, $clientIP);
                $blockedUntil = $status['blocked_until'];
                $timeRemaining = $blockedUntil ? 
                    $blockedUntil->diff(new \DateTime())->format('%i minut %s sekund') : 
                    'neznámý čas';
                
                $this->flashMessage(
                    "Příliš mnoho odeslaných formulářů. Zkuste to znovu za {$timeRemaining}.", 
                    'danger'
                );
                
                // Přesměruj na GET verzi stejné stránky
                $this->redirect('this');
            }
        }
    }

    /**
     * ✅ NOVÉ: Automatické zaznamenávání formulářových pokusů
     */
    public function createComponent($name): ?\Nette\ComponentModel\IComponent
    {
        $component = parent::createComponent($name);
        
        // Pokud je to formulář, přidáme rate limiting
        if ($component instanceof Nette\Application\UI\Form && !$this->disableRateLimit) {
            // Přidáme rate limiting callback pro error
            $component->onError[] = function($form) {
                $this->recordFormSubmission(false);
            };
            
            // Přidáme rate limiting callback pro success
            // Přidáváme na začátek, takže se spustí jako první
            array_unshift($component->onSuccess, function($form, $values) {
                $this->recordFormSubmission(true);
            });
        }
        
        return $component;
    }

    /**
     * ✅ NOVÉ: Zaznamenání odeslaného formuláře
     */
    private function recordFormSubmission(bool $successful): void
    {
        if (!$this->disableRateLimit) {
            $clientIP = $this->rateLimiter->getClientIP();
            $this->rateLimiter->recordAttempt('form_submit', $clientIP, $successful);
        }
    }

    /**
     * NOVÉ: Kontrola statusu tenanta pro přihlášené uživatele
     */
    private function checkTenantStatus(): void
    {
        $identity = $this->getUser()->getIdentity();
        
        // Super admini mají vždy přístup
        if (!$identity || $this->isSuperAdmin()) {
            return;
        }

        // Kontrola statusu tenanta
        $tenantId = $identity->tenant_id ?? null;
        if ($tenantId) {
            $tenant = $this->database->table('tenants')
                ->where('id', $tenantId)
                ->fetch();
            
            if (!$tenant || $tenant->status !== 'active') {
                // Tenant je deaktivovaný - odhlásíme uživatele
                $this->getUser()->logout();
                
                // Uložíme důvod do session pro zobrazení na přihlašovací stránce
                // OPRAVA: Bez časového limitu - zpráva se zobrazuje trvale
                $section = $this->getSession('deactivation');
                $section->message = 'Váš účet byl deaktivován. Pro obnovení přístupu kontaktujte správce systému.';
                $section->type = 'danger';
                $section->tenant_id = $tenantId;
                
                $this->redirect('Sign:in');
            }
        }
    }

    // =====================================================
    // NOVÁ METODA PRO NASTAVENÍ MODULU KONTEXTU
    // =====================================================

    /**
     * Nastaví kontext uživatele v ModuleManager
     */
    private function setupModuleContext(): void
    {
        if (!$this->moduleManager || !$this->getUser()->isLoggedIn()) {
            return;
        }

        $identity = $this->getUser()->getIdentity();
        if (!$identity) {
            return;
        }

        // Nastavíme kontext: userID, tenantID, isSuperAdmin
        $this->moduleManager->setUserContext(
            $identity->id,
            $this->getCurrentTenantId(),
            $this->isSuperAdmin()
        );
    }

    // =====================================================
    // MULTI-TENANCY METODY (NOVÉ)
    // =====================================================

    /**
     * Získá aktuální tenant ID přihlášeného uživatele
     */
    protected function getCurrentTenantId(): ?int
    {
        if (!$this->getUser()->isLoggedIn()) {
            return null;
        }

        $identity = $this->getUser()->getIdentity();
        return $identity && isset($identity->tenant_id) ? (int)$identity->tenant_id : null;
    }

    /**
     * Kontroluje, zda je uživatel super admin
     */
    public function isSuperAdmin(): bool
    {
        if (!$this->getUser()->isLoggedIn()) {
            return false;
        }

        $identity = $this->getUser()->getIdentity();
        return $identity && isset($identity->is_super_admin) && $identity->is_super_admin == 1;
    }

    /**
     * Kontroluje, zda má uživatel přístup k danému tenantu
     */
    public function canAccessTenant(int $tenantId): bool
    {
        // Super admin může přistupovat ke všem tenantům
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Ostatní uživatelé mohou přistupovat pouze ke svému tenantu
        return $this->getCurrentTenantId() === $tenantId;
    }

    /**
     * Zajistí, že uživatel může přistupovat pouze ke svému tenantu
     * Automaticky filtruje dotazy podle tenant_id
     */
    protected function filterByTenant(Nette\Database\Table\Selection $selection): Nette\Database\Table\Selection
    {
        // Super admin vidí všechna data
        if ($this->isSuperAdmin()) {
            return $selection;
        }

        // Ostatní uživatelé vidí pouze data svého tenanta
        $tenantId = $this->getCurrentTenantId();
        if ($tenantId === null) {
            // Pokud nemá tenant_id, nevidí nic
            return $selection->where('1 = 0'); // Prázdný výsledek
        }

        return $selection->where('tenant_id', $tenantId);
    }

    /**
     * Získá seznam všech tenantů (pouze pro super admina)
     */
    protected function getAllTenants(): array
    {
        if (!$this->isSuperAdmin()) {
            return [];
        }

        $tenants = [];
        foreach ($this->database->table('tenants')->order('name ASC') as $tenant) {
            $tenants[$tenant->id] = $tenant->name;
        }

        return $tenants;
    }

    /**
     * Získá informace o aktuálním tenantu
     */
    protected function getCurrentTenant(): ?Nette\Database\Table\ActiveRow
    {
        $tenantId = $this->getCurrentTenantId();
        if ($tenantId === null) {
            return null;
        }

        return $this->database->table('tenants')->get($tenantId);
    }

    // =====================================================
    // PŮVODNÍ METODY S MULTI-TENANCY ROZŠÍŘENÍM
    // =====================================================

    /**
     * Kontroluje, zda má uživatel roli potřebnou pro danou akci
     * ROZŠÍŘENO: Super admin má automaticky všechna práva
     */
    protected function hasRequiredRoleForAction(string $action, string $userRole): bool
    {
        // Super admin má přístup ke všemu
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (!isset($this->actionRoles[$action])) {
            return true; // Pokud akce nemá definované role, je povolena
        }

        $requiredRoles = $this->actionRoles[$action];

        // Hierarchie rolí:
        // - admin: má přístup ke všemu (admin, accountant, readonly akce)
        // - accountant: má přístup k accountant a readonly akcím
        // - readonly: má přístup pouze k readonly akcím
        $roleHierarchy = [
            'admin' => ['admin', 'accountant', 'readonly'],
            'accountant' => ['accountant', 'readonly'],
            'readonly' => ['readonly']
        ];

        // Kontrola, zda uživatelská role je v seznamu povolených rolí pro akci
        foreach ($requiredRoles as $requiredRole) {
            if (in_array($requiredRole, $roleHierarchy[$userRole] ?? [])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Získá menu položky z aktivních modulů - OPRAVENÁ VERZE!
     * KLÍČOVÁ OPRAVA: Používá physical_path z moduleInfo místo špatné cesty
     */
    protected function getModuleMenuItems(): array
    {
        if (!$this->moduleManager) {
            $this->securityLogger->logSecurityEvent(
                'module_menu_error',
                "ModuleManager není dostupný v getModuleMenuItems()"
            );
            return [];
        }

        $menuItems = [];

        try {
            // Načteme aktivní moduly pro aktuálního uživatele
            $activeModules = $this->moduleManager->getActiveModules();

            $this->securityLogger->logSecurityEvent(
                'module_menu_debug',
                "Načítání menu z " . count($activeModules) . " aktivních modulů"
            );

            foreach ($activeModules as $moduleId => $moduleInfo) {
                try {
                    // Aktualizujeme čas posledního použití při každém zobrazení menu
                    if ($this->getUser()->isLoggedIn()) {
                        $identity = $this->getUser()->getIdentity();
                        if ($identity && $identity->id) {
                            $this->moduleManager->updateLastUsed($moduleId, $identity->id);
                        }
                    }

                    // KLÍČOVÁ OPRAVA: Používáme physical_path z moduleInfo
                    $modulePath = $moduleInfo['physical_path'] ?? null;

                    if (!$modulePath || !is_dir($modulePath)) {
                        $this->securityLogger->logSecurityEvent(
                            'module_menu_warning',
                            "Modul $moduleId nemá platnou physical_path: " . ($modulePath ?? 'null')
                        );
                        continue;
                    }

                    $moduleFile = $modulePath . '/Module.php';

                    $this->securityLogger->logSecurityEvent(
                        'module_menu_debug',
                        "Hledám Module.php pro $moduleId na cestě: $moduleFile"
                    );

                    if (file_exists($moduleFile)) {
                        require_once $moduleFile;

                        // OPRAVA: Používáme skutečné ID modulu místo klíče (který může být tenant_X_moduleId)
                        $realModuleId = $moduleInfo['id'] ?? $moduleId;
                        $moduleClassName = 'Modules\\' . ucfirst($realModuleId) . '\\Module';

                        $this->securityLogger->logSecurityEvent(
                            'module_menu_debug',
                            "Vytvářím instanci třídy: $moduleClassName pro modul: $realModuleId"
                        );

                        if (class_exists($moduleClassName)) {
                            $moduleInstance = new $moduleClassName();

                            if (method_exists($moduleInstance, 'getMenuItems')) {
                                $moduleMenuItems = $moduleInstance->getMenuItems();

                                $this->securityLogger->logSecurityEvent(
                                    'module_menu_debug',
                                    "Modul $moduleId vrátil " . count($moduleMenuItems) . " menu položek"
                                );

                                if (!empty($moduleMenuItems)) {
                                    // Zpracujeme menu položky a vygenerujeme odkazy
                                    $processedMenuItems = [];

                                    foreach ($moduleMenuItems as $menuItem) {
                                        $processedItem = $menuItem;

                                        // Pokud má presenter a action, vygenerujeme Nette link
                                        if (isset($menuItem['presenter']) && isset($menuItem['action'])) {
                                            $params = $menuItem['params'] ?? [];
                                            $processedItem['link'] = $this->link($menuItem['presenter'] . ':' . $menuItem['action'], $params);
                                            $processedItem['linkType'] = 'nette';
                                        } elseif (isset($menuItem['onclick'])) {
                                            $processedItem['linkType'] = 'javascript';
                                        } elseif (isset($menuItem['link'])) {
                                            $processedItem['linkType'] = 'direct';
                                        }

                                        $processedMenuItems[] = $processedItem;
                                    }

                                    $menuItems[$moduleId] = [
                                        'moduleInfo' => $moduleInfo,
                                        'menuItems' => $processedMenuItems
                                    ];

                                    $this->securityLogger->logSecurityEvent(
                                        'module_menu_success',
                                        "Úspěšně zpracován modul $moduleId s " . count($processedMenuItems) . " menu položkami"
                                    );
                                }
                            } else {
                                $this->securityLogger->logSecurityEvent(
                                    'module_menu_info',
                                    "Modul $moduleId nemá metodu getMenuItems()"
                                );
                            }
                        } else {
                            $this->securityLogger->logSecurityEvent(
                                'module_menu_warning',
                                "Třída $moduleClassName pro modul $moduleId neexistuje"
                            );
                        }
                    } else {
                        $this->securityLogger->logSecurityEvent(
                            'module_menu_warning',
                            "Soubor Module.php pro modul $moduleId neexistuje: $moduleFile"
                        );
                    }
                } catch (\Throwable $e) {
                    // Logujeme chybu, ale pokračujeme
                    $this->securityLogger->logSecurityEvent(
                        'module_menu_error',
                        "Chyba při načítání menu z modulu $moduleId: " . $e->getMessage()
                    );
                }
            }
        } catch (\Throwable $e) {
            // Logujeme kritickou chybu s moduly
            $this->securityLogger->logSecurityEvent(
                'module_system_error',
                "Kritická chyba modulového systému: " . $e->getMessage()
            );
        }

        $this->securityLogger->logSecurityEvent(
            'module_menu_final',
            "Finální počet modulů s menu: " . count($menuItems)
        );

        return $menuItems;
    }

    /**
     * Připraví proměnné pro šablonu
     */
    public function beforeRender(): void
    {
        parent::beforeRender();

        // Informace o uživateli
        if ($this->getUser()->isLoggedIn()) {
            $this->template->add('userLoggedIn', true);
            $identity = $this->getUser()->getIdentity();
            $this->template->add('currentUser', $identity);
            $this->template->add('currentUserRole', $identity && isset($identity->role) ? $identity->role : 'readonly');
        } else {
            $this->template->add('userLoggedIn', false);
            $this->template->add('currentUser', null);
            $this->template->add('currentUserRole', 'readonly');
        }

        // Helper funkce pro šablony (ROZŠÍŘENO)
        $this->template->add('isUserAdmin', $this->isAdmin());
        $this->template->add('isUserAccountant', $this->isAccountant());
        $this->template->add('isUserReadonly', $this->isReadonly());
        $this->template->add('isSuperAdmin', $this->isSuperAdmin()); // NOVÉ!

        // Multi-tenancy informace (NOVÉ!)
        $this->template->add('currentTenantId', $this->getCurrentTenantId());
        $this->template->add('currentTenant', $this->getCurrentTenant());

        // ✅ NOVÉ: Rate Limiting informace pro šablony
        if (!$this->disableRateLimit) {
            $this->template->add('rateLimitInfo', $this->getRateLimitInfo());
        }

        // Přidání helper funkcí pro skloňování do šablony
        $this->template->addFunction('pluralizeInvoices', [$this, 'pluralizeInvoices']);
        $this->template->addFunction('getInvoiceCountText', [$this, 'getInvoiceCountText']);

        // Přidání helper funkce pro vokativ do šablony
        $this->template->addFunction('vocative', [$this, 'getVocativeName']);

        // DŮLEŽITÉ: Přidání menu položek z modulů do šablony
        $moduleMenuItems = $this->getModuleMenuItems();
        $this->template->add('moduleMenuItems', $moduleMenuItems);

        // DEBUG: Přidáme informaci o počtu menu položek do šablony pro ladění
        $this->template->add('moduleMenuItemsCount', count($moduleMenuItems));
    }

    /**
     * ✅ NOVÉ: Získá informace o rate limitingu pro šablony
     */
    private function getRateLimitInfo(): array
    {
        $clientIP = $this->rateLimiter->getClientIP();
        
        return [
            'form_submit' => $this->rateLimiter->getLimitStatus('form_submit', $clientIP),
            'client_ip' => $clientIP,
        ];
    }

    /**
     * Získá aktuální roli uživatele
     */
    private function getCurrentUserRole(): string
    {
        if (!$this->getUser()->isLoggedIn()) {
            return 'guest';
        }

        $identity = $this->getUser()->getIdentity();
        return $identity && isset($identity->role) ? $identity->role : 'readonly';
    }

    /**
     * Kontroluje, zda má uživatel přístup k akci na základě jeho role
     * OPRAVENO: nullable parameter
     */
    protected function checkAccess(string $resource, ?string $privilege = null): bool
    {
        if (!$this->getUser()->isLoggedIn()) {
            return false;
        }

        $role = $this->getCurrentUserRole();

        // Super admin může všechno (NOVÉ!)
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Pro zjednodušení používáme hierarchii rolí
        // Admin může všechno
        if ($role === 'admin') {
            return true;
        }

        // Podle potřeby zde můžete implementovat složitější logiku
        // např. kontrolu na úrovni objektů, vlastnictví záznamů atd.

        return false;
    }

    /**
     * Vytvoří komponentu pro CSRF token
     */
    protected function createComponentCsrfToken(): Nette\Application\UI\Form
    {
        $form = new Nette\Application\UI\Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');
        return $form;
    }

    /**
     * Kontroluje, zda má uživatel požadovanou roli
     * ROZŠÍŘENO: Super admin má automaticky všechna práva
     */
    public function hasRole(string $role): bool
    {
        if (!$this->getUser()->isLoggedIn()) {
            return false;
        }

        // Super admin má automaticky všechna práva
        if ($this->isSuperAdmin()) {
            return true;
        }

        $identity = $this->getUser()->getIdentity();
        if (!$identity || !isset($identity->role)) {
            return false;
        }

        $userRole = $identity->role;

        // Hierarchie rolí
        $roleHierarchy = [
            'admin' => ['admin', 'accountant', 'readonly'],
            'accountant' => ['accountant', 'readonly'],
            'readonly' => ['readonly']
        ];

        return in_array($role, $roleHierarchy[$userRole] ?? []);
    }

    /**
     * Kontroluje, zda je uživatel admin (nebo super admin)
     */
    public function isAdmin(): bool
    {
        return $this->isSuperAdmin() || $this->hasRole('admin');
    }

    /**
     * Kontroluje, zda je uživatel účetní (nebo admin/super admin)
     */
    public function isAccountant(): bool
    {
        return $this->isSuperAdmin() || $this->hasRole('accountant');
    }

    /**
     * Kontroluje, zda má uživatel roli readonly nebo vyšší (nebo super admin)
     */
    public function isReadonly(): bool
    {
        return $this->isSuperAdmin() || $this->hasRole('readonly');
    }

    // =====================================================
    // ✅ NOVÉ: Rate Limiting Helper Metody
    // =====================================================

    /**
     * Helper metoda pro získání RateLimiteru (pro potomky)
     */
    protected function getRateLimiter(): RateLimiter
    {
        return $this->rateLimiter;
    }

    /**
     * Helper metoda pro manuální rate limit kontrolu
     */
    protected function checkCustomRateLimit(string $action): bool
    {
        if ($this->disableRateLimit) {
            return true;
        }
        
        $clientIP = $this->rateLimiter->getClientIP();
        return $this->rateLimiter->isAllowed($action, $clientIP);
    }

    /**
     * Helper metoda pro zaznamenání custom akce
     */
    protected function recordCustomAttempt(string $action, bool $successful): void
    {
        if (!$this->disableRateLimit) {
            $clientIP = $this->rateLimiter->getClientIP();
            $this->rateLimiter->recordAttempt($action, $clientIP, $successful);
        }
    }

    /**
     * Správné skloňování slova "faktura" podle českých gramatických pravidel
     * 
     * @param int $count Počet faktur
     * @return string Správně skloňované slovo
     */
    public function pluralizeInvoices(int $count): string
    {
        if ($count === 1) {
            return 'fakturu';
        } elseif ($count >= 2 && $count <= 4) {
            return 'faktury';
        } else {
            return 'faktur';
        }
    }

    /**
     * Vytvoří celou větu s počtem faktur
     * 
     * @param int $count Počet faktur
     * @return string Celá věta s počtem a správně skloňovaným slovem
     */
    public function getInvoiceCountText(int $count): string
    {
        return $count . ' ' . $this->pluralizeInvoices($count);
    }

    /**
     * Převede české křestní jméno do 5. pádu (vokativ) pro oslovení
     * 
     * @param string $name Křestní jméno v 1. pádě
     * @return string Jméno v 5. pádě pro oslovení
     */
    public function getVocativeName(string $name): string
    {
        if (empty($name)) {
            return $name;
        }

        $name = trim($name);
        $lowerName = mb_strtolower($name, 'UTF-8');

        // Slovník nejčastějších mužských jmen a jejich vokativů
        $maleNames = [
            'aleš' => 'Aleši',
            'pavel' => 'Pavle',
            'martin' => 'Martine',
            'tomáš' => 'Tomáši',
            'jan' => 'Jane',
            'petr' => 'Petře',
            'david' => 'Davide',
            'michal' => 'Michale',
            'lukáš' => 'Lukáši',
            'jakub' => 'Jakube',
            'milan' => 'Milane',
            'roman' => 'Romane',
            'marek' => 'Marku',
            'jiří' => 'Jiří',
            'adam' => 'Adame',
            'ondřej' => 'Ondřeji',
            'daniel' => 'Danieli',
            'ladislav' => 'Ladislave',
            'václav' => 'Václave',
            'stanislav' => 'Stanislave',
            'františek' => 'Františku',
            'josef' => 'Josefe',
            'jaroslav' => 'Jaroslave',
            'zdeněk' => 'Zdeňku',
            'miroslav' => 'Miroslave',
            'vladimír' => 'Vladimíre',
            'radek' => 'Radku',
            'patrik' => 'Patriku',
            'robert' => 'Roberte',
            'antonín' => 'Antoníne',
        ];

        // Slovník nejčastějších ženských jmen a jejich vokativů
        $femaleNames = [
            'jana' => 'Jano',
            'marie' => 'Marie',
            'eva' => 'Evo',
            'anna' => 'Anno',
            'lenka' => 'Lenko',
            'kateřina' => 'Kateřino',
            'petra' => 'Petro',
            'věra' => 'Věro',
            'alena' => 'Aleno',
            'zuzana' => 'Zuzano',
            'michaela' => 'Michaelo',
            'hana' => 'Hano',
            'martina' => 'Martino',
            'tereza' => 'Terezo',
            'lucie' => 'Lucko',
            'jitka' => 'Jitko',
            'barbora' => 'Barbaro',
            'klára' => 'Kláro',
            'ivana' => 'Ivano',
            'dagmar' => 'Dagmar',
            'simona' => 'Simono',
            'andrea' => 'Andreo',
            'romana' => 'Romano',
            'vendula' => 'Vendulo',
            'nikola' => 'Nikolo',
            'denisa' => 'Deniso',
            'markéta' => 'Markéto',
            'radka' => 'Radko',
            'monika' => 'Moniko',
            'kristýna' => 'Kristýno',
            'gabriela' => 'Gabrielo',
            'silvie' => 'Silvie',
            'renata' => 'Renato',
            'štěpánka' => 'Štěpánko',
            'božena' => 'Boženo',
            'vlasta' => 'Vlasto',
            'jarmila' => 'Jarmilo',
            'milada' => 'Milado',
            'libuše' => 'Libuše',
            'růžena' => 'Růženo',
            'ludmila' => 'Ludmilo',
            'naděžda' => 'Naděždo',
            'květa' => 'Květo',
            'jiřina' => 'Jiřino',
            'irena' => 'Ireno',
            'helena' => 'Heleno',
            'olga' => 'Olgo',
            'františka' => 'Františko',
            'božena' => 'Boženo',
            'anežka' => 'Anežko',
            'blanka' => 'Blanko',
            'zdenka' => 'Zdenko',
            'milena' => 'Mileno',
            'drahomíra' => 'Drahomíro',
            'blažena' => 'Blaženo',
            'kamila' => 'Kamilo',
            'stanislava' => 'Stanisalvo',
            'miroslava' => 'Miroslavo',
            'jaroslava' => 'Jaroslavo',
            'vladimíra' => 'Vladimíro',
            'miloslava' => 'Miloslavo',
            'bohumila' => 'Bohumilo',
            'jindřiška' => 'Jindřiško',
            'dominika' => 'Dominiko',
            'veronika' => 'Veroniko',
            'sabina' => 'Sabino',
            'adéla' => 'Adélo',
            'ema' => 'Emo',
            'julie' => 'Julie',
            'natálie' => 'Natálie',
            'eliška' => 'Eliško',
            'karolína' => 'Karolíno',
            'laura' => 'Lauro',
            'nela' => 'Nelo',
            'sofie' => 'Sofie',
            'viktorie' => 'Viktorie',
            'amálie' => 'Amálie',
            'adéla' => 'Adélo',
            'aneta' => 'Aneto',
            'nikol' => 'Nikol',
            'patricie' => 'Patricie',
            'daniela' => 'Danielo',
            'nikolka' => 'Nikolko',
            'sandra' => 'Sandro',
            'lenka' => 'Lenko',
        ];

        // Pokud je jméno ve slovníku, vrátíme správný vokativ
        if (isset($maleNames[$lowerName])) {
            return $maleNames[$lowerName];
        }

        if (isset($femaleNames[$lowerName])) {
            return $femaleNames[$lowerName];
        }

        // Pokud jméno není ve slovníku, pokusíme se odhadnout podle koncovky

        // Ženská jména končící na 'a' -> změna na 'o'
        if (mb_substr($lowerName, -1, 1, 'UTF-8') === 'a') {
            return mb_substr($name, 0, -1, 'UTF-8') . 'o';
        }

        // Ženská jména končící na 'e' -> zůstávají stejně
        if (mb_substr($lowerName, -1, 1, 'UTF-8') === 'e') {
            return $name;
        }

        // Mužská jména končící na souhlásku
        $lastChar = mb_substr($lowerName, -1, 1, 'UTF-8');

        // Některé specifické koncovky pro mužská jména
        if (in_array($lastChar, ['k', 'h', 'g'], true)) {
            return $name . 'u';
        }

        // Tvrdé souhlásky
        if (in_array($lastChar, ['p', 'b', 't', 'd', 'n', 'l', 'm', 'r', 'v', 's', 'z'], true)) {
            return $name . 'e';
        }

        // Měkké souhlásky
        if (in_array($lastChar, ['j', 'c', 'č', 'š', 'ž', 'ň', 'ť', 'ď', 'ř'], true)) {
            return $name . 'i';
        }

        // Pokud si nejsme jisti, necháme jméno beze změny
        return $name;
    }
}