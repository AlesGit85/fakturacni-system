<?php
/**
 * Debug cest k logu po přenahrání
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🖼️ Debug cest k logu</h1>";

try {
    require __DIR__ . '/vendor/autoload.php';
    
    $bootstrap = new App\Bootstrap;
    $container = $bootstrap->bootWebApplication();
    $companyManager = $container->getByType('App\Model\CompanyManager');
    
    echo "<h2>1. Test firemních údajů:</h2>";
    $company = $companyManager->getCompanyInfo();
    
    if ($company && $company->logo) {
        echo "<p>✅ Logo v databázi: <strong>" . htmlspecialchars($company->logo) . "</strong></p>";
        
        echo "<h2>2. Test fyzických cest:</h2>";
        $possiblePaths = [
            'www/uploads/logo/' . $company->logo => '/web/www/uploads/logo/',
            'uploads/logo/' . $company->logo => '/web/uploads/logo/',
        ];
        
        foreach ($possiblePaths as $webPath => $diskPath) {
            $fullDiskPath = __DIR__ . '/' . $webPath;
            echo "<p><strong>Cesta:</strong> $webPath</p>";
            echo "<ul>";
            echo "<li>📁 Disk: $fullDiskPath</li>";
            echo "<li>🗂️ Existuje: " . (file_exists($fullDiskPath) ? '✅ ANO' : '❌ NE') . "</li>";
            if (file_exists($fullDiskPath)) {
                echo "<li>📏 Velikost: " . filesize($fullDiskPath) . " bytů</li>";
            }
            echo "</ul>";
        }
        
        echo "<h2>3. Test webových URL:</h2>";
        $webUrls = [
            '/uploads/logo/' . $company->logo,
            '/www/uploads/logo/' . $company->logo,
        ];
        
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
        foreach ($webUrls as $url) {
            echo "<p>🌐 <a href='$url' target='_blank'>$url</a> ";
            echo "<img src='$url' style='max-height: 50px; margin-left: 10px; border: 1px solid #ccc;' onerror='this.style.display=\"none\"; this.nextSibling.style.display=\"inline\";'>";
            echo "<span style='display: none; color: red;'>❌ Nenačítá se</span></p>";
        }
        echo "</div>";
        
        echo "<h2>4. Kontrola šablony:</h2>";
        echo "<p>V Latte šabloně by mělo být:</p>";
        echo "<pre style='background: #f8f9fa; padding: 10px;'>";
        echo htmlspecialchars('<img src="{$basePath}/uploads/logo/{$company->logo}" alt="Logo">');
        echo "</pre>";
        
        echo "<p>Nebo:</p>";
        echo "<pre style='background: #f8f9fa; padding: 10px;'>";
        echo htmlspecialchars('<img src="/uploads/logo/{$company->logo}" alt="Logo">');
        echo "</pre>";
        
    } else {
        echo "<p>❌ Logo není v databázi nebo company údaje chybí</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ CHYBA:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='/Settings/default'>⚙️ Jít do Nastavení</a> | <a href='/'>🏠 Úvod</a></p>";
?>