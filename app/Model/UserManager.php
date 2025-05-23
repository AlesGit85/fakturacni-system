<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;

class UserManager implements Nette\Security\Authenticator
{
    use Nette\SmartObject;

    /** @var Nette\Database\Explorer */
    private $database;

    /** @var Passwords */
    private $passwords;

    /** @var int Maximální počet neúspěšných přihlášení */
    private $maxLoginAttempts = 5;

    /** @var int Doba blokování v minutách */
    private $lockoutTime = 15;

    public function __construct(
        Nette\Database\Explorer $database,
        Passwords $passwords
    ) {
        $this->database = $database;
        $this->passwords = $passwords;
    }

    /**
     * Přihlášení uživatele
     */
    public function authenticate(string $username, string $password): Nette\Security\SimpleIdentity
    {
        $row = $this->database->table('users')
            ->where('username', $username)
            ->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('Uživatelské jméno není správné.', self::IDENTITY_NOT_FOUND);
        }

        // Kontrola blokování
        if ($this->isUserBlocked($row->id)) {
            throw new Nette\Security\AuthenticationException('Účet je dočasně zablokován kvůli příliš mnoha neúspěšným pokusům o přihlášení. Zkuste to prosím později.', self::INVALID_CREDENTIAL);
        }

        if (!$this->passwords->verify($password, $row->password)) {
            // Zaznamenáme neúspěšný pokus o přihlášení
            $this->logFailedLoginAttempt($row->id);
            
            throw new Nette\Security\AuthenticationException('Heslo není správné.', self::INVALID_CREDENTIAL);
        }

        // Reset neúspěšných pokusů při úspěšném přihlášení
        $this->resetFailedLoginAttempts($row->id);

        // Aktualizace posledního přihlášení
        $this->database->table('users')
            ->where('id', $row->id)
            ->update(['last_login' => new \DateTime()]);

        $arr = $row->toArray();
        unset($arr['password']);

        return new Nette\Security\SimpleIdentity($row->id, $row->role, $arr);
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
     */
    public function add(string $username, string $email, string $password, string $role = 'readonly'): void
    {
        $this->database->table('users')->insert([
            'username' => $username,
            'password' => $this->passwords->hash($password),
            'email' => $email,
            'role' => $role,
            'created_at' => new \DateTime(),
            'last_login' => null,
        ]);
    }

    /**
     * Získá všechny uživatele
     */
    public function getAll()
    {
        return $this->database->table('users')->order('username ASC');
    }

    /**
     * Získá uživatele podle ID
     */
    public function getById($id)
    {
        return $this->database->table('users')->get($id);
    }

    /**
     * Aktualizuje uživatele
     */
    public function update($id, $data)
    {
        try {
            // Pokud se mění heslo, zahashujeme ho
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = $this->passwords->hash($data['password']);
            } else {
                unset($data['password']);
            }

            return $this->database->table('users')->where('id', $id)->update($data);
        } catch (\Exception $e) {
            // Logování chyby - v produkci by bylo vhodné použít logger
            error_log('Chyba při aktualizaci uživatele: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Smaže uživatele
     */
    public function delete($id)
    {
        return $this->database->table('users')->where('id', $id)->delete();
    }

    /**
     * Změna hesla uživatele
     */
    public function changePassword($userId, string $newPassword): bool
    {
        try {
            $hashedPassword = $this->passwords->hash($newPassword);
            
            $result = $this->database->table('users')
                ->where('id', $userId)
                ->update(['password' => $hashedPassword]);
                
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
     * Získá počet uživatelů podle rolí
     */
    public function getRoleStatistics(): array
    {
        $stats = $this->database->table('users')
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
}