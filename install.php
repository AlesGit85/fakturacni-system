<?php
/**
 * QRdoklad Instalátor
 * Kompletní instalace aplikace pro inv.allimedia.cz
 * 
 * DŮLEŽITÉ: Tento soubor smažte po dokončení instalace!
 */

// Bezpečnostní kontrola
if (!isset($_GET['action']) || $_GET['action'] !== 'install') {
    die('🔒 Přístup odepřen. Použijte: install.php?action=install');
}

// Kontrola, zda už není aplikace nainstalovaná
if (file_exists('config/installed.lock')) {
    die('🔒 Aplikace je již nainstalována. Smažte soubor config/installed.lock pro přeinstalaci.');
}

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QRdoklad - Instalátor</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #B1D235 0%, #95B11F 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .header {
            background: #212529;
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        .content {
            padding: 40px;
        }
        .step {
            margin-bottom: 30px;
            padding: 25px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            position: relative;
        }
        .step.active {
            border-color: #B1D235;
            background: #f8fff0;
        }
        .step.success {
            border-color: #28a745;
            background: #d4edda;
        }
        .step.error {
            border-color: #dc3545;
            background: #f8d7da;
        }
        .step h3 {
            margin-bottom: 15px;
            color: #212529;
            display: flex;
            align-items: center;
        }
        .step-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
        }
        .step.success .step-icon {
            background: #28a745;
            color: white;
        }
        .step.error .step-icon {
            background: #dc3545;
            color: white;
        }
        .step.active .step-icon {
            background: #B1D235;
            color: white;
        }
        .step-icon.pending {
            background: #6c757d;
            color: white;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #212529;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #B1D235;
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
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #95B11F;
            transform: translateY(-2px);
        }
        .btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
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
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .progress {
            width: 100%;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #B1D235, #95B11F);
            transition: width 0.5s ease;
        }
        .text-center { text-align: center; }
        .hidden { display: none; }
        .log {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            max-height: 300px;
            overflow-y: auto;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 QRdoklad Instalátor</h1>
            <p>Připravujeme váš fakturační systém pro inv.allimedia.cz</p>
        </div>
        
        <div class="content">
            <?php
            $currentStep = $_GET['step'] ?? 'start';
            $errors = [];
            $warnings = [];
            $success = [];

            // Funkce pro kontrolu databáze
            function checkDatabase() {
                try {
                    $pdo = new PDO(
                        'mysql:host=localhost;dbname=c4invallimedia;charset=utf8mb4',
                        'c4alpho',
                        'nzsm_YJH6',
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                    
                    // Kontrola tabulek
                    $tables = ['users', 'company', 'clients', 'invoices', 'invoice_items', 'login_attempts', 'modules'];
                    $existingTables = [];
                    
                    foreach ($tables as $table) {
                        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                        if ($stmt->rowCount() > 0) {
                            $existingTables[] = $table;
                        }
                    }
                    
                    return [
                        'success' => true,
                        'connection' => true,
                        'tables' => $existingTables,
                        'allTables' => count($existingTables) === count($tables)
                    ];
                } catch (Exception $e) {
                    return [
                        'success' => false,
                        'connection' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }

            // Funkce pro vytvoření adresářů
            function createDirectories() {
                $dirs = [
                    'temp' => 0755,
                    'log' => 0755,
                    'www/uploads' => 0755,
                    'www/uploads/logo' => 0755,
                    'www/uploads/signature' => 0755,
                    'www/Modules' => 0755
                ];
                
                $created = [];
                $failed = [];
                
                foreach ($dirs as $dir => $perms) {
                    if (!is_dir($dir)) {
                        if (mkdir($dir, $perms, true)) {
                            $created[] = $dir;
                        } else {
                            $failed[] = $dir;
                        }
                    } else {
                        $created[] = $dir . ' (již existuje)';
                    }
                }
                
                return ['created' => $created, 'failed' => $failed];
            }

            // Zpracování kroků
            switch ($currentStep) {
                case 'start':
                    ?>
                    <div class="step active">
                        <h3><span class="step-icon">1</span>Vítejte v instalátoru QRdoklad!</h3>
                        <p>Tento instalátor nastaví váš fakturační systém a ověří všechny požadavky.</p>
                        <p><strong>Co se bude dít:</strong></p>
                        <ul style="margin: 15px 0 15px 30px;">
                            <li>Kontrola databáze a připojení</li>
                            <li>Vytvoření potřebných adresářů</li>
                            <li>Konfigurace administrátorského účtu</li>
                            <li>Nastavení základních firemních údajů</li>
                            <li>Finální bezpečnostní kontrola</li>
                        </ul>
                        <div class="text-center" style="margin-top: 30px;">
                            <a href="?action=install&step=database" class="btn">🚀 Začít instalaci</a>
                        </div>
                    </div>
                    <?php
                    break;

                case 'database':
                    $dbCheck = checkDatabase();
                    ?>
                    <div class="progress">
                        <div class="progress-bar" style="width: 20%;"></div>
                    </div>
                    
                    <div class="step <?php echo $dbCheck['success'] ? 'success' : 'error'; ?>">
                        <h3>
                            <span class="step-icon"><?php echo $dbCheck['success'] ? '✓' : '✗'; ?></span>
                            Kontrola databáze
                        </h3>
                        
                        <?php if ($dbCheck['success']): ?>
                            <div class="alert alert-success">
                                ✅ Připojení k databázi je funkční!<br>
                                📊 Nalezeno tabulek: <?php echo count($dbCheck['tables']); ?>/7<br>
                                🏗️ Tabulky: <?php echo implode(', ', $dbCheck['tables']); ?>
                            </div>
                            
                            <?php if ($dbCheck['allTables']): ?>
                                <div class="text-center">
                                    <a href="?action=install&step=directories" class="btn">📁 Pokračovat na adresáře</a>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    ⚠️ Některé tabulky chybí. Vraťte se k importu databáze.
                                </div>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="alert alert-danger">
                                ❌ Chyba připojení k databázi:<br>
                                <strong><?php echo htmlspecialchars($dbCheck['error']); ?></strong>
                            </div>
                            <div class="text-center">
                                <a href="?action=install&step=database" class="btn">🔄 Zkusit znovu</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php
                    break;

                case 'directories':
                    $dirResult = createDirectories();
                    ?>
                    <div class="progress">
                        <div class="progress-bar" style="width: 40%;"></div>
                    </div>
                    
                    <div class="step <?php echo empty($dirResult['failed']) ? 'success' : 'error'; ?>">
                        <h3>
                            <span class="step-icon"><?php echo empty($dirResult['failed']) ? '✓' : '✗'; ?></span>
                            Vytváření adresářů
                        </h3>
                        
                        <?php if (!empty($dirResult['created'])): ?>
                            <div class="alert alert-success">
                                ✅ Úspěšně vytvořené/ověřené adresáře:<br>
                                • <?php echo implode('<br>• ', $dirResult['created']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($dirResult['failed'])): ?>
                            <div class="alert alert-danger">
                                ❌ Nepodařilo se vytvořit:<br>
                                • <?php echo implode('<br>• ', $dirResult['failed']); ?>
                                <br><br>Zkontrolujte oprávnění serveru.
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center">
                            <?php if (empty($dirResult['failed'])): ?>
                                <a href="?action=install&step=admin" class="btn">👤 Nastavit administrátora</a>
                            <?php else: ?>
                                <a href="?action=install&step=directories" class="btn">🔄 Zkusit znovu</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                    break;

                case 'admin':
                    ?>
                    <div class="progress">
                        <div class="progress-bar" style="width: 60%;"></div>
                    </div>
                    
                    <div class="step active">
                        <h3><span class="step-icon">3</span>Nastavení administrátorského účtu</h3>
                        
                        <div class="alert alert-warning">
                            <strong>⚠️ Bezpečnost:</strong> Změňte výchozí heslo z "admin123" na silné heslo!
                        </div>
                        
                        <form method="POST" action="?action=install&step=admin_save">
                            <div class="form-group">
                                <label>Uživatelské jméno:</label>
                                <input type="text" name="username" value="admin" required>
                            </div>
                            
                            <div class="form-group">
                                <label>E-mail:</label>
                                <input type="email" name="email" value="info@allimedia.cz" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Nové heslo:</label>
                                <input type="password" name="password" placeholder="Zadejte silné heslo (min. 8 znaků)" required minlength="8">
                            </div>
                            
                            <div class="form-group">
                                <label>Potvrzení hesla:</label>
                                <input type="password" name="password_confirm" placeholder="Zadejte heslo znovu" required minlength="8">
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn">💾 Uložit administrátora</button>
                            </div>
                        </form>
                    </div>
                    <?php
                    break;

                case 'admin_save':
                    if ($_POST['password'] !== $_POST['password_confirm']) {
                        echo '<div class="alert alert-danger">❌ Hesla se neshodují!</div>';
                        echo '<div class="text-center"><a href="?action=install&step=admin" class="btn">🔙 Zpět</a></div>';
                        break;
                    }
                    
                    try {
                        $pdo = new PDO(
                            'mysql:host=localhost;dbname=c4invallimedia;charset=utf8mb4',
                            'c4alpho',
                            'nzsm_YJH6',
                            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                        );
                        
                        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        
                        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = 1");
                        $stmt->execute([$_POST['username'], $_POST['email'], $hashedPassword]);
                        
                        ?>
                        <div class="progress">
                            <div class="progress-bar" style="width: 80%;"></div>
                        </div>
                        
                        <div class="step success">
                            <h3><span class="step-icon">✓</span>Administrátor upraven!</h3>
                            
                            <div class="alert alert-success">
                                ✅ Administrátorský účet byl úspěšně nastaven:<br>
                                👤 <strong>Uživatel:</strong> <?php echo htmlspecialchars($_POST['username']); ?><br>
                                📧 <strong>E-mail:</strong> <?php echo htmlspecialchars($_POST['email']); ?><br>
                                🔐 <strong>Heslo:</strong> Bezpečně uloženo
                            </div>
                            
                            <div class="text-center">
                                <a href="?action=install&step=finish" class="btn">🏁 Dokončit instalaci</a>
                            </div>
                        </div>
                        <?php
                    } catch (Exception $e) {
                        echo '<div class="alert alert-danger">❌ Chyba při ukládání: ' . htmlspecialchars($e->getMessage()) . '</div>';
                        echo '<div class="text-center"><a href="?action=install&step=admin" class="btn">🔙 Zkusit znovu</a></div>';
                    }
                    break;

                case 'finish':
                    // Vytvoření lock souboru
                    file_put_contents('config/installed.lock', date('Y-m-d H:i:s') . ' - Instalováno pro inv.allimedia.cz');
                    ?>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%;"></div>
                    </div>
                    
                    <div class="step success">
                        <h3><span class="step-icon">🎉</span>Instalace dokončena!</h3>
                        
                        <div class="alert alert-success">
                            <strong>🎊 Gratulujeme!</strong> QRdoklad je úspěšně nainstalován a připraven k použití.
                        </div>
                        
                        <div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0;">
                            <h4 style="margin-bottom: 15px;">📋 Důležité informace:</h4>
                            <p><strong>🌐 URL aplikace:</strong> <a href="https://inv.allimedia.cz/" target="_blank">https://inv.allimedia.cz/</a></p>
                            <p><strong>👤 Přihlašovací údaje:</strong> Ty, které jste nastavili v předchozím kroku</p>
                            <p><strong>🔒 Bezpečnost:</strong> Smažte tento instalátor hned po dokončení!</p>
                            <p><strong>📧 Podpora:</strong> info@allimedia.cz</p>
                        </div>
                        
                        <div class="alert alert-warning">
                            <strong>⚠️ DŮLEŽITÉ:</strong> 
                            <ol style="margin: 10px 0 10px 20px;">
                                <li>Smažte soubor <code>install.php</code></li>
                                <li>Smažte soubor <code>server-check.php</code></li>
                                <li>Smažte soubor <code>database.sql</code></li>
                            </ol>
                        </div>
                        
                        <div class="text-center">
                            <a href="/" class="btn" style="margin-right: 10px;">🏠 Přejít do aplikace</a>
                            <a href="/Sign/in" class="btn">🔑 Přihlásit se</a>
                        </div>
                    </div>
                    <?php
                    break;
            }
            ?>
        </div>
    </div>

    <script>
        // Automatické skrytí alertů po 5 sekundách
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.classList.contains('alert-success')) {
                    alert.style.opacity = '0.7';
                }
            });
        }, 5000);
    </script>
</body>
</html>