<?php
/**
 * Debug skript pro kontrolu databáze
 * Spusť v browseru nebo CLI pro ověření připojení a tabulek
 */

try {
    // Databázové údaje
    $host = 'localhost';
    $dbname = 'c4devqrdoklad';
    $username = 'c4alpho';
    $password = 'nzsm_YJH6'; // Nastav skutečné heslo
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>✅ Připojení k databázi úspěšné!</h2>";
    echo "<p><strong>Database:</strong> $dbname</p>";
    echo "<p><strong>Host:</strong> $host</p>";
    echo "<p><strong>User:</strong> $username</p>";
    
    // Kontrola existence tabulek
    $requiredTables = [
        'tenants', 'users', 'company_info', 'clients', 'invoices', 
        'user_modules', 'security_logs', 'rate_limits', 'rate_limit_blocks'
    ];
    
    echo "<h3>Kontrola tabulek:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Tabulka</th><th>Status</th><th>Počet záznamů</th></tr>";
    
    foreach ($requiredTables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
            $count = $stmt->fetchColumn();
            echo "<tr style='background-color: #d4edda;'>";
            echo "<td><strong>$table</strong></td>";
            echo "<td>✅ Existuje</td>";
            echo "<td>$count</td>";
            echo "</tr>";
        } catch (PDOException $e) {
            echo "<tr style='background-color: #f8d7da;'>";
            echo "<td><strong>$table</strong></td>";
            echo "<td>❌ Neexistuje</td>";
            echo "<td>-</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    
    // Zkouška přihlášení
    echo "<h3>Testovací účty:</h3>";
    $stmt = $pdo->query("SELECT id, username, email, role, is_super_admin, tenant_id FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($users) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Super Admin</th><th>Tenant ID</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td><strong>{$user['username']}</strong></td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>" . ($user['is_super_admin'] ? 'Ano' : 'Ne') . "</td>";
            echo "<td>{$user['tenant_id']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><strong>Testovací heslo pro všechny účty:</strong> password</p>";
    } else {
        echo "<p>❌ Žádní uživatelé v databázi!</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2>❌ Chyba připojení k databázi!</h2>";
    echo "<p><strong>Chyba:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Kontroluj:</strong></p>";
    echo "<ul>";
    echo "<li>Existuje databáze 'c4devqrdoklad'?</li>";
    echo "<li>Jsou správné přihlašovací údaje?</li>";
    echo "<li>Běží MySQL server?</li>";
    echo "</ul>";
}