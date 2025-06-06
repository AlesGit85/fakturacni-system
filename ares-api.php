<?php
/**
 * Samostatný ARES API endpoint - bez Nette overhead
 */

// Vypnout veškerý output a debug
ini_set('display_errors', 0);
error_reporting(0);

// Nastavit JSON header
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Pouze pokud je to AJAX požadavek
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
        throw new Exception('Pouze AJAX požadavky');
    }
    
    $ico = $_GET['ico'] ?? $_POST['ico'] ?? '';
    if (empty($ico)) {
        throw new Exception('IČO není zadáno');
    }
    
    // Čistý ARES dotaz bez Nette
    $aresUrl = "https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/" . urlencode($ico);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $aresUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'QRdoklad/1.0');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response) {
        throw new Exception('ARES server nedostupný');
    }
    
    $aresData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Neplatná odpověď z ARES');
    }
    
    // Zpracování dat
    if (isset($aresData['ico'])) {
        $companyData = [
            'name' => $aresData['obchodniJmeno'] ?? '',
            'address' => $aresData['sidlo']['nazevOkresu'] ?? '',
            'city' => $aresData['sidlo']['nazevObce'] ?? '',
            'zip' => $aresData['sidlo']['psc'] ?? '',
            'country' => 'Česká republika',
            'ic' => $aresData['ico'] ?? '',
            'dic' => $aresData['dic'] ?? ''
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $companyData
        ], JSON_UNESCAPED_UNICODE);
        
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Firma nebyla nalezena'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit; // Ukončit bez dalšího output
?>