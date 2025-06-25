<?php

declare(strict_types=1);

namespace App\Presentation\Users;

use Nette;
use Nette\Application\UI\Form;
use App\Model\UserManager;
use App\Presentation\BasePresenter;

final class UsersPresenter extends BasePresenter
{
    /** @var UserManager */
    private $userManager;

    // Celý presenter je primárně pro adminy, kromě profilu
    protected array $requiredRoles = [];

    // Konkrétní role pro jednotlivé akce
    protected array $actionRoles = [
        'profile' => ['readonly', 'accountant', 'admin'], // Svůj profil může upravovat každý
        'default' => ['admin'], // Seznam uživatelů může vidět jen admin
        'add' => ['admin'], // Přidat uživatele může jen admin
        'edit' => ['admin'], // Upravit uživatele může jen admin
        'delete' => ['admin'], // Smazat uživatele může jen admin
    ];

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function renderDefault(): void
    {
        // Získáme všechny uživatele
        $allUsers = $this->userManager->getAll();
        
        // Spočítáme adminy přímo zde
        $adminCount = 0;
        foreach ($allUsers as $user) {
            if ($user->role === 'admin') {
                $adminCount++;
            }
        }
        
        $this->template->users = $this->userManager->getAll();
        $this->template->totalUsers = $this->userManager->getAll()->count();
        $this->template->adminCount = $adminCount;
    }

    public function renderProfile(): void
    {
        // Každý přihlášený uživatel může upravovat svůj profil
        $userId = $this->getUser()->getId();
        $user = $this->userManager->getById($userId);

        if (!$user) {
            $this->error('Uživatel nebyl nalezen');
        }

        $this->template->profileUser = $user;
        $this['profileForm']->setDefaults($user);
    }

    public function actionAdd(): void
    {
        // Pouze admin může přidávat uživatele - kontrola je už v actionRoles
    }

    public function actionEdit(int $id): void
    {
        $user = $this->userManager->getById($id);

        if (!$user) {
            $this->error('Uživatel nebyl nalezen');
        }

        $this->template->editUser = $user;
        $this['userForm']->setDefaults($user);
    }

    public function actionDelete(int $id): void
    {
        $user = $this->userManager->getById($id);

        if (!$user) {
            $this->error('Uživatel nebyl nalezen');
        }

        // Nelze smazat sebe sama
        if ($id === $this->getUser()->getId()) {
            $this->flashMessage('Nemůžete smazat sám sebe.', 'danger');
            $this->redirect('default');
        }

        // Kontrola, zda se nejedná o posledního admina
        if ($user->role === 'admin') {
            $adminCount = $this->userManager->getAll()->where('role', 'admin')->count();
            if ($adminCount <= 1) {
                $this->flashMessage('Nemůžete smazat posledního administrátora.', 'danger');
                $this->redirect('default');
            }
        }

        // Použijeme upravený delete s informací o adminovi
        $adminId = $this->getUser()->getId();
        $adminName = $this->getUser()->getIdentity()->username;

        $this->userManager->delete($id, $adminId, $adminName);
        $this->flashMessage("Uživatel '{$user->username}' byl úspěšně smazán.", 'success');
        $this->redirect('default');
    }

    protected function createComponentUserForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Zadejte uživatelské jméno')
            ->addRule(Form::MIN_LENGTH, 'Uživatelské jméno musí mít alespoň %d znaků', 3)
            ->addRule(Form::MAX_LENGTH, 'Uživatelské jméno může mít maximálně %d znaků', 50);

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte e-mailovou adresu');

        $form->addSelect('role', 'Role:', [
            'readonly' => 'Pouze čtení',
            'accountant' => 'Účetní',
            'admin' => 'Administrátor'
        ])
            ->setRequired('Vyberte roli');

        $passwordField = $form->addPassword('password', 'Nové heslo:')
            ->setRequired(false);

        $passwordField->addCondition($form::FILLED)
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 8)
            ->addRule(Form::PATTERN, 'Heslo musí obsahovat alespoň jednu číslici', '.*[0-9].*')
            ->addRule(Form::PATTERN, 'Heslo musí obsahovat alespoň jedno velké písmeno', '.*[A-Z].*');

        $form->addPassword('passwordVerify', 'Nové heslo znovu:')
            ->setRequired(false)
            ->addConditionOn($passwordField, $form::FILLED)
            ->setRequired('Zadejte heslo znovu pro kontrolu')
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $passwordField);

        $form->addSubmit('send', 'Uložit změny');

        $form->onSuccess[] = [$this, 'userFormSucceeded'];

        return $form;
    }

    public function userFormSucceeded(Form $form, \stdClass $data): void
    {
        $id = $this->getParameter('id');

        try {
            // Kontrola jedinečnosti uživatelského jména a e-mailu
            $existingUsername = $this->userManager->getAll()
                ->where('username', $data->username)
                ->where('id != ?', $id ?: 0)
                ->fetch();

            $existingEmail = $this->userManager->getAll()
                ->where('email', $data->email)
                ->where('id != ?', $id ?: 0)
                ->fetch();

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

            if ($id) {
                // Úprava stávajícího uživatele
                $updateData = [
                    'username' => $data->username,
                    'email' => $data->email,
                    'role' => $data->role,
                ];

                if (!empty($data->password)) {
                    $updateData['password'] = $data->password;
                }

                $adminId = $this->getUser()->getId();
                $adminName = $this->getUser()->getIdentity()->username;
                $this->userManager->update($id, $updateData, $adminId, $adminName);

                $this->flashMessage('Uživatel byl úspěšně upraven.', 'success');
            } else {
                // Přidání nového uživatele
                if (empty($data->password)) {
                    /** @var Nette\Forms\Controls\PasswordInput $passwordField */
                    $passwordField = $form['password'];
                    $passwordField->addError('Pro nového uživatele je nutné zadat heslo.');
                    return;
                }

                $adminId = $this->getUser()->getId();
                $adminName = $this->getUser()->getIdentity()->username;
                $this->userManager->add($data->username, $data->email, $data->password, $data->role, $adminId, $adminName);

                $this->flashMessage('Uživatel byl úspěšně přidán.', 'success');
            }

            $this->redirect('default');
        } catch (\Exception $e) {
            $form->addError('Při ukládání uživatele došlo k chybě: ' . $e->getMessage());
        }
    }

    protected function createComponentAddForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Zadejte uživatelské jméno')
            ->addRule(Form::MIN_LENGTH, 'Uživatelské jméno musí mít alespoň %d znaků', 3)
            ->addRule(Form::MAX_LENGTH, 'Uživatelské jméno může mít maximálně %d znaků', 50);

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte e-mailovou adresu');

        $form->addSelect('role', 'Role:', [
            'readonly' => 'Pouze čtení',
            'accountant' => 'Účetní',
            'admin' => 'Administrátor'
        ])
            ->setRequired('Vyberte roli');

        $passwordField = $form->addPassword('password', 'Heslo:')
            ->setRequired('Zadejte heslo')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 8)
            ->addRule(Form::PATTERN, 'Heslo musí obsahovat alespoň jednu číslici', '.*[0-9].*')
            ->addRule(Form::PATTERN, 'Heslo musí obsahovat alespoň jedno velké písmeno', '.*[A-Z].*');

        $form->addPassword('passwordVerify', 'Heslo znovu:')
            ->setRequired('Zadejte heslo znovu pro kontrolu')
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $passwordField);

        $form->addSubmit('send', 'Přidat uživatele');

        $form->onSuccess[] = [$this, 'addFormSucceeded'];

        return $form;
    }

    public function addFormSucceeded(Form $form, \stdClass $data): void
    {
        try {
            // Kontrola jedinečnosti uživatelského jména a e-mailu
            $existingUsername = $this->userManager->getAll()
                ->where('username', $data->username)
                ->fetch();

            $existingEmail = $this->userManager->getAll()
                ->where('email', $data->email)
                ->fetch();

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

            // Vytvoření uživatele
            $this->userManager->add($data->username, $data->email, $data->password, $data->role);

            $this->flashMessage('Uživatel byl úspěšně přidán.', 'success');
            $this->redirect('default');
        } catch (\Exception $e) {
            $form->addError('Při přidávání uživatele došlo k chybě: ' . $e->getMessage());
        }
    }

    protected function createComponentProfileForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        // Základní údaje
        $form->addText('first_name', 'Křestní jméno:')
            ->setRequired(false)
            ->addRule(Form::MAX_LENGTH, 'Křestní jméno může mít maximálně %d znaků', 100);

        $form->addText('last_name', 'Příjmení:')
            ->setRequired(false)
            ->addRule(Form::MAX_LENGTH, 'Příjmení může mít maximálně %d znaků', 100);

        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Zadejte uživatelské jméno')
            ->addRule(Form::MIN_LENGTH, 'Uživatelské jméno musí mít alespoň %d znaků', 3)
            ->addRule(Form::MAX_LENGTH, 'Uživatelské jméno může mít maximálně %d znaků', 50);

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte e-mailovou adresu');

        // Změna hesla
        $form->addPassword('currentPassword', 'Současné heslo:')
            ->setRequired(false);

        $passwordField = $form->addPassword('password', 'Nové heslo:')
            ->setRequired(false);

        $passwordField->addCondition($form::FILLED)
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 8)
            ->addRule(Form::PATTERN, 'Heslo musí obsahovat alespoň jednu číslici', '.*[0-9].*')
            ->addRule(Form::PATTERN, 'Heslo musí obsahovat alespoň jedno velké písmeno', '.*[A-Z].*');

        $form->addPassword('passwordVerify', 'Nové heslo znovu:')
            ->setRequired(false)
            ->addConditionOn($passwordField, $form::FILLED)
            ->setRequired('Zadejte heslo znovu pro kontrolu')
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $passwordField);

        $form->addSubmit('send', 'Uložit změny');

        $form->onSuccess[] = [$this, 'profileFormSucceeded'];

        return $form;
    }

    public function profileFormSucceeded(Form $form, \stdClass $data): void
    {
        $userId = $this->getUser()->getId();
        $originalUsername = $this->getUser()->getIdentity()->username;

        try {
            // Kontrola jedinečnosti uživatelského jména a e-mailu
            $existingUsername = $this->userManager->getAll()
                ->where('username', $data->username)
                ->where('id != ?', $userId)
                ->fetch();

            $existingEmail = $this->userManager->getAll()
                ->where('email', $data->email)
                ->where('id != ?', $userId)
                ->fetch();

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

            // Pokud se mění heslo, ověříme současné heslo
            if (!empty($data->password)) {
                if (empty($data->currentPassword)) {
                    /** @var Nette\Forms\Controls\PasswordInput $currentPasswordField */
                    $currentPasswordField = $form['currentPassword'];
                    $currentPasswordField->addError('Pro změnu hesla musíte zadat současné heslo.');
                    return;
                }

                $currentUsername = $this->getUser()->getIdentity()->username;
                if (!$this->userManager->verifyPassword($currentUsername, $data->currentPassword)) {
                    /** @var Nette\Forms\Controls\PasswordInput $currentPasswordField */
                    $currentPasswordField = $form['currentPassword'];
                    $currentPasswordField->addError('Současné heslo není správné.');
                    return;
                }
            }

            $updateData = [
                'first_name' => $data->first_name ?: null,
                'last_name' => $data->last_name ?: null,
                'username' => $data->username,
                'email' => $data->email,
            ];

            if (!empty($data->password)) {
                $updateData['password'] = $data->password;
                $this->flashMessage('Heslo bylo úspěšně změněno.', 'success');
            }

            // Provedeme aktualizaci
            $result = $this->userManager->update($userId, $updateData);

            if ($result) {
                // Pokud se změnilo uživatelské jméno - zobrazíme countdown modal
                if ($data->username !== $originalUsername) {
                    $this->flashMessage('Uživatelské jméno bylo úspěšně změněno.', 'success');
                    $this->template->showLogoutCountdown = true;
                    $this->template->newUsername = $data->username;
                    $this->template->originalUsername = $originalUsername;
                    return; // Zobrazíme countdown místo redirectu
                }
                
                $this->flashMessage('Váš profil byl úspěšně upraven.', 'success');
            } else {
                $form->addError('Při aktualizaci profilu došlo k chybě. Žádné změny nebyly uloženy.');
                return;
            }

            $this->redirect('profile');
        } catch (\Exception $e) {
            $form->addError('Při úpravě profilu došlo k chybě: ' . $e->getMessage());
        }
    }
}