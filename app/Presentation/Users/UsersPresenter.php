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

    protected array $requiredRoles = ['admin'];

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    private function debugLog($message)
    {
        $logFile = __DIR__ . '/../../../temp/profile_debug.log';
        file_put_contents($logFile, 
            date('Y-m-d H:i:s') . " - " . $message . "\n", 
            FILE_APPEND
        );
    }

    public function renderDefault(): void
    {
        $this->template->add('users', $this->userManager->getAll());
        $this->template->add('totalUsers', $this->userManager->getAll()->count());
    }

    public function renderProfile(): void
    {
        $this->debugLog("=== RENDER PROFILE START ===");
        
        // Každý přihlášený uživatel může upravovat svůj profil
        $this->requiredRoles = ['readonly', 'accountant', 'admin'];
        
        $userId = $this->getUser()->getId();
        $this->debugLog("User ID: " . $userId);
        
        $user = $this->userManager->getById($userId);
        
        if (!$user) {
            $this->debugLog("ERROR: User not found");
            $this->error('Uživatel nebyl nalezen');
        }
        
        $this->debugLog("User found: " . $user->username);
        $this->template->add('profileUser', $user);
        $this['profileForm']->setDefaults($user);
        $this->debugLog("=== RENDER PROFILE END ===");
    }

    public function actionAdd(): void
    {
        // Pouze admin může přidávat uživatele
    }

    public function actionEdit(int $id): void
    {
        $user = $this->userManager->getById($id);
        
        if (!$user) {
            $this->error('Uživatel nebyl nalezen');
        }
        
        $this->template->add('editUser', $user);
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
        
        $this->userManager->delete($id);
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
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 6);

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
            
            $updateData = [
                'username' => $data->username,
                'email' => $data->email,
                'role' => $data->role,
            ];
            
            // Pokud je zadáno heslo, přidáme ho
            if (!empty($data->password)) {
                $updateData['password'] = $data->password;
            }
            
            if ($id) {
                // Editace existujícího uživatele
                $this->userManager->update($id, $updateData);
                $this->flashMessage('Uživatel byl úspěšně upraven.', 'success');
            } else {
                // Přidání nového uživatele
                if (empty($data->password)) {
                    /** @var Nette\Forms\Controls\PasswordInput $passwordField */
                    $passwordField = $form['password'];
                    $passwordField->addError('Pro nového uživatele je heslo povinné.');
                    return;
                }
                $this->userManager->add($data->username, $data->email, $data->password, $data->role);
                $this->flashMessage('Uživatel byl úspěšně přidán.', 'success');
            }
            
            $this->redirect('default');
            
        } catch (\Exception $e) {
            $form->addError('Při ukládání uživatele došlo k chybě: ' . $e->getMessage());
        }
    }

    protected function createComponentAddUserForm(): Form
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
            ->setRequired('Vyberte roli')
            ->setDefaultValue('readonly');

        $passwordField = $form->addPassword('password', 'Heslo:')
            ->setRequired('Zadejte heslo')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 6);

        $form->addPassword('passwordVerify', 'Heslo znovu:')
            ->setRequired('Zadejte heslo znovu pro kontrolu')
            ->addConditionOn($passwordField, $form::VALID)
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $passwordField);

        $form->addSubmit('send', 'Přidat uživatele');

        $form->onSuccess[] = [$this, 'addUserFormSucceeded'];

        return $form;
    }

    public function addUserFormSucceeded(Form $form, \stdClass $data): void
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
        $this->debugLog("=== CREATE PROFILE FORM START ===");
        
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Zadejte uživatelské jméno')
            ->addRule(Form::MIN_LENGTH, 'Uživatelské jméno musí mít alespoň %d znaků', 3)
            ->addRule(Form::MAX_LENGTH, 'Uživatelské jméno může mít maximálně %d znaků', 50);

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte e-mailovou adresu');

        $form->addPassword('currentPassword', 'Současné heslo:')
            ->setRequired(false);

        $passwordField = $form->addPassword('password', 'Nové heslo:')
            ->setRequired(false);

        $passwordField->addCondition($form::FILLED)
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 6);

        $form->addPassword('passwordVerify', 'Nové heslo znovu:')
            ->setRequired(false)
            ->addConditionOn($passwordField, $form::FILLED)
            ->setRequired('Zadejte heslo znovu pro kontrolu')
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $passwordField);

        $form->addSubmit('send', 'Uložit změny');

        $form->onSuccess[] = [$this, 'profileFormSucceeded'];
        
        $this->debugLog("=== CREATE PROFILE FORM END ===");
        return $form;
    }

    public function profileFormSucceeded(Form $form, \stdClass $data): void
    {
        $this->debugLog("=== PROFILE FORM SUCCEEDED START ===");
        $this->debugLog("Form data: " . print_r($data, true));
        
        $userId = $this->getUser()->getId();
        $this->debugLog("Current user ID: " . $userId);
        
        try {
            // Získáme aktuální údaje uživatele
            $currentUser = $this->userManager->getById($userId);
            if (!$currentUser) {
                $this->debugLog("ERROR: Current user not found");
                $form->addError('Uživatel nebyl nalezen.');
                return;
            }
            
            $this->debugLog("Current user found: " . $currentUser->username);

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
                $this->debugLog("ERROR: Username already exists");
                /** @var Nette\Forms\Controls\TextInput $usernameField */
                $usernameField = $form['username'];
                $usernameField->addError('Uživatelské jméno už je obsazené.');
                return;
            }
            
            if ($existingEmail) {
                $this->debugLog("ERROR: Email already exists");
                /** @var Nette\Forms\Controls\TextInput $emailField */
                $emailField = $form['email'];
                $emailField->addError('E-mailová adresa už je registrovaná.');
                return;
            }
            
            // Pokud se mění heslo, ověříme současné heslo
            if (!empty($data->password)) {
                $this->debugLog("Password change requested");
                $this->debugLog("Current password provided: " . (!empty($data->currentPassword) ? "YES" : "NO"));
                
                if (empty($data->currentPassword)) {
                    $this->debugLog("ERROR: Current password not provided");
                    /** @var Nette\Forms\Controls\PasswordInput $currentPasswordField */
                    $currentPasswordField = $form['currentPassword'];
                    $currentPasswordField->addError('Pro změnu hesla musíte zadat současné heslo.');
                    return;
                }
                
                // Ověření současného hesla
                $passwordValid = $this->userManager->verifyPassword($currentUser->username, $data->currentPassword);
                $this->debugLog("Current password verification: " . ($passwordValid ? "SUCCESS" : "FAILED"));
                
                if (!$passwordValid) {
                    $this->debugLog("ERROR: Current password verification failed");
                    /** @var Nette\Forms\Controls\PasswordInput $currentPasswordField */
                    $currentPasswordField = $form['currentPassword'];
                    $currentPasswordField->addError('Současné heslo není správné.');
                    return;
                }
                
                $this->debugLog("Password verification successful, proceeding with password change");
            }
            
            $updateData = [
                'username' => $data->username,
                'email' => $data->email,
            ];
            
            // Pokud je zadáno heslo, přidáme ho
            if (!empty($data->password)) {
                $updateData['password'] = $data->password;
                $this->debugLog("Adding new password to update data");
            }
            
            $this->debugLog("Update data: " . print_r($updateData, true));
            
            // Provedeme aktualizaci
            $result = $this->userManager->update($userId, $updateData);
            $this->debugLog("Update result: " . ($result ? "SUCCESS" : "FAILED"));
            
            if ($result) {
                $this->debugLog("SUCCESS: Profile updated successfully");
                $this->flashMessage('Váš profil byl úspěšně upraven.', 'success');
                
                // Pokud se změnilo uživatelské jméno, musíme uživatele znovu přihlásit
                if ($data->username !== $currentUser->username) {
                    $this->debugLog("Username changed, logging out user");
                    $this->getUser()->logout();
                    $this->flashMessage('Změnil se váš uživatelský název. Přihlaste se prosím znovu.', 'info');
                    $this->redirect('Sign:in');
                }
                
                $this->redirect('profile');
            } else {
                $this->debugLog("ERROR: Update failed");
                $form->addError('Při ukládání změn došlo k chybě.');
            }
            
        } catch (\Exception $e) {
            $this->debugLog("EXCEPTION: " . $e->getMessage());
            $this->debugLog("Stack trace: " . $e->getTraceAsString());
            $form->addError('Při úpravě profilu došlo k chybě: ' . $e->getMessage());
        }
        
        $this->debugLog("=== PROFILE FORM SUCCEEDED END ===");
    }
}