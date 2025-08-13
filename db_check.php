<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

try {
    $bootstrap = new App\Bootstrap();
    $container = $bootstrap->bootWebApplication();
    $database = $container->getByType(Nette\Database\Explorer::class);
    
    echo "<h1>QRdoklad - Kontrola databáze</h1>";
    echo "<style>body{font-family:Arial;margin:20px;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#B1D235;} .missing{background:#f8d7da;color:#721c24;} .ok{background:#d4edda;color:#155724;}</style>";
    
    // Test připojení k databázi
    echo "<h2>✅ Připojení k databázi: ÚSPĚŠNÉ</h2>";
    
    // Kontrola tabulek
    $tables = $database->query("SHOW TABLES")->fetchAll();
    echo "<h2>📋 Existující tabulky (" . count($tables) . "):</h2>";
    echo "<ul>";
    foreach ($tables as $table) {
        $tableName = current($table);
        echo "<li><strong>$tableName</strong></li>";
    }
    echo "</ul>";
    
    // Kontrola struktury tabulky clients
    echo "<h2>🔍 Struktura tabulky 'clients':</h2>";
    
    try {
        $columns = $database->query("DESCRIBE clients")->fetchAll();
        echo "<table>";
        echo "<tr><th>Sloupec</th><th>Typ</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        $existingColumns = [];
        foreach ($columns as $column) {
            $existingColumns[] = $column['Field'];
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Kontrola chybějících sloupců
        $requiredColumns = [
            'id', 'name', 'street', 'city', 'zip', 'country',
            'ic', 'dic', 'email', 'phone', 'additional_info',
            'created_at', 'updated_at', 'tenant_id'
        ];
        
        echo "<h2>⚠️ Kontrola chybějících sloupců:</h2>";
        echo "<table>";
        echo "<tr><th>Sloupec</th><th>Status</th></tr>";
        
        $missingColumns = [];
        foreach ($requiredColumns as $column) {
            $exists = in_array($column, $existingColumns);
            if (!$exists) {
                $missingColumns[] = $column;
            }
            
            echo "<tr class='" . ($exists ? 'ok' : 'missing') . "'>";
            echo "<td><strong>$column</strong></td>";
            echo "<td>" . ($exists ? '✅ EXISTUJE' : '❌ CHYBÍ') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // SQL pro doplnění chybějících sloupců
        if (!empty($missingColumns)) {
            echo "<h2>🔧 SQL příkazy pro opravu:</h2>";
            echo "<pre style='background:#f8f9fa;padding:15px;border-radius:5px;'>";
            
            foreach ($missingColumns as $column) {
                switch ($column) {
                    case 'additional_info':
                        echo "ALTER TABLE clients ADD COLUMN additional_info TEXT NULL;\n";
                        break;
                    case 'created_at':
                        echo "ALTER TABLE clients ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;\n";
                        break;
                    case 'updated_at':
                        echo "ALTER TABLE clients ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;\n";
                        break;
                    case 'tenant_id':
                        echo "ALTER TABLE clients ADD COLUMN tenant_id INT NOT NULL DEFAULT 1;\n";
                        break;
                    default:
                        echo "-- Chybí definice pro sloupec: $column\n";
                }
            }
            
            echo "</pre>";
            echo "<p><strong>⚠️ Spusťte tyto SQL příkazy v phpMyAdmin nebo jiném databázovém klientu!</strong></p>";
        } else {
            echo "<h2>🎉 Všechny požadované sloupce existují!</h2>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color:red;'>❌ Chyba při kontrole tabulky clients: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // Kontrola jiných důležitých tabulek
    $importantTables = ['companies', 'invoices', 'invoice_items', 'users', 'settings'];
    echo "<h2>📊 Kontrola dalších tabulek:</h2>";
    echo "<ul>";
    
    foreach ($importantTables as $tableName) {
        try {
            $count = $database->query("SELECT COUNT(*) as count FROM `$tableName`")->fetch();
            echo "<li class='ok'>✅ <strong>$tableName</strong> - " . $count['count'] . " záznamů</li>";
        } catch (Exception $e) {
            echo "<li class='missing'>❌ <strong>$tableName</strong> - chyba: " . htmlspecialchars($e->getMessage()) . "</li>";
        }
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h1 style='color:red;'>❌ Chyba při připojení k databázi</h1>";
    echo "<p>Chyba: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Soubor: " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p>Řádek: " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>⬅️ Zpět na aplikaci</a> | <a href='simple_test.php'>🔧 Základní test</a></p>";
?>