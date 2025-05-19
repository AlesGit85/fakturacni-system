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

        $arr = $row->toArray();
        unset($arr['password']);

        return new Nette\Security\SimpleIdentity($row->id, $row->role, $arr);
    }

    /**
     * Přidá nového uživatele
     */
    public function add(string $username, string $email, string $password): void
    {
        $this->database->table('users')->insert([
            'username' => $username,
            'password' => $this->passwords->hash($password),
            'email' => $email,
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
        return $this->database->table('users')->where('id', $id)->update($data);
    }

    /**
     * Smaže uživatele
     */
    public function delete($id)
    {
        return $this->database->table('users')->where('id', $id)->delete();
    }
}
