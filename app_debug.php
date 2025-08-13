<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

try {
    $bootstrap = new App\Bootstrap();
    $container = $bootstrap->bootWebApplication();
    $database = $container->getByType(Nette\Database\Explorer::class);
    
    echo "<h1>QRdoklad - Diagnostika aplikace</h1>";
    echo "<style>body{font-family:Arial;margin:20px;} .error{color:red;background:#f8d7da;padding:10px;margin:10px 0;border-radius:5px;} .success{color:green;background:#d4edda;padding:10px;margin:10px 0;border-radius:5px;} .info{color:#0c5460;background:#d1ecf1;padding:10px;margin:10px 0;border-radius:5px;} pre{background:#f8f9fa;padding:15px;border-radius:5px;overflow-x:auto;}</style>";
    
    // Test 1: CompanyManager
    echo "<h2>🏢 Test CompanyManager:</h2>";
    try {
        $companyManager = $container->getByType(App\Model\CompanyManager::class);
        echo "<div class='success'>✅ CompanyManager se načetl</div>";
        
        $company = $companyManager->getCompanyInfo();
        if ($company) {
            echo "<div class='success'>✅ CompanyManager vrací data</div>";
            echo "<div class='info'>";
            echo "<strong>Název:</strong> " . htmlspecialchars($company->name ?? 'N/A') . "<br>";
            echo "<strong>Logo:</strong> " . htmlspecialchars($company->logo ?? 'Není nastaveno') . "<br>";
            echo "<strong>Podpis:</strong> " . htmlspecialchars($company->signature ?? 'Není nastaveno') . "<br>";
            echo "</div>";
            
            // Test cest k souborům
            if ($company->logo) {
                $logoPath = __DIR__ . '/web/uploads/logo/' . $company->logo;
                echo "<p><strong>Cesta k logu:</strong> " . $logoPath . "</p>";
                echo "<p><strong>Logo existuje:</strong> " . (file_exists($logoPath) ? "✅ ANO" : "❌ NE") . "</p>";
                
                if (!file_exists($logoPath)) {
                    // Pokusíme se najít logo jinde
                    $alternativePaths = [
                        __DIR__ . '/uploads/logo/' . $company->logo,
                        __DIR__ . '/assets/logo/' . $company->logo,
                        __DIR__ . '/images/logo/' . $company->logo
                    ];
                    
                    foreach ($alternativePaths as $altPath) {
                        if (file_exists($altPath)) {
                            echo "<p><strong>Logo nalezeno na:</strong> " . $altPath . "</p>";
                            break;
                        }
                    }
                }
            }
            
            if ($company->signature) {
                $signaturePath = __DIR__ . '/web/uploads/signature/' . $company->signature;
                echo "<p><strong>Cesta k podpisu:</strong> " . $signaturePath . "</p>";
                echo "<p><strong>Podpis existuje:</strong> " . (file_exists($signaturePath) ? "✅ ANO" : "❌ NE") . "</p>";
            }
            
        } else {
            echo "<div class='error'>❌ CompanyManager nevrací žádná data</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Chyba v CompanyManager: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<div class='error'>Soubor: " . htmlspecialchars($e->getFile()) . " | Řádek: " . $e->getLine() . "</div>";
    }
    
    // Test 2: ClientManager
    echo "<h2>👥 Test ClientManager:</h2>";
    try {
        $clientManager = $container->getByType(App\Model\ClientManager::class);
        echo "<div class='success'>✅ ClientManager se načetl</div>";
        
        // Test získání klientů
        $clients = $clientManager->getAllClients();
        echo "<div class='info'>Počet klientů: " . count($clients) . "</div>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Chyba v ClientManager: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<div class='error'>Soubor: " . htmlspecialchars($e->getFile()) . " | Řádek: " . $e->getLine() . "</div>";
        
        // Pokud je chyba related k additional_info, ukážeme to
        if (strpos($e->getMessage(), 'additional_info') !== false) {
            echo "<div class='info'>🔍 Chyba je related k 'additional_info' - potřebujeme opravit kód</div>";
        }
    }
    
    // Test 3: Kontrola upload adresářů
    echo "<h2>📁 Kontrola upload adresářů:</h2>";
    $uploadDirs = [
        'web/uploads',
        'web/uploads/logo',
        'web/uploads/signature'
    ];
    
    foreach ($uploadDirs as $dir) {
        $fullPath = __DIR__ . '/' . $dir;
        $exists = is_dir($fullPath);
        $writable = $exists && is_writable($fullPath);
        
        if ($exists && $writable) {
            echo "<div class='success'>✅ $dir - OK</div>";
        } elseif ($exists) {
            echo "<div class='error'>⚠️ $dir - Existuje, ale není zapisovatelný</div>";
        } else {
            echo "<div class='error'>❌ $dir - Neexistuje</div>";
            echo "<div class='info'>Příkaz pro vytvoření: mkdir -p " . $fullPath . " && chmod 755 " . $fullPath . "</div>";
        }
    }
    
    // Test 4: Kontrola company_info vs companies
    echo "<h2>🔍 Test databázových tabulek:</h2>";
    
    try {
        $companyInfoCount = $database->query("SELECT COUNT(*) as count FROM company_info")->fetch();
        echo "<div class='success'>✅ company_info: " . $companyInfoCount['count'] . " záznamů</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ company_info: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
    try {
        $companiesCount = $database->query("SELECT COUNT(*) as count FROM companies")->fetch();
        echo "<div class='success'>✅ companies: " . $companiesCount['count'] . " záznamů</div>";
    } catch (Exception $e) {
        echo "<div class='info'>ℹ️ Tabulka 'companies' neexistuje (to je OK, používáme company_info)</div>";
    }
    
    // Test 5: Zkontrolujeme strukturu clients tabulky
    echo "<h2>📋 Aktuální struktura clients tabulky:</h2>";
    try {
        $columns = $database->query("DESCRIBE clients")->fetchAll();
        echo "<div class='info'>Sloupce v clients: ";
        $columnNames = array_map(function($col) { return $col['Field']; }, $columns);
        echo implode(', ', $columnNames);
        echo "</div>";
        
        if (in_array('additional_info', $columnNames)) {
            echo "<div class='success'>✅ additional_info sloupec existuje</div>";
        } else {
            echo "<div class='info'>ℹ️ additional_info sloupec neexistuje (což je v pořádku podle vás)</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Chyba při kontrole clients: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
    echo "<h2>📄 Závěr diagnostiky:</h2>";
    echo "<div class='info'>";
    echo "Na základě výsledků výše můžeme identifikovat problémy a připravit opravy kódu aplikace.<br>";
    echo "Místo změn databáze upravíme aplikaci tak, aby fungovala s vaší stávající strukturou.";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h1 style='color:red;'>❌ Chyba při spuštění diagnostiky</h1>";
    echo "<p>Chyba: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Soubor: " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p>Řádek: " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>⬅️ Zpět na aplikaci</a> | <a href='db_check.php'>🔧 DB test</a></p>";
?>