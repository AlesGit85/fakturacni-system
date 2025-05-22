<?php

declare(strict_types=1);

namespace App\Presentation;

use Nette;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{
    /** @var array Definice požadovaných rolí pro jednotlivé presentery */
    protected array $requiredRoles = [];

    /** @var bool Zda presenter vyžaduje přihlášení */
    protected bool $requiresLogin = true;

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

        // Kontrola rolí
        if ($this->requiresLogin && !empty($this->requiredRoles)) {
            $identity = $this->getUser()->getIdentity();
            if ($identity && isset($identity->role)) {
                $userRole = $identity->role;
                if (!in_array($userRole, $this->requiredRoles)) {
                    $this->flashMessage('Nemáte oprávnění pro přístup k této stránce.', 'danger');
                    $this->redirect('Home:default');
                }
            }
        }
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
}