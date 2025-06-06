<?php
/**
 * Vyčištění Nette cache
 */

echo "<h1>🧹 Čištění cache</h1>";

function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    return rmdir($dir);
}

// Vyčištění temp/cache
$tempDir = __DIR__ . '/temp';
$cacheDir = $tempDir . '/cache';

echo "<p>📁 Temp adresář: $tempDir</p>";

if (is_dir($cacheDir)) {
    echo "<p>🗑️ Mažu cache...</p>";
    deleteDirectory($cacheDir);
    echo "<p>✅ Cache smazána</p>";
} else {
    echo "<p>ℹ️ Cache adresář neexistuje</p>";
}

// Vytvoření prázdných adresářů
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0755, true);
    echo "<p>✅ Temp adresář vytvořen</p>";
}

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
    echo "<p>✅ Cache adresář vytvořen</p>";
}

echo "<p><a href='/'>🔙 Zkusit aplikaci</a></p>";
?>