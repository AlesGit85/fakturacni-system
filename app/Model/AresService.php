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
        
        // Zkusíme nejprve XML API, které je stabilnější
        $result = $this->fetchFromXmlApi($ico);
        
        // Podrobný zápis do logu
        $this->logger->log("Výsledek ARES pro IČO $ico: " . json_encode($result), ILogger::INFO);
        
        return $result;
    }
    
    /**
     * Načte data z XML API ARESu
     */
    private function fetchFromXmlApi(string $ico): ?array
    {
        // URL pro veřejné XML API
        $url = "https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=" . $ico;
        
        // Podrobné logování
        $this->logger->log("Volání ARES API: $url", ILogger::INFO);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorMsg = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$response) {
            $this->logger->log("Chyba při volání ARES API: HTTP $httpCode, Chyba: $errorMsg", ILogger::ERROR);
            return null;
        }
        
        // Zápis raw odpovědi do logu pro debugging
        $this->logger->log("Raw odpověď z ARES: " . substr($response, 0, 1000), ILogger::DEBUG);
        
        // Zpracování XML odpovědi
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response);
        
        if (!$xml) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $errorMsg = "Chyba při zpracování XML: ";
            foreach ($errors as $error) {
                $errorMsg .= "[Level: {$error->level}, Line: {$error->line}] {$error->message}\n";
            }
            $this->logger->log($errorMsg, ILogger::ERROR);
            return null;
        }
        
        // Kontrola existence namespace
        $namespaces = $xml->getNamespaces(true);
        if (!isset($namespaces['are'])) {
            $this->logger->log("XML neobsahuje očekávaný namespace 'are'. Dostupné: " . json_encode($namespaces), ILogger::ERROR);
            return null;
        }
        
        // Získání dat z XML
        $data = $xml->children($namespaces['are']);
        
        if (!isset($data->Odpoved) || !isset($data->Odpoved->Zaznam)) {
            $this->logger->log("Očekávaná struktura XML nenalezena", ILogger::ERROR);
            return null;
        }
        
        $zaznam = $data->Odpoved->Zaznam;
        
        // Kontrola, zda je záznam nalezen
        if (isset($data->Odpoved->Error)) {
            $errorText = (string)$data->Odpoved->Error->Error_text;
            $this->logger->log("ARES vrátil chybu: $errorText", ILogger::ERROR);
            return null;
        }
        
        // Extrakce údajů
        $name = '';
        if (isset($zaznam->Obchodni_firma)) {
            $name = (string)$zaznam->Obchodni_firma;
        } elseif (isset($zaznam->Jmeno)) {
            // Pro fyzické osoby spojíme jméno a příjmení
            $name = trim((string)$zaznam->Jmeno . ' ' . (string)$zaznam->Prijmeni);
        }
        
        $dic = '';
        if (isset($zaznam->DIC)) {
            $dic = (string)$zaznam->DIC;
        }
        
        // Adresa
        $adresa = null;
        if (isset($zaznam->Identifikace->Adresa_ARES)) {
            $adresa = $zaznam->Identifikace->Adresa_ARES;
        }
        
        // Formátování adresy
        $street = isset($adresa->Nazev_ulice) ? (string)$adresa->Nazev_ulice : '';
        $houseNum = isset($adresa->Cislo_domovni) ? (string)$adresa->Cislo_domovni : '';
        $orientNum = isset($adresa->Cislo_orientacni) ? '/' . (string)$adresa->Cislo_orientacni : '';
        $city = isset($adresa->Nazev_obce) ? (string)$adresa->Nazev_obce : '';
        $district = isset($adresa->Nazev_casti_obce) && (string)$adresa->Nazev_casti_obce !== (string)$adresa->Nazev_obce ? 
            (string)$adresa->Nazev_casti_obce : '';
        $zip = isset($adresa->PSC) ? (string)$adresa->PSC : '';
        
        // Sestavení adresy
        $addressStr = $street;
        if (!empty($houseNum)) {
            $addressStr .= ' ' . $houseNum . $orientNum;
        }
        
        // Pokud není ulice (např. malé obce), použijeme jako adresu část obce nebo obec
        if (empty($addressStr)) {
            $addressStr = !empty($district) ? $district : $city;
        }
        
        // Výsledný objekt
        $result = [
            'name' => $name,
            'ic' => $ico,
            'dic' => $dic,
            'address' => trim($addressStr),
            'city' => $city,
            'zip' => $zip,
            'country' => 'Česká republika',
        ];
        
        return $result;
    }
}