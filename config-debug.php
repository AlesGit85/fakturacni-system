<?php
/**
 * Debug Nette konfigurace
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>⚙️ Debug Nette konfigurace</h1>";

try {
    // 1. Autoloader
    echo "<h2>1. Autoloader:</h2>";
    require __DIR__ . '/vendor/autoload.php';
    echo "<p>✅ Autoloader načten</p>";
    
    // 2. Kontrola config souborů
    echo "<h2>2. Kontrola config souborů:</h2>";
    $configFiles = [
        'config/common.neon' => 'Hlavní konfigurace',
        'config/local.neon' => 'Lokální konfigurace',
        'config/services.neon' => 'Služby'
    ];
    
    foreach ($configFiles as $file => $desc) {
        if (file_exists($file)) {
            echo "<p>✅ <strong>$file</strong> ($desc) - " . filesize($file) . " bytů</p>";
            
            // Ukázka obsahu (první 3 řádky)
            $content = file_get_contents($file);
            $lines = explode("\n", $content);
            echo "<details><summary>První řádky souboru:</summary>";
            echo "<pre>";
            for ($i = 0; $i < min(10, count($lines)); $i++) {
                echo htmlspecialchars($lines[$i]) . "\n";
            }
            echo "</pre></details>";
            
        } else {
            echo "<p>❌ <strong>$file</strong> - neexistuje</p>";
        }
    }
    
    // 3. Test vytvoření Bootstrap (bez spuštění)
    echo "<h2>3. Test Bootstrap:</h2>";
    $bootstrap = new App\Bootstrap;
    echo "<p>✅ Bootstrap instance vytvořena</p>";
    
    // 4. Test Configurator (reflection)
    echo "<h2>4. Test Configurator:</h2>";
    $reflection = new ReflectionClass($bootstrap);
    $tempMethod = $reflection->getMethod('bootWebApplication');
    
    // Pokusíme se získat configurator info pomocí reflection
    try {
        // Zkusíme vytvořit Configurator přímo
        $configurator = new Nette\Bootstrap\Configurator;
        $configurator->setTempDirectory(__DIR__ . '/temp');
        
        echo "<p>✅ Configurator vytvořen</p>";
        echo "<p>📁 Temp adresář: " . __DIR__ . '/temp</p>';
        
        // Test přidání config souborů
        $configDir = __DIR__ . '/config';
        echo "<p>📁 Config adresář: $configDir</p>";
        
        if (file_exists($configDir . '/common.neon')) {
            echo "<p>✅ Přidávám common.neon</p>";
            $configurator->addConfig($configDir . '/common.neon');
        }
        
        if (file_exists($configDir . '/services.neon')) {
            echo "<p>✅ Přidávám services.neon</p>";
            $configurator->addConfig($configDir . '/services.neon');
        }
        
        if (file_exists($configDir . '/local.neon')) {
            echo "<p>✅ Přidávám local.neon</p>";
            $configurator->addConfig($configDir . '/local.neon');
        }
        
        echo "<p>🔄 Pokusím se vytvořit container...</p>";
        $container = $configurator->createContainer();
        echo "<p>✅ Container vytvořen!</p>";
        
        // Test databázové služby
        echo "<h2>5. Test databázové služby:</h2>";
        $database = $container->getByType(Nette\Database\Explorer::class);
        echo "<p>✅ Database service získán</p>";
        
        // Test připojení
        $connection = $database->getConnection();
        echo "<p>✅ Database connection získán</p>";
        
        // Test skutečného dotazu
        $result = $database->query("SELECT 1 as test")->fetch();
        echo "<p>✅ Test dotaz proběhl: " . json_encode($result) . "</p>";
        
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>🎉 KONFIGURACE FUNGUJE!</h3>";
        echo "<p>Problém není v konfiguraci databáze.</p>";
        echo "<p>Nejspíš je problém v cache nebo v některé části aplikace.</p>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
        echo "<h3>❌ CHYBA V KONFIGURACI:</h3>";
        echo "<p><strong>Typ:</strong> " . get_class($e) . "</p>";
        echo "<p><strong>Zpráva:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Soubor:</strong> " . $e->getFile() . " (řádek " . $e->getLine() . ")</p>";
        echo "<details><summary>Stack trace</summary>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</details>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ OBECNÁ CHYBA:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h2>🧹 Zkuste také:</h2>";
echo "<ul>";
echo "<li><a href='clear-cache.php'>Vyčistit cache</a> (vytvoříme další skript)</li>";
echo "<li><a href='/'>Zkusit hlavní aplikaci</a></li>";
echo "</ul>";
?>