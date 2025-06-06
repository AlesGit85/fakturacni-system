<?php
/**
 * Finální test upload funkce
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🧪 Finální test upload</h1>";

// Test upload
if ($_POST && isset($_FILES['test_file'])) {
    $file = $_FILES['test_file'];
    
    echo "<h2>📤 Zpracovávám upload:</h2>";
    echo "<ul>";
    echo "<li><strong>Název:</strong> " . htmlspecialchars($file['name']) . "</li>";
    echo "<li><strong>Velikost:</strong> " . $file['size'] . " bytů</li>";
    echo "<li><strong>Typ:</strong> " . htmlspecialchars($file['type']) . "</li>";
    echo "<li><strong>Error:</strong> " . $file['error'] . "</li>";
    echo "</ul>";
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Test různých upload cest
        $uploadPaths = [
            'Cesta 1 (www/uploads/logo)' => __DIR__ . '/www/uploads/logo/',
            'Cesta 2 (uploads/logo)' => __DIR__ . '/uploads/logo/',
            'Cesta 3 (absolutní)' => dirname(__DIR__) . '/www/uploads/logo/'
        ];
        
        foreach ($uploadPaths as $label => $path) {
            echo "<h3>🔄 Test: $label</h3>";
            echo "<p>📁 Cesta: $path</p>";
            
            // Vytvoření složky pokud neexistuje
            if (!is_dir($path)) {
                if (mkdir($path, 0755, true)) {
                    echo "<p>✅ Složka vytvořena</p>";
                } else {
                    echo "<p>❌ Nepodařilo se vytvořit složku</p>";
                    continue;
                }
            } else {
                echo "<p>✅ Složka existuje</p>";
            }
            
            // Test uploadu
            $fileName = 'test_' . time() . '_' . $file['name'];
            $uploadFile = $path . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                echo "<p>✅ <strong>Upload úspěšný!</strong></p>";
                echo "<p>📁 Uloženo: $uploadFile</p>";
                
                // Test přístupu přes web
                $webPaths = [
                    '/www/uploads/logo/' . $fileName,
                    '/uploads/logo/' . $fileName
                ];
                
                foreach ($webPaths as $webPath) {
                    echo "<p>🌐 Test URL: <a href='$webPath' target='_blank'>$webPath</a>";
                    echo " <img src='$webPath' style='max-height: 50px; margin-left: 10px;' onerror='this.style.display=\"none\"; this.nextSibling.style.display=\"inline\";'>";
                    echo "<span style='display: none; color: red;'>❌</span></p>";
                }
                
                // Ukončíme po prvním úspěšném uploadu
                break;
                
            } else {
                echo "<p>❌ Upload selhal</p>";
            }
        }
    } else {
        echo "<p>❌ <strong>Upload error:</strong> " . $file['error'] . "</p>";
    }
}

// Test kompanyManager
echo "<h2>🏢 Test CompanyManager:</h2>";
try {
    require __DIR__ . '/vendor/autoload.php';
    $bootstrap = new App\Bootstrap;
    $container = $bootstrap->bootWebApplication();
    $companyManager = $container->getByType('App\Model\CompanyManager');
    
    echo "<p>✅ CompanyManager načten</p>";
    
    // Test save funkce
    if ($_POST && isset($_POST['test_save'])) {
        $testData = [
            'logo' => 'test_logo.png',
            'name' => 'Test firma'
        ];
        
        $companyManager->save($testData);
        echo "<p>✅ Test save proběhl</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Chyba: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<hr>
<h2>🧪 Test formulář:</h2>
<form method="POST" enctype="multipart/form-data" style="background: #f8f9fa; padding: 20px; border-radius: 5px;">
    <p>
        <label>Testovací logo:</label><br>
        <input type="file" name="test_file" accept="image/*" required>
    </p>
    <p>
        <button type="submit" style="background: #B1D235; border: none; padding: 10px 20px; border-radius: 5px;">
            🚀 Test upload
        </button>
    </p>
</form>

<form method="POST" style="margin-top: 20px;">
    <input type="hidden" name="test_save" value="1">
    <button type="submit" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 5px;">
        🧪 Test save
    </button>
</form>

<p><a href="/Settings/default">⚙️ Jít do Nastavení</a> | <a href="/">🏠 Úvod</a></p>