<?php
/**
 * Debug ARES API problému
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Debug ARES API</h1>";

// Test IČO
$testIco = '87894912'; // Vaše IČO z obrázku

echo "<h2>1. Test základních funkcí:</h2>";

// Test cURL
if (function_exists('curl_init')) {
    echo "<p>✅ cURL je dostupné</p>";
} else {
    echo "<p>❌ cURL není dostupné</p>";
}

// Test allow_url_fopen
if (ini_get('allow_url_fopen')) {
    echo "<p>✅ allow_url_fopen je povoleno</p>";
} else {
    echo "<p>❌ allow_url_fopen je zakázáno</p>";
}

echo "<h2>2. Test ARES endpointů:</h2>";

// Různé ARES API endpointy
$aresEndpoints = [
    'Nový ARES' => "https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/$testIco",
    'ARES Basic' => "http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=$testIco",
    'ARES XML' => "https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=$testIco",
    'ARES JSON (nový)' => "https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/vyhledat?ico=$testIco"
];

foreach ($aresEndpoints as $name => $url) {
    echo "<h3>🔄 Test: $name</h3>";
    echo "<p>🌐 URL: <a href='$url' target='_blank'>$url</a></p>";
    
    try {
        // Test s cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'QRdoklad/1.0');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        echo "<ul>";
        echo "<li><strong>HTTP kód:</strong> $httpCode</li>";
        
        if ($error) {
            echo "<li><strong>cURL chyba:</strong> " . htmlspecialchars($error) . "</li>";
        }
        
        if ($response) {
            echo "<li><strong>Délka odpovědi:</strong> " . strlen($response) . " znaků</li>";
            echo "<li><strong>Začátek odpovědi:</strong> " . htmlspecialchars(substr($response, 0, 200)) . "...</li>";
            
            // Test jestli je to JSON
            $jsonData = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "<li>✅ <strong>Validní JSON</strong></li>";
                if (isset($jsonData['ico']) || isset($jsonData['obchodniJmeno'])) {
                    echo "<li>✅ <strong>Obsahuje firemní data</strong></li>";
                }
            } else {
                echo "<li>❌ <strong>Není JSON:</strong> " . json_last_error_msg() . "</li>";
            }
        } else {
            echo "<li>❌ <strong>Prázdná odpověď</strong></li>";
        }
        echo "</ul>";
        
        if ($httpCode === 200 && $response && json_last_error() === JSON_ERROR_NONE) {
            echo "<p>🎯 <strong>TENTO ENDPOINT FUNGUJE!</strong></p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Chyba: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<hr>";
}

echo "<h2>3. Test současného AresService:</h2>";

try {
    require __DIR__ . '/vendor/autoload.php';
    
    $bootstrap = new App\Bootstrap;
    $container = $bootstrap->bootWebApplication();
    $aresService = $container->getByType('App\Model\AresService');
    
    echo "<p>✅ AresService načten</p>";
    
    echo "<p>🔄 Testuji getCompanyDataByIco('$testIco')...</p>";
    
    $companyData = $aresService->getCompanyDataByIco($testIco);
    
    echo "<h4>Výsledek:</h4>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
    echo htmlspecialchars(json_encode($companyData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ CHYBA v AresService:</h4>";
    echo "<p><strong>Typ:</strong> " . get_class($e) . "</p>";
    echo "<p><strong>Zpráva:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Soubor:</strong> " . $e->getFile() . " (řádek " . $e->getLine() . ")</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h2>💡 Možná řešení:</h2>";
echo "<ol>";
echo "<li>Aktualizovat ARES endpoint v AresService</li>";
echo "<li>Změnit HTTP hlavičky (User-Agent)</li>";
echo "<li>Použít backup API nebo testovací data</li>";
echo "</ol>";

echo "<p><a href='/Clients/add'>👥 Test v přidání klienta</a> | <a href='/'>🏠 Úvod</a></p>";
?>