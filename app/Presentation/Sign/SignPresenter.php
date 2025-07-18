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
use App\Security\RateLimiter;

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

    /** @var RateLimiter */
    private $rateLimiter;

    protected bool $requiresLogin = false;
    
    /** @var bool Vypnutí globálního rate limitingu - SignPresenter má vlastní */
    protected bool $disableRateLimit = true;

    public function __construct(
        UserManager $userManager,
        SecurityLogger $securityLogger,
        EmailService $emailService,
        TenantManager $tenantManager,
        RateLimiter $rateLimiter,
        Nette\Database\Explorer $database
    ) {
        $this->userManager = $userManager;
        $this->securityLogger = $securityLogger;
        $this->emailService = $emailService;
        $this->tenantManager = $tenantManager;
        $this->rateLimiter = $rateLimiter;
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

        // ✅ NOVÉ: Kontrola rate limiting pro přihlašovací stránku
        $clientIP = $this->rateLimiter->getClientIP();
        
        // Kontrola rate limiting pro login akci
        $loginStatus = $this->rateLimiter->getLimitStatus('login', $clientIP);
        if ($loginStatus['is_blocked']) {
            $blockedUntil = $loginStatus['blocked_until'];
            $timeRemaining = $blockedUntil ? $blockedUntil->diff(new \DateTime())->format('%i minut %s sekund') : 'neznámý čas';
            
            $this->template->rateLimitBlocked = true;
            $this->template->blockedUntil = $blockedUntil;
            $this->template->timeRemaining = $timeRemaining;
            $this->flashMessage("Příliš mnoho neúspěšných pokusů o přihlášení. Zkuste to znovu za {$timeRemaining}.", 'danger');
        } else {
            $this->template->rateLimitBlocked = false;
            $this->template->attemptsRemaining = $loginStatus['attempts_remaining'];
        }

        // NOVÉ: Kontrola zprávy o deaktivaci z session
        $section = $this->getSession('deactivation');
        if (isset($section->message) && isset($section->tenant_id)) {
            // OPRAVA: Zpráva se zobrazuje trvale (bez časového limitu)
            $this->template->deactivationMessage = $section->message;
            $this->template->deactivationType = $section->type ?? 'danger';
            $this->template->deactivationTenantId = $section->tenant_id;
        }
    }

    /**
     * NOVÉ: Jednoduchá akce pro vymazání zprávy o deaktivaci (místo signálu)
     */
    public function actionClearDeactivation(): void
    {
        $section = $this->getSession('deactivation');
        unset($section->message);
        unset($section->type);
        unset($section->tenant_id);
        
        $this->redirect('Sign:in');
    }

    public function actionUp(): void
    {
        // Pokud je už přihlášen, přesměruj na hlavní stránku
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Home:default');
        }

        // ✅ NOVÉ: Rate limiting pro registraci
        $clientIP = $this->rateLimiter->getClientIP();
        $userCreationStatus = $this->rateLimiter->getLimitStatus('user_creation', $clientIP);
        
        if ($userCreationStatus['is_blocked']) {
            $blockedUntil = $userCreationStatus['blocked_until'];
            $timeRemaining = $blockedUntil ? $blockedUntil->diff(new \DateTime())->format('%i minut %s sekund') : 'neznámý čas';
            
            $this->flashMessage("Příliš mnoho pokusů o registraci. Zkuste to znovu za {$timeRemaining}.", 'danger');
            $this->redirect('Sign:in');
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

    /**
     * ✅ VYLEPŠENÁ METODA s kompletním Rate Limiting
     */
    public function signInFormSucceeded(Form $form, \stdClass $data): void
    {
        $clientIP = $this->rateLimiter->getClientIP();

        // ✅ KROK 1: Kontrola rate limiting PŘED pokusem o přihlášení
        if (!$this->rateLimiter->isAllowed('login', $clientIP)) {
            $loginStatus = $this->rateLimiter->getLimitStatus('login', $clientIP);
            $blockedUntil = $loginStatus['blocked_until'];
            $timeRemaining = $blockedUntil ? $blockedUntil->diff(new \DateTime())->format('%i minut %s sekund') : 'neznámý čas';
            
            $form->addError("Příliš mnoho neúspěšných pokusů o přihlášení. Zkuste to znovu za {$timeRemaining}.");
            return;
        }

        try {
            // Nastavení délky přihlášení podle zaškrtnutí "Zůstat přihlášen"
            if ($data->remember) {
                $this->getUser()->setExpiration('14 days');
            } else {
                $this->getUser()->setExpiration('20 minutes', true);
            }

            // ✅ KROK 2: Pokus o přihlášení
            $this->getUser()->login($data->username, $data->password);
            
            // ✅ KROK 3: ÚSPĚŠNÉ přihlášení - zaznamenat úspěšný pokus
            $this->rateLimiter->recordAttempt('login', $clientIP, true);
            
            // NOVÉ: Vymažeme zprávu o deaktivaci při úspěšném přihlášení
            $section = $this->getSession('deactivation');
            unset($section->message);
            unset($section->type);
            unset($section->tenant_id);
            
            // Logování úspěšného přihlášení
            $identity = $this->getUser()->getIdentity();
            $this->securityLogger->logLogin($identity->id, $identity->username);
            
            $this->flashMessage('Úspěšně jste se přihlásili.', 'success');
            $this->redirect('Home:default');
            
        } catch (Nette\Security\AuthenticationException $e) {
            // ✅ KROK 4: NEÚSPĚŠNÉ přihlášení - zaznamenat failed pokus
            $this->rateLimiter->recordAttempt('login', $clientIP, false);
            
            // Logování neúspěšného pokusu o přihlášení
            $this->securityLogger->logFailedLogin($data->username, $e->getMessage());
            
            // Zjistíme stav rate limitingu po tomto pokusu
            $loginStatus = $this->rateLimiter->getLimitStatus('login', $clientIP);
            
            if ($loginStatus['is_blocked']) {
                // Právě jsme překročili limit
                $form->addError('Příliš mnoho neúspěšných pokusů. Váš přístup byl dočasně zablokován.');
            } else {
                // Ještě nejsme blokovaní, ale ukážeme kolik pokusů zbývá
                $attemptsLeft = $loginStatus['attempts_remaining'];
                $form->addError($e->getMessage() . " (Zbývá pokusů: {$attemptsLeft})");
            }
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
            ->setHtmlAttribute('placeholder', 'např. admin');

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte váš e-mail')
            ->setHtmlAttribute('placeholder', 'vas@email.cz');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Zadejte heslo')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 6);

        $form->addPassword('password_confirm', 'Heslo znovu:')
            ->setRequired('Zadejte heslo znovu')
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password']);

        $form->addSubmit('send', 'Registrovat se');

        $form->onSuccess[] = [$this, 'signUpFormSucceeded'];

        return $form;
    }

    /**
     * ✅ VYLEPŠENÁ METODA s Rate Limiting pro registraci
     */
    public function signUpFormSucceeded(Form $form, \stdClass $data): void
    {
        $clientIP = $this->rateLimiter->getClientIP();

        // ✅ Rate limiting pro user creation
        if (!$this->rateLimiter->isAllowed('user_creation', $clientIP)) {
            $userCreationStatus = $this->rateLimiter->getLimitStatus('user_creation', $clientIP);
            $blockedUntil = $userCreationStatus['blocked_until'];
            $timeRemaining = $blockedUntil ? $blockedUntil->diff(new \DateTime())->format('%i minut %s sekund') : 'neznámý čas';
            
            $form->addError("Příliš mnoho pokusů o registraci. Zkuste to znovu za {$timeRemaining}.");
            return;
        }

        try {
            // Získání celkového počtu uživatelů
            $userCount = $this->getTotalUserCount();

            // Vždy vytváříme nový firemní účet
            $this->processCompanyAccountCreation($form, $data);
            
            // ✅ Úspěšná registrace
            $this->rateLimiter->recordAttempt('user_creation', $clientIP, true);
            
        } catch (\Exception $e) {
            // ✅ Neúspěšná registrace
            $this->rateLimiter->recordAttempt('user_creation', $clientIP, false);
            
            error_log('Chyba při registraci: ' . $e->getMessage());
            $form->addError('Při registraci došlo k chybě. Zkuste to prosím znovu.');
        }
    }

    /**
     * Akce pro reset hesla - formulář pro zadání emailu
     */
    public function actionForgotPassword(): void
    {
        // Nic speciálního, jen zobrazí formulář
    }

    /**
     * Akce pro reset hesla - formulář pro nové heslo s tokenem
     */
    public function actionResetPassword(string $token): void
    {
        $this->template->token = $token;
    }

    /**
     * Formulář pro žádost o reset hesla
     */
    protected function createComponentForgotPasswordForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addEmail('email', 'E-mailová adresa:')
            ->setRequired('Zadejte váš e-mail')
            ->setHtmlAttribute('placeholder', 'vas@email.cz');

        $form->addSubmit('send', 'Odeslat odkaz pro obnovení');

        $form->onSuccess[] = [$this, 'forgotPasswordFormSucceeded'];

        return $form;
    }

    /**
     * ✅ VYLEPŠENÁ METODA s Rate Limiting pro reset hesla
     */
    public function forgotPasswordFormSucceeded(Form $form, \stdClass $data): void
    {
        $clientIP = $this->rateLimiter->getClientIP();

        // ✅ Rate limiting pro password reset
        if (!$this->rateLimiter->isAllowed('password_reset', $clientIP)) {
            $passwordResetStatus = $this->rateLimiter->getLimitStatus('password_reset', $clientIP);
            $blockedUntil = $passwordResetStatus['blocked_until'];
            $timeRemaining = $blockedUntil ? $blockedUntil->diff(new \DateTime())->format('%i minut %s sekund') : 'neznámý čas';
            
            $form->addError("Příliš mnoho pokusů o reset hesla. Zkuste to znovu za {$timeRemaining}.");
            return;
        }

        try {
            // Najdi uživatele podle emailu
            $user = $this->database->table('users')
                ->where('email', $data->email)
                ->fetch();

            if (!$user) {
                // ✅ Neúspěšný pokus - email neexistuje
                $this->rateLimiter->recordAttempt('password_reset', $clientIP, false);
                $form->addError('E-mailová adresa není v systému registrována.');
                return;
            }

            // Vytvoř reset token
            $token = bin2hex(random_bytes(32));
            $expiresAt = new \DateTime('+1 hour');

            $this->database->table('password_reset_tokens')->insert([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => $expiresAt,
                'created_at' => new \DateTime(),
            ]);

            // Pošli email s odkazem
            $resetLink = $this->link('//Sign:resetPassword', ['token' => $token]);
            $this->emailService->sendPasswordReset($data->email, $user->username, $token);

            // ✅ Úspěšný pokus
            $this->rateLimiter->recordAttempt('password_reset', $clientIP, true);

            $this->flashMessage('Odkaz pro obnovení hesla byl odeslán na váš e-mail.', 'success');
            $this->redirect('Sign:in');

        } catch (\Exception $e) {
            // ✅ Neúspěšný pokus - chyba při odesílání
            $this->rateLimiter->recordAttempt('password_reset', $clientIP, false);
            
            error_log('Chyba při resetování hesla: ' . $e->getMessage());
            $form->addError('Při odesílání emailu došlo k chybě. Zkuste to prosím znovu.');
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

        $form->addPassword('password', 'Nové heslo:')
            ->setRequired('Zadejte nové heslo')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 6);

        $form->addPassword('password_confirm', 'Heslo znovu:')
            ->setRequired('Zadejte heslo znovu')
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password']);

        $form->addSubmit('send', 'Změnit heslo');

        $form->onSuccess[] = [$this, 'resetPasswordFormSucceeded'];

        return $form;
    }

    public function resetPasswordFormSucceeded(Form $form, \stdClass $data): void
    {
        try {
            // Ověř token
            $resetToken = $this->database->table('password_reset_tokens')
                ->where('token', $data->token)
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
     * Formulář pro test emailů
     */
    protected function createComponentTestEmailForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte e-mail')
            ->setDefaultValue('test@example.com');

        $form->addSubmit('send', 'Odeslat testovací e-mail');

        $form->onSuccess[] = [$this, 'testEmailFormSucceeded'];

        return $form;
    }

    public function testEmailFormSucceeded(Form $form, \stdClass $data): void
    {
        try {
            $this->emailService->sendTestEmail($data->email);
            $this->flashMessage('Testovací e-mail byl úspěšně odeslán.', 'success');
        } catch (\Exception $e) {
            $form->addError('Chyba při odesílání e-mailu: ' . $e->getMessage());
        }
    }

    /**
     * Zpracuje vytvoření nového firemního účtu
     */
    private function processCompanyAccountCreation(Form $form, \stdClass $data): void
    {
        // Kontrola jedinečnosti uživatelského jména a e-mailu
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
            throw new \Exception($result['message'] ?? 'Nepodařilo se vytvořit firemní účet.');
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