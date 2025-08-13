<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Full Page Output</h2>";

try {
    require_once 'vendor/autoload.php';
    
    $bootstrap = new App\Bootstrap;
    $container = $bootstrap->bootWebApplication();
    
    // Test skutečného běhu aplikace
    $_SERVER['HTTP_HOST'] = 'dev.qrdoklad.cz';
    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    $application = $container->getByType(Nette\Application\Application::class);
    
    // Zachytit kompletní výstup
    ob_start();
    $application->run();
    $output = ob_get_clean();
    
    echo "<h3>Kompletní HTML výstup:</h3>";
    echo "<textarea style='width:100%; height:400px;'>" . htmlspecialchars($output) . "</textarea>";
    
    echo "<h3>Výstup jako HTML:</h3>";
    echo "<iframe style='width:100%; height:600px; border:1px solid #ccc;' srcdoc='" . htmlspecialchars($output) . "'></iframe>";
    
} catch (Exception $e) {
    echo "❌ CHYBA: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>