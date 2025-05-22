<?php
// Skript pro sledování logů v reálném čase
// Spusťte v samostatném terminálu: php watch_logs.php

$logFile = __DIR__ . '/temp/profile_debug.log';

echo "=== SLEDOVÁNÍ LOGŮ ===\n";
echo "Log soubor: $logFile\n";
echo "Čekám na změny... (Ctrl+C pro ukončení)\n";
echo str_repeat("-", 50) . "\n";

// Vyčistíme log soubor
file_put_contents($logFile, '');

$lastSize = 0;

while (true) {
    if (file_exists($logFile)) {
        $currentSize = filesize($logFile);
        
        if ($currentSize > $lastSize) {
            // Přečteme nový obsah
            $handle = fopen($logFile, 'r');
            fseek($handle, $lastSize);
            
            while (($line = fgets($handle)) !== false) {
                echo $line;
            }
            
            fclose($handle);
            $lastSize = $currentSize;
        }
    }
    
    // Čekáme půl sekundy
    usleep(500000);
}
?>