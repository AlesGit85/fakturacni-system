<?php

declare(strict_types=1);

namespace App\Presentation;

use Nette;
use Nette\Application\UI\Presenter;
use App\Security\SecurityLogger;
use App\Security\RateLimiter;
use App\Model\ModuleManager;
use App\Model\SessionSettingsManager;
use App\Security\SecurityValidator;
use App\Security\AntiSpam;

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
    protected $securityLogger;

    /** @var RateLimiter */
    protected $rateLimiter;

    /** @var ModuleManager */
    private $moduleManager;

    /** @var SessionSettingsManager */
    private $sessionSettingsManager;

    /** @var Nette\Database\Explorer Databáze pro multi-tenancy dotazy */
    protected $database;

    /** @var bool Zapíná automatickou XSS kontrolu formulářů */
    protected bool $enableXssProtection = true;

    /** @var array Pole pro XSS logování */
    private array $xssAttempts = [];

    /** @var AntiSpam ✅ NOVÉ: Anti-spam systém */
    protected $antiSpam;

    /** @var bool ✅ NOVÉ: Zapíná automatickou honeypot ochranu formulářů */
    protected bool $enableHoneypotProtection = true;

    /** @var bool ✅ NOVÉ: Zapíná automatickou timing ochranu formulářů */
    protected bool $enableTimingProtection = true;

    /** @var array ✅ NOVÉ: Pole pro spam logování */
    private array $spamAttempts = [];

    /**
     * ✅ NOVÉ: CSRF ochrana - proměnné
     */
    private string $csrfTokenSessionKey = '_csrf_token';

    /**
     * ✅ NOVÉ: Seznam handlerů/akcí které vyžadují CSRF token
     */
    protected array $csrfProtectedActions = [
        // === INVOICES PRESENTER === ✅ implementováno
        'handleMarkAsPaid',           // označit fakturu jako zaplacenou
        'handleMarkAsCreated',        // označit fakturu jako vystavěnou

        // === CLIENTS PRESENTER === ⚠️ k implementaci
        'handleAresLookup',           // ARES vyhledávání (AJAX)

        // === USERS PRESENTER === ⚠️ k implementaci
        'handleClearRateLimit',       // vymazání rate limit pro IP
        'handleClearAllRateLimits',   // vymazání všech rate limitů

        // === MODULE ADMIN PRESENTER === ⚠️ k implementaci  
        'handleToggleModule',         // aktivace/deaktivace modulu
        'handleUninstallModule',      // odinstalace modulu
        'handleToggleUserModule',     // toggle modulu jiného uživatele
        'handleDeleteUserModule',     // smazání modulu jiného uživatele

        // === OBECNÉ DELETE AKCE === ⚠️ k implementaci
        'actionDelete',               // mazání záznamů (Clients, Users, Invoices)
        'actionDeleteLogo',           // mazání loga (Settings)
        'actionDeleteSignature'       // mazání podpisu (Settings)
    ];

    /**
     * ✅ NOVÉ: Seznam akcí, které jsou vždy povolené bez CSRF (čtení dat)
     */
    protected array $csrfExemptActions = [
        'actionDefault',
        'renderDefault',
        'renderShow',
        'renderAdd',
        'renderEdit'
    ];

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

    public function injectAntiSpam(AntiSpam $antiSpam): void
    {
        $this->antiSpam = $antiSpam;
    }

    public function injectSessionSettingsManager(SessionSettingsManager $sessionSettingsManager): void
    {
        $this->sessionSettingsManager = $sessionSettingsManager;
    }

    public function startup(): void
    {
        parent::startup();

        // ✅ NOVÉ: CSRF ochrana PŘED rate limitingem
        if ($this->requiresLogin) {
            $this->checkGlobalCsrfProtection();
        }

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

        // 🔒 NOVÉ: Session security kontroly
        if ($this->requiresLogin && $this->getUser()->isLoggedIn()) {
            $this->checkSessionSecurity();
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
     * ✅ NOVÉ: Globální CSRF ochrana pro celou aplikaci
     */
    private function checkGlobalCsrfProtection(): void
    {
        $httpRequest = $this->getHttpRequest();
        $method = $httpRequest->getMethod();
        $actionName = $this->getAction();

        // Jen pro nebezpečné HTTP metody
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return;
        }

        // Kontrola, zda je akce v seznamu exemptních akcí (obvykle GET akce)
        $fullActionName = "action{$actionName}";
        if (in_array($fullActionName, $this->csrfExemptActions)) {
            return;
        }

        // Kontrola pro handlery - zjistíme, zda se jedná o signal
        $signal = $this->getSignal();
        if ($signal) {
            $handlerName = 'handle' . ucfirst($signal[1]);

            // Pokud handler není v seznamu chráněných, nemusíme kontrolovat CSRF
            if (!in_array($handlerName, $this->csrfProtectedActions)) {
                return;
            }
        } else {
            // Pro běžné akce kontrolujeme pouze ty v seznamu chráněných
            if (!in_array($fullActionName, $this->csrfProtectedActions)) {
                return;
            }
        }

        // ✅ KLÍČOVÁ ČÁST: Kontrola CSRF tokenu
        $this->validateCsrfToken();
    }

    /**
     * ✅ NOVÉ: Validace CSRF tokenu
     */
    private function validateCsrfToken(): void
    {
        $httpRequest = $this->getHttpRequest();

        // Získáme token z různých zdrojů (POST data, headers, GET parametry)
        $submittedToken = null;

        // 1. Pokusíme se najít token v POST datech
        $postData = $httpRequest->getPost();
        if (isset($postData['_csrf_token'])) {
            $submittedToken = $postData['_csrf_token'];
        }

        // 2. Pokud ne, zkusíme hlavičku X-CSRF-Token (pro AJAX)
        if (!$submittedToken) {
            $submittedToken = $httpRequest->getHeader('X-CSRF-Token');
        }

        // 3. Pokud ne, zkusíme GET parametr (pro odkazy)
        if (!$submittedToken) {
            $submittedToken = $httpRequest->getQuery('_csrf_token');
        }

        // Získáme očekávaný token ze session
        $expectedToken = $this->getCsrfToken();

        // Validace
        if (!$submittedToken || !hash_equals($expectedToken, $submittedToken)) {
            // Logování CSRF pokusu
            $this->logCsrfAttempt($submittedToken);

            // Chyba pro uživatele
            $this->flashMessage(
                'Bezpečnostní token není platný nebo vypršel. Obnovte stránku a zkuste akci znovu.',
                'danger'
            );

            // Přesměrování zpět
            $this->redirect('this');
        }
    }

    /**
     * ✅ NOVÉ: Získání nebo vytvoření CSRF tokenu
     */
    public function getCsrfToken(): string
    {
        $session = $this->getSession();
        $section = $session->getSection('csrf');

        if (!isset($section->token)) {
            // Vytvoříme nový token
            $section->token = bin2hex(random_bytes(32));
        }

        return $section->token;
    }

    /**
     * ✅ NOVÉ: Obnovení CSRF tokenu (po úspěšném formuláři)
     */
    public function regenerateCsrfToken(): void
    {
        $session = $this->getSession();
        $section = $session->getSection('csrf');
        $section->token = bin2hex(random_bytes(32));
    }

    /**
     * ✅ NOVÉ: Logování CSRF pokusu
     */
    private function logCsrfAttempt(?string $submittedToken): void
    {
        $clientIP = $this->rateLimiter->getClientIP();
        $userAgent = $this->getHttpRequest()->getHeader('User-Agent') ?? 'unknown';
        $userId = $this->getUser()->isLoggedIn() ? $this->getUser()->getId() : null;

        $this->securityLogger->logSecurityEvent(
            'csrf_attack',
            "CSRF útok z IP {$clientIP}",
            [
                'presenter' => $this->getName(),
                'action' => $this->getAction(),
                'signal' => $this->getSignal() ? $this->getSignal()[1] : null,
                'submitted_token' => $submittedToken ? 'exists_but_invalid' : 'missing',
                'client_ip' => $clientIP,
                'user_agent' => $userAgent,
                'user_id' => $userId,
                'referer' => $this->getHttpRequest()->getReferer(),
                'method' => $this->getHttpRequest()->getMethod()
            ]
        );
    }

    /**
     * ✅ AKTUALIZACE: checkRateLimit() - s tenant podporou
     */
    private function checkRateLimit(): void
    {
        $clientIP = $this->rateLimiter->getClientIP();
        $action = 'form_submit'; // Obecné rate limiting pro všechny formuláře

        // ✅ NOVÉ: Získání tenant a user informací
        $tenantId = $this->getCurrentTenantId();
        $userId = $this->getUser()->isLoggedIn() ? $this->getUser()->getId() : null;

        // Kontrola pouze pro POST požadavky (odesílání formulářů)
        if ($this->getHttpRequest()->isMethod('POST')) {
            if (!$this->rateLimiter->isAllowed($action, $clientIP, $tenantId)) {
                $status = $this->rateLimiter->getLimitStatus($action, $clientIP, $tenantId);
                $blockedUntil = $status['blocked_until'];
                $timeRemaining = $blockedUntil ?
                    $blockedUntil->diff(new \DateTime())->format('%i minut %s sekund') :
                    'neznámý čas';

                $this->flashMessage(
                    "Příliš mnoho odeslaných formulářů. Zkuste to znovu za {$timeRemaining}.",
                    'warning'
                );

                // ✅ ROZŠÍŘENO: Záznam neúspěšného pokusu s tenant informacemi
                $this->rateLimiter->recordAttempt($action, $clientIP, false, $tenantId, $userId);

                $this->redirect('Home:default');
            }

            // ✅ ROZŠÍŘENO: Záznam úspěšného pokusu s tenant informacemi  
            $this->rateLimiter->recordAttempt($action, $clientIP, true, $tenantId, $userId);
        }
    }

    /**
     * ✅ NOVÉ: Automatické zaznamenávání formulářových pokusů
     */
    public function createComponent($name): ?\Nette\ComponentModel\IComponent
    {
        $component = parent::createComponent($name);

        if ($component instanceof Nette\Application\UI\Form && !$this->disableRateLimit) {
            // Pouze XSS ochrana a rate limiting
            if ($this->enableXssProtection) {
                $this->addXssProtectionToForm($component);
            }

            $component->onError[] = function ($form) {
                $this->recordFormSubmission(false);
            };

            array_unshift($component->onSuccess, function ($form, $values) {
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
                        // ✅ OPRAVENO: Používáme tenant-specific namespace
                        $tenantId = $moduleInfo['tenant_id'] ?? 1;
                        $moduleNameForClass = ucfirst($realModuleId); // např. "Financial_reports"
                        $moduleClassName = "Modules\\Tenant{$tenantId}\\{$moduleNameForClass}\\Module";

                        $this->securityLogger->logSecurityEvent(
                            'module_menu_debug',
                            "Vytvářím instanci tenant-specific třídy: $moduleClassName pro modul: $realModuleId (tenant: $tenantId)"
                        );

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

        // ✅ NOVÉ: CSRF token pro šablony
        if ($this->requiresLogin && $this->getUser()->isLoggedIn()) {
            $this->template->add('csrfToken', $this->getCsrfToken());

            // Helper funkce pro vytváření bezpečných odkazů
            $this->template->addFilter('csrfLink', function (string $destination, array $args = []): string {
                $args['_csrf_token'] = $this->getCsrfToken();
                return $this->link($destination, $args);
            });
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
     * ✅ AKTUALIZACE: getRateLimitInfo() - s tenant podporou
     */
    private function getRateLimitInfo(): array
    {
        $clientIP = $this->rateLimiter->getClientIP();
        $tenantId = $this->getCurrentTenantId();

        return [
            'form_submit' => $this->rateLimiter->getLimitStatus('form_submit', $clientIP, $tenantId),
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
     * ✅ AKTUALIZACE: checkCustomRateLimit() - s tenant podporou
     */
    protected function checkCustomRateLimit(string $action): bool
    {
        if ($this->disableRateLimit) {
            return true;
        }

        $clientIP = $this->rateLimiter->getClientIP();
        $tenantId = $this->getCurrentTenantId();

        return $this->rateLimiter->isAllowed($action, $clientIP, $tenantId);
    }

    /**
     * ✅ AKTUALIZACE: recordCustomAttempt() - s tenant podporou
     */
    protected function recordCustomAttempt(string $action, bool $successful): void
    {
        if (!$this->disableRateLimit) {
            $clientIP = $this->rateLimiter->getClientIP();
            $tenantId = $this->getCurrentTenantId();
            $userId = $this->getUser()->isLoggedIn() ? $this->getUser()->getId() : null;

            $this->rateLimiter->recordAttempt($action, $clientIP, $successful, $tenantId, $userId);
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
    /**
     * ✅ NOVÉ: Přidání XSS ochrany k formuláři
     */
    private function addXssProtectionToForm(Nette\Application\UI\Form $form): void
    {
        // Přidáme globální validaci na začátek
        array_unshift($form->onValidate, function ($form) {
            $this->validateFormAgainstXss($form);
        });
    }

    /**
     * ✅ NOVÉ: Validace formuláře proti XSS útokům
     */
    private function validateFormAgainstXss(Nette\Application\UI\Form $form): void
    {
        // ✅ OPRAVA: Používáme getHttpData() místo getValues() pro čtení raw dat
        $httpData = $form->getHttpData();
        if ($httpData) {
            $this->checkForXssInData($httpData, $form->getName() ?? 'unknown', '', $form);
        }
    }

    /**
     * ✅ NOVÉ: Rekurzivní kontrola XSS v datech
     */
    /**
     * ✅ FINÁLNÍ OPRAVA: Rekurzivní kontrola XSS v datech s flash message
     */
    private function checkForXssInData(array $data, string $formName, string $prefix = '', ?Nette\Application\UI\Form $form = null): void
    {
        $xssFound = false;

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $fieldName = $prefix ? "{$prefix}.{$key}" : $key;

                // Detekce XSS pokusu
                if (SecurityValidator::detectXssAttempt($value)) {
                    // Zalogování XSS pokusu
                    $this->logXssAttempt($formName, $fieldName, $value);

                    // ✅ NOVÉ: Přidáme chybu do formuláře pro zneplatnění
                    if ($form !== null) {
                        $form->addError("Pole '{$fieldName}' obsahuje nebezpečný obsah.");
                    }

                    // Uložení pro pozdější zpracování
                    $this->xssAttempts[] = [
                        'form' => $formName,
                        'field' => $fieldName,
                        'value' => SecurityValidator::safeLogString($value)
                    ];

                    $xssFound = true;
                }
            } elseif (is_array($value)) {
                $this->checkForXssInData($value, $formName, $prefix ? "{$prefix}.{$key}" : $key, $form);
            }
        }

        // ✅ NOVÉ: Přidáme flash message pro uživatele
        if ($xssFound && $form !== null) {
            $this->flashMessage(
                'Formulář obsahuje nebezpečný obsah (HTML/JavaScript kód). Zkontrolujte zadané údaje a odešlete formulář znovu.',
                'danger'
            );
        }
    }

    /**
     * ✅ NOVÉ: Logování XSS pokusu
     */
    private function logXssAttempt(string $formName, string $fieldName, string $value): void
    {
        $clientIP = $this->rateLimiter->getClientIP();
        $userAgent = $this->getHttpRequest()->getHeader('User-Agent') ?? 'unknown';
        $userId = $this->getUser()->isLoggedIn() ? $this->getUser()->getId() : null;

        $this->securityLogger->logSecurityEvent(
            'xss_attempt',
            "XSS pokus v formuláři '{$formName}', pole '{$fieldName}' z IP {$clientIP}",
            [
                'form_name' => $formName,
                'field_name' => $fieldName,
                'client_ip' => $clientIP,
                'user_agent' => $userAgent,
                'user_id' => $userId,
                'value_preview' => SecurityValidator::safeLogString($value, 50),
                'presenter' => $this->getName(),
                'action' => $this->getAction()
            ]
        );
    }

    /**
     * ✅ NOVÉ: Sanitizace formulářových dat
     */
    protected function sanitizeFormData(array $data, array $richTextFields = []): array
    {
        return SecurityValidator::sanitizeFormData($data);
    }

    /**
     * ✅ NOVÉ: Kontrola, zda formulář obsahoval XSS pokusy
     */
    protected function hasXssAttempts(): bool
    {
        return !empty($this->xssAttempts);
    }

    /**
     * ✅ NOVÉ: Získání XSS pokusů pro reporting
     */
    protected function getXssAttempts(): array
    {
        return $this->xssAttempts;
    }

    /**
     * ✅ NOVÉ: Základní továrna na formuláře s automatickou ochranou
     */
    protected function createComponentForm(): \Nette\Application\UI\Form
    {
        $form = new \Nette\Application\UI\Form;

        // ✅ NOVÉ: Přidání XSS ochrany k formuláři
        if ($this->enableXssProtection) {
            $this->addXssProtectionToForm($form);
        }

        // ✅ NOVÉ: Přidání anti-spam ochrany k formuláři
        if ($this->enableHoneypotProtection) {
            $this->addAntiSpamProtectionToForm($form);
        }

        return $form;
    }

    /**
     * ✅ NOVÉ: Přidání bezpečnostních filtrů k formulářovému poli
     */
    protected function addSecurityFilters(Nette\Forms\Controls\BaseControl $control, string $type = 'string'): void
    {
        switch ($type) {
            case 'email':
                $control->addFilter('trim');
                break;

            case 'phone':
                $control->addFilter([SecurityValidator::class, 'sanitizePhoneNumber']);
                break;

            case 'amount':
                $control->addFilter([SecurityValidator::class, 'sanitizeAmount']);
                break;

            case 'invoice_number':
                $control->addFilter([SecurityValidator::class, 'sanitizeInvoiceNumber']);
                break;

            case 'rich_text':
                $control->addFilter([SecurityValidator::class, 'sanitizeRichText']);
                break;

            case 'url':
                $control->addFilter([SecurityValidator::class, 'sanitizeUrl']);
                break;

            default: // 'string'
                $control->addFilter([SecurityValidator::class, 'sanitizeString']);
                break;
        }
    }

    /**
     * ✅ NOVÉ: Přidání bezpečnostních validací k formulářovému poli
     */
    protected function addSecurityValidation(Nette\Forms\Controls\BaseControl $control, string $type = 'string'): void
    {
        switch ($type) {
            case 'email':
                $control->addRule(function ($control) {
                    return SecurityValidator::validateEmail(trim($control->getValue()));
                }, 'Zadejte platnou e-mailovou adresu.');
                break;

            case 'phone':
                $control->addRule(function ($control) {
                    $value = $control->getValue();
                    return empty($value) || SecurityValidator::validatePhoneNumber($value);
                }, 'Zadejte platné telefonní číslo.');
                break;

            case 'username':
                $control->addRule(function ($control) {
                    $errors = SecurityValidator::validateUsername($control->getValue());
                    return empty($errors) ? true : $errors[0];
                }, '');
                break;

            case 'password':
                $control->addRule(function ($control) {
                    $errors = SecurityValidator::validatePassword($control->getValue());
                    return empty($errors) ? true : $errors[0];
                }, '');
                break;

            case 'ico':
                $control->addRule(function ($control) {
                    $value = $control->getValue();
                    return empty($value) || SecurityValidator::validateICO($value);
                }, 'Zadejte platné IČO.');
                break;

            case 'dic':
                $control->addRule(function ($control) {
                    $value = $control->getValue();
                    return empty($value) || SecurityValidator::validateDIC($value);
                }, 'Zadejte platné DIČ.');
                break;

            case 'amount':
                $control->addRule(function ($control) {
                    $value = $control->getValue();
                    return empty($value) || SecurityValidator::validateAmount($value);
                }, 'Zadejte platnou částku.');
                break;

            case 'company_name':
                $control->addRule(function ($control) {
                    $errors = SecurityValidator::validateCompanyName($control->getValue());
                    return empty($errors) ? true : $errors[0];
                }, '');
                break;
        }
    }

    /**
     * ✅ NOVÉ: Přidá kompletní anti-spam ochranu k formuláři
     */
    protected function addAntiSpamProtectionToForm(Nette\Application\UI\Form $form): void
    {
        // 1. Honeypot ochrana
        if ($this->enableHoneypotProtection) {
            $honeypotField = $this->antiSpam->addHoneypotToForm($form);
        }

        // 2. Timing ochrana
        if ($this->enableTimingProtection) {
            $this->antiSpam->addTimingProtection($form);
        }

        // ✅ OPRAVENO: Přidáme anti-spam validaci JAKO POSLEDNÍ (aby se spustila až po honeypot validaci)
        $form->onValidate[] = function ($form) {
            $this->validateFormAgainstSpam($form);
        };
    }

    /**
     * ✅ OPRAVENO: Validace formuláře proti spam pokusům - s lepším handling
     */
    private function validateFormAgainstSpam(Nette\Application\UI\Form $form): void
    {
        // ✅ OPRAVENO: Pouze pokud je formulář stále validní, kontrolujeme další spam vzory
        if ($form->isValid()) {
            $isValid = $this->antiSpam->validateFormAgainstSpam($form);

            if (!$isValid) {
                // Spam byl detekován, formulář už má chybovou hlášku
                $this->spamAttempts[] = [
                    'form' => $form->getName() ?? 'unknown',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'client_ip' => $this->getHttpRequest()->getRemoteAddress()
                ];

                // Přidáme flash message pro uživatele
                $this->flashMessage(
                    'Formulář obsahuje podezřelý obsah nebo byl odeslán příliš rychle. Pokud jste člověk, zkuste to znovu za chvilku.',
                    'danger'
                );
            }
        }
        // Pokud formulář už není validní (kvůli honeypot), neděláme nic dalšího
    }

    /**
     * ✅ NOVÉ: Getter pro kontrolu spam pokusů
     */
    protected function hasSpamAttempts(): bool
    {
        return !empty($this->spamAttempts);
    }

    /**
     * ✅ NOVÉ: Getter pro spam pokusy
     */
    protected function getSpamAttempts(): array
    {
        return $this->spamAttempts;
    }

    // =====================================================
    // ✅ NOVÉ: Clean AJAX Response Helper Metody
    // =====================================================

    /**
     * ✅ NOVÉ: Bezpečné odeslání JSON odpovědi s čištěním output bufferu
     * Řeší problémy s Tracy debuggerem a dalšími systémy, které kontaminují output buffer
     */
    protected function sendCleanJson(array $data): void
    {
        // Vyčištění všech output bufferů (Tracy, PHP output buffering, atd.)
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Nastavení správných headers pro JSON
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Odeslání JSON odpovědi s UTF-8 podporou
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * ✅ NOVÉ: Zkratka pro úspěšné AJAX odpovědi
     * 
     * @param string $message Zpráva pro uživatele
     * @param array $data Dodatečná data (volitelné)
     */
    protected function sendSuccess(string $message, array $data = []): void
    {
        $response = array_merge([
            'success' => true,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ], $data);

        $this->sendCleanJson($response);
    }

    /**
     * ✅ NOVÉ: Zkratka pro chybové AJAX odpovědi
     * 
     * @param string $error Chybová zpráva pro uživatele
     * @param array $data Dodatečná data (volitelné)
     */
    protected function sendError(string $error, array $data = []): void
    {
        $response = array_merge([
            'success' => false,
            'error' => $error,
            'timestamp' => date('Y-m-d H:i:s')
        ], $data);

        $this->sendCleanJson($response);
    }

    /**
     * ✅ NOVÉ: Zkratka pro AJAX odpovědi s přesměrováním
     * 
     * @param string $message Zpráva pro uživatele
     * @param string $redirectUrl URL pro přesměrování (volitelné)
     */
    protected function sendSuccessWithRedirect(string $message, string $redirectUrl = ''): void
    {
        $data = [
            'success' => true,
            'message' => $message,
            'redirect' => true,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if ($redirectUrl) {
            $data['redirect_url'] = $redirectUrl;
        }

        $this->sendCleanJson($data);
    }

    /**
     * ✅ NOVÉ: Helper pro kontrolu AJAX požadavku s automatickou odpovědí
     * 
     * @param string $successMessage Zpráva při úspěchu
     * @param string $errorMessage Zpráva při chybě (volitelné)
     * @return bool True pokud je AJAX (a už byla odeslána odpověď), False pokud pokračovat s non-AJAX
     */
    public function handleAjaxResponse(string $successMessage, string $errorMessage = ''): bool
    {
        if ($this->isAjax()) {
            if ($errorMessage) {
                $this->sendError($errorMessage);
            } else {
                $this->sendSuccess($successMessage);
            }
            return true; // AJAX zpracován
        }

        return false; // Pokračuj s non-AJAX logikou
    }

    /**
     * 🔒 DYNAMICKÁ: Kontrola session security s konfigurovatelné timeouty
     */
    private function checkSessionSecurity(): void
    {
        $session = $this->getSession();
        $securitySection = $session->getSection('security');
        $now = time();

        // 🔒 Ochrana proti vícenásobné inicializaci během jednoho requestu
        static $alreadyChecked = false;
        if ($alreadyChecked) {
            return;
        }
        $alreadyChecked = true;

        // Načtení dynamických nastavení
        $sessionSettings = $this->getSessionSettings();

        // 1. Nastavení session security údajů při prvním přístupu
        if (!isset($securitySection->initialized)) {
            $securitySection->initialized = true;
            $securitySection->loginTime = $now;
            $securitySection->lastActivity = $now;
            $securitySection->lastRegeneration = $now;
            $securitySection->loginIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

            // Regenerace session ID po přihlášení
            $session->regenerateId();

            return; // Ukončit, nekontroluji timeout při inicializaci
        }

        // 🔒 Grace period - konfigurovatelná doba po přihlášení
        if (($now - $securitySection->loginTime) < $sessionSettings['grace_period']) {
            $securitySection->lastActivity = $now;
            return;
        }

        // 2. Kontrola timeoutu neaktivity - konfigurovatelná
        if (($now - $securitySection->lastActivity) > $sessionSettings['inactivity_timeout']) {
            $this->getUser()->logout(true);
            $timeoutMinutes = round($sessionSettings['inactivity_timeout'] / 60);
            $this->flashMessage("Byli jste odhlášeni z důvodu neaktivity ({$timeoutMinutes} minut).", 'warning');
            $this->redirect('Sign:in');
        }

        // 3. Kontrola maximální doby života session - konfigurovatelná
        if (($now - $securitySection->loginTime) > $sessionSettings['max_lifetime']) {
            $this->getUser()->logout(true);
            $maxHours = round($sessionSettings['max_lifetime'] / 3600);
            $this->flashMessage("Byli jste odhlášeni z důvodu překročení maximální doby přihlášení ({$maxHours} hodin).", 'warning');
            $this->redirect('Sign:in');
        }

        // 4. Periodická regenerace session ID - konfigurovatelná
        if (($now - $securitySection->lastRegeneration) > $sessionSettings['regeneration_interval']) {
            $session->regenerateId();
            $securitySection->lastRegeneration = $now;
        }

        // 5. Aktualizace poslední aktivity
        $securitySection->lastActivity = $now;
    }

    /**
     * 🔒 NOVÉ: Získání session nastavení s fallback hodnotami
     */
    private function getSessionSettings(): array
    {
        static $cachedSettings = null;

        // Cache nastavení během jednoho requestu
        if ($cachedSettings !== null) {
            return $cachedSettings;
        }

        try {
            // Pokusíme se získat nastavení přes SessionSettingsManager
            if (isset($this->sessionSettingsManager)) {
                $this->sessionSettingsManager->setTenantContext(
                    $this->getCurrentTenantId(),
                    $this->isSuperAdmin()
                );
                $cachedSettings = $this->sessionSettingsManager->getSessionSettings();
                return $cachedSettings;
            }
        } catch (\Exception $e) {
            // Logování chyby, ale pokračujeme s výchozími hodnotami
            \Tracy\Debugger::log("Chyba při načítání session nastavení: " . $e->getMessage(), \Tracy\ILogger::WARNING);
        }

        // Fallback - výchozí hodnoty pokud se nepodaří načíst z databáze
        $cachedSettings = [
            'grace_period' => 120,          // 2 minuty
            'inactivity_timeout' => 14400,  // 4 hodiny  
            'max_lifetime' => 43200,        // 12 hodin
            'regeneration_interval' => 1800  // 30 minut
        ];

        return $cachedSettings;
    }
}
