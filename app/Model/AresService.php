<?php

namespace App\Model;

use Nette;
use Tracy\ILogger;

class AresService
{
    use Nette\SmartObject;
    
    /** @var ILogger */
    private $logger;
    
    public function __construct(ILogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Načte data firmy z ARESu podle IČO
     */
    public function getCompanyDataByIco(string $ico): ?array
    {
        $ico = trim($ico);
        
        // Validace IČO
        if (!preg_match('/^\d{8}$/', $ico)) {
            $this->logger->log("Neplatné IČO: $ico", ILogger::WARNING);
            return null;
        }
        
        // Zkusíme nejprve nové REST API
        $result = $this->fetchFromRestApi($ico);
        
        // Pokud selže, zkusíme původní XML API
        if (!$result) {
            $this->logger->log("REST API selhalo, zkouším původní XML API", ILogger::INFO);
            $result = $this->fetchFromXmlApi($ico);
        }
        
        // Podrobný zápis do logu
        $this->logger->log("Výsledek ARES pro IČO $ico: " . json_encode($result), ILogger::INFO);
        
        return $result;
    }
    
    /**
     * Načte data z nového REST API ARESu
     */
    private function fetchFromRestApi(string $ico): ?array
    {
        // Aktuální URL pro REST API ARESu
        $url = "https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/$ico";
        
        // Podrobné logování
        $this->logger->log("Volání REST API ARES: $url", ILogger::INFO);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Pro vývoj, v produkci nastavte na true
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Pro vývoj, v produkci nastavte na true
        curl_setopt($ch, CURLOPT_USERAGENT, 'QRdoklad/1.0');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorMsg = curl_error($ch);
        curl_close($ch);
        
        // Logování odpovědi
        $this->logger->log("REST API HTTP kód: $httpCode", ILogger::INFO);
        if ($errorMsg) {
            $this->logger->log("REST API chyba: $errorMsg", ILogger::ERROR);
        }
        
        if ($httpCode !== 200 || !$response) {
            $this->logger->log("Neúspěšné volání REST API: HTTP $httpCode", ILogger::ERROR);
            return null;
        }
        
        // Zápis raw odpovědi do logu pro debugging
        $this->logger->log("REST API odpověď (prvních 500 znaků): " . substr($response, 0, 500), ILogger::DEBUG);
        
        // Parsování JSON odpovědi
        $data = json_decode($response, true);
        if (!$data) {
            $this->logger->log("Chyba při parsování JSON: " . json_last_error_msg(), ILogger::ERROR);
            return null;
        }
        
        try {
            // Mapování dat z REST API na strukturu aplikace
            $result = $this->mapRestApiData($data, $ico);
            return $result;
        } catch (\Exception $e) {
            $this->logger->log("Chyba při mapování dat: " . $e->getMessage(), ILogger::ERROR);
            return null;
        }
    }
    
    /**
     * Mapuje data z REST API na strukturu aplikace
     */
    private function mapRestApiData(array $data, string $ico): array
    {
        // Logování struktury dat pro lepší debugování
        $this->logger->log("Struktura dat z REST API: " . json_encode(array_keys($data)), ILogger::DEBUG);
        
        $name = $data['obchodniJmeno'] ?? '';
        
        // DIČ
        $dic = $data['dic'] ?? '';
        
        // Adresa
        $address = '';
        $city = '';
        $zip = '';
        
        if (isset($data['sidlo'])) {
            $sidlo = $data['sidlo'];
            
            // Ulice
            $street = '';
            if (isset($sidlo['ulice']) && isset($sidlo['ulice']['nazev'])) {
                $street = $sidlo['ulice']['nazev'];
            }
            
            // Číslo domu
            $houseNum = $sidlo['cisloDomovni'] ?? '';
            $orientNum = isset($sidlo['cisloOrientacni']) ? '/' . $sidlo['cisloOrientacni'] : '';
            
            // Město
            if (isset($sidlo['obec']) && isset($sidlo['obec']['nazev'])) {
                $city = $sidlo['obec']['nazev'];
            }
            
            // PSČ
            $zip = $sidlo['psc'] ?? '';
            
            // Sestavení adresy
            $address = trim($street . ' ' . $houseNum . $orientNum);
            
            // Pokud není ulice, použijeme obec
            if (empty($address) && !empty($city)) {
                $address = $city;
            }
        }
        
        return [
            'name' => $name,
            'ic' => $ico,
            'dic' => $dic,
            'address' => $address,
            'city' => $city,
            'zip' => $zip,
            'country' => 'Česká republika',
        ];
    }
    
    /**
     * Načte data z původního XML API ARESu (jako záloha)
     */
    private function fetchFromXmlApi(string $ico): ?array
    {
        // URL pro veřejné XML API
        $url = "https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=$ico";
        
        // Podrobné logování
        $this->logger->log("Volání XML API ARES: $url", ILogger::INFO);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'QRdoklad/1.0');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorMsg = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$response) {
            $this->logger->log("Neúspěšné volání XML API: HTTP $httpCode, Chyba: $errorMsg", ILogger::ERROR);
            return null;
        }
        
        // Zpracování XML odpovědi
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response);
        
        if (!$xml) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $this->logger->log("Chyba při parsování XML", ILogger::ERROR);
            return null;
        }
        
        // Kontrola namespace
        $namespaces = $xml->getNamespaces(true);
        if (!isset($namespaces['are'])) {
            $this->logger->log("Chybějící namespace 'are' v XML", ILogger::ERROR);
            return null;
        }
        
        // Získání dat z XML
        $data = $xml->children($namespaces['are']);
        
        if (!isset($data->Odpoved) || !isset($data->Odpoved->Zaznam)) {
            $this->logger->log("Chybějící struktura dat v XML", ILogger::ERROR);
            return null;
        }
        
        // Kontrola chyby
        if (isset($data->Odpoved->Error)) {
            $this->logger->log("ARES XML API vrátilo chybu", ILogger::ERROR);
            return null;
        }
        
        // Mapování dat
        return $this->mapXmlData($data->Odpoved->Zaznam, $ico);
    }
    
    /**
     * Mapuje data z XML API na strukturu aplikace
     */
    private function mapXmlData($zaznam, string $ico): array
    {
        // Jméno firmy
        $name = '';
        if (isset($zaznam->Obchodni_firma)) {
            $name = (string)$zaznam->Obchodni_firma;
        } elseif (isset($zaznam->Jmeno)) {
            $name = trim((string)$zaznam->Jmeno . ' ' . (string)$zaznam->Prijmeni);
        }
        
        // DIČ
        $dic = '';
        if (isset($zaznam->DIC)) {
            $dic = (string)$zaznam->DIC;
        }
        
        // Adresa
        $address = '';
        $city = '';
        $zip = '';
        
        if (isset($zaznam->Identifikace->Adresa_ARES)) {
            $adresa = $zaznam->Identifikace->Adresa_ARES;
            
            // Ulice
            $street = isset($adresa->Nazev_ulice) ? (string)$adresa->Nazev_ulice : '';
            
            // Číslo domu
            $houseNum = isset($adresa->Cislo_domovni) ? (string)$adresa->Cislo_domovni : '';
            $orientNum = isset($adresa->Cislo_orientacni) ? '/' . (string)$adresa->Cislo_orientacni : '';
            
            // Město
            $city = isset($adresa->Nazev_obce) ? (string)$adresa->Nazev_obce : '';
            
            // PSČ
            $zip = isset($adresa->PSC) ? (string)$adresa->PSC : '';
            
            // Sestavení adresy
            $address = trim($street . ' ' . $houseNum . $orientNum);
            
            // Pokud není ulice, použijeme obec
            if (empty($address) && !empty($city)) {
                $address = $city;
            }
        }
        
        return [
            'name' => $name,
            'ic' => $ico,
            'dic' => $dic,
            'address' => $address,
            'city' => $city,
            'zip' => $zip,
            'country' => 'Česká republika',
        ];
    }
}