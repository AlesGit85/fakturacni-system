<?php

declare(strict_types=1);

namespace App\Presentation\Users;

use Nette;
use Nette\Application\UI\Form;
use App\Model\UserManager;
use App\Model\CompanyManager;
use App\Presentation\BasePresenter;
use App\Security\SecurityValidator; // ✅ NOVÉ: Import našeho validátoru

final class UsersPresenter extends BasePresenter
{
    /** @var UserManager */
    private $userManager;

    /** @var CompanyManager */
    private $companyManager;

    // Celý presenter je primárně pro adminy, kromě profilu
    protected array $requiredRoles = [];

    // Konkrétní role pro jednotlivé akce
    protected array $actionRoles = [
        'profile' => ['readonly', 'accountant', 'admin'], // Svůj profil může upravovat každý
        'default' => ['admin'], // Seznam uživatelů může vidět jen admin
        'add' => ['admin'], // Přidat uživatele může jen admin
        'edit' => ['admin'], // Upravit uživatele může jen admin
        'delete' => ['admin'], // Smazat uživatele může jen admin
        'moveUser' => ['admin'], // Přesunout uživatele může jen admin (ale reálně jen super admin)
    ];

    public function __construct(UserManager $userManager, CompanyManager $companyManager)
    {
        $this->userManager = $userManager;
        $this->companyManager = $companyManager;
    }

    /**
     * MULTI-TENANCY: Nastavení tenant kontextu po spuštění presenteru
     */
    public function startup(): void
    {
        parent::startup();

        // Nastavíme tenant kontext v UserManager
        $this->userManager->setTenantContext(
            $this->getCurrentTenantId(),
            $this->isSuperAdmin()
        );

        // NOVÉ: Nastavíme tenant kontext i v CompanyManager
        $this->companyManager->setTenantContext(
            $this->getCurrentTenantId(),
            $this->isSuperAdmin()
        );
    }

    public function renderDefault(): void
    {
        // Předání informace o tom, zda je uživatel super admin
        $this->template->isSuperAdmin = $this->isSuperAdmin();
        $this->template->currentUser = $this->getUser()->getIdentity();

        if ($this->isSuperAdmin()) {
            // SUPER ADMIN - pokročilé zobrazení
            $this->prepareSuperAdminView();
        } else {
            // NORMÁLNÍ ADMIN - klasické zobrazení
            $this->prepareNormalAdminView();
        }
    }

    /**
     * Příprava zobrazení pro super admina s vyhledáváním a grupováním podle tenantů
     */
    private function prepareSuperAdminView(): void
    {
        // Načtení parametrů vyhledávání
        $searchQuery = $this->getParameter('search');
        $this->template->searchQuery = $searchQuery;

        if ($searchQuery) {
            // VYHLEDÁVÁNÍ - zobrazíme výsledky vyhledávání
            $searchResults = $this->performUserSearch($searchQuery);
            $this->template->searchResults = $searchResults;
            $this->template->groupedUsers = [];

            // Pro search výsledky spočítáme count
            $this->template->totalUsers = count($searchResults);
        } else {
            // NORMÁLNÍ ZOBRAZENÍ - seskupení podle tenantů
            $groupedUsers = $this->userManager->getAllUsersGroupedByTenants();
            $this->template->groupedUsers = $groupedUsers;
            $this->template->searchResults = [];

            // Spočítáme celkový počet uživatelů ze všech tenantů
            $totalUsers = 0;
            foreach ($groupedUsers as $tenantGroup) {
                $totalUsers += $tenantGroup['user_count'];
            }
            $this->template->totalUsers = $totalUsers;
        }

        // Statistiky pro super admina
        $this->template->superAdminStats = $this->getSuperAdminStatistics();
    }

    /**
     * Příprava zobrazení pro normálního admina
     */
    private function prepareNormalAdminView(): void
    {
        // ✅ POUŽÍVÁME UserManager s automatickým dešifrováním
        $users = $this->userManager->getAll();
        $this->template->users = $users;
        $this->template->totalUsers = count($users);
        $this->template->groupedUsers = [];
        $this->template->searchResults = [];

        // NOVÉ: Načtení firemních údajů aktuálního tenanta s automatickým dešifrováním
        $this->template->currentTenantCompany = $this->companyManager->getCompanyInfo();
        $this->template->currentTenant = $this->getCurrentTenant();
    }

    /**
     * Vyhledá uživatele podle různých kritérií
     * ✅ PŮVODNÍ KÓD - již byl bezpečný s parametrizovanými dotazy
     */
    private function performUserSearch(string $query): array
    {
        // ✅ POUŽÍVÁME UserManager místo přímých SQL dotazů
        if ($this->isSuperAdmin()) {
            return $this->userManager->searchUsersForSuperAdmin($query);
        } else {
            // Pro normální admina vyhledáváme jen v jeho tenantu
            $allUsers = $this->userManager->getAll();
            $searchQuery = mb_strtolower(trim($query), 'UTF-8');

            $results = [];
            foreach ($allUsers as $user) {
                $userArray = (array) $user;

                // Vyhledáváme v relevantních polích
                if (
                    stripos($user->username, $searchQuery) !== false ||
                    stripos($user->email, $searchQuery) !== false ||
                    stripos($user->first_name, $searchQuery) !== false ||
                    stripos($user->last_name, $searchQuery) !== false
                ) {
                    $userArray['tenant_name'] = null; // Normální admin nevidí tenant name
                    $userArray['company_name'] = null;
                    $results[] = (object) $userArray;
                }
            }

            return $results;
        }
    }

/**
     * Získá všechny uživatele seskupené podle tenantů (pouze pro super admina)
     * NOVÁ METODA: Základní implementace bez automatického dešifrování firemních údajů
     */
    public function getAllUsersGroupedByTenants(): array
    {
        if (!$this->isSuperAdmin) {
            return [];
        }

        // Získáme všechny tenanty s informacemi o společnosti (POZOR: firemní údaje budou šifrované)
        $tenants = $this->database->query('
            SELECT 
                t.id as tenant_id,
                t.name as tenant_name,
                c.name as company_name,
                c.email as company_email,
                c.phone as company_phone
            FROM tenants t
            LEFT JOIN company_info c ON c.tenant_id = t.id
            ORDER BY t.name ASC
        ')->fetchAll();

        $result = [];

        foreach ($tenants as $tenant) {
            // Získáme uživatele pro tento tenant s automatickým dešifrováním
            $userSelection = $this->database->table('users')
                ->where('tenant_id', $tenant->tenant_id)
                ->order('role DESC, username ASC') // Admini první, pak alfabeticky
                ->fetchAll();

            // 🔓 AUTOMATICKÉ DEŠIFROVÁNÍ uživatelských dat
            $users = $this->decryptUserRecords($userSelection);

            // Najdeme majitele (prvního admina v tenantu)
            $owner = null;
            foreach ($users as $user) {
                if ($user->role === 'admin') {
                    $owner = $user;
                    break;
                }
            }

            $result[] = [
                'tenant_id' => $tenant->tenant_id,
                'tenant_name' => $tenant->tenant_name,
                'company_name' => $tenant->company_name ?? $tenant->tenant_name,
                'company_email' => $tenant->company_email, // POZOR: Bude šifrované - opraví se v UsersPresenter
                'company_phone' => $tenant->company_phone, // POZOR: Bude šifrované - opraví se v UsersPresenter
                'owner' => $owner,
                'users' => $users,
                'user_count' => count($users),
                'admin_count' => count(array_filter($users, fn($u) => $u->role === 'admin'))
            ];
        }

        return $result;
    }

    /**
     * Super admin statistiky
     */
    private function getSuperAdminStatistics(): array
    {
        try {
            $totalTenants = $this->database->table('tenants')->count();
            $totalUsers = $this->database->table('users')->where('tenant_id IS NOT NULL')->count();
            $totalAdmins = $this->database->table('users')
                ->where('tenant_id IS NOT NULL')
                ->where('role', 'admin')
                ->count();
            $totalAccountants = $this->database->table('users')
                ->where('tenant_id IS NOT NULL')
                ->where('role', 'accountant')
                ->count();
            $totalReadonly = $this->database->table('users')
                ->where('tenant_id IS NOT NULL')
                ->where('role', 'readonly')
                ->count();

            // Aktivní uživatelé za posledních 30 dní
            $thirtyDaysAgo = new \DateTime('-30 days');
            $activeUsers30d = $this->database->table('users')
                ->where('tenant_id IS NOT NULL')
                ->where('last_login >= ?', $thirtyDaysAgo)
                ->count();

            return [
                'total_tenants' => $totalTenants,
                'total_users' => $totalUsers,
                'total_admins' => $totalAdmins,
                'total_accountants' => $totalAccountants,
                'total_readonly' => $totalReadonly,
                'active_users_30d' => $activeUsers30d
            ];
        } catch (\Exception $e) {
            return [
                'total_tenants' => 0,
                'total_users' => 0,
                'total_admins' => 0,
                'total_accountants' => 0,
                'total_readonly' => 0,
                'active_users_30d' => 0
            ];
        }
    }

    public function renderProfile(): void
    {
        // Každý přihlášený uživatel může upravovat svůj profil
        $userId = $this->getUser()->getId();
        $user = $this->userManager->getById($userId);

        if (!$user) {
            $this->error('Uživatel nebyl nalezen');
        }

        // OPRAVENO: Přidáno nastavení proměnné pro šablonu
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

        // OPRAVENO: Kontrola, zda se nejedná o posledního admina - používáme getAllSelection()
        if ($user->role === 'admin') {
            $adminCount = $this->userManager->getAllSelection()->where('role', 'admin')->count();
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

    public function actionMoveUser(int $userId, int $newTenantId, ?string $reason = null): void
    {
        if (!$this->isSuperAdmin()) {
            $this->flashMessage('Pouze super admin může přesouvat uživatele mezi tenanty.', 'danger');
            $this->redirect('default');
        }

        // Kontrola, že máme platné ID
        if ($userId <= 0) {
            $this->flashMessage('Neplatné ID uživatele.', 'danger');
            $this->redirect('default');
        }

        if ($newTenantId <= 0) {
            $this->flashMessage('Neplatné ID tenanta.', 'danger');
            $this->redirect('default');
        }

        try {
            // Ověření, že uživatel existuje
            $user = $this->userManager->getByIdForSuperAdmin($userId);
            if (!$user) {
                $this->flashMessage('Uživatel nebyl nalezen.', 'danger');
                $this->redirect('default');
            }

            // Ověření, že tenant existuje
            $tenant = $this->database->table('tenants')->get($newTenantId);
            if (!$tenant) {
                $this->flashMessage('Tenant nebyl nalezen.', 'danger');
                $this->redirect('default');
            }

            // Kontrola, zda uživatel už není v tomto tenantu
            if ($user->tenant_id == $newTenantId) {
                $this->flashMessage('Uživatel už je v tomto tenantu.', 'warning');
                $this->redirect('default');
            }

            // Nelze přesunout sebe sama
            if ($userId === $this->getUser()->getId()) {
                $this->flashMessage('Nemůžete přesunout sám sebe.', 'danger');
                $this->redirect('default');
            }

            // ✅ OPRAVENO: Definujeme adminId a adminName
            $adminId = $this->getUser()->getId();
            $adminName = $this->getUser()->getIdentity()->username;

            $success = $this->userManager->moveUserToTenant($userId, $newTenantId, $adminId, $adminName);

            if ($success) {
                $message = "Uživatel '{$user->username}' byl úspěšně přesunut do tenanta '{$tenant->name}'.";
                if ($reason) {
                    $message .= " Důvod: {$reason}";
                }
                $this->flashMessage($message, 'success');
            } else {
                $this->flashMessage('Nepodařilo se přesunout uživatele. Zkuste to prosím znovu.', 'danger');
            }

            // ✅ OPRAVENO: Redirect na konci try bloku
            $this->redirect('default');
        } catch (Nette\Application\AbortException $e) {
            // ✅ KLÍČOVÁ OPRAVA: AbortException (redirect) necháme projít
            throw $e;
        } catch (\Exception $e) {
            $this->flashMessage('Chyba při přesouvání uživatele: ' . $e->getMessage(), 'danger');
            $this->redirect('default');
        }
    }

    /**
     * ✅ VYLEPŠENÝ formulář pro úpravu uživatele s SecurityValidator
     */
    protected function createComponentUserForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        // ✅ Anti-spam ochrana
        $this->addAntiSpamProtectionToForm($form);

        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Zadejte uživatelské jméno')
            ->addFilter([SecurityValidator::class, 'sanitizeString']) // ✅ NOVÉ: Sanitizace
            ->addRule(Form::MIN_LENGTH, 'Uživatelské jméno musí mít alespoň %d znaků', 3)
            ->addRule(Form::MAX_LENGTH, 'Uživatelské jméno může mít maximálně %d znaků', 50)
            ->addRule(function ($control) { // ✅ NOVÉ: Vlastní validace
                $errors = SecurityValidator::validateUsername($control->getValue());
                return empty($errors) ? true : $errors[0];
            }, '');

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte e-mailovou adresu')
            ->addRule(function ($control) { // ✅ OPRAVENO: Přímá validace bez sanitizace
                $email = trim($control->getValue());
                return SecurityValidator::validateEmail($email);
            }, 'Zadejte platnou e-mailovou adresu.');

        // ✅ NOVÉ: Volitelná pole pro jméno a příjmení
        $form->addText('first_name', 'Křestní jméno:')
            ->setRequired(false)
            ->addFilter([SecurityValidator::class, 'sanitizeString'])
            ->addRule(Form::MAX_LENGTH, 'Křestní jméno může mít maximálně %d znaků', 100);

        $form->addText('last_name', 'Příjmení:')
            ->setRequired(false)
            ->addFilter([SecurityValidator::class, 'sanitizeString'])
            ->addRule(Form::MAX_LENGTH, 'Příjmení může mít maximálně %d znaků', 100);

        $form->addSelect('role', 'Role:', [
            'readonly' => 'Pouze čtení',
            'accountant' => 'Účetní',
            'admin' => 'Administrátor'
        ])
            ->setRequired('Vyberte roli');

        // Heslo je povinné pouze při přidávání nového uživatele
        $id = $this->getParameter('id');
        $passwordField = $form->addPassword('password', 'Heslo:')
            ->setRequired($id ? false : 'Zadejte heslo');

        if (!$id) {
            // ✅ VYLEPŠENÁ validace hesla pro nového uživatele
            $passwordField->addRule(function ($control) {
                $errors = SecurityValidator::validatePassword($control->getValue());
                return empty($errors) ? true : implode(' ', $errors);
            }, '');
        } else {
            // ✅ VYLEPŠENÁ validace hesla pro editaci
            $passwordField->addCondition($form::FILLED)
                ->addRule(function ($control) {
                    $errors = SecurityValidator::validatePassword($control->getValue());
                    return empty($errors) ? true : implode(' ', $errors);
                }, '');
        }

        $form->addPassword('passwordVerify', 'Heslo znovu:')
            ->setRequired($id ? false : 'Zadejte heslo znovu pro kontrolu')
            ->addConditionOn($passwordField, $form::FILLED)
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $passwordField);

        if ($id) {
            $form->addSubmit('send', 'Uložit změny');
        } else {
            $form->addSubmit('send', 'Přidat uživatele');
        }

        $form->onSuccess[] = [$this, 'userFormSucceeded'];

        return $form;
    }

    /**
     * ✅ OPRAVENÉ zpracování formuláře - bez zdvojených hlášek
     */
    public function userFormSucceeded(Form $form, \stdClass $data): void
    {
        $id = $this->getParameter('id');
        $successMessage = null; // Flag pro úspěšnou operaci

        try {
            // ✅ OPRAVENO: Kontrola rate limitingu pro vytváření uživatelů
            if (!$id) { // Pouze pro nové uživatele
                if (!$this->checkCustomRateLimit('user_creation')) {
                    $form->addError('Příliš mnoho pokusů o vytvoření uživatele. Zkuste to později.');
                    $this->recordCustomAttempt('user_creation', false);
                    return;
                }
            }

            // ✅ NOVÉ: Kompletní sanitizace všech dat
            $sanitizedData = SecurityValidator::sanitizeFormData((array) $data);
            $data = (object) $sanitizedData;

            // ✅ NOVÉ: Dodatečná validace po sanitizaci
            $validationErrors = [];

            // Validace uživatelského jména
            $usernameErrors = SecurityValidator::validateUsername($data->username);
            $validationErrors = array_merge($validationErrors, $usernameErrors);

            // Validace emailu
            if (!SecurityValidator::validateEmail($data->email)) {
                $validationErrors[] = 'E-mailová adresa není platná.';
            }

            // Validace hesla (jen pokud je vyplněno)
            if (!empty($data->password)) {
                $passwordErrors = SecurityValidator::validatePassword($data->password);
                $validationErrors = array_merge($validationErrors, $passwordErrors);
            }

            // Pokud jsou chyby validace, zobrazíme je a ukončíme
            if (!empty($validationErrors)) {
                foreach ($validationErrors as $error) {
                    $form->addError($error);
                }
                if (!$id) {
                    $this->recordCustomAttempt('user_creation', false);
                } else {
                    $this->recordCustomAttempt('form_submit', false);
                }
                return;
            }

            $adminId = $this->getUser()->getId();
            $adminName = $this->getUser()->getIdentity()->username;

            if ($id) {
                // EDITACE existujícího uživatele
                $userData = [
                    'username' => $data->username,
                    'email' => $data->email,
                    'first_name' => $data->first_name ?? null,
                    'last_name' => $data->last_name ?? null,
                    'role' => $data->role,
                ];

                // Přidání hesla pouze pokud bylo zadáno
                if (!empty($data->password)) {
                    $userData['password'] = $data->password;
                }

                $this->userManager->update($id, $userData, $adminId, $adminName);
                $successMessage = 'Uživatel byl úspěšně aktualizován.';
                $this->recordCustomAttempt('form_submit', true);
            } else {
                // PŘIDÁNÍ nového uživatele do aktuálního tenanta
                $tenantId = $this->getCurrentTenantId();

                if (!$tenantId) {
                    $form->addError('Chyba: Nepodařilo se určit aktuální tenant.');
                    $this->recordCustomAttempt('user_creation', false);
                    return;
                }

                $newUserId = $this->userManager->add(
                    $data->username,
                    $data->email,
                    $data->password,
                    $data->role,
                    $tenantId,
                    $adminId,
                    $adminName,
                    $data->first_name ?? null,
                    $data->last_name ?? null
                );

                if ($newUserId) {
                    $successMessage = 'Uživatel byl úspěšně přidán do vašeho firemního účtu.';
                    $this->recordCustomAttempt('user_creation', true);
                } else {
                    $form->addError('Nepodařilo se přidat uživatele. Zkuste to prosím znovu.');
                    $this->recordCustomAttempt('user_creation', false);
                    return;
                }
            }

            // ✅ OPRAVENO: FlashMessage a redirect až na konci - atomicky
            if ($successMessage) {
                $this->flashMessage($successMessage, 'success');
                $this->redirect('default');
            }
        } catch (Nette\Application\AbortException $e) {
            // ✅ KLÍČOVÁ OPRAVA: AbortException (redirect) necháme projít
            throw $e;
        } catch (\Exception $e) {
            // ✅ OPRAVENO: Pouze chybové zpracování, žádné dvojité hlášky
            if (!$id) {
                $this->recordCustomAttempt('user_creation', false);
            } else {
                $this->recordCustomAttempt('form_submit', false);
            }

            // Pouze chybová hláška - žádný flashMessage už nebyl nastaven
            $form->addError('Chyba při ukládání uživatele: ' . $e->getMessage());
        }
    }

    /**
     * ✅ VYLEPŠENÝ formulář pro úpravu profilu s SecurityValidator
     */
    protected function createComponentProfileForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        // ✅ Anti-spam ochrana
        $this->addAntiSpamProtectionToForm($form);

        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Zadejte uživatelské jméno')
            ->addFilter([SecurityValidator::class, 'sanitizeString']) // ✅ NOVÉ
            ->addRule(function ($control) { // ✅ NOVÉ: Bezpečná validace
                $errors = SecurityValidator::validateUsername($control->getValue());
                return empty($errors) ? true : $errors[0];
            }, '');

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte e-mailovou adresu')
            ->addRule(function ($control) { // ✅ OPRAVENO: Přímá validace
                $email = trim($control->getValue());
                return SecurityValidator::validateEmail($email);
            }, 'Zadejte platnou e-mailovou adresu.');

        $form->addText('first_name', 'Křestní jméno:')
            ->setRequired(false)
            ->addFilter([SecurityValidator::class, 'sanitizeString']); // ✅ NOVÉ

        $form->addText('last_name', 'Příjmení:')
            ->setRequired(false)
            ->addFilter([SecurityValidator::class, 'sanitizeString']); // ✅ NOVÉ

        // PŘIDÁNO: Pole pro současné heslo
        $currentPasswordField = $form->addPassword('currentPassword', 'Současné heslo:')
            ->setRequired(false);

        $passwordField = $form->addPassword('password', 'Nové heslo:')
            ->setRequired(false);

        // ✅ VYLEPŠENÁ validace hesla
        $passwordField->addCondition($form::FILLED)
            ->addRule(function ($control) {
                $errors = SecurityValidator::validatePassword($control->getValue());
                return empty($errors) ? true : implode(' ', $errors);
            }, '');

        $form->addPassword('passwordVerify', 'Nové heslo znovu:')
            ->setRequired(false)
            ->addConditionOn($passwordField, $form::FILLED)
            ->setRequired('Zadejte heslo znovu pro kontrolu')
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $passwordField);

        // Pokud je vyplněno nové heslo, vyžadujeme i současné heslo
        $currentPasswordField->addConditionOn($passwordField, $form::FILLED)
            ->setRequired('Pro změnu hesla musíte zadat současné heslo');

        $form->addSubmit('send', 'Uložit změny');

        $form->onSuccess[] = [$this, 'profileFormSucceeded'];

        return $form;
    }

    /**
     * ✅ OPRAVENÉ zpracování profilu s rate limiting a modal logikou
     */
    public function profileFormSucceeded(Form $form, \stdClass $data): void
    {
        $userId = $this->getUser()->getId();

        try {
            // ✅ OPRAVENO: Kontrola rate limitingu
            if (!$this->checkCustomRateLimit('form_submit')) {
                $form->addError('Příliš mnoho pokusů o úpravu profilu. Zkuste to později.');
                $this->recordCustomAttempt('form_submit', false);
                return;
            }

            // ✅ NOVÉ: Sanitizace dat
            $sanitizedData = SecurityValidator::sanitizeFormData((array) $data);
            $data = (object) $sanitizedData;

            // ✅ NOVÉ: Validace po sanitizaci
            $validationErrors = [];

            $usernameErrors = SecurityValidator::validateUsername($data->username);
            $validationErrors = array_merge($validationErrors, $usernameErrors);

            if (!SecurityValidator::validateEmail($data->email)) {
                $validationErrors[] = 'E-mailová adresa není platná.';
            }

            if (!empty($data->password)) {
                $passwordErrors = SecurityValidator::validatePassword($data->password);
                $validationErrors = array_merge($validationErrors, $passwordErrors);
            }

            if (!empty($validationErrors)) {
                foreach ($validationErrors as $error) {
                    $form->addError($error);
                }
                $this->recordCustomAttempt('form_submit', false);
                return;
            }

            // Získáme aktuální uživatele
            $currentUser = $this->userManager->getById($userId);
            if (!$currentUser) {
                $this->flashMessage('Uživatel nebyl nalezen.', 'danger');
                $this->recordCustomAttempt('form_submit', false);
                return;
            }

            // ✅ NOVÉ: Detekce změny username PŘED aktualizací
            $usernameChanged = ($currentUser->username !== $data->username);
            $originalUsername = $currentUser->username;

            // Kontrola hesla (jen pokud se mění)
            $passwordWillChange = !empty($data->password);
            if ($passwordWillChange) {
                if (empty($data->currentPassword)) {
                    $form->addError('Pro změnu hesla musíte zadat současné heslo.');
                    $this->recordCustomAttempt('form_submit', false);
                    return;
                }

                if (!password_verify($data->currentPassword, $currentUser->password)) {
                    $form->addError('Současné heslo není správné.');
                    $this->recordCustomAttempt('form_submit', false);
                    return;
                }
            }

            // Kontrola jedinečnosti dat
            $existingUsername = $this->userManager->getAllSelection()
                ->where('username', $data->username)
                ->where('id != ?', $userId)
                ->fetch();

            if ($existingUsername) {
                $form->addError('Uživatelské jméno už existuje.');
                $this->recordCustomAttempt('form_submit', false);
                return;
            }

            $existingEmail = $this->userManager->getAllSelection()
                ->where('email', $data->email)
                ->where('id != ?', $userId)
                ->fetch();

            if ($existingEmail) {
                $form->addError('E-mailová adresa už je používána.');
                $this->recordCustomAttempt('form_submit', false);
                return;
            }

            // Příprava dat pro aktualizaci
            $updateData = [
                'username' => $data->username,
                'email' => $data->email,
                'first_name' => $data->first_name ?? null,
                'last_name' => $data->last_name ?? null,
            ];

            // Přidání hesla pouze pokud se mění
            if ($passwordWillChange) {
                $updateData['password'] = $data->password;
            }

            // Aktualizace
            $this->userManager->update($userId, $updateData, $userId);
            $this->recordCustomAttempt('form_submit', true);

            // ✅ NOVÉ: Logika pro změnu username
            if ($usernameChanged) {
                // Nastavíme proměnné pro modal
                $this->template->showLogoutCountdown = true;
                $this->template->originalUsername = $originalUsername;
                $this->template->newUsername = $data->username;

                // Úspěšná hláška pro změnu username
                $this->flashMessage("Uživatelské jméno bylo změněno z '{$originalUsername}' na '{$data->username}'. Budete odhlášeni z bezpečnostních důvodů.", 'success');
            } else {
                // Normální úspěšná hláška
                if ($passwordWillChange) {
                    $this->flashMessage('Profil a heslo byly úspěšně aktualizovány.', 'success');
                } else {
                    $this->flashMessage('Profil byl úspěšně aktualizován.', 'success');
                }

                // Normální redirect
                $this->redirect('this');
            }

            // ✅ OPRAVENO: Pro změnu username se NEPOUŽÍVÁ redirect (kvůli modalu)
            // Modal se zobrazí a JavaScript provede odhlášení

        } catch (Nette\Application\AbortException $e) {
            // ✅ KLÍČOVÁ OPRAVA: AbortException (redirect) necháme projít
            throw $e;
        } catch (\Exception $e) {
            $this->recordCustomAttempt('form_submit', false);
            $form->addError('Chyba při ukládání profilu: ' . $e->getMessage());
        }
    }

    /**
     * Formulář pro přesouvání uživatele mezi tenanty (pouze super admin)
     */
    protected function createComponentMoveTenantForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addHidden('user_id');

        // Získáme všechny tenanty pro výběr
        $tenants = [];
        foreach ($this->database->table('tenants')->order('name ASC') as $tenant) {
            // Získáme i informace o společnosti pro lepší zobrazení
            $company = $this->database->table('company_info')->where('tenant_id', $tenant->id)->fetch();

            $companyName = $company ? $company->name : $tenant->name;
            $tenants[$tenant->id] = "{$tenant->name} ({$companyName})";
        }

        $form->addSelect('new_tenant_id', 'Nový tenant:', $tenants)
            ->setRequired('Vyberte nový tenant');

        $form->addTextArea('reason', 'Důvod přesunutí:')
            ->setRequired(false)
            ->addFilter([SecurityValidator::class, 'sanitizeString']) // ✅ NOVÉ: Sanitizace
            ->setHtmlAttribute('rows', 3);

        $form->addSubmit('send', 'Přesunout uživatele');

        $form->onSuccess[] = [$this, 'moveTenantFormSucceeded'];

        return $form;
    }

    public function moveTenantFormSucceeded(Form $form, \stdClass $data): void
    {
        if (!$this->isSuperAdmin()) {
            $this->flashMessage('Pouze super admin může přesouvat uživatele mezi tenanty.', 'danger');
            $this->redirect('default');
        }

        try {
            // ✅ NOVÉ: Sanitizace dat
            $sanitizedData = SecurityValidator::sanitizeFormData((array) $data);
            $data = (object) $sanitizedData;

            $userId = (int) $data->user_id;
            $newTenantId = (int) $data->new_tenant_id;
            $reason = $data->reason ?? null;

            // Delegace na akci pro zpracování
            $this->actionMoveUser($userId, $newTenantId, $reason);
        } catch (Nette\Application\AbortException $e) {
            // ✅ KLÍČOVÁ OPRAVA: AbortException (redirect) necháme projít
            throw $e;
        } catch (\Exception $e) {
            $this->flashMessage('Chyba při přesouvání uživatele: ' . $e->getMessage(), 'danger');
        }
    }

    /**
     * ✅ OPRAVENÝ: Formulář pro vyhledávání uživatelů s CSRF ochranou
     */
    protected function createComponentSearchForm(): Form
    {
        $form = new Form;
        // ✅ PŘIDÁNA CSRF OCHRANA - jediná změna oproti původnímu souboru
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addText('search', 'Vyhledat:')
            ->setHtmlAttribute('placeholder', 'Zadejte jméno, email, firmu...')
            ->addFilter([SecurityValidator::class, 'sanitizeString']) // ✅ Sanitizace
            ->setDefaultValue($this->getParameter('search') ?: ''); // ✅ Zachování hodnoty

        $form->addSubmit('send', 'Vyhledat')
            ->setHtmlAttribute('class', 'btn btn-primary');

        $form->addSubmit('clear', 'Vymazat')
            ->setHtmlAttribute('class', 'btn btn-outline-secondary')
            ->setValidationScope([]); // ✅ NOVÉ: Clear tlačítko

        $form->onSuccess[] = [$this, 'searchFormSucceeded'];

        return $form;
    }

    /**
     * ✅ OPRAVENÝ: Zpracování vyhledávacího formuláře s lepší syntaxí
     */
    public function searchFormSucceeded(Form $form, \stdClass $data): void
    {
        // ✅ OPRAVENO: Lepší způsob zjištění stisknutého tlačítka
        if (isset($data->clear)) {
            // Clear tlačítko - přesměruj bez parametrů
            $this->redirect('default');
        }

        // ✅ Sanitizace vyhledávacího dotazu
        $searchQuery = SecurityValidator::sanitizeString($data->search ?? '');

        // Přesměrování na stránku s výsledky vyhledávání
        $this->redirect('default', ['search' => $searchQuery]);
    }

    /**
     * ✅ NOVÉ: Akce pro zobrazení rate limiting statistik (pouze super admin)
     */
    public function actionRateLimitStats(): void
    {
        if (!$this->isSuperAdmin()) {
            $this->error('Stránka nenalezena', 404);
        }
    }

    /**
     * ✅ NOVÉ: Odblokování uživatele z rate limit blokování (AJAX)
     */
    public function handleUnblockUser(int $userId): void
    {
        try {
            // Kontrola oprávnění - pouze admin může odblokovat uživatele
            if (!$this->isAdmin()) {
                $this->sendJson([
                    'success' => false,
                    'error' => 'Nemáte oprávnění k této akci.'
                ]);
                return;
            }

            // Ověříme, že uživatel existuje a máme k němu přístup
            $user = $this->userManager->getById($userId);
            if (!$user) {
                $this->sendJson([
                    'success' => false,
                    'error' => 'Uživatel nebyl nalezen.'
                ]);
                return;
            }

            // Získáme informace o adminovi, který provádí odblokování
            $adminId = $this->getUser()->getId();
            $adminName = $this->getUser()->getIdentity()->username;

            // Najdeme všechny rate limit bloky pro tohoto uživatele
            $blockedRecords = $this->database->table('rate_limit_blocks')
                ->where('user_id', $userId);

            $unblockedCount = 0;
            foreach ($blockedRecords as $block) {
                // Smažeme blokování
                $block->delete();
                $unblockedCount++;
            }

            // Vymažeme také pokusy o přihlášení
            $this->database->table('login_attempts')
                ->where('user_id', $userId)
                ->delete();

            // Vymažeme rate limit pokusy
            $this->database->table('rate_limits')
                ->where('user_id', $userId)
                ->delete();

            // Zalogujeme akci
            $this->securityLogger->logSecurityEvent(
                'user_unblocked_by_admin',
                "Uživatel {$user->username} (ID: {$userId}) byl odblokován adminem {$adminName} (ID: {$adminId}). Odstraněno {$unblockedCount} blokování."
            );

            $this->sendJson([
                'success' => true,
                'message' => "Uživatel '{$user->username}' byl úspěšně odblokován. Odstraněno {$unblockedCount} blokování."
            ]);
        } catch (\Exception $e) {
            // Zalogujeme chybu
            $this->securityLogger->logSecurityEvent(
                'user_unblock_error',
                "Chyba při odblokování uživatele ID: {$userId}: " . $e->getMessage()
            );

            $this->sendJson([
                'success' => false,
                'error' => 'Nastala chyba při odblokování uživatele: ' . $e->getMessage()
            ]);
        }
    }
}
