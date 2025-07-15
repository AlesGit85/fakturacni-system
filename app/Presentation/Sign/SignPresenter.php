<?php

declare(strict_types=1);

namespace App\Presentation\Sign;

use Nette;
use Nette\Application\UI\Form;
use App\Model\UserManager;
use App\Model\EmailService;
use App\Model\TenantManager;
use App\Presentation\BasePresenter;
use App\Security\SecurityLogger;

final class SignPresenter extends BasePresenter
{
    /** @var UserManager */
    private $userManager;

    /** @var SecurityLogger */
    private $securityLogger;

    /** @var EmailService */
    private $emailService;

    /** @var TenantManager */
    private $tenantManager;

    protected bool $requiresLogin = false;

    public function __construct(
        UserManager $userManager, 
        SecurityLogger $securityLogger,
        EmailService $emailService,
        TenantManager $tenantManager,
        Nette\Database\Explorer $database
    ) {
        $this->userManager = $userManager;
        $this->securityLogger = $securityLogger;
        $this->emailService = $emailService;
        $this->tenantManager = $tenantManager;
        $this->database = $database;
    }

    /**
     * Default akce - přesměruje na přihlášení
     */
    public function actionDefault(): void
    {
        $this->redirect('Sign:in');
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

        // Registrace = vždy vytvoření nového firemního účtu
        // Neřešíme, jestli už existují jiní uživatelé
        $this->template->isNewCompanyRegistration = true;
    }

    /**
     * Testovací akce pro ověření funkčnosti emailů
     * URL: /sign/test-email
     */
    public function actionTestEmail(): void
    {
        // Pouze pro vývojové a testovací účely
        // V produkci můžete tuto akci odstranit nebo omezit přístup
    }

    public function renderTestEmail(): void
    {
        // Explicitně nastavíme šablonu (pokud chceš zachovat název s pomlčkou)
        $this->template->setFile(__DIR__ . '/templates/test-email.latte');
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

    /**
     * OPRAVENÁ METODA - checkbox bez duplicitního textu
     */
    protected function createComponentSignInForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Zadejte uživatelské jméno');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Zadejte heslo');

        // Checkbox BEZ labelu - label se přidá v šabloně
        $form->addCheckbox('remember');

        $form->addSubmit('send', 'Přihlásit se');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];

        return $form;
    }

    public function signInFormSucceeded(Form $form, \stdClass $data): void
    {
        try {
            // Nastavení délky přihlášení podle zaškrtnutí "Zůstat přihlášen"
            if ($data->remember) {
                $this->getUser()->setExpiration('14 days');
            } else {
                $this->getUser()->setExpiration('20 minutes', true);
            }

            $this->getUser()->login($data->username, $data->password);
            
            // Logování úspěšného přihlášení
            $identity = $this->getUser()->getIdentity();
            $this->securityLogger->logLogin($identity->id, $identity->username);
            
            $this->flashMessage('Úspěšně jste se přihlásili.', 'success');
            $this->redirect('Home:default');
        } catch (Nette\Security\AuthenticationException $e) {
            // Logování neúspěšného pokusu o přihlášení
            $this->securityLogger->logFailedLogin($data->username, $e->getMessage());
            
            $form->addError('Nesprávné uživatelské jméno nebo heslo.');
        }
    }

    protected function createComponentSignUpForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        // VŽDY přidáme pole pro nový firemní účet
        $form->addText('company_account_name', 'Název firemního účtu:')
            ->setRequired('Zadejte název vašeho firemního účtu')
            ->setHtmlAttribute('placeholder', 'např. Firma ABC s.r.o.');
            
        $form->addText('company_name', 'Název společnosti:')
            ->setRequired('Zadejte název společnosti')
            ->setHtmlAttribute('placeholder', 'např. Firma ABC s.r.o.');

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

        $form->addSubmit('send', 'Vytvořit firemní účet');

        $form->onSuccess[] = [$this, 'signUpFormSucceeded'];

        return $form;
    }

    /**
     * Testovací formulář pro odesílání emailů
     */
    protected function createComponentTestEmailForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addEmail('email', 'E-mail pro test:')
            ->setRequired('Zadejte e-mailovou adresu')
            ->setDefaultValue('info@allimedia.cz');

        $form->addSubmit('send', 'Odeslat testovací email');

        $form->onSuccess[] = [$this, 'testEmailFormSucceeded'];

        return $form;
    }

    public function testEmailFormSucceeded(Form $form, \stdClass $data): void
    {
        try {
            $this->emailService->sendTestEmail($data->email);
            $this->flashMessage('Testovací email byl úspěšně odeslán na ' . $data->email, 'success');
        } catch (\Exception $e) {
            $this->flashMessage('Chyba při odesílání emailu: ' . $e->getMessage(), 'danger');
        }

        $this->redirect('this');
    }

    public function signUpFormSucceeded(Form $form, \stdClass $data): void
    {
        try {
            // VŽDY vytváříme nový firemní účet (tenant) s admin uživatelem
            $this->processNewCompanyRegistration($form, $data);

        } catch (Nette\Application\AbortException $e) {
            // AbortException je normální při redirect - necháme ji projít
            throw $e;
        } catch (\Exception $e) {
            // Logování pouze závažných chyb
            error_log('Chyba při registraci: ' . $e->getMessage());
            $form->addError('Při registraci došlo k neočekávané chybě: ' . $e->getMessage());
        }
    }

    /**
     * Zpracuje registraci nového firemního účtu - vytvoří tenant a admin uživatele
     */
    private function processNewCompanyRegistration(Form $form, \stdClass $data): void
    {
        // Kontrola jedinečnosti uživatelského jména a e-mailu napříč všemi tenanty
        $existingUsername = $this->database->table('users')
            ->where('username', $data->username)
            ->fetch();

        $existingEmail = $this->database->table('users')
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

        // Příprava dat pro vytvoření tenanta
        $tenantData = [
            'name' => $data->company_account_name,
            'domain' => null, // Zatím nevyžadujeme doménu
            'settings' => []
        ];

        $adminData = [
            'username' => $data->username,
            'email' => $data->email,
            'password' => $data->password,
            'first_name' => null,
            'last_name' => null
        ];

        $companyData = [
            'company_name' => $data->company_name,
            'ic' => '',
            'dic' => '',
            'phone' => '',
            'address' => '',
            'city' => '',
            'zip' => '',
            'country' => 'Česká republika',
            'vat_payer' => false
        ];

        // Vytvoření tenanta pomocí TenantManager
        $result = $this->tenantManager->createTenant($tenantData, $adminData, $companyData);

        if ($result['success']) {
            // Odeslání emailů
            try {
                $this->emailService->sendRegistrationConfirmation($data->email, $data->username, 'admin');
            } catch (\Exception $e) {
                error_log('Chyba při odesílání emailu: ' . $e->getMessage());
            }

            $this->flashMessage(
                'Firemní účet "' . $data->company_account_name . '" byl úspěšně vytvořen! Nyní se můžete přihlásit jako administrátor.',
                'success'
            );
            $this->redirect('Sign:in');
        } else {
            $form->addError($result['message'] ?? 'Nepodařilo se vytvořit firemní účet.');
        }
    }

    /**
     * Zpracuje běžnou registraci dalšího uživatele
     */
    private function processRegularUserRegistration(Form $form, \stdClass $data, int $userCount): void
    {
        // Kontrola jedinečnosti uživatelského jména a e-mailu
        $existingUsername = null;
        $existingEmail = null;

        try {
            $existingUsername = $this->userManager->getAll()
                ->where('username', $data->username)
                ->fetch();

            $existingEmail = $this->userManager->getAll()
                ->where('email', $data->email)
                ->fetch();
        } catch (\Exception $e) {
            error_log('Chyba při kontrole uživatele: ' . $e->getMessage());
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
        $role = isset($data->role) ? $data->role : 'readonly';

        // Admin ID a jméno pro logování (null pro samoregistraci)
        $adminId = null;
        $adminName = null;

        // Pokud je přihlášen admin, který vytváří uživatele
        if ($this->getUser()->isLoggedIn() && $this->isAdmin()) {
            $adminId = $this->getUser()->getId();
            $adminName = $this->getUser()->getIdentity()->username;
        }

        // Vytvoření uživatele
        $newUserId = $this->userManager->add($data->username, $data->email, $data->password, $role, null, $adminId, $adminName);

        // Kontrola, zda se uživatel skutečně vytvořil
        if ($newUserId && $newUserId > 0) {
            // Odesílání emailů
            try {
                // Potvrzení registrace uživateli
                $this->emailService->sendRegistrationConfirmation($data->email, $data->username, $role);
                
                // Upozornění adminovi (pouze pokud to není první uživatel)
                if ($userCount > 0) {
                    $this->emailService->sendAdminNotification($data->username, $data->email, $role);
                }
            } catch (\Exception $e) {
                // Email se nepodařilo odeslat, ale registrace proběhla úspěšně
                error_log('Chyba při odesílání emailu: ' . $e->getMessage());
                $this->flashMessage('Registrace byla úspěšná, ale nepodařilo se odeslat potvrzovací email.', 'warning');
                $this->redirect('Sign:in');
                return;
            }

            $this->flashMessage('Registrace byla úspěšná. Na váš email bylo odesláno potvrzení. Nyní se můžete přihlásit.', 'success');
            $this->redirect('Sign:in');
        } else {
            $form->addError('Nepodařilo se vytvořit uživatelský účet. Zkuste to prosím znovu.');
        }
    }

    /**
     * Zapomenuté heslo - zobrazí formulář pro zadání emailu
     */
    public function actionForgotPassword(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Home:default');
        }
    }

    /**
     * Reset hesla s tokenem
     */
    public function actionResetPassword(string $token): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Home:default');
        }

        // Ověření platnosti tokenu
        $resetToken = $this->database->table('password_reset_tokens')
            ->where('token', $token)
            ->where('expires_at > ?', new \DateTime())
            ->where('used_at IS NULL')
            ->fetch();

        if (!$resetToken) {
            $this->flashMessage('Odkaz pro obnovení hesla je neplatný nebo vypršel.', 'danger');
            $this->redirect('Sign:forgotPassword');
        }

        $this->template->token = $token;
    }

    /**
     * Formulář pro zapomenuté heslo
     */
    protected function createComponentForgotPasswordForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addEmail('email', 'E-mailová adresa:')
            ->setRequired('Zadejte e-mailovou adresu');

        $form->addSubmit('send', 'Odeslat odkaz pro obnovení');

        $form->onSuccess[] = [$this, 'forgotPasswordFormSucceeded'];

        return $form;
    }

    public function forgotPasswordFormSucceeded(Form $form, \stdClass $data): void
    {
        try {
            // Najdeme uživatele podle emailu
            $user = $this->database->table('users')
                ->where('email', $data->email)
                ->fetch();

            if (!$user) {
                // I když uživatel neexistuje, zobrazíme stejnou zprávu (bezpečnost)
                $this->flashMessage('Pokud účet s touto e-mailovou adresou existuje, byl odeslán odkaz pro obnovení hesla.', 'info');
                $this->redirect('Sign:in');
                return;
            }

            // Vygenerujeme reset token
            $token = bin2hex(random_bytes(32));
            $expiresAt = new \DateTime('+24 hours');

            // Smazání starých tokenů pro tohoto uživatele
            $this->database->table('password_reset_tokens')
                ->where('user_id', $user->id)
                ->delete();

            // Vytvoření nového tokenu
            $this->database->table('password_reset_tokens')->insert([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => $expiresAt,
                'created_at' => new \DateTime(),
            ]);

            // Odeslání emailu
            $this->emailService->sendPasswordReset($user->email, $user->username, $token);

            // Úspěšné dokončení
            $this->flashMessage('Odkaz pro obnovení hesla byl odeslán na vaši e-mailovou adresu.', 'success');
            $this->redirect('Sign:in');

        } catch (Nette\Application\AbortException $e) {
            // AbortException je normální při redirect - necháme ji projít
            throw $e;
        } catch (\Exception $e) {
            error_log('Chyba při resetování hesla: ' . $e->getMessage());
            $form->addError('Při odesílání odkazu došlo k chybě. Zkuste to prosím znovu.');
        }
    }

    /**
     * Formulář pro nastavení nového hesla
     */
    protected function createComponentResetPasswordForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addHidden('token');

        $passwordField = $form->addPassword('password', 'Nové heslo:')
            ->setRequired('Zadejte nové heslo')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 8)
            ->addRule(Form::PATTERN, 'Heslo musí obsahovat alespoň jednu číslici', '.*[0-9].*')
            ->addRule(Form::PATTERN, 'Heslo musí obsahovat alespoň jedno velké písmeno', '.*[A-Z].*');

        $form->addPassword('passwordVerify', 'Heslo znovu:')
            ->setRequired('Zadejte heslo znovu pro kontrolu')
            ->addConditionOn($passwordField, $form::VALID)
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $passwordField);

        $form->addSubmit('send', 'Nastavit nové heslo');

        $form->onSuccess[] = [$this, 'resetPasswordFormSucceeded'];

        return $form;
    }

    public function resetPasswordFormSucceeded(Form $form, \stdClass $data): void
    {
        try {
            $token = $this->getParameter('token');

            // Ověření platnosti tokenu
            $resetToken = $this->database->table('password_reset_tokens')
                ->where('token', $token)
                ->where('expires_at > ?', new \DateTime())
                ->where('used_at IS NULL')
                ->fetch();

            if (!$resetToken) {
                $form->addError('Odkaz pro obnovení hesla je neplatný nebo vypršel.');
                return;
            }

            // Aktualizace hesla
            $this->userManager->update($resetToken->user_id, [
                'password' => $data->password
            ]);

            // Označení tokenu jako použitého
            $this->database->table('password_reset_tokens')
                ->where('id', $resetToken->id)
                ->update(['used_at' => new \DateTime()]);

            $this->flashMessage('Heslo bylo úspěšně změněno. Nyní se můžete přihlásit.', 'success');
            $this->redirect('Sign:in');

        } catch (Nette\Application\AbortException $e) {
            // AbortException je normální při redirect - necháme ji projít
            throw $e;
        } catch (\Exception $e) {
            error_log('Chyba při změně hesla: ' . $e->getMessage());
            $form->addError('Při změně hesla došlo k chybě. Zkuste to prosím znovu.');
        }
    }

    /**
     * Získá celkový počet uživatelů v systému (bez tenant filtru)
     * Používáme přímý databázový dotaz, abychom obešli tenant filtrování
     */
    private function getTotalUserCount(): int
    {
        try {
            return $this->database->table('users')->count();
        } catch (\Exception $e) {
            // V případě chyby předpokládáme, že uživatelé neexistují
            return 0;
        }
    }
}