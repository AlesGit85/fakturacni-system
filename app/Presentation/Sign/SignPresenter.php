<?php

declare(strict_types=1);

namespace App\Presentation\Sign;

use Nette;
use Nette\Application\UI\Form;
use App\Model\UserManager;
use App\Model\EmailService;
use App\Model\TenantManager;
use App\Model\ModuleManager;
use App\Presentation\BasePresenter;
use App\Security\SecurityLogger;
use App\Security\RateLimiter;
use App\Security\AntiSpam;

final class SignPresenter extends BasePresenter
{
    /** @var UserManager */
    private $userManager;

    /** @var SecurityLogger */
    protected $securityLogger;

    /** @var EmailService */
    private $emailService;

    /** @var TenantManager */
    private $tenantManager;

    /** @var RateLimiter */
    protected $rateLimiter;

    protected bool $requiresLogin = false;
    
    /** @var bool Vypnutí globálního rate limitingu - SignPresenter má vlastní */
    protected bool $disableRateLimit = true;

    public function __construct(
        UserManager $userManager,
        SecurityLogger $securityLogger,
        EmailService $emailService,
        TenantManager $tenantManager,
        RateLimiter $rateLimiter,
        Nette\Database\Explorer $database,
        ModuleManager $moduleManager,
        AntiSpam $antiSpam
    ) {
        // ✅ KRITICKÉ: Volání parent konstruktoru s BasePresenter parametry
        parent::__construct($securityLogger, $rateLimiter, $moduleManager, $database, $antiSpam);
        
        // SignPresenter specifické vlastnosti
        $this->userManager = $userManager;
        $this->emailService = $emailService;
        $this->tenantManager = $tenantManager;
    }

    /**
     * Default akce - přesměruje na přihlášení
     */
    public function actionDefault(): void
    {
        $this->redirect('Sign:in');
    }

    /**
     * ✅ AKTUALIZACE: actionIn() - s tenant podporou
     */
    public function actionIn(): void
    {
        // Pokud je už přihlášen, přesměruj na hlavní stránku
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Home:default');
        }

        $clientIP = $this->rateLimiter->getClientIP();
        
        // ✅ ZMĚNA: Pro login checking používáme null tenant_id (uživatel se ještě nepřihlásil)
        $loginStatus = $this->rateLimiter->getLimitStatus('login', $clientIP, null);
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

    /**
     * ✅ AKTUALIZACE: actionUp() - s tenant podporou
     */
    public function actionUp(): void
    {
        // Pokud je už přihlášen, přesměruj na hlavní stránku
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Home:default');
        }

        $clientIP = $this->rateLimiter->getClientIP();
        
        // ✅ ZMĚNA: Pro registraci používáme null tenant_id (tenant se teprve vytváří)
        $userCreationStatus = $this->rateLimiter->getLimitStatus('user_creation', $clientIP, null);
        
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
     * OPRAVENÁ METODA - přihlašovací formulář s anti-spam ochranou
     */
    protected function createComponentSignInForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');
        
        // ✅ Anti-spam ochrana
        $this->addAntiSpamProtectionToForm($form);

        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Zadejte uživatelské jméno');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Zadejte heslo');

        $form->addCheckbox('remember');

        $form->addSubmit('send', 'Přihlásit se');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];

        return $form;
    }

    /**
     * ✅ OPRAVENO: signInFormSucceeded() - s kontrolou validity formuláře před získáním hodnot
     */
    public function signInFormSucceeded(Form $form, \stdClass $data): void
    {
        // ✅ KRITICKÁ OPRAVA: Nejdříve zkontrolujeme, zda je formulář validní
        if (!$form->isValid()) {
            $this->flashMessage('Formulář obsahuje neplatné údaje. Zkuste to prosím znovu.', 'warning');
            return;
        }

        $clientIP = $this->rateLimiter->getClientIP();
        $tenantId = $this->getTenantIdFromCredentials($data->username);

        if (!$this->rateLimiter->isAllowed('login', $clientIP, $tenantId)) {
            $loginStatus = $this->rateLimiter->getLimitStatus('login', $clientIP, $tenantId);
            $blockedUntil = $loginStatus['blocked_until'];
            $timeRemaining = $blockedUntil ? $blockedUntil->diff(new \DateTime())->format('%i minut %s sekund') : 'neznámý čas';
            
            $form->addError("Příliš mnoho neúspěšných pokusů o přihlášení. Zkuste to znovu za {$timeRemaining}.");
            return;
        }

        try {
            if ($data->remember) {
                $this->getUser()->setExpiration('14 days');
            } else {
                $this->getUser()->setExpiration('20 minutes', true);
            }

            $this->getUser()->login($data->username, $data->password);
            
            $identity = $this->getUser()->getIdentity();
            $actualTenantId = $identity->tenant_id ?? null;
            $userId = $identity->id ?? null;
            
            $this->rateLimiter->recordAttempt('login', $clientIP, true, $actualTenantId, $userId);
            
            $section = $this->getSession('deactivation');
            unset($section->message);
            unset($section->type);
            unset($section->tenant_id);
            
            $this->securityLogger->logLogin($identity->id, $identity->username);
            
            $this->flashMessage('Úspěšně jste se přihlásili.', 'success');
            $this->redirect('Home:default');
            
        } catch (Nette\Security\AuthenticationException $e) {
            $this->rateLimiter->recordAttempt('login', $clientIP, false, $tenantId, null);
            $this->securityLogger->logFailedLogin($data->username, $e->getMessage());
            
            $loginStatus = $this->rateLimiter->getLimitStatus('login', $clientIP, $tenantId);
            
            if ($loginStatus['is_blocked']) {
                $form->addError('Příliš mnoho neúspěšných pokusů. Váš přístup byl dočasně zablokován.');
            } else {
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
     * ✅ AKTUALIZACE: signUpFormSucceeded() - s tenant podporou a návratovými hodnotami
     */
    public function signUpFormSucceeded(Form $form, \stdClass $data): void
    {
        $clientIP = $this->rateLimiter->getClientIP();

        // ✅ ZMĚNA: Pro registraci používáme null tenant_id (tenant se teprve vytváří)
        if (!$this->rateLimiter->isAllowed('user_creation', $clientIP, null)) {
            $userCreationStatus = $this->rateLimiter->getLimitStatus('user_creation', $clientIP, null);
            $blockedUntil = $userCreationStatus['blocked_until'];
            $timeRemaining = $blockedUntil ? $blockedUntil->diff(new \DateTime())->format('%i minut %s sekund') : 'neznámý čas';
            
            $form->addError("Příliš mnoho pokusů o registraci. Zkuste to znovu za {$timeRemaining}.");
            return;
        }

        try {
            // Získání celkového počtu uživatelů
            $userCount = $this->getTotalUserCount();

            // ✅ ZMĚNA: Vždy vytváříme nový firemní účet a získáme výsledek
            $result = $this->processCompanyAccountCreation($form, $data);
            
            // ✅ ZMĚNA: Úspěšná registrace s nově vytvořenými tenant_id a user_id
            $newTenantId = $result['tenant_id'] ?? null;
            $newUserId = $result['user_id'] ?? null;
            
            $this->rateLimiter->recordAttempt('user_creation', $clientIP, true, $newTenantId, $newUserId);
            
        } catch (\Exception $e) {
            // ✅ ZMĚNA: Neúspěšná registrace
            $this->rateLimiter->recordAttempt('user_creation', $clientIP, false, null, null);
            
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
     * ✅ AKTUALIZACE: forgotPasswordFormSucceeded() - s tenant podporou
     */
    public function forgotPasswordFormSucceeded(Form $form, \stdClass $data): void
    {
        $clientIP = $this->rateLimiter->getClientIP();

        // ✅ NOVÉ: Pokus o získání tenant_id z emailu
        $tenantId = $this->getTenantIdFromEmail($data->email);

        // ✅ ZMĚNA: Rate limiting s tenant podporou
        if (!$this->rateLimiter->isAllowed('password_reset', $clientIP, $tenantId)) {
            $passwordResetStatus = $this->rateLimiter->getLimitStatus('password_reset', $clientIP, $tenantId);
            $blockedUntil = $passwordResetStatus['blocked_until'];
            $timeRemaining = $blockedUntil ? $blockedUntil->diff(new \DateTime())->format('%i minut %s sekund') : 'neznámý čas';
            
            $form->addError("Příliš mnoho pokusů o obnovení hesla. Zkuste to znovu za {$timeRemaining}.");
            return;
        }

        try {
            // Najdi uživatele podle e-mailu
            $user = $this->database->table('users')
                ->where('email', $data->email)
                ->fetch();

            if (!$user) {
                // Z bezpečnostních důvodů neříkáme, že email neexistuje
                $this->flashMessage('Pokud je váš e-mail registrovaný, obdržíte odkaz pro obnovení hesla.', 'info');
                $this->redirect('Sign:in');
            }

            // Vytvoř nový reset token
            $token = bin2hex(random_bytes(32));
            $this->database->table('password_reset_tokens')->insert([
                'user_id' => $user->id,
                'token' => $token,
                'created_at' => new \DateTime(),
                'expires_at' => new \DateTime('+1 hour'),
            ]);

            // Odešli email s odkazem pro reset
            $this->emailService->sendPasswordReset($data->email, $user->username, $token);

            // ✅ ZMĚNA: Úspěšné odeslání s tenant a user parametry
            $this->rateLimiter->recordAttempt('password_reset', $clientIP, true, $tenantId, $user->id);

            $this->flashMessage('Odkaz pro obnovení hesla byl odeslán na váš e-mail.', 'success');
            $this->redirect('Sign:in');

        } catch (\Exception $e) {
            // ✅ ZMĚNA: Neúspěšné odeslání
            $this->rateLimiter->recordAttempt('password_reset', $clientIP, false, $tenantId, null);

            error_log('Chyba při odesílání emailu pro reset hesla: ' . $e->getMessage());
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
     * ✅ AKTUALIZACE: processCompanyAccountCreation() - s návratovými hodnotami
     */
    private function processCompanyAccountCreation(Form $form, \stdClass $data): array
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
            throw new \Exception('Uživatelské jméno už je obsazené.');
        }

        if ($existingEmail) {
            /** @var Nette\Forms\Controls\TextInput $emailField */
            $emailField = $form['email'];
            $emailField->addError('E-mailová adresa už je registrovaná.');
            throw new \Exception('E-mailová adresa už je registrovaná.');
        }

        // Vytvoření nového tenanta a uživatele
        $result = $this->tenantManager->createTenantWithAdmin(
            $data->company_account_name,
            $data->company_name,
            $data->username,
            $data->email,
            $data->password
        );

        $this->flashMessage('Firemní účet byl úspěšně vytvořen. Nyní se můžete přihlásit.', 'success');
        $this->redirect('Sign:in');
        
        return $result; // Vracíme výsledek s tenant_id a user_id
    }

    /**
     * ✅ AKTUALIZACE: Získání tenant_id z uživatelských credentials
     */
    private function getTenantIdFromCredentials(string $username): ?int
    {
        try {
            $user = $this->database->table('users')
                ->where('username', $username)
                ->fetch();
            
            return $user ? $user->tenant_id : null;
        } catch (\Exception $e) {
            error_log('Chyba při získávání tenant_id z credentials: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ NOVÉ: Získání tenant_id z emailu (pro password reset)
     */
    private function getTenantIdFromEmail(string $email): ?int
    {
        try {
            $user = $this->database->table('users')
                ->where('email', $email)
                ->fetch();
            
            return $user ? $user->tenant_id : null;
        } catch (\Exception $e) {
            error_log('Chyba při získávání tenant_id z emailu: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ NOVÉ: Získání celkového počtu uživatelů (pro registraci)
     */
    private function getTotalUserCount(): int
    {
        try {
            return $this->database->table('users')->count();
        } catch (\Exception $e) {
            error_log('Chyba při získávání počtu uživatelů: ' . $e->getMessage());
            return 0;
        }
    }
}