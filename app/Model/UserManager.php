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

        if (!$this->passwords->verify($password, $row->password)) {
            throw new Nette\Security\AuthenticationException('Heslo není správné.', self::INVALID_CREDENTIAL);
        }

        // Aktualizace posledního přihlášení
        $this->database->table('users')
            ->where('id', $row->id)
            ->update(['last_login' => new \DateTime()]);

        $arr = $row->toArray();
        unset($arr['password']);

        return new Nette\Security\SimpleIdentity($row->id, $row->role, $arr);
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
    public function add(string $username, string $email, string $password, string $role = 'readonly'): Nette\Database\Table\ActiveRow
    {
        return $this->database->table('users')->insert([
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
        // Převedeme data na pole pokud jsou objektem
        if (is_object($data)) {
            $data = (array) $data;
        }

        // Pokud se mění heslo, zahashujeme ho
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = $this->passwords->hash($data['password']);
        } else {
            // Pokud heslo není zadané, odstraníme ho z dat
            unset($data['password']);
        }

        // Provedeme aktualizaci
        return $this->database->table('users')->where('id', $id)->update($data);
    }

    /**
     * Smaže uživatele
     */
    public function delete($id)
    {
        return $this->database->table('users')->where('id', $id)->delete();
    }

    /**
     * Změna hesla uživatele - pomocná metoda
     */
    public function changePassword($userId, string $newPassword): bool
    {
        $hashedPassword = $this->passwords->hash($newPassword);
        
        $result = $this->database->table('users')
            ->where('id', $userId)
            ->update(['password' => $hashedPassword]);
            
        return $result > 0;
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