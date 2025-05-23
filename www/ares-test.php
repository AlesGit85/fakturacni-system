<?php
header('Content-Type: application/json');

// Testovací data
$ico = $_GET['ico'] ?? '12345678';
$testData = [
    'name' => 'Testovací Společnost s.r.o.',
    'ic' => $ico,
    'dic' => 'CZ' . $ico,
    'address' => 'Příkladová 123/45',
    'city' => 'Praha',
    'zip' => '11000',
    'country' => 'Česká republika',
];

// Log pro debugging - použijeme cestu relativní k aktuálnímu souboru
$logDir = __DIR__ . '/../log';
if (!is_dir($logDir)) {
    // Vytvoříme adresář log, pokud neexistuje
    if (!mkdir($logDir, 0777, true)) {
        // Pokud nelze vytvořit adresář, budeme pokračovat bez logování
    }
}

// Pokus o zápis do logu, pokud je to možné
if (is_dir($logDir) && is_writable($logDir)) {
    $logFile = $logDir . '/ares_test_standalone.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - ARES test volán pro IČO: $ico\n", FILE_APPEND);
}

// Vracíme data
echo json_encode($testData);