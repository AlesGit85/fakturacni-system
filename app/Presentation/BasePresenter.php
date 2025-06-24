<?php

declare(strict_types=1);

namespace App\Presentation;

use Nette;
use Nette\Application\UI\Presenter;
use Nette\Security\User;

abstract class BasePresenter extends Presenter
{
    /** @var bool Vyžaduje přihlášení (default true) */
    protected bool $requiresLogin = true;

    /** @var array Požadované role pro celý presenter */
    protected array $requiredRoles = [];

    /** @var array Specifické role pro jednotlivé akce */
    protected array $actionRoles = [];

    public function checkRequirements($element): void
    {
        // Nejdřív zavoláme původní kontrolu
        parent::checkRequirements($element);

        // Kontrola přihlášení - pokud presenter vyžaduje přihlášení
        if ($this->requiresLogin && !$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        // Kontrola rolí - pokud jsou definované
        if ($this->getUser()->isLoggedIn()) {
            $currentAction = $this->getAction();
            $requiredRoles = [];

            // Zkusíme najít specifické role pro akci
            if (isset($this->actionRoles[$currentAction])) {
                $requiredRoles = $this->actionRoles[$currentAction];
            } elseif (!empty($this->requiredRoles)) {
                // Pokud nejsou specifické role pro akci, použijeme obecné role presenteru
                $requiredRoles = $this->requiredRoles;
            }

            // Pokud jsou definované nějaké role, zkontrolujeme je
            if (!empty($requiredRoles)) {
                $hasRequiredRole = false;
                foreach ($requiredRoles as $role) {
                    if ($this->hasRole($role)) {
                        $hasRequiredRole = true;
                        break;
                    }
                }

                if (!$hasRequiredRole) {
                    $this->error('Nemáte oprávnění k této akci', 403);
                }
            }
        }
    }

    /**
     * Vytvoří navigační menu podle oprávnění uživatele
     */
    protected function getNavigationMenu(): array
    {
        $menuItems = [];
        
        try {
            if ($this->getUser()->isLoggedIn()) {
                // Domů - pro všechny přihlášené
                $menuItems[] = [
                    'title' => 'Domů',
                    'link' => $this->link('Home:default'),
                    'icon' => 'bi-house',
                    'active' => $this->getPresenter()->getName() === 'Home'
                ];

                // Faktury - pro readonly a vyšší
                if ($this->hasRole('readonly')) {
                    $menuItems[] = [
                        'title' => 'Faktury',
                        'link' => $this->link('Invoices:default'),
                        'icon' => 'bi-receipt',
                        'active' => $this->getPresenter()->getName() === 'Invoices'
                    ];
                }

                // Klienti - pro readonly a vyšší
                if ($this->hasRole('readonly')) {
                    $menuItems[] = [
                        'title' => 'Klienti',
                        'link' => $this->link('Clients:default'),
                        'icon' => 'bi-people',
                        'active' => $this->getPresenter()->getName() === 'Clients'
                    ];
                }



                // Nastavení - pro accountant a vyšší
                if ($this->hasRole('accountant')) {
                    $menuItems[] = [
                        'title' => 'Nastavení',
                        'link' => $this->link('Settings:default'),
                        'icon' => 'bi-gear',
                        'active' => $this->getPresenter()->getName() === 'Settings'
                    ];
                }

                // Uživatelé - jen pro admina, ale profil pro všechny
                if ($this->hasRole('admin')) {
                    $menuItems[] = [
                        'title' => 'Uživatelé',
                        'link' => $this->link('Users:default'),
                        'icon' => 'bi-person-gear',
                        'active' => $this->getPresenter()->getName() === 'Users'
                    ];
                }
            }
        } catch (\Exception $e) {
            // V případě chyby logujeme a pokračujeme s prázdným menu
            if (class_exists('\Tracy\Debugger')) {
                \Tracy\Debugger::log('Chyba při vytváření navigačního menu: ' . 
                    $e->getMessage());
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
        $lastTwoChars = mb_substr($name, -2, 2, 'UTF-8');
        
        // Základní pravidla pro mužská jména
        if (mb_substr($lowerName, -1) === 'š') {
            return $name . 'i';
        }
        
        if (in_array($lastChar, ['l', 'r', 'n', 't', 'd', 'k', 'm', 'p', 'b', 'v', 'f', 'g', 'h', 'ch', 's', 'z'])) {
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
            $this->template->add('currentUserRole', $identity && isset($identity->role) ? $identity->role : null);
            
            // Přidáme helper proměnné pro role do šablony
            $this->template->add('isUserAdmin', $this->isAdmin());
            $this->template->add('isUserAccountant', $this->isAccountant());
            $this->template->add('isUserReadonly', $this->isReadonly());
            
            // Přidáme helper pro vokativ do šablony
            $this->template->addFunction('vocative', function($name) {
                return $this->getVocativeName($name);
            });
            
            // Přidáme helper pro skloňování faktur do šablony
            $this->template->addFunction('getInvoiceCountText', function($count) {
                return $this->getInvoiceCountText($count);
            });
            
            $this->template->addFunction('pluralizeInvoices', function($count) {
                return $this->pluralizeInvoices($count);
            });
        }
        
        // Nastavení navigačního menu
        $this->template->menuItems = $this->getNavigationMenu();
    }
}