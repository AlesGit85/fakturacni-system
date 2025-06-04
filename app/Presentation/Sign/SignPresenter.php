<?php

declare(strict_types=1);

namespace App\Presentation\Sign;

use Nette;
use Nette\Application\UI\Form;
use App\Model\UserManager;
use App\Presentation\BasePresenter;
use App\Security\SecurityLogger;

final class SignPresenter extends BasePresenter
{
    /** @var UserManager */
    private $userManager;

    /** @var SecurityLogger */
    private $securityLogger;

    protected bool $requiresLogin = false;

    public function __construct(UserManager $userManager, SecurityLogger $securityLogger)
    {
        $this->userManager = $userManager;
        $this->securityLogger = $securityLogger;
    }

    public function actionIn(): void
    {
        // Pokud je už přihlášen, přesměruj na hlavní stránku
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Home:default');
        }
    }

    public function actionUp(): void
    {
        // Pokud je už přihlášen, přesměruj na hlavní stránku
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Home:default');
        }

        // Kontrola, zda už existuje nějaký uživatel
        $userCount = 0;
        try {
            $allUsers = $this->userManager->getAll();
            if ($allUsers && method_exists($allUsers, 'count')) {
                $userCount = $allUsers->count();
            } elseif (is_array($allUsers)) {
                $userCount = count($allUsers);
            }
        } catch (\Exception $e) {
            // V případě chyby předpokládáme, že uživatelé neexistují
            $userCount = 0;
        }

        if ($userCount === 0) {
            // První uživatel bude automaticky admin
            $this->template->isFirstUser = true;
        } else {
            // Registrace dalších uživatelů
            $this->template->isFirstUser = false;
        }
    }

    public function actionOut(): void
    {
        // Logování odhlášení před samotným odhlášením
        if ($this->getUser()->isLoggedIn()) {
            $identity = $this->getUser()->getIdentity();
            $this->securityLogger->logLogout($identity->id, $identity->username);
        }

        $this->getUser()->logout();
        $this->flashMessage('Byli jste úspěšně odhlášeni.', 'success');
        $this->redirect('Sign:in');
    }

    public function actionRelogin(): void
    {
        // Tato akce je určena pro situace, kdy se změnilo uživatelské jméno
        // a je potřeba uživatele bezpečně odhlásit a přesměrovat na přihlášení

        // Pokud už není přihlášen, přesměruj na přihlášení
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage('Byli jste odhlášeni. Přihlaste se prosím znovu.', 'info');
            $this->redirect('Sign:in');
        }

        // Získáme informace pro šablonu (currentUser už je nastaveno v BasePresenter)
        $identity = $this->getUser()->getIdentity();
        $this->template->username = $identity ? $identity->username : 'neznámý uživatel';
    }

    public function actionForceLogout(): void
    {
        // Tato akce provede skutečné odhlášení s logováním
        if ($this->getUser()->isLoggedIn()) {
            $identity = $this->getUser()->getIdentity();
            $this->securityLogger->logLogout($identity->id, $identity->username);
            $this->getUser()->logout();
        }

        $this->flashMessage('Byli jste úspěšně odhlášeni. Přihlaste se prosím s novým uživatelským jménem.', 'info');
        $this->redirect('Sign:in');
    }

    protected function createComponentSignInForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Zadejte uživatelské jméno');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Zadejte heslo');

        $form->addCheckbox('remember', 'Zůstat přihlášen');

        $form->addSubmit('send', 'Přihlásit se');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];

        return $form;
    }

    public function signInFormSucceeded(Form $form, \stdClass $data): void
    {
        try {
            if ($data->remember) {
                $this->getUser()->setExpiration('14 days');
            } else {
                $this->getUser()->setExpiration('20 minutes', true);
            }

            $this->getUser()->login($data->username, $data->password);

            $this->flashMessage('Úspěšně jste se přihlásili.', 'success');

            // Přesměrování na původně požadovanou stránku nebo na dashboard
            $backlink = $this->getParameter('backlink');
            if ($backlink) {
                $this->restoreRequest($backlink);
            }
            $this->redirect('Home:default');
        } catch (Nette\Security\AuthenticationException $e) {
            // Přidáme chybovou hlášku jako globální chybu formuláře
            $form->addError($e->getMessage());
        }
    }

    protected function createComponentSignUpForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Zadejte uživatelské jméno')
            ->addRule(Form::MIN_LENGTH, 'Uživatelské jméno musí mít alespoň %d znaků', 3)
            ->addRule(Form::MAX_LENGTH, 'Uživatelské jméno může mít maximálně %d znaků', 50);

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte e-mailovou adresu');

        $passwordField = $form->addPassword('password', 'Heslo:')
            ->setRequired('Zadejte heslo')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 8)
            ->addRule(Form::PATTERN, 'Heslo musí obsahovat alespoň jednu číslici', '.*[0-9].*')
            ->addRule(Form::PATTERN, 'Heslo musí obsahovat alespoň jedno velké písmeno', '.*[A-Z].*');

        $form->addPassword('passwordVerify', 'Heslo znovu:')
            ->setRequired('Zadejte heslo znovu pro kontrolu')
            ->addConditionOn($passwordField, $form::VALID)
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $passwordField);

        // Role - pouze pokud už existují uživatelé (první uživatel je automaticky admin)
        $userCount = 0;
        try {
            $allUsers = $this->userManager->getAll();
            if ($allUsers && method_exists($allUsers, 'count')) {
                $userCount = $allUsers->count();
            } elseif (is_array($allUsers)) {
                $userCount = count($allUsers);
            }
        } catch (\Exception $e) {
            $userCount = 0;
        }

        if ($userCount > 0) {
            $form->addSelect('role', 'Role:', [
                'readonly' => 'Pouze čtení',
                'accountant' => 'Účetní',
                'admin' => 'Administrátor'
            ])
                ->setRequired('Vyberte roli')
                ->setDefaultValue('readonly');
        }

        $form->addSubmit('send', 'Registrovat se');

        $form->onSuccess[] = [$this, 'signUpFormSucceeded'];

        return $form;
    }

    public function signUpFormSucceeded(Form $form, \stdClass $data): void
    {
        try {
            // Kontrola, zda už uživatel neexistuje - kontrolujeme zvlášť username a email
            $existingUsername = null;
            $existingEmail = null;

            try {
                $allUsers = $this->userManager->getAll();
                if ($allUsers) {
                    $existingUsername = $allUsers->where('username', $data->username)->fetch();
                    $existingEmail = $allUsers->where('email', $data->email)->fetch();
                }
            } catch (\Exception $e) {
                // Pokud selže dotaz, pokračujeme (může být první uživatel)
                error_log('Chyba při kontrole existujících uživatelů: ' . $e->getMessage());
            }

            if ($existingUsername) {
                /** @var Nette\Forms\Controls\TextInput $usernameField */
                $usernameField = $form['username'];
                $usernameField->addError('Uživatelské jméno už je obsazené.');
                return;
            }

            if ($existingEmail) {
                /** @var Nette\Forms\Controls\TextInput $emailField */
                $emailField = $form['email'];
                $emailField->addError('E-mailová adresa už je registrovaná.');
                return;
            }

            // Určení role
            $userCount = 0;
            try {
                $allUsers = $this->userManager->getAll();
                if ($allUsers && method_exists($allUsers, 'count')) {
                    $userCount = $allUsers->count();
                } elseif (is_array($allUsers)) {
                    $userCount = count($allUsers);
                }
            } catch (\Exception $e) {
                $userCount = 0;
            }

            if ($userCount === 0) {
                // První uživatel je automaticky admin
                $role = 'admin';
            } else {
                $role = isset($data->role) ? $data->role : 'readonly';
            }

            // Admin ID a jméno pro logování (null pro samoregistraci)
            $adminId = null;
            $adminName = null;

            // Pokud je přihlášen admin, který vytváří uživatele
            if ($this->getUser()->isLoggedIn() && $this->isAdmin()) {
                $adminId = $this->getUser()->getId();
                $adminName = $this->getUser()->getIdentity()->username;
            }

            // Vytvoření uživatele
            $newUserId = $this->userManager->add($data->username, $data->email, $data->password, $role, $adminId, $adminName);

            // Kontrola, zda se uživatel skutečně vytvořil
            if ($newUserId && $newUserId > 0) {
                $this->flashMessage('Registrace byla úspěšná. Nyní se můžete přihlásit.', 'success');
                $this->redirect('Sign:in');
            } else {
                $form->addError('Nepodařilo se vytvořit uživatelský účet. Zkuste to prosím znovu.');
            }
        } catch (Nette\Application\AbortException $e) {
            // AbortException je normální při redirect - necháme ji projít
            throw $e;
        } catch (\Exception $e) {
            // Logování pouze závažných chyb
            error_log('Chyba při registraci: ' . $e->getMessage());
            $form->addError('Při registraci došlo k neočekávané chybě. Zkuste to prosím znovu.');
        }
    }
}
