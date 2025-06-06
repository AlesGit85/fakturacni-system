<?php
/**
 * QRdoklad - Úklidový skript
 * Smaže všechny instalační soubory
 */

// Bezpečnostní kontrola
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'delete-install-files') {
    die('🔒 Přístup odepřen. Použijte: cleanup.php?confirm=delete-install-files');
}

// Kontrola, zda je aplikace nainstalovaná
if (!file_exists('config/installed.lock')) {
    die('❌ Aplikace není nainstalována. Úklid byl zrušen.');
}

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QRdoklad - Úklid instalačních souborů</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #B1D235 0%, #95B11F 100%);
            min-height: 100vh;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .header {
            background: #212529;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 40px;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .btn {
            background: #B1D235;
            color: #212529;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn:hover {
            background: #95B11F;
        }
        .text-center { text-align: center; }
        .file-list {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧹 QRdoklad Úklid</h1>
            <p>Smazání instalačních souborů</p>
        </div>
        
        <div class="content">
            <?php
            $filesToDelete = [
                'install.php' => 'Hlavní instalátor',
                'server-check.php' => 'Diagnostický skript',
                'database.sql' => 'SQL skript databáze',
                'cleanup.php' => 'Tento úklidový skript'
            ];

            $action = $_GET['action'] ?? 'show';

            if ($action === 'delete') {
                echo '<div class="alert alert-success">';
                echo '<h3>🗑️ Mazání souborů...</h3>';
                
                $deleted = [];
                $notFound = [];
                $failed = [];
                
                foreach ($filesToDelete as $file => $desc) {
                    if (file_exists($file)) {
                        if (unlink($file)) {
                            $deleted[] = "$file ($desc)";
                        } else {
                            $failed[] = "$file ($desc)";
                        }
                    } else {
                        $notFound[] = "$file ($desc)";
                    }
                }
                
                if (!empty($deleted)) {
                    echo '<h4>✅ Úspěšně smazáno:</h4>';
                    echo '<ul>';
                    foreach ($deleted as $file) {
                        echo '<li>' . htmlspecialchars($file) . '</li>';
                    }
                    echo '</ul>';
                }
                
                if (!empty($notFound)) {
                    echo '<h4>ℹ️ Soubory nebyly nalezeny:</h4>';
                    echo '<ul>';
                    foreach ($notFound as $file) {
                        echo '<li>' . htmlspecialchars($file) . '</li>';
                    }
                    echo '</ul>';
                }
                
                if (!empty($failed)) {
                    echo '</div><div class="alert alert-danger">';
                    echo '<h4>❌ Nepodařilo se smazat:</h4>';
                    echo '<ul>';
                    foreach ($failed as $file) {
                        echo '<li>' . htmlspecialchars($file) . '</li>';
                    }
                    echo '</ul>';
                    echo '<p>Smažte tyto soubory ručně přes FTP nebo správce souborů.</p>';
                }
                
                echo '</div>';
                
                if (empty($failed)) {
                    echo '<div class="text-center">';
                    echo '<h3>🎉 Úklid dokončen!</h3>';
                    echo '<p>Všechny instalační soubory byly bezpečně smazány.</p>';
                    echo '<a href="/" class="btn">🏠 Přejít do aplikace</a>';
                    echo '</div>';
                }
                
            } else {
                echo '<h3>🔍 Kontrola instalačních souborů</h3>';
                
                $foundFiles = [];
                foreach ($filesToDelete as $file => $desc) {
                    if (file_exists($file)) {
                        $foundFiles[] = "$file ($desc)";
                    }
                }
                
                if (empty($foundFiles)) {
                    echo '<div class="alert alert-success">';
                    echo '<h4>✅ Výborně!</h4>';
                    echo '<p>Žádné instalační soubory nebyly nalezeny. Váš systém je již vyčištěn.</p>';
                    echo '</div>';
                    echo '<div class="text-center">';
                    echo '<a href="/" class="btn">🏠 Přejít do aplikace</a>';
                    echo '</div>';
                } else {
                    echo '<div class="alert alert-danger">';
                    echo '<h4>⚠️ Nalezeny instalační soubory:</h4>';
                    echo '<div class="file-list">';
                    foreach ($foundFiles as $file) {
                        echo '• ' . htmlspecialchars($file) . '<br>';
                    }
                    echo '</div>';
                    echo '<p><strong>Z bezpečnostních důvodů je třeba tyto soubory smazat!</strong></p>';
                    echo '</div>';
                    
                    echo '<div class="text-center">';
                    echo '<a href="?confirm=delete-install-files&action=delete" class="btn" onclick="return confirm(\'Opravdu chcete smazat všechny instalační soubory?\')">🗑️ Smazat všechny soubory</a>';
                    echo '<a href="/" class="btn">🏠 Přejít do aplikace</a>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>
</body>
</html>