<?php
/**
 * Debug a oprava basePath problému
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔗 Debug basePath</h1>";

try {
    require __DIR__ . '/vendor/autoload.php';
    
    $bootstrap = new App\Bootstrap;
    $container = $bootstrap->bootWebApplication();
    
    echo "<h2>1. Test basePath z HTTP Request:</h2>";
    
    $httpRequest = $container->getByType(Nette\Http\IRequest::class);
    $url = $httpRequest->getUrl();
    
    echo "<ul>";
    echo "<li><strong>Base URL:</strong> " . $url->getBaseUrl() . "</li>";
    echo "<li><strong>Base Path:</strong> " . $url->getBasePath() . "</li>";
    echo "<li><strong>Host:</strong> " . $url->getHost() . "</li>";
    echo "<li><strong>Path:</strong> " . $url->getPath() . "</li>";
    echo "</ul>";
    
    $basePath = $url->getBasePath();
    echo "<p>🎯 <strong>Detekovaný basePath:</strong> '$basePath'</p>";
    
    if (empty($basePath)) {
        echo "<p>⚠️ BasePath je prázdný - to může být problém!</p>";
    }
    
    echo "<h2>2. Test správné URL cesty k logu:</h2>";
    
    $companyManager = $container->getByType('App\Model\CompanyManager');
    $company = $companyManager->getCompanyInfo();
    
    if ($company && $company->logo) {
        // Různé možnosti basePath
        $basePathOptions = [
            '' => 'Prázdný basePath',
            '/www' => 'BasePath /www',
            $basePath => 'Detekovaný basePath'
        ];
        
        foreach ($basePathOptions as $testBasePath => $label) {
            $logoUrl = $testBasePath . '/uploads/logo/' . $company->logo;
            echo "<p><strong>$label:</strong> ";
            echo "<a href='$logoUrl' target='_blank'>$logoUrl</a>";
            echo " <img src='$logoUrl' style='max-height: 30px; margin-left: 10px;' onerror='this.style.display=\"none\"; this.nextSibling.style.display=\"inline\";'>";
            echo "<span style='display: none; color: red;'>❌</span>";
            echo "</p>";
        }
    }
    
    echo "<h2>3. Oprava - update Bootstrap.php:</h2>";
    
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
    echo "<h4>🔧 Řešení:</h4>";
    echo "<p>Upravte soubor <code>app/Bootstrap.php</code> - přidejte správné nastavení basePath:</p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
    echo htmlspecialchars('
// V metodě bootWebApplication() přidejte před return $container:

$httpRequest = $container->getByType(Nette\Http\IRequest::class);
$httpResponse = $container->getByType(Nette\Http\IResponse::class);

// Nastavení správného basePath pro soubory
$basePath = $httpRequest->getUrl()->getBasePath();
if (empty($basePath)) {
    $basePath = "/www";  // Náš specifický basePath
}
');
    echo "</pre>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ CHYBA:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h2>🚀 Rychlé řešení BEZ úpravy kódu:</h2>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
echo "<h4>✨ Hotfix pro okamžité zobrazení loga:</h4>";
echo "<ol>";
echo "<li>Jděte do <strong>WinSCP</strong></li>";
echo "<li>Přejděte do složky <code>/web/www/uploads/logo/</code></li>";
echo "<li><strong>Zkopírujte</strong> soubor <code>68416235e84b9.png</code></li>";
echo "<li><strong>Vložte ho</strong> do složky <code>/web/uploads/logo/</code></li>";
echo "<li>Logo se okamžitě zobrazí!</li>";
echo "</ol>";
echo "</div>";

echo "<p><a href='/Settings/default'>⚙️ Test v Nastavení</a> | <a href='/'>🏠 Zpět na úvod</a></p>";
?>