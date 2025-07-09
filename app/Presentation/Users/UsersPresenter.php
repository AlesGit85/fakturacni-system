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
        'moveUser' => ['admin'], // Přesunout uživatele může jen admin (ale reálně jen super admin)
    ];

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
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
        } else {
            // NORMÁLNÍ ZOBRAZENÍ - seskupení podle tenantů
            $groupedUsers = $this->getUsersGroupedByTenants();
            $this->template->groupedUsers = $groupedUsers;
            $this->template->searchResults = [];
        }

        // Celkové statistiky pro super admina
        $superAdminStats = $this->getSuperAdminStatistics();
        $this->template->superAdminStats = $superAdminStats;
        $this->template->totalUsers = $superAdminStats['total_users'];
    }

    /**
     * Příprava zobrazení pro normálního admina
     */
    private function prepareNormalAdminView(): void
    {
        // Získání uživatelů z aktuálního tenanta
        $tenantId = $this->getCurrentTenantId();
        $users = $this->userManager->getAll($tenantId, false);

        $this->template->users = $users;
        $this->template->totalUsers = $users->count();
        $this->template->searchQuery = null;
        $this->template->searchResults = [];
        $this->template->groupedUsers = [];
        $this->template->superAdminStats = [];
    }

    /**
     * Provede vyhledávání uživatelů (pouze pro super admina)
     */
    private function performUserSearch(string $query): array
    {
        if (!$this->isSuperAdmin()) {
            return [];
        }

        $searchResults = [];
        $queryLower = strtolower($query);

        // SQL dotaz pro vyhledávání napříč všemi tenapty
        $sql = "
            SELECT u.*, t.name as tenant_name, c.name as company_name
            FROM users u
            LEFT JOIN tenants t ON u.tenant_id = t.id  
            LEFT JOIN companies c ON t.id = c.tenant_id
            WHERE 
                LOWER(u.username) LIKE ? OR
                LOWER(u.email) LIKE ? OR
                LOWER(u.first_name) LIKE ? OR
                LOWER(u.last_name) LIKE ? OR
                LOWER(t.name) LIKE ? OR
                LOWER(c.name) LIKE ?
            ORDER BY u.username ASC
        ";

        $searchParam = "%{$queryLower}%";
        $results = $this->database->query($sql, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam);

        foreach ($results as $row) {
            $searchResults[] = $row;
        }

        return $searchResults;
    }

    /**
     * Získá uživatele seskupené podle tenantů (pouze pro super admina)
     */
    private function getUsersGroupedByTenants(): array
    {
        if (!$this->isSuperAdmin()) {
            return [];
        }

        $groupedUsers = [];

        // Získání všech aktivních tenantů z databáze
        $tenants = $this->database->table('tenants')->where('status', 'active')->order('name ASC');

        foreach ($tenants as $tenant) {
            // Získání uživatelů pro tento tenant
            $users = $this->database->table('users')
                ->where('tenant_id', $tenant->id)
                ->order('username ASC');

            $usersArray = [];
            $adminCount = 0;
            $owner = null;

            foreach ($users as $user) {
                $usersArray[] = $user;
                if ($user->role === 'admin') {
                    $adminCount++;
                    if (!$owner) { // První admin bude označen jako majitel
                        $owner = $user;
                    }
                }
            }

            // Získání informací o firmě
            $company = $this->database->table('company_info')->where('tenant_id', $tenant->id)->fetch();

            if (count($usersArray) > 0) { // Pouze tenanty s uživateli
                $groupedUsers[] = [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'company_name' => $company ? $company->name : $tenant->name,
                    'company_email' => $company ? $company->email : null,
                    'company_phone' => $company ? $company->phone : null,
                    'users' => $usersArray,
                    'user_count' => count($usersArray),
                    'admin_count' => $adminCount,
                    'owner' => $owner
                ];
            }
        }

        return $groupedUsers;
    }

    /**
     * Získá statistiky pro super admina
     */
    private function getSuperAdminStatistics(): array
    {
        if (!$this->isSuperAdmin()) {
            return [];
        }

        try {
            // Celkový počet aktivních tenantů
            $totalTenants = $this->database->table('tenants')->where('status', 'active')->count();

            // Celkový počet uživatelů
            $totalUsers = $this->database->table('users')->count();

            // Počet adminů
            $totalAdmins = $this->database->table('users')->where('role', 'admin')->count();

            // Počet účetních
            $totalAccountants = $this->database->table('users')->where('role', 'accountant')->count();

            // Počet readonly uživatelů
            $totalReadonly = $this->database->table('users')->where('role', 'readonly')->count();

            // Aktivní uživatelé za posledních 30 dní
            $thirtyDaysAgo = new \DateTime('-30 days');
            $activeUsers30d = $this->database->table('users')
                ->where('last_login > ?', $thirtyDaysAgo)
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

    // =============================================================
    // KOMPONENTY - FORMULÁŘE
    // =============================================================

    /**
     * Vytvoří formulář pro vyhledávání (pouze pro super admina)
     */
    protected function createComponentSearchForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addText('search', 'Vyhledávání')
            ->setHtmlAttribute('placeholder', 'Zadejte jméno, e-mail, firmu nebo tenanta...')
            ->setRequired(false);

        $form->addSubmit('send', 'Hledat')
            ->setHtmlAttribute('class', 'btn btn-primary');

        $form->addSubmit('clear', 'Vymazat')
            ->setHtmlAttribute('class', 'btn btn-outline-secondary')
            ->setValidationScope([]);

        $form->onSuccess[] = [$this, 'searchFormSucceeded'];

        return $form;
    }

    public function searchFormSucceeded(Form $form, \stdClass $data): void
    {
        if ($form->isSubmittedBy($form['clear'])) {
            // Vymazání vyhledávání
            $this->redirect('default');
        } else {
            // Provedení vyhledávání
            $searchQuery = trim($data->search);
            if ($searchQuery) {
                $this->redirect('default', ['search' => $searchQuery]);
            } else {
                $this->redirect('default');
            }
        }
    }

    /**
     * Vytvoří formulář pro přesunutí uživatele do jiného tenanta (pouze pro super admina)
     */
    protected function createComponentMoveTenantForm(): Form
    {
        if (!$this->isSuperAdmin()) {
            throw new \Nette\Application\ForbiddenRequestException('Pouze super admin může přesouvat uživatele mezi tenanty.');
        }

        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addHidden('user_id');

        // Načtení seznamu tenantů s informacemi o firmách
        $tenants = [];
        $tenantsQuery = $this->database->query('
        SELECT 
            t.id as tenant_id,
            t.name as tenant_name,
            c.name as company_name
        FROM tenants t
        LEFT JOIN company_info c ON c.tenant_id = t.id
        WHERE t.status = "active"
        ORDER BY c.name ASC, t.name ASC
    ');

        foreach ($tenantsQuery as $tenant) {
            // Vytvoříme pěkný popisek: "Název firmy - Tenant (ID: 123)"
            $companyName = $tenant->company_name ?: $tenant->tenant_name;
            $label = sprintf(
                '%s - %s (ID: %d)',
                $companyName,
                $tenant->tenant_name,
                $tenant->tenant_id
            );

            $tenants[$tenant->tenant_id] = $label;
        }

        $form->addSelect('new_tenant_id', 'Cílový tenant:')
            ->setItems($tenants)
            ->setRequired('Vyberte tenant, do kterého chcete uživatele přesunout')
            ->setHtmlAttribute('class', 'form-select');

        $form->addTextArea('reason', 'Důvod přesunutí:')
            ->setHtmlAttribute('rows', 3)
            ->setHtmlAttribute('placeholder', 'Volitelně uveďte důvod přesunutí uživatele...')
            ->setRequired(false);

        $form->addSubmit('send', 'Přesunout uživatele')
            ->setHtmlAttribute('class', 'btn btn-warning');

        $form->onSuccess[] = [$this, 'moveTenantFormSucceeded'];

        return $form;
    }

    public function moveTenantFormSucceeded(Form $form, \stdClass $data): void
    {
        if (!$this->isSuperAdmin()) {
            $this->flashMessage('Pouze super admin může přesouvat uživatele mezi tenanty.', 'danger');
            $this->redirect('default');
        }

        $userId = (int) $data->user_id;
        $newTenantId = (int) $data->new_tenant_id;
        $reason = trim($data->reason);

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

            // Provedení přesunutí
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
        } catch (\Exception $e) {
            $this->flashMessage('Chyba při přesouvání uživatele: ' . $e->getMessage(), 'danger');
        }

        $this->redirect('default');
    }

    /**
     * Formulář pro úpravu uživatele
     */
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

            if ($existingUsername) {
                $this->flashMessage('Uživatelské jméno už existuje.', 'danger');
                return;
            }

            $existingEmail = $this->userManager->getAll()
                ->where('email', $data->email)
                ->where('id != ?', $id ?: 0)
                ->fetch();

            if ($existingEmail) {
                $this->flashMessage('E-mailová adresa už je používána.', 'danger');
                return;
            }

            $userData = [
                'username' => $data->username,
                'email' => $data->email,
                'role' => $data->role,
            ];

            // Přidání hesla pouze pokud bylo zadáno
            if (!empty($data->password)) {
                $userData['password'] = password_hash($data->password, PASSWORD_DEFAULT);
            }

            // Informace o adminovi pro logování
            $adminId = $this->getUser()->getId();
            $adminName = $this->getUser()->getIdentity()->username;

            if ($id) {
                // Editace
                $this->userManager->update($id, $userData, $adminId, $adminName);
                $this->flashMessage('Uživatel byl úspěšně aktualizován.', 'success');
            } else {
                // Přidání - předáme základní parametry podle původní metody
                $tenantId = $this->isSuperAdmin() ? null : $this->getCurrentTenantId();
                $this->userManager->add($userData['username'], $userData['email'], $userData['password'], $userData['role'], $adminId, $adminName, $tenantId);
                $this->flashMessage('Uživatel byl úspěšně přidán.', 'success');
            }

            $this->redirect('default');
        } catch (\Exception $e) {
            $this->flashMessage('Chyba při ukládání uživatele: ' . $e->getMessage(), 'danger');
        }
    }

    /**
     * Formulář pro úpravu profilu
     */
    protected function createComponentProfileForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Zadejte uživatelské jméno')
            ->addRule(Form::MIN_LENGTH, 'Uživatelské jméno musí mít alespoň %d znaků', 3);

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte e-mailovou adresu');

        $form->addText('first_name', 'Křestní jméno:')
            ->setRequired(false);

        $form->addText('last_name', 'Příjmení:')
            ->setRequired(false);

        $passwordField = $form->addPassword('password', 'Nové heslo:')
            ->setRequired(false);

        $passwordField->addCondition($form::FILLED)
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 8);

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

        try {
            $userData = [
                'username' => $data->username,
                'email' => $data->email,
                'first_name' => $data->first_name,
                'last_name' => $data->last_name,
            ];

            if (!empty($data->password)) {
                $userData['password'] = password_hash($data->password, PASSWORD_DEFAULT);
            }

            $this->userManager->update($userId, $userData);
            $this->flashMessage('Váš profil byl úspěšně aktualizován.', 'success');
        } catch (\Exception $e) {
            $this->flashMessage('Chyba při ukládání profilu: ' . $e->getMessage(), 'danger');
        }
    }
}
