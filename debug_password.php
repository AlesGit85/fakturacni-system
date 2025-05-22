<?php
// Debug skript pro testování hesla v databázi
// Uložte jako debug_password.php v root složce projektu a spusťte

require_once __DIR__ . '/vendor/autoload.php';

use Nette\Security\Passwords;
use Nette\Configurator;

// Načtení konfigurace
$configurator = new Configurator;
$configurator->setTempDirectory(__DIR__ . '/temp');
$configurator->addConfig(__DIR__ . '/config/common.neon');
$container = $configurator->createContainer();

// Získání databáze a password service
$database = $container->getByType(Nette\Database\Explorer::class);
$passwords = $container->getByType(Passwords::class);

echo "=== DEBUG HESLA V DATABÁZI ===\n\n";

// Získání všech uživatelů
$users = $database->table('users')->fetchAll();

foreach ($users as $user) {
    echo "Uživatel ID: {$user->id}\n";
    echo "Username: {$user->username}\n";
    echo "Email: {$user->email}\n";
    echo "Role: {$user->role}\n";
    echo "Hash hesla: {$user->password}\n";
    
    // Test hesla - zkuste různá hesla
    $testPasswords = ['admin', 'password', '123456', 'test', 'heslo'];
    
    foreach ($testPasswords as $testPassword) {
        $isValid = $passwords->verify($testPassword, $user->password);
        echo "  Test hesla '{$testPassword}': " . ($isValid ? "✓ PLATNÉ" : "✗ neplatné") . "\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

// Test vytvoření nového hash
echo "=== TEST VYTVOŘENÍ NOVÉHO HASH ===\n";
$newPassword = 'noveheslo123';
$newHash = $passwords->hash($newPassword);
echo "Nové heslo: {$newPassword}\n";
echo "Nový hash: {$newHash}\n";
echo "Ověření: " . ($passwords->verify($newPassword, $newHash) ? "✓ OK" : "✗ CHYBA") . "\n\n";

echo "=== STRUKTURA TABULKY USERS ===\n";
try {
    $result = $database->query("DESCRIBE users");
    foreach ($result as $column) {
        echo "Sloupec: {$column->Field} | Typ: {$column->Type} | Null: {$column->Null} | Default: {$column->Default}\n";
    }
} catch (Exception $e) {
    echo "Chyba při načítání struktury tabulky: " . $e->getMessage() . "\n";
}
?>