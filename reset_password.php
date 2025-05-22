<?php
// Skript pro reset hesla uživatele
// Uložte jako reset_password.php v root složce projektu

require_once __DIR__ . '/vendor/autoload.php';

use Nette\Security\Passwords;
use Nette\Configurator;

// NASTAVTE ZDE ÚDAJE PRO RESET
$resetUsername = 'admin';  // Změňte na vaše uživatelské jméno
$newPassword = 'noveheslo123';  // Změňte na nové heslo

echo "=== RESET HESLA UŽIVATELE ===\n\n";

try {
    // Načtení konfigurace
    $configurator = new Configurator;
    $configurator->setTempDirectory(__DIR__ . '/temp');
    $configurator->addConfig(__DIR__ . '/config/common.neon');
    $container = $configurator->createContainer();

    // Získání databáze a password service
    $database = $container->getByType(Nette\Database\Explorer::class);
    $passwords = $container->getByType(Passwords::class);

    // Najdeme uživatele
    $user = $database->table('users')->where('username', $resetUsername)->fetch();

    if (!$user) {
        echo "❌ CHYBA: Uživatel '{$resetUsername}' nebyl nalezen!\n";
        echo "Dostupní uživatelé:\n";
        $allUsers = $database->table('users')->fetchAll();
        foreach ($allUsers as $u) {
            echo "  - {$u->username} (ID: {$u->id})\n";
        }
        exit(1);
    }

    echo "✓ Uživatel nalezen: {$user->username} (ID: {$user->id})\n";
    echo "Staré heslo hash: {$user->password}\n\n";

    // Vytvoření nového hash
    $newHash = $passwords->hash($newPassword);
    echo "Nové heslo: {$newPassword}\n";
    echo "Nový hash: {$newHash}\n\n";

    // Aktualizace v databázi
    $result = $database->table('users')
        ->where('id', $user->id)
        ->update(['password' => $newHash]);

    if ($result) {
        echo "✅ ÚSPĚCH: Heslo bylo úspěšně změněno!\n";
        echo "Nyní se můžete přihlásit s:\n";
        echo "  Uživatelské jméno: {$resetUsername}\n";
        echo "  Heslo: {$newPassword}\n\n";
        
        // Ověření změny
        $updatedUser = $database->table('users')->get($user->id);
        $verification = $passwords->verify($newPassword, $updatedUser->password);
        echo "Ověření nového hesla: " . ($verification ? "✓ OK" : "❌ CHYBA") . "\n";
    } else {
        echo "❌ CHYBA: Nepodařilo se aktualizovat heslo v databázi!\n";
    }

} catch (Exception $e) {
    echo "❌ VÝJIMKA: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== KONEC ===\n";
?>