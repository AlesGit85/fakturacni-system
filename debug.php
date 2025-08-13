<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>QRdoklad Debug - Detailní</h2>";

try {
    require_once 'vendor/autoload.php';
    echo "✅ Autoload OK<br>";
    
    echo "🔍 Testuju Bootstrap konstruktor...<br>";
    $bootstrap = new App\Bootstrap;
    echo "✅ Bootstrap konstruktor OK<br>";
    
    echo "🔍 Testuju bootWebApplication...<br>";
    $container = $bootstrap->bootWebApplication();
    echo "✅ Bootstrap boot OK<br>";
    
} catch (ParseError $e) {
    echo "❌ SYNTAX CHYBA: " . $e->getMessage() . "<br>";
    echo "Soubor: " . $e->getFile() . " řádek " . $e->getLine() . "<br>";
} catch (Exception $e) {
    echo "❌ CHYBA: " . $e->getMessage() . "<br>";
    echo "Soubor: " . $e->getFile() . " řádek " . $e->getLine() . "<br>";
    echo "<h4>Stack trace:</h4>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "<br>";
    echo "Soubor: " . $e->getFile() . " řádek " . $e->getLine() . "<br>";
    echo "<h4>Stack trace:</h4>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>Config kontrola:</h3>";

// Zkontroluj jestli dev.neon má správnou syntax
echo "<strong>common.neon includes:</strong><br>";
if (file_exists('config/common.neon')) {
    $content = file_get_contents('config/common.neon');
    echo "<pre>" . htmlspecialchars(substr($content, 0, 500)) . "</pre>";
}

echo "<strong>dev.neon heslo:</strong><br>";
if (file_exists('config/dev.neon')) {
    $content = file_get_contents('config/dev.neon');
    if (strpos($content, 'HESLO_PRO_DEV') !== false) {
        echo "❌ Heslo není nastavené!<br>";
    } else {
        echo "✅ Heslo je nastavené<br>";
    }
}
?>