<?php
/**
 * Debug ARES AJAX endpointu
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>📡 Debug ARES AJAX</h1>";

// Simulace AJAX požadavku
$testIco = $_GET['ico'] ?? '87894912';

echo "<h2>1. Test manuálního volání:</h2>";
echo "<p>🔗 Test URL: <a href='/Clients/loadFromAres?ico=$testIco' target='_blank'>/Clients/loadFromAres?ico=$testIco</a></p>";

echo "<h2>2. Test přes cURL (simulace AJAX):</h2>";

try {
    $url = "https://inv.allimedia.cz/Clients/loadFromAres?ico=$testIco";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'X-Requested-With: XMLHttpRequest'  // Simulace AJAX
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>HTTP kód:</strong> $httpCode</p>";
    echo "<p><strong>Délka odpovědi:</strong> " . strlen($response) . " znaků</p>";
    
    echo "<h3>Odpověď:</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 400px; overflow: auto;'>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    echo "</div>";
    
    // Test JSON
    $jsonData = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p>✅ <strong>Validní JSON!</strong></p>";
        echo "<h4>Parsovaná data:</h4>";
        echo "<pre>" . htmlspecialchars(json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
    } else {
        echo "<p>❌ <strong>Není validní JSON:</strong> " . json_last_error_msg() . "</p>";
        echo "<p>🔍 <strong>Pravděpodobně dostáváme HTML error stránku místo JSON!</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Chyba: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>3. Test JavaScript konzole:</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
echo "<h4>🔧 V prohlížeči:</h4>";
echo "<ol>";
echo "<li>Jděte na stránku <strong>Přidat klienta</strong></li>";
echo "<li>Otevřete <strong>Developer Tools</strong> (F12)</li>";
echo "<li>Klikněte na <strong>Console</strong></li>";
echo "<li>Zadejte IČO a klikněte <strong>Načíst z ARES</strong></li>";
echo "<li>Podívejte se na <strong>Network tab</strong> - jaký request se posílá</li>";
echo "<li>Zkontrolujte <strong>Response</strong> - je to JSON nebo HTML?</li>";
echo "</ol>";
echo "</div>";
?>

<h2>🧪 Test formulář:</h2>
<form method="GET" style="background: #f8f9fa; padding: 20px; border-radius: 5px;">
    <p>
        <label>IČO:</label><br>
        <input type="text" name="ico" value="<?php echo htmlspecialchars($testIco); ?>" style="padding: 8px; width: 200px;">
    </p>
    <p>
        <button type="submit" style="background: #B1D235; border: none; padding: 10px 20px; border-radius: 5px;">
            🔍 Test ARES
        </button>
    </p>
</form>

<p><a href="/Clients/add">👥 Přidat klienta</a> | <a href="/">🏠 Úvod</a></p>