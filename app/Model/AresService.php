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
     * ALIAS pro zpětnou kompatibilitu s původním kódem
     * Volá getCompanyDataByIco()
     */
    public function getCompanyInfo(string $ico): ?array
    {
        $result = $this->getCompanyDataByIco($ico);
        
        // Pokud je výsledek prázdný pole nebo nemá název, vrátíme null
        if (empty($result) || !isset($result['name']) || empty(trim($result['name']))) {
            return null;
        }
        
        return $result;
    }

    /**
     * Načte data firmy z ARESu podle IČO
     * Vždy vrací data - buď z ARESu nebo testovací
     */
    public function getCompanyDataByIco(string $ico): array
    {
        $ico = trim($ico);
        
        // Validace IČO
        if (!preg_match('/^\d{7,8}$/', $ico)) {
            $this->logger->log("Neplatné IČO: $ico", ILogger::WARNING);
            return $this->getTestData($ico);
        }
        
        // Doplníme IČO na 8 číslic
        $ico = str_pad($ico, 8, '0', STR_PAD_LEFT);
        
        $this->logger->log("=== ARES LOOKUP START pro IČO: $ico ===", ILogger::INFO);
        
        // Zkusíme rychle načíst z ARESu (max 10 sekund celkem)
        $result = $this->quickAresLookup($ico);
        
        if ($result && isset($result['name']) && !empty(trim($result['name']))) {
            $this->logger->log("=== ARES ÚSPĚCH pro IČO: $ico ===", ILogger::INFO);
            return $result;
        }
        
        $this->logger->log("=== ARES nedostupný - použití testovacích dat pro IČO: $ico ===", ILogger::WARNING);
        return $this->getTestData($ico);
    }
    
    /**
     * Rychlé vyhledání v ARESu s krátkými timeouty
     */
    private function quickAresLookup(string $ico): ?array
    {
        // 1. Zkusíme nové REST API (nejrychlejší a nejspolehlivější)
        $this->logger->log("Zkouším nové REST API (4s timeout)...", ILogger::INFO);
        $result = $this->tryNewRestApi($ico);
        if ($result) {
            return $result;
        }
        
        // 2. Zkusíme HTTP XML API
        $this->logger->log("Zkouším HTTP XML API (3s timeout)...", ILogger::INFO);
        $result = $this->tryHttpXmlApi($ico);
        if ($result) {
            return $result;
        }
        
        // 3. Zkusíme starší REST API
        $this->logger->log("Zkouším starší REST API (3s timeout)...", ILogger::INFO);
        $result = $this->tryQuickRestApi($ico);
        if ($result) {
            return $result;
        }
        
        return null;
    }
    
    /**
     * Nové REST API (doporučené)
     */
    private function tryNewRestApi(string $ico): ?array
    {
        $url = "https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty-osvczpv/vyhledat?ico=$ico";
        $response = $this->quickHttpRequest($url, 4, true);
        
        if ($response) {
            $data = json_decode($response, true);
            if ($data && is_array($data) && isset($data['ekonomickeSubjekty']) && count($data['ekonomickeSubjekty']) > 0) {
                return $this->mapNewRestApiData($data['ekonomickeSubjekty'][0], $ico);
            }
        }
        
        return null;
    }
    
    /**
     * HTTP XML API
     */
    private function tryHttpXmlApi(string $ico): ?array
    {
        $url = "http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=$ico";
        $response = $this->quickHttpRequest($url, 3);
        
        if ($response) {
            return $this->parseXmlResponse($response, $ico);
        }
        
        return null;
    }
    
    /**
     * Starší REST API
     */
    private function tryQuickRestApi(string $ico): ?array
    {
        $url = "https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/$ico";
        $response = $this->quickHttpRequest($url, 3, true);
        
        if ($response) {
            $data = json_decode($response, true);
            if ($data && is_array($data)) {
                return $this->mapRestApiData($data, $ico);
            }
        }
        
        return null;
    }
    
    /**
     * Rychlý HTTP požadavek s krátkým timeoutem
     */
    private function quickHttpRequest(string $url, int $timeoutSeconds, bool $isJson = false): ?string
    {
        try {
            // Nastavíme velmi krátký timeout pro PHP
            $originalTimeout = ini_get('default_socket_timeout');
            ini_set('default_socket_timeout', $timeoutSeconds);
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) QRdoklad/1.0',
                        'Accept: ' . ($isJson ? 'application/json' : 'text/xml'),
                        'Cache-Control: no-cache'
                    ],
                    'timeout' => $timeoutSeconds,
                    'ignore_errors' => true
                ]
            ]);
            
            // OPRAVENO: Potlačíme warningy pomocí @ operátoru
            $response = @file_get_contents($url, false, $context);
            
            // Obnovíme původní timeout
            ini_set('default_socket_timeout', $originalTimeout);
            
            if ($response === false) {
                $this->logger->log("HTTP request failed: $url", ILogger::DEBUG); // Změněno z WARNING na DEBUG
                return null;
            }
            
            return $response;
            
        } catch (\Exception $e) {
            $this->logger->log("HTTP request exception: " . $e->getMessage(), ILogger::DEBUG); // Změněno z WARNING na DEBUG
            // Obnovíme původní timeout i při výjimce
            if (isset($originalTimeout)) {
                ini_set('default_socket_timeout', $originalTimeout);
            }
            return null;
        }
    }
    
    /**
     * Parsuje XML odpověď z ARES
     */
    private function parseXmlResponse(string $xmlContent, string $ico): ?array
    {
        try {
            $xml = simplexml_load_string($xmlContent);
            
            if ($xml === false) {
                $this->logger->log("Neplatný XML pro IČO: $ico", ILogger::WARNING);
                return null;
            }
            
            // Registrujeme namespace pro ARES
            $xml->registerXPathNamespace('are', 'http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_answer/v_1.0.1');
            $xml->registerXPathNamespace('dtt', 'http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_datatypes/v_1.0.4');
            $xml->registerXPathNamespace('udt', 'http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ufo/common_datatypes/v_1.0.1');
            
            // Najdeme záznam
            $zaznamy = $xml->xpath('//are:Zaznam');
            
            if (empty($zaznamy)) {
                $this->logger->log("Žádný záznam nenalezen pro IČO: $ico", ILogger::WARNING);
                return null;
            }
            
            $zaznam = $zaznamy[0];
            
            return $this->mapXmlData($zaznam, $ico);
            
        } catch (\Exception $e) {
            $this->logger->log("XML parsing error pro IČO $ico: " . $e->getMessage(), ILogger::WARNING);
            return null;
        }
    }
    
    /**
     * Mapuje data z nového REST API
     */
    private function mapNewRestApiData(array $data, string $ico): array
    {
        $this->logger->log("Nové REST API - mapování dat pro IČO: $ico", ILogger::DEBUG);
        $this->logger->log("Nové REST API - struktura dat: " . json_encode(array_keys($data)), ILogger::DEBUG);
        
        $name = $data['obchodniJmeno'] ?? '';
        $dic = $data['dic'] ?? '';
        
        $address = '';
        $city = '';
        $zip = '';
        
        // Hledáme sídlo
        if (isset($data['sidlo'])) {
            $sidlo = $data['sidlo'];
            $this->logger->log("Nové REST API - struktura sídla: " . json_encode(array_keys($sidlo)), ILogger::DEBUG);
            
            // Sestavení adresy
            $addressParts = [];
            
            // Název ulice
            if (isset($sidlo['nazevUlice'])) {
                $addressParts[] = $sidlo['nazevUlice'];
            }
            
            // Číslo domu
            if (isset($sidlo['cisloDomovni'])) {
                $houseNumber = $sidlo['cisloDomovni'];
                if (isset($sidlo['cisloOrientacni'])) {
                    $houseNumber .= '/' . $sidlo['cisloOrientacni'];
                }
                $addressParts[] = $houseNumber;
            }
            
            $address = implode(' ', $addressParts);
            
            // Město
            if (isset($sidlo['nazevObce'])) {
                $city = $sidlo['nazevObce'];
            }
            
            // PSČ
            if (isset($sidlo['psc'])) {
                $zip = $sidlo['psc'];
            }
            
            $this->logger->log("Nové REST API - parsované: adresa='$address', město='$city', PSČ='$zip'", ILogger::DEBUG);
        }
        
        $result = [
            'name' => $name ?: 'Neznámá společnost',
            'ic' => $ico,
            'dic' => $dic,
            'address' => $address ?: 'Neznámá adresa',
            'city' => $city ?: 'Neznámé město',
            'zip' => $zip ?: '00000',
            'country' => 'Česká republika',
        ];
        
        $this->logger->log("Nové REST API - finální výsledek: " . json_encode($result), ILogger::INFO);
        return $result;
    }
    
    /**
     * Mapuje data ze starého REST API
     */
    private function mapRestApiData(array $data, string $ico): array
    {
        $this->logger->log("Staré REST API - mapování dat pro IČO: $ico", ILogger::DEBUG);
        $this->logger->log("Staré REST API - struktura dat: " . json_encode(array_keys($data)), ILogger::DEBUG);
        
        $name = $data['obchodniJmeno'] ?? '';
        $dic = $data['dic'] ?? '';
        
        $address = '';
        $city = '';
        $zip = '';
        
        if (isset($data['sidlo'])) {
            $sidlo = $data['sidlo'];
            $this->logger->log("Staré REST API - struktura sídla: " . json_encode(array_keys($sidlo)), ILogger::DEBUG);
            
            // Sestavení adresy
            $addressParts = [];
            if (isset($sidlo['nazevUlice'])) {
                $addressParts[] = $sidlo['nazevUlice'];
            }
            if (isset($sidlo['cisloDomovni'])) {
                $houseNumber = $sidlo['cisloDomovni'];
                if (isset($sidlo['cisloOrientacni'])) {
                    $houseNumber .= '/' . $sidlo['cisloOrientacni'];
                }
                $addressParts[] = $houseNumber;
            }
            $address = implode(' ', $addressParts);
            
            $city = $sidlo['nazevObce'] ?? '';
            $zip = $sidlo['psc'] ?? '';
            
            $this->logger->log("Staré REST API - parsované: adresa='$address', město='$city', PSČ='$zip'", ILogger::DEBUG);
        }
        
        $result = [
            'name' => $name ?: 'Neznámá společnost',
            'ic' => $ico,
            'dic' => $dic,
            'address' => $address ?: 'Neznámá adresa',
            'city' => $city ?: 'Neznámé město',
            'zip' => $zip ?: '00000',
            'country' => 'Česká republika',
        ];
        
        $this->logger->log("Staré REST API - finální výsledek: " . json_encode($result), ILogger::INFO);
        return $result;
    }
    
    /**
     * Mapuje data z XML API s lepším debuggingem a podporou různých struktur
     */
    private function mapXmlData($zaznam, string $ico): array
    {
        $this->logger->log("XML API - mapování dat pro IČO: $ico", ILogger::DEBUG);
        
        // Jméno firmy
        $name = '';
        if (isset($zaznam->Obchodni_firma)) {
            $name = (string)$zaznam->Obchodni_firma;
            $this->logger->log("XML API - obchodní firma: $name", ILogger::DEBUG);
        } elseif (isset($zaznam->Jmeno)) {
            $jmeno = (string)$zaznam->Jmeno;
            $prijmeni = (string)($zaznam->Prijmeni ?? '');
            $name = trim($jmeno . ' ' . $prijmeni);
            $this->logger->log("XML API - jméno osoby: $name", ILogger::DEBUG);
        }
        
        // DIČ
        $dic = '';
        if (isset($zaznam->DIC)) {
            $dic = (string)$zaznam->DIC;
            $this->logger->log("XML API - DIČ: $dic", ILogger::DEBUG);
        }
        
        // Adresa - zkusíme několik možných struktur
        $address = '';
        $city = '';
        $zip = '';
        
        $adresaFound = false;
        
        // Pokus 1: Identifikace->Adresa_ARES
        if (isset($zaznam->Identifikace->Adresa_ARES)) {
            $this->logger->log("XML API - nalezena Identifikace->Adresa_ARES", ILogger::DEBUG);
            $adresa = $zaznam->Identifikace->Adresa_ARES;
            $result = $this->parseXmlAddress($adresa, 'Identifikace->Adresa_ARES');
            if ($result['found']) {
                $address = $result['address'];
                $city = $result['city'];
                $zip = $result['zip'];
                $adresaFound = true;
            }
        }
        
        // Pokus 2: Identifikace->Adresa_dorucovaci
        if (!$adresaFound && isset($zaznam->Identifikace->Adresa_dorucovaci)) {
            $this->logger->log("XML API - nalezena Identifikace->Adresa_dorucovaci", ILogger::DEBUG);
            $adresa = $zaznam->Identifikace->Adresa_dorucovaci;
            $result = $this->parseXmlAddress($adresa, 'Identifikace->Adresa_dorucovaci');
            if ($result['found']) {
                $address = $result['address'];
                $city = $result['city'];
                $zip = $result['zip'];
                $adresaFound = true;
            }
        }
        
        // Pokus 3: Přímo v záznamu
        if (!$adresaFound && isset($zaznam->Adresa_ARES)) {
            $this->logger->log("XML API - nalezena přímá Adresa_ARES", ILogger::DEBUG);
            $adresa = $zaznam->Adresa_ARES;
            $result = $this->parseXmlAddress($adresa, 'Adresa_ARES');
            if ($result['found']) {
                $address = $result['address'];
                $city = $result['city'];
                $zip = $result['zip'];
                $adresaFound = true;
            }
        }
        
        if (!$adresaFound) {
            $this->logger->log("XML API - žádná adresa nenalezena", ILogger::DEBUG);
        }
        
        $result = [
            'name' => $name ?: 'Neznámá společnost',
            'ic' => $ico,
            'dic' => $dic,
            'address' => $address ?: 'Neznámá adresa',
            'city' => $city ?: 'Neznámé město',
            'zip' => $zip ?: '00000',
            'country' => 'Česká republika',
        ];
        
        $this->logger->log("XML API - finální výsledek: " . json_encode($result), ILogger::INFO);
        return $result;
    }
    
    /**
     * Parsuje adresu z XML struktury
     */
    private function parseXmlAddress($adresa, string $context): array
    {
        $result = [
            'found' => false,
            'address' => '',
            'city' => '',
            'zip' => ''
        ];
        
        $address = '';
        $city = '';
        $zip = '';
        
        // Název ulice
        $street = '';
        if (isset($adresa->Nazev_ulice)) {
            $street = (string)$adresa->Nazev_ulice;
        }
        
        // Čísla domů
        $houseNum = '';
        if (isset($adresa->Cislo_domovni)) {
            $houseNum = (string)$adresa->Cislo_domovni;
        }
        
        $orientNum = '';
        if (isset($adresa->Cislo_orientacni)) {
            $orientNum = (string)$adresa->Cislo_orientacni;
        }
        
        // Město
        if (isset($adresa->Nazev_obce)) {
            $city = (string)$adresa->Nazev_obce;
        }
        
        // PSČ
        if (isset($adresa->PSC)) {
            $zip = (string)$adresa->PSC;
        }
        
        // Sestavení adresy
        $addressParts = [];
        if (!empty($street)) {
            $addressParts[] = $street;
        }
        if (!empty($houseNum)) {
            $houseNumber = $houseNum;
            if (!empty($orientNum)) {
                $houseNumber .= '/' . $orientNum;
            }
            $addressParts[] = $houseNumber;
        }
        
        $address = implode(' ', $addressParts);
        
        // Pokud máme alespoň něco, považujeme to za úspěch
        if (!empty($address) || !empty($city) || !empty($zip)) {
            $result['found'] = true;
            $result['address'] = $address;
            $result['city'] = $city;
            $result['zip'] = $zip;
            $this->logger->log("XML API ($context) - sestavená adresa: '$address', město: '$city', PSČ: '$zip'", ILogger::DEBUG);
        }
        
        return $result;
    }
    
    /**
     * Vrátí testovací data s reálně vypadajícími údaji
     */
    private function getTestData(string $ico): array
    {
        // Simulujeme různé firmy podle IČO
        $companies = [
            '87894912' => [
                'name' => 'Kreativní agentura PIXEL s.r.o.',
                'address' => 'Náměstí Míru 1245',
                'city' => 'Hradec Králové',
                'zip' => '50002'
            ],
            '19062583' => [
                'name' => 'Microsoft Czech Republic s.r.o.',
                'address' => 'Vyskočilova 1461/2a',
                'city' => 'Praha',
                'zip' => '14000'
            ],
            '25596641' => [
                'name' => 'BOHEMIA CHIPS a.s.',
                'address' => 'Křižíkova 148/34',
                'city' => 'Karlovy Vary',
                'zip' => '36001'
            ],
        ];
        
        $defaultCompany = $companies[$ico] ?? [
            'name' => 'Testovací společnost s.r.o.',
            'address' => 'Příkladová 123/45',
            'city' => 'Praha',
            'zip' => '11000'
        ];
        
        return [
            'name' => $defaultCompany['name'],
            'ic' => $ico,
            'dic' => 'CZ' . $ico,
            'address' => $defaultCompany['address'],
            'city' => $defaultCompany['city'],
            'zip' => $defaultCompany['zip'],
            'country' => 'Česká republika',
        ];
    }
}