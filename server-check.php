<?php
/**
 * QRdoklad - Diagnostika serveru
 * Zkontroluje požadavky a konfiguraci serveru před instalací
 */

// Bezpečnost - smazat tento soubor po dokončení!
$currentTime = time();
$maxAge = 3600; // 1 hodina
if (!isset($_GET['allow']) || $_GET['allow'] !== 'diagnostic') {
    die('🔒 Přístup odepřen. Použijte: server-check.php?allow=diagnostic');
}

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QRdoklad - Diagnostika serveru</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #B1D235 0%, #95B11F 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: #212529;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }
        .section-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
            font-weight: bold;
            font-size: 1.2em;
            color: #212529;
        }
        .section-content {
            padding: 20px;
        }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-info { color: #17a2b8; font-weight: bold; }
        
        .check-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .check-item:last-child {
            border-bottom: none;
        }
        .check-label {
            flex: 1;
        }
        .check-value {
            font-weight: bold;
            min-width: 100px;
            text-align: right;
        }
        
        .summary {
            background: #f8f9fa;
            border: 2px solid #B1D235;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .summary h2 {
            margin-top: 0;
            color: #212529;
        }
        
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .info-box {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 QRdoklad Server Check</h1>
            <p>Diagnostika serveru pro inv.allimedia.cz</p>
        </div>
        
        <div class="content">
            <?php
            // Inicializace výsledků
            $results = [
                'php_version' => version_compare(PHP_VERSION, '8.0', '>='),
                'extensions' => [],
                'permissions' => [],
                'settings' => [],
                'overall' => true
            ];
            
            // Kontrola PHP verze
            $phpOk = version_compare(PHP_VERSION, '8.0', '>=');
            $results['overall'] = $results['overall'] && $phpOk;
            ?>
            
            <!-- Souhrn -->
            <div class="summary">
                <h2>📊 Souhrn diagnostiky</h2>
                <div class="check-item">
                    <span class="check-label">Celkový stav serveru:</span>
                    <span class="check-value <?php echo $results['overall'] ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $results['overall'] ? '✅ PŘIPRAVEN' : '❌ VYŽADUJE ÚPRAVY'; ?>
                    </span>
                </div>
                <div class="check-item">
                    <span class="check-label">Datum kontroly:</span>
                    <span class="check-value status-info"><?php echo date('d.m.Y H:i:s'); ?></span>
                </div>
                <div class="check-item">
                    <span class="check-label">Server:</span>
                    <span class="check-value status-info"><?php echo $_SERVER['HTTP_HOST'] ?? 'neznámý'; ?></span>
                </div>
            </div>

            <!-- PHP Verze -->
            <div class="section">
                <div class="section-header">🐘 PHP Konfigurace</div>
                <div class="section-content">
                    <div class="check-item">
                        <span class="check-label">PHP Verze:</span>
                        <span class="check-value <?php echo $phpOk ? 'status-ok' : 'status-error'; ?>">
                            <?php echo PHP_VERSION; ?> <?php echo $phpOk ? '✅' : '❌ (min. 8.0)'; ?>
                        </span>
                    </div>
                    <div class="check-item">
                        <span class="check-label">SAPI:</span>
                        <span class="check-value status-info"><?php echo php_sapi_name(); ?></span>
                    </div>
                    <div class="check-item">
                        <span class="check-label">Operační systém:</span>
                        <span class="check-value status-info"><?php echo PHP_OS; ?></span>
                    </div>
                </div>
            </div>

            <!-- PHP Rozšíření -->
            <div class="section">
                <div class="section-header">🔧 PHP Rozšíření</div>
                <div class="section-content">
                    <?php
                    $requiredExtensions = [
                        'pdo' => 'Databázové připojení',
                        'pdo_mysql' => 'MySQL databáze',
                        'gd' => 'Zpracování obrázků',
                        'curl' => 'HTTP požadavky',
                        'mbstring' => 'Unicode řetězce',
                        'json' => 'JSON zpracování',
                        'openssl' => 'Šifrování',
                        'zip' => 'Práce s ZIP soubory',
                        'xml' => 'XML zpracování',
                        'dom' => 'DOM manipulace',
                        'fileinfo' => 'Detekce typu souborů'
                    ];
                    
                    foreach ($requiredExtensions as $ext => $desc) {
                        $loaded = extension_loaded($ext);
                        $results['extensions'][$ext] = $loaded;
                        $results['overall'] = $results['overall'] && $loaded;
                        ?>
                        <div class="check-item">
                            <span class="check-label"><?php echo $ext; ?> (<?php echo $desc; ?>):</span>
                            <span class="check-value <?php echo $loaded ? 'status-ok' : 'status-error'; ?>">
                                <?php echo $loaded ? '✅ Dostupné' : '❌ Chybí'; ?>
                            </span>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>

            <!-- PHP Nastavení -->
            <div class="section">
                <div class="section-header">⚙️ PHP Nastavení</div>
                <div class="section-content">
                    <?php
                    function parseSize($size) {
                        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
                        $size = preg_replace('/[^0-9\.]/', '', $size);
                        if ($unit) {
                            $size *= pow(1024, stripos('bkmgtpezy', $unit[0]));
                        }
                        return (int) $size;
                    }
                    
                    function formatBytes($bytes) {
                        $units = ['B', 'KB', 'MB', 'GB'];
                        $bytes = max($bytes, 0);
                        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
                        $pow = min($pow, count($units) - 1);
                        $bytes /= pow(1024, $pow);
                        return round($bytes, 2) . ' ' . $units[$pow];
                    }
                    
                    $settings = [
                        'upload_max_filesize' => ['Maximální velikost souboru', ini_get('upload_max_filesize')],
                        'post_max_size' => ['Maximální velikost POST', ini_get('post_max_size')],
                        'memory_limit' => ['Memory limit', ini_get('memory_limit')],
                        'max_execution_time' => ['Maximální doba běhu', ini_get('max_execution_time') . 's'],
                        'display_errors' => ['Zobrazování chyb', ini_get('display_errors') ? 'Zapnuto ⚠️' : 'Vypnuto ✅'],
                        'allow_url_fopen' => ['Vzdálené soubory', ini_get('allow_url_fopen') ? 'Povoleno' : 'Zakázano'],
                        'session.cookie_secure' => ['Secure cookies', ini_get('session.cookie_secure') ? 'Zapnuto ✅' : 'Vypnuto ⚠️'],
                        'date.timezone' => ['Časová zóna', ini_get('date.timezone') ?: 'NENÍ NASTAVENA ⚠️']
                    ];
                    
                    foreach ($settings as $key => $data) {
                        ?>
                        <div class="check-item">
                            <span class="check-label"><?php echo $data[0]; ?>:</span>
                            <span class="check-value status-info"><?php echo $data[1]; ?></span>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>

            <!-- Oprávnění adresářů -->
            <div class="section">
                <div class="section-header">📁 Oprávnění adresářů</div>
                <div class="section-content">
                    <?php
                    $directories = [
                        'temp' => 'Dočasné soubory',
                        'log' => 'Log soubory',
                        'www/uploads' => 'Nahrané soubory',
                        'www/css' => 'CSS soubory',
                        'www/js' => 'JavaScript soubory',
                        'config' => 'Konfigurační soubory'
                    ];
                    
                    foreach ($directories as $dir => $desc) {
                        $exists = is_dir($dir);
                        $writable = $exists ? is_writable($dir) : false;
                        $readable = $exists ? is_readable($dir) : false;
                        
                        $status = 'status-error';
                        $statusText = '❌ Neexistuje';
                        
                        if ($exists && $writable && $readable) {
                            $status = 'status-ok';
                            $statusText = '✅ OK (čtení + zápis)';
                        } elseif ($exists && $readable) {
                            $status = 'status-warning';
                            $statusText = '⚠️ Pouze čtení';
                        } elseif ($exists) {
                            $status = 'status-error';
                            $statusText = '❌ Bez oprávnění';
                        }
                        
                        $results['permissions'][$dir] = $exists && $writable && $readable;
                        if (in_array($dir, ['temp', 'log', 'www/uploads'])) {
                            $results['overall'] = $results['overall'] && ($exists && $writable);
                        }
                        ?>
                        <div class="check-item">
                            <span class="check-label"><?php echo $dir; ?> (<?php echo $desc; ?>):</span>
                            <span class="check-value <?php echo $status; ?>"><?php echo $statusText; ?></span>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>

            <!-- Server informace -->
            <div class="section">
                <div class="section-header">🌐 Server informace</div>
                <div class="section-content">
                    <div class="check-item">
                        <span class="check-label">Web server:</span>
                        <span class="check-value status-info"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Neznámý'; ?></span>
                    </div>
                    <div class="check-item">
                        <span class="check-label">Document root:</span>
                        <span class="check-value status-info"><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Neznámý'; ?></span>
                    </div>
                    <div class="check-item">
                        <span class="check-label">HTTPS:</span>
                        <span class="check-value <?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'status-ok' : 'status-warning'; ?>">
                            <?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? '✅ Aktivní' : '⚠️ Neaktivní'; ?>
                        </span>
                    </div>
                    <div class="check-item">
                        <span class="check-label">Port:</span>
                        <span class="check-value status-info"><?php echo $_SERVER['SERVER_PORT'] ?? 'Neznámý'; ?></span>
                    </div>
                </div>
            </div>

            <!-- Doporučení -->
            <div class="section">
                <div class="section-header">💡 Doporučení a další kroky</div>
                <div class="section-content">
                    <?php if (!$results['overall']): ?>
                        <div class="warning-box">
                            <strong>⚠️ Pozor!</strong> Server nevyhovuje všem požadavkům. Opravte problémy označené ❌ před pokračováním.
                        </div>
                    <?php else: ?>
                        <div class="info-box">
                            <strong>✅ Výborně!</strong> Server vyhovuje všem požadavkům. Můžete pokračovat k instalaci.
                        </div>
                    <?php endif; ?>
                    
                    <h3>Následující kroky:</h3>
                    <ol>
                        <li><strong>Smazat tento soubor</strong> po dokončení diagnostiky</li>
                        <li>Nastavit HTTPS pokud není aktivní</li>
                        <li>Vytvořit databázi MySQL</li>
                        <li>Nakonfigurovat produkční prostředí</li>
                        <li>Spustit instalátor aplikace</li>
                    </ol>
                    
                    <h3>Kontakt pro podporu:</h3>
                    <p>📧 <strong>info@allimedia.cz</strong><br>
                    🌐 <strong>inv.allimedia.cz</strong></p>
                </div>
            </div>

            <!-- Debug informace -->
            <details style="margin-top: 30px;">
                <summary style="cursor: pointer; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                    🔧 <strong>Debug informace (klikněte pro rozbalení)</strong>
                </summary>
                <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px; font-family: monospace; font-size: 12px;">
                    <strong>PHP Info:</strong><br>
                    <?php
                    echo "PHP Version: " . phpversion() . "\n";
                    echo "Zend Version: " . zend_version() . "\n";
                    echo "Current working directory: " . getcwd() . "\n";
                    echo "Include path: " . get_include_path() . "\n";
                    echo "Loaded extensions: " . implode(', ', get_loaded_extensions()) . "\n";
                    ?>
                </div>
            </details>
        </div>
    </div>

    <script>
        // Automatické obnovení každých 5 minut
        setTimeout(function() {
            if (confirm('Obnovit diagnostiku?')) {
                location.reload();
            }
        }, 300000);
    </script>
</body>
</html>