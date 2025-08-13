<?php
echo "<h2>Composer Diagnostika</h2>";

// Zjistit aktuální cestu
echo "<strong>Aktuální cesta:</strong> " . getcwd() . "<br>";

// Kontrola, zda existuje composer.json
echo "<strong>Composer.json existuje:</strong> " . (file_exists('composer.json') ? 'ANO' : 'NE') . "<br>";

// Kontrola, zda je composer dostupný
echo "<strong>Composer test:</strong><br>";
$composer_test = shell_exec('composer --version 2>&1');
echo "<pre>" . htmlspecialchars($composer_test) . "</pre>";

// Pokus o composer install s detailním výstupem
echo "<strong>Composer install výstup:</strong><br>";
$output = shell_exec('composer install --no-dev --verbose 2>&1');
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Kontrola obsahu vendor složky
echo "<strong>Obsah vendor složky:</strong><br>";
if (is_dir('vendor')) {
    $files = scandir('vendor');
    echo "<pre>" . print_r($files, true) . "</pre>";
} else {
    echo "Vendor složka neexistuje<br>";
}

echo "<br><strong>PHP verze:</strong> " . phpversion();
?>