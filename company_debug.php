<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

try {
    $bootstrap = new App\Bootstrap();
    $container = $bootstrap->bootWebApplication();
    $database = $container->getByType(Nette\Database\Explorer::class);
    
    echo "<h1>QRdoklad - Debug informací o společnosti</h1>";
    echo "<style>body{font-family:Arial;margin:20px;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#B1D235;} .error{color:red;} .success{color:green;} pre{background:#f8f9fa;padding:15px;border-radius:5px;}</style>";
    
    // Kontrola tabulky company_info
    echo "<h2>📋 Kontrola tabulky company_info:</h2>";
    
    try {
        $companyInfo = $database->query("SELECT * FROM company_info LIMIT 1")->fetch();
        
        if ($companyInfo) {
            echo "<div class='success'>✅ Tabulka company_info existuje a obsahuje data</div>";
            echo "<table>";
            echo "<tr><th>Sloupec</th><th>Hodnota</th></tr>";
            
            foreach ($companyInfo as $key => $value) {
                echo "<tr>";
                echo "<td><strong>" . htmlspecialchars($key) . "</strong></td>";
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Kontrola souborů loga a podpisu
            echo "<h2>🖼️ Kontrola souborů loga a podpisu:</h2>";
            
            $logoPath = null;
            $signaturePath = null;
            
            // Hledání sloupců pro logo a podpis
            if (isset($companyInfo['logo'])) {
                $logoPath = __DIR__ . '/web/uploads/logo/' . $companyInfo['logo'];
                echo "<p><strong>Logo:</strong> " . htmlspecialchars($companyInfo['logo'] ?? 'Není nastaveno') . "</p>";
                echo "<p><strong>Cesta k logu:</strong> " . htmlspecialchars($logoPath) . "</p>";
                echo "<p><strong>Logo existuje:</strong> " . (file_exists($logoPath) ? "✅ ANO" : "❌ NE") . "</p>";
            }
            
            if (isset($companyInfo['signature'])) {
                $signaturePath = __DIR__ . '/web/uploads/signature/' . $companyInfo['signature'];
                echo "<p><strong>Podpis:</strong> " . htmlspecialchars($companyInfo['signature'] ?? 'Není nastaveno') . "</p>";
                echo "<p><strong>Cesta k podpisu:</strong> " . htmlspecialchars($signaturePath) . "</p>";
                echo "<p><strong>Podpis existuje:</strong> " . (file_exists($signaturePath) ? "✅ ANO" : "❌ NE") . "</p>";
            }
            
            // Kontrola adresářů pro uploads
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
                
                echo "<p><strong>$dir:</strong> ";
                echo $exists ? "✅ Existuje" : "❌ Neexistuje";
                if ($exists) {
                    echo " | Zapisovatelný: " . ($writable ? "✅ ANO" : "❌ NE");
                }
                echo "</p>";
                
                if (!$exists) {
                    echo "<p class='error'>➡️ Vytvořte adresář: mkdir -p $fullPath && chmod 755 $fullPath</p>";
                }
            }
            
        } else {
            echo "<div class='error'>❌ Tabulka company_info je prázdná</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Chyba při načítání company_info: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
    // Zkouška CompanyManager třídy
    echo "<h2>🏢 Test CompanyManager třídy:</h2>";
    
    try {
        $companyManager = $container->getByType(App\Model\CompanyManager::class);
        $company = $companyManager->getCompanyInfo();
        
        if ($company) {
            echo "<div class='success'>✅ CompanyManager funguje</div>";
            echo "<pre>";
            echo "Název: " . htmlspecialchars($company->name ?? 'N/A') . "\n";
            echo "Logo: " . htmlspecialchars($company->logo ?? 'N/A') . "\n";
            echo "Podpis: " . htmlspecialchars($company->signature ?? 'N/A') . "\n";
            echo "IČO: " . htmlspecialchars($company->ic ?? 'N/A') . "\n";
            echo "DIČ: " . htmlspecialchars($company->dic ?? 'N/A') . "\n";
            echo "</pre>";
        } else {
            echo "<div class='error'>❌ CompanyManager nevrací data</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Chyba v CompanyManager: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<p>Možná aplikace hledá tabulku 'companies' místo 'company_info'</p>";
    }
    
    // Kontrola, jestli existuje tabulka companies
    echo "<h2>🔍 Kontrola tabulky 'companies':</h2>";
    try {
        $database->query("SELECT 1 FROM companies LIMIT 1");
        echo "<div class='success'>✅ Tabulka 'companies' existuje</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Tabulka 'companies' neexistuje: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<p>➡️ Aplikace pravděpodobně hledá 'companies' místo 'company_info'</p>";
    }
    
} catch (Exception $e) {
    echo "<h1 style='color:red;'>❌ Chyba</h1>";
    echo "<p>Chyba: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>⬅️ Zpět na aplikaci</a> | <a href='db_check.php'>🔧 DB test</a></p>";
?>