<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use App\Security\SecurityLogger;

class UserManager implements Nette\Security\Authenticator
{
    use Nette\SmartObject;

    /** @var Nette\Database\Explorer */
    private $database;

    /** @var Passwords */
    private $passwords;

    /** @var SecurityLogger */
    private $securityLogger;

    /** @var int Maximální počet neúspěšných přihlášení */
    private $maxLoginAttempts = 5;

    /** @var int Doba blokování v minutách */
    private $lockoutTime = 15;

    /** @var int|null Current tenant ID pro filtrování */
    private $currentTenantId = null;

    /** @var bool Je uživatel super admin? */
    private $isSuperAdmin = false;

    public function __construct(
        Nette\Database\Explorer $database,
        Passwords $passwords,
        SecurityLogger $securityLogger
    ) {
        $this->database = $database;
        $this->passwords = $passwords;
        $this->securityLogger = $securityLogger;
    }

    // =====================================================
    // MULTI-TENANCY NASTAVENÍ (NOVÉ)
    // =====================================================

    /**
     * Nastaví current tenant ID pro filtrování dat
     * Volá se z BasePresenter nebo jiných služeb
     */
    public function setTenantContext(?int $tenantId, bool $isSuperAdmin = false): void
    {
        $this->currentTenantId = $tenantId;
        $this->isSuperAdmin = $isSuperAdmin;
    }

    /**
     * Aplikuje tenant filtr na databázový dotaz
     */
    private function applyTenantFilter(Nette\Database\Table\Selection $selection): Nette\Database\Table\Selection
    {
        // Super admin vidí všechna data
        if ($this->isSuperAdmin) {
            return $selection;
        }

        // Ostatní uživatelé vidí pouze data svého tenanta
        if ($this->currentTenantId !== null) {
            return $selection->where('tenant_id', $this->currentTenantId);
        }

        // Pokud nemá tenant_id, nevidí nic (fallback bezpečnost)
        return $selection->where('1 = 0');
    }

    // =====================================================
    // PŮVODNÍ METODY S MULTI-TENANCY ROZŠÍŘENÍM
    // =====================================================

    /**
     * Přihlášení uživatele
     * ROZŠÍŘENO: Automaticky načítá tenant_id a is_super_admin z databáze
     */
    public function authenticate(string $username, string $password): Nette\Security\SimpleIdentity
    {
        $row = $this->database->table('users')
            ->where('username', $username)
            ->fetch();

        if (!$row) {
            $this->securityLogger->logFailedLogin($username, 'neexistující uživatel');
            throw new Nette\Security\AuthenticationException('Uživatelské jméno není správné.', self::IDENTITY_NOT_FOUND);
        }

        // Kontrola blokování
        if ($this->isUserBlocked($row->id)) {
            $this->securityLogger->logFailedLogin($username, 'účet je zablokován');
            throw new Nette\Security\AuthenticationException('Účet je dočasně zablokován kvůli příliš mnoha neúspěšným pokusům o přihlášení. Zkuste to prosím později.', self::INVALID_CREDENTIAL);
        }

        if (!$this->passwords->verify($password, $row->password)) {
            // Zaznamenáme neúspěšný pokus o přihlášení
            $this->logFailedLoginAttempt($row->id);
            $this->securityLogger->logFailedLogin($username);
            
            // Kontrola, zda jsme dosáhli limitu pokusů
            $attempts = $this->getLoginAttempts($row->id);
            if ($attempts >= $this->maxLoginAttempts) {
                $this->securityLogger->logAccountLockout($row->id, $username);
            }
            
            throw new Nette\Security\AuthenticationException('Heslo není správné.', self::INVALID_CREDENTIAL);
        }

        // Reset neúspěšných pokusů při úspěšném přihlášení
        $this->resetFailedLoginAttempts($row->id);
        
        // Logování úspěšného přihlášení
        $this->securityLogger->logLogin($row->id, $username);

        // Aktualizace posledního přihlášení
        $this->database->table('users')
            ->where('id', $row->id)
            ->update(['last_login' => new \DateTime()]);

        $arr = $row->toArray();
        unset($arr['password']);

        // MULTI-TENANCY: Explicitně zajistíme, že tenant_id a is_super_admin jsou v datech
        // (Měly by už být, ale pro jistotu)
        if (!isset($arr['tenant_id'])) {
            $arr['tenant_id'] = null;
        }
        if (!isset($arr['is_super_admin'])) {
            $arr['is_super_admin'] = 0;
        }

        return new Nette\Security\SimpleIdentity($row->id, $row->role, $arr);
    }

    /**
     * Získá počet neúspěšných pokusů o přihlášení
     */
    private function getLoginAttempts(int $userId): int
    {
        $record = $this->database->table('login_attempts')
            ->where('user_id', $userId)
            ->fetch();
            
        return $record ? $record->attempts : 0;
    }

    /**
     * Zaznamená neúspěšný pokus o přihlášení
     */
    private function logFailedLoginAttempt(int $userId): void
    {
        // Nejprve zkontrolujeme, zda už existuje záznam
        $record = $this->database->table('login_attempts')
            ->where('user_id', $userId)
            ->fetch();

        if ($record) {
            // Aktualizace existujícího záznamu
            $this->database->table('login_attempts')
                ->where('user_id', $userId)
                ->update([
                    'attempts' => $record->attempts + 1,
                    'last_attempt' => new \DateTime(),
                ]);
        } else {
            // Vytvoření nového záznamu
            $this->database->table('login_attempts')->insert([
                'user_id' => $userId,
                'attempts' => 1,
                'last_attempt' => new \DateTime(),
            ]);
        }
    }

    /**
     * Resetuje počet neúspěšných pokusů o přihlášení
     */
    private function resetFailedLoginAttempts(int $userId): void
    {
        $this->database->table('login_attempts')
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Zkontroluje, zda je uživatel blokován kvůli příliš mnoha neúspěšným pokusům
     */
    private function isUserBlocked(int $userId): bool
    {
        $record = $this->database->table('login_attempts')
            ->where('user_id', $userId)
            ->fetch();

        if (!$record) {
            return false;
        }

        // Pokud počet pokusů nepřekročil limit, uživatel není blokován
        if ($record->attempts < $this->maxLoginAttempts) {
            return false;
        }

        // Kontrola, zda uplynula doba blokování
        $lastAttempt = $record->last_attempt;
        $now = new \DateTime();
        $interval = $now->diff(new \DateTime($lastAttempt));
        $minutesPassed = $interval->days * 24 * 60 + $interval->h * 60 + $interval->i;

        return $minutesPassed < $this->lockoutTime;
    }

    /**
     * Ověří heslo uživatele bez přihlášení (pro změnu hesla)
     */
    public function verifyPassword(string $username, string $password): bool
    {
        $row = $this->database->table('users')
            ->where('username', $username)
            ->fetch();

        if (!$row) {
            return false;
        }

        return $this->passwords->verify($password, $row->password);
    }

    /**
     * Přidá nového uživatele
     * ROZŠÍŘENO: Automaticky nastaví tenant_id podle kontextu
     */
    public function add(
        string $username, 
        string $email, 
        string $password, 
        string $role = 'readonly', 
        ?int $tenantId = null,
        ?int $adminId = null, 
        ?string $adminName = null, 
        ?string $firstName = null, 
        ?string $lastName = null
    ): int {
        // Pokud není zadáno tenant_id, použijeme aktuální kontext
        if ($tenantId === null) {
            $tenantId = $this->currentTenantId ?? 1; // Fallback na default tenant
        }

        $userData = [
            'username' => $username,
            'password' => $this->passwords->hash($password),
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $role,
            'tenant_id' => $tenantId,
            'is_super_admin' => 0,
            'created_at' => new \DateTime(),
            'last_login' => null,
        ];

        $result = $this->database->table('users')->insert($userData);
        $newUserId = $result->id;
        
        // Logování vytvoření uživatele
        $this->securityLogger->logUserCreation($newUserId, $username, $role, $adminId, $adminName);
        
        return $newUserId;
    }

    /**
     * Získá všechny uživatele (filtrované podle tenant_id)
     * AKTUALIZOVÁNO: Nyní používá tenant kontext místo parametrů
     */
    public function getAll()
    {
        $selection = $this->database->table('users')->order('username ASC');
        return $this->applyTenantFilter($selection);
    }

    /**
     * Získá uživatele podle ID (s kontrolou tenant_id)
     * AKTUALIZOVÁNO: Nyní používá tenant kontext
     */
    public function getById($id)
    {
        $selection = $this->database->table('users')->where('id', $id);
        $filteredSelection = $this->applyTenantFilter($selection);
        return $filteredSelection->fetch();
    }

    /**
     * Aktualizuje uživatele
     * ROZŠÍŘENO: Kontroluje tenant přístup při editaci
     */
    public function update($id, $data, ?int $adminId = null, ?string $adminName = null)
    {
        try {
            // MULTI-TENANCY: Ověříme, že uživatel existuje a máme k němu přístup
            $existingUser = $this->getById($id);
            if (!$existingUser) {
                throw new \Exception('Uživatel neexistuje nebo k němu nemáte přístup.');
            }

            // Logování změny role
            if (isset($data['role'])) {
                if ($existingUser->role !== $data['role']) {
                    $this->securityLogger->logRoleChange(
                        $id, 
                        $existingUser->username, 
                        $existingUser->role, 
                        $data['role'], 
                        $adminId ?: -1, 
                        $adminName ?: 'Systém'
                    );
                }
            }
            
            // Logování změny tenant_id (pouze pro super admina)
            if (isset($data['tenant_id']) && $this->isSuperAdmin) {
                if ($existingUser->tenant_id != $data['tenant_id']) {
                    $this->securityLogger->logSecurityEvent(
                        'tenant_change',
                        "Uživatel {$existingUser->username} (ID: $id) byl přesunut z tenanta {$existingUser->tenant_id} do tenanta {$data['tenant_id']} administrátorem " . ($adminName ?: 'Systém') . " (ID: " . ($adminId ?: -1) . ")"
                    );
                }
            } elseif (isset($data['tenant_id']) && !$this->isSuperAdmin) {
                // Nestandardní případ - tenant_id se nemá měnit bez super admin práv
                unset($data['tenant_id']);
            }

            // Logování změny super admin statusu (pouze pro super admina)
            if (isset($data['is_super_admin']) && $this->isSuperAdmin) {
                if ($existingUser->is_super_admin != $data['is_super_admin']) {
                    $action = $data['is_super_admin'] ? 'přidělena' : 'odebrána';
                    $this->securityLogger->logSecurityEvent(
                        'super_admin_change',
                        "Super admin role byla $action uživateli {$existingUser->username} (ID: $id) administrátorem " . ($adminName ?: 'Systém') . " (ID: " . ($adminId ?: -1) . ")"
                    );
                }
            } elseif (isset($data['is_super_admin']) && !$this->isSuperAdmin) {
                // Super admin status může měnit pouze super admin
                unset($data['is_super_admin']);
            }

            $result = $this->database->table('users')
                ->where('id', $id)
                ->update($data);
                
            return $result > 0;
        } catch (\Exception $e) {
            error_log('Chyba při aktualizaci uživatele: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Smaže uživatele
     * ROZŠÍŘENO: Kontroluje tenant přístup při mazání
     */
    public function delete($id, int $adminId, string $adminName)
    {
        // MULTI-TENANCY: Ověříme přístup k uživateli
        $user = $this->getById($id);
        if (!$user) {
            return false;
        }
        
        // Logování smazání uživatele
        $this->securityLogger->logUserDeletion($id, $user->username, $adminId, $adminName);
        
        return $this->database->table('users')->where('id', $id)->delete();
    }

    /**
     * Změna hesla uživatele
     */
    public function changePassword($userId, string $newPassword, ?int $adminId = null): bool
    {
        try {
            $hashedPassword = $this->passwords->hash($newPassword);
            
            $result = $this->database->table('users')
                ->where('id', $userId)
                ->update(['password' => $hashedPassword]);
                
            if ($result) {
                $user = $this->getById($userId);
                if ($user) {
                    $this->securityLogger->logPasswordChange(
                        $userId,
                        $user->username,
                        $adminId !== null && $adminId !== $userId
                    );
                }
            }
            
            return $result > 0;
        } catch (\Exception $e) {
            error_log('Chyba při změně hesla: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kontrola, zda je uživatelské jméno dostupné
     */
    public function isUsernameAvailable(string $username, ?int $excludeUserId = null): bool
    {
        $query = $this->database->table('users')->where('username', $username);
        
        if ($excludeUserId) {
            $query->where('id != ?', $excludeUserId);
        }
        
        return $query->count() === 0;
    }

    /**
     * Kontrola, zda je e-mail dostupný
     */
    public function isEmailAvailable(string $email, ?int $excludeUserId = null): bool
    {
        $query = $this->database->table('users')->where('email', $email);
        
        if ($excludeUserId) {
            $query->where('id != ?', $excludeUserId);
        }
        
        return $query->count() === 0;
    }

    /**
     * Získá počet uživatelů podle rolí (filtrované podle tenant_id)
     * AKTUALIZOVÁNO: Nyní používá tenant kontext místo parametrů
     */
    public function getRoleStatistics(): array
    {
        $selection = $this->database->table('users');
        $filteredSelection = $this->applyTenantFilter($selection);

        $stats = $filteredSelection
            ->select('role, COUNT(*) as count')
            ->group('role')
            ->fetchPairs('role', 'count');

        return [
            'admin' => $stats['admin'] ?? 0,
            'accountant' => $stats['accountant'] ?? 0,
            'readonly' => $stats['readonly'] ?? 0,
            'total' => array_sum($stats)
        ];
    }

    /**
     * Získá plné jméno uživatele
     */
    public function getUserDisplayName($user): string
    {
        if (!$user) {
            return '';
        }

        // Pokud má křestní jméno, použijeme ho
        if (!empty($user->first_name)) {
            return $user->first_name;
        }

        // Jinak použijeme username
        return $user->username;
    }

    /**
     * Získá plné jméno s příjmením
     */
    public function getUserFullName($user): string
    {
        if (!$user) {
            return '';
        }

        $parts = [];
        
        if (!empty($user->first_name)) {
            $parts[] = $user->first_name;
        }
        
        if (!empty($user->last_name)) {
            $parts[] = $user->last_name;
        }

        if (empty($parts)) {
            return $user->username;
        }

        return implode(' ', $parts);
    }

    /**
     * Zahashuje heslo pomocí Nette Passwords
     */
    public function hashPassword(string $password): string
    {
        return $this->passwords->hash($password);
    }

    // =====================================================
    // SUPER ADMIN METODY
    // =====================================================

    /**
     * Vytvoří super admin uživatele (bez tenant omezení)
     */
    public function createSuperAdmin(
        string $username, 
        string $email, 
        string $password, 
        ?string $firstName = null, 
        ?string $lastName = null
    ): int {
        $userData = [
            'username' => $username,
            'password' => $this->passwords->hash($password),
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => 'admin',
            'tenant_id' => null, // Super admin není vázán na tenant
            'is_super_admin' => 1,
            'created_at' => new \DateTime(),
            'last_login' => null,
        ];

        $result = $this->database->table('users')->insert($userData);
        $newUserId = $result->id;
        
        // Logování vytvoření super admina
        $this->securityLogger->logSecurityEvent(
            'super_admin_creation',
            "Super admin $username (ID: $newUserId) byl vytvořen"
        );
        
        return $newUserId;
    }

    /**
     * Získá všechny uživatele z daného tenanta (pouze pro super admina)
     */
    public function getUsersByTenant(int $tenantId)
    {
        return $this->database->table('users')
            ->where('tenant_id', $tenantId)
            ->order('username ASC');
    }

    /**
     * Přesune uživatele do jiného tenanta (pouze pro super admina)
     */
    public function moveUserToTenant(int $userId, int $newTenantId, int $adminId, string $adminName): bool
    {
        // Zde nepoužíváme getById(), protože super admin potřebuje přístup ke všem uživatelům
        $user = $this->database->table('users')->get($userId);
        if (!$user) {
            return false;
        }

        $oldTenantId = $user->tenant_id;
        
        $result = $this->database->table('users')
            ->where('id', $userId)
            ->update(['tenant_id' => $newTenantId]);

        if ($result) {
            $this->securityLogger->logSecurityEvent(
                'user_tenant_move',
                "Uživatel {$user->username} (ID: $userId) byl přesunut z tenanta $oldTenantId do tenanta $newTenantId super adminem $adminName (ID: $adminId)"
            );
        }

        return $result > 0;
    }
}