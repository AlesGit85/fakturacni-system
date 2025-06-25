<?php

declare(strict_types=1);

namespace App\Presentation;

use Nette;
use Nette\Application\UI\Presenter;
use App\Security\SecurityLogger;
use App\Model\ModuleManager;

abstract class BasePresenter extends Presenter
{
    /** @var array Definice požadovaných rolí pro jednotlivé presentery */
    protected array $requiredRoles = [];

    /** @var array Definice požadovaných rolí pro jednotlivé akce */
    protected array $actionRoles = [];

    /** @var bool Zda presenter vyžaduje přihlášení */
    protected bool $requiresLogin = true;

    /** @var SecurityLogger */
    private $securityLogger;

    /** @var ModuleManager */
    private $moduleManager;

    public function injectSecurityLogger(SecurityLogger $securityLogger): void
    {
        $this->securityLogger = $securityLogger;
    }

    public function injectModuleManager(ModuleManager $moduleManager): void
    {
        $this->moduleManager = $moduleManager;
    }

    public function startup(): void
    {
        parent::startup();
        
        // Kontrola přihlášení
        if ($this->requiresLogin && !$this->getUser()->isLoggedIn()) {
            if ($this->getUser()->getLogoutReason() === Nette\Security\UserStorage::LOGOUT_INACTIVITY) {
                $this->flashMessage('Byli jste odhlášeni z důvodu neaktivity. Přihlaste se prosím znovu.', 'warning');
            } else {
                $this->flashMessage('Pro přístup k této stránce se musíte přihlásit.', 'info');
            }
            $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
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
     * Kontroluje, zda má uživatel roli potřebnou pro danou akci
     */
    protected function hasRequiredRoleForAction(string $action, string $userRole): bool
    {
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
     * Získá menu položky z aktivních modulů
     */
    protected function getModuleMenuItems(): array
    {
        if (!$this->moduleManager) {
            return [];
        }

        $menuItems = [];
        $activeModules = $this->moduleManager->getActiveModules();
        
        foreach ($activeModules as $moduleId => $moduleInfo) {
            try {
                // Pokusíme se načíst modul a získat jeho menu položky
                $modulePath = dirname(__DIR__) . '/Modules/' . $moduleId;
                $moduleFile = $modulePath . '/Module.php';
                
                if (file_exists($moduleFile)) {
                    require_once $moduleFile;
                    $moduleClassName = 'Modules\\' . ucfirst($moduleId) . '\\Module';
                    
                    if (class_exists($moduleClassName)) {
                        $moduleInstance = new $moduleClassName();
                        
                        if (method_exists($moduleInstance, 'getMenuItems')) {
                            $moduleMenuItems = $moduleInstance->getMenuItems();
                            
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
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Logujeme chybu, ale pokračujeme
                if (isset($this->securityLogger)) {
                    $this->securityLogger->logSecurityEvent('module_menu_error', 
                        "Chyba při načítání menu z modulu $moduleId: " . $e->getMessage());
                }
            }
        }
        
        return $menuItems;
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
     * Používá hierarchii rolí - admin může vše, accountant může accountant + readonly, readonly jen readonly
     */
    public function hasRole(string $role): bool
    {
        if (!$this->getUser()->isLoggedIn()) {
            return false;
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
     * Kontroluje, zda je uživatel admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Kontroluje, zda je uživatel účetní nebo admin
     */
    public function isAccountant(): bool
    {
        return $this->hasRole('accountant');
    }

    /**
     * Kontroluje, zda má uživatel roli readonly nebo vyšší
     */
    public function isReadonly(): bool
    {
        return $this->hasRole('readonly');
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
            'lucie' => 'Lucie',
            'barbora' => 'Barbaro',
            'veronika' => 'Veroniko',
            'kristýna' => 'Kristýno',
            'nikola' => 'Nikolo',
            'simona' => 'Simono',
            'monika' => 'Moniko',
            'klára' => 'Kláro',
            'adéla' => 'Adélo',
            'denisa' => 'Deniso',
            'pavlína' => 'Pavlíno',
            'markéta' => 'Markéto',
            'ivana' => 'Ivano',
            'helena' => 'Heleno',
            'jiřina' => 'Jiřino',
            'dagmar' => 'Dagmar',
        ];

        // Nejdříve zkusíme najít v předdefinovaných slovnících
        if (isset($maleNames[$lowerName])) {
            return $maleNames[$lowerName];
        }
        
        if (isset($femaleNames[$lowerName])) {
            return $femaleNames[$lowerName];
        }

        // Pokud jméno není ve slovníku, použijeme základní pravidla
        $lastChar = mb_substr($name, -1, 1, 'UTF-8');
        
        // Základní pravidla pro mužská jména
        if (mb_substr($lowerName, -1) === 'š') {
            return $name . 'i';
        }
        
        if (in_array($lastChar, ['l', 'r', 'n', 't', 'd', 'k', 'm', 'p', 'b', 'v', 'f', 'g', 'h', 's', 'z'])) {
            return $name . 'e';
        }
        
        // Základní pravidla pro ženská jména končící na -a
        if ($lastChar === 'a') {
            return mb_substr($name, 0, -1, 'UTF-8') . 'o';
        }
        
        // Ženská jména končící na -e zůstávají stejná
        if ($lastChar === 'e') {
            return $name;
        }
        
        // Pokud nepoznáme vzor, vrátíme původní jméno
        return $name;
    }

    /**
     * Template helper pro kontrolu rolí v šablonách
     */
    public function beforeRender(): void
    {
        parent::beforeRender();
        
        // Nastavení uživatelských dat pro šablonu
        $user = $this->getUser();
        $this->template->add('userLoggedIn', $user->isLoggedIn());
        
        if ($user->isLoggedIn()) {
            $identity = $user->getIdentity();
            $this->template->add('currentUser', $identity);
            $this->template->add('currentUserRole', $identity && isset($identity->role) ? $identity->role : 'readonly');
        } else {
            $this->template->add('currentUser', null);
            $this->template->add('currentUserRole', 'readonly');
        }
        
        // Helper funkce pro šablony
        $this->template->add('isUserAdmin', $this->isAdmin());
        $this->template->add('isUserAccountant', $this->isAccountant());
        $this->template->add('isUserReadonly', $this->isReadonly());
        
        // Přidání helper funkcí pro skloňování do šablony
        $this->template->addFunction('pluralizeInvoices', [$this, 'pluralizeInvoices']);
        $this->template->addFunction('getInvoiceCountText', [$this, 'getInvoiceCountText']);
        
        // Přidání helper funkce pro vokativ do šablony
        $this->template->addFunction('vocative', [$this, 'getVocativeName']);
        
        // Přidání menu položek z modulů do šablony
        $this->template->add('moduleMenuItems', $this->getModuleMenuItems());
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
     * Metoda může být použita pro složitější kontroly přístupu v presenterech
     */
    protected function checkAccess(string $resource, string $privilege = null): bool
    {
        if (!$this->getUser()->isLoggedIn()) {
            return false;
        }
        
        $role = $this->getCurrentUserRole();
        
        // Pro zjednodušení používáme hierarchii rolí
        // Admin může všechno
        if ($role === 'admin') {
            return true;
        }
        
        // Podle potřeby zde můžete implementovat složitější logiku
        // např. kontrolu na úrovni objektů, vlastnictví záznamů atd.
        
        return false;
    }
}