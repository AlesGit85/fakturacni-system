<?php

declare(strict_types=1);

namespace App\Security;

use Nette\Http\FileUpload;
use Nette;

/**
 * ✅ ROZŠÍŘENÝ Bezpečnostní validátor pro vstupní data
 * Chrání proti XSS, zajišťuje validaci a sanitizaci vstupů
 */
class SecurityValidator
{
    use Nette\SmartObject;

    /** @var array ✅ NOVÉ: Nebezpečné XSS výrazy pro detekci útoků */
    private static array $xssPatterns = [
        '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
        '/javascript:/i',
        '/vbscript:/i',
        '/onload\s*=/i',
        '/onerror\s*=/i',
        '/onclick\s*=/i',
        '/onmouseover\s*=/i',
        '/onfocus\s*=/i',
        '/onblur\s*=/i',
        '/onchange\s*=/i',
        '/onsubmit\s*=/i',
        '/<iframe\b[^>]*>/i',
        '/<object\b[^>]*>/i',
        '/<embed\b[^>]*>/i',
        '/<form\b[^>]*>/i',
        '/<input\b[^>]*>/i',
        '/<link\b[^>]*>/i',
        '/<meta\b[^>]*>/i',
    ];

    /** @var array Povolené MIME typy pro obrázky */
    private static array $allowedImageMimes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp'
    ];

    /** @var array Povolené MIME typy pro dokumenty */
    private static array $allowedDocumentMimes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
        'text/csv'
    ];

    /** @var array Povolené MIME typy pro archivy */
    private static array $allowedArchiveMimes = [
        'application/zip',
        'application/x-zip-compressed',
        'application/x-rar-compressed',
        'application/gzip'
    ];

    /** @var array Nebezpečné file extensions */
    private static array $dangerousExtensions = [
        'exe',
        'bat',
        'cmd',
        'com',
        'pif',
        'scr',
        'vbs',
        'js',
        'jar',
        'php',
        'asp',
        'aspx',
        'jsp',
        'cgi',
        'pl',
        'py',
        'rb',
        'sh'
    ];

    /** @var array ✅ NOVÉ: Povolené HTML tagy pro rich text editory */
    private static array $allowedTags = [
        'strong',
        'b',
        'em',
        'i',
        'u',
        'br',
        'p',
        'ul',
        'ol',
        'li',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6'
    ];

    /**
     * ✅ VYLEPŠENO: Sanitizuje vstupní řetězec proti XSS útokům
     */
    public static function sanitizeString(string $input): string
    {
        // Odstranění nebezpečných tagů a atributů
        $input = strip_tags($input);

        // Převod HTML entit
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // ✅ NOVÉ: Odstranění XSS výrazů
        $input = self::removeXssPatterns($input);

        // Odstranění přebytečných mezer
        $input = trim($input);

        return $input;
    }

    /**
     * ✅ NOVÉ: Sanitizace pro rich text (ponechá některé povolené HTML tagy)
     */
    public static function sanitizeRichText(string $input): string
    {
        // Trim
        $input = trim($input);

        // Povolíme pouze bezpečné HTML tagy
        $allowedTagsString = '<' . implode('><', self::$allowedTags) . '>';
        $input = strip_tags($input, $allowedTagsString);

        // Odstranění nebezpečných atributů ze zbývajících tagů
        $input = self::removeHtmlAttributes($input);

        // Odstranění nebezpečných výrazů
        $input = self::removeXssPatterns($input);

        return $input;
    }

    /**
     * ✅ NOVÉ: Detekce XSS pokusu pro logování
     */
    public static function detectXssAttempt(string $input): bool
    {
        foreach (self::$xssPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }

    /**
     * ✅ NOVÉ: Sanitizace URL adresy
     */
    public static function sanitizeUrl(string $url): string
    {
        $url = trim($url);

        // Povolíme pouze HTTP a HTTPS protokoly
        if (!preg_match('/^https?:\/\//', $url)) {
            return '';
        }

        // Validace URL
        $sanitized = filter_var($url, FILTER_SANITIZE_URL);
        if (!filter_var($sanitized, FILTER_VALIDATE_URL)) {
            return '';
        }

        return $sanitized;
    }

    /**
     * ✅ NOVÉ: Sanitizace názvu souboru
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Odstranění cesty
        $filename = basename($filename);

        // Povolené znaky: písmena, číslice, pomlčka, podtržítko, tečka
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $filename);

        // Omezení délky
        if (strlen($filename) > 255) {
            $filename = substr($filename, 0, 255);
        }

        return $filename;
    }

    /**
     * ✅ NOVÉ: Sanitizace pro SQL LIKE dotazy
     */
    public static function sanitizeForLike(string $input): string
    {
        $input = self::sanitizeString($input);

        // Escapování SQL LIKE speciálních znaků
        $input = str_replace(['%', '_'], ['\%', '\_'], $input);

        return $input;
    }

    /**
     * ✅ NOVÉ: Pro účely logování - safe zobrazení potenciálně nebezpečného obsahu
     */
    public static function safeLogString(string $input, int $maxLength = 100): string
    {
        // Odstranění nebezpečných znaků pro logování
        $safe = preg_replace('/[\x00-\x1F\x7F]/', '', $input);

        // Omezení délky
        if (strlen($safe) > $maxLength) {
            $safe = substr($safe, 0, $maxLength) . '...';
        }

        return $safe;
    }

    /**
     * Validuje e-mailovou adresu
     */
    public static function validateEmail(string $email): bool
    {
        // Základní sanitizace
        $email = trim($email);

        // Kontrola prázdného řetězce
        if (empty($email)) {
            return false;
        }

        // Kontrola délky
        if (strlen($email) > 254) { // RFC 5321 limit
            return false;
        }

        // ✅ ZPŘÍSNĚNO: Musí obsahovat @ a . a něco před @ i za .
        if (!preg_match('/^[^@]+@[^@]+\.[^@]+$/', $email)) {
            return false;
        }

        // Kontrola formátu pomocí PHP filtru
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // ✅ NOVÉ: Dodatečné kontroly pro běžné chyby
        // Nesmí končit nebo začínat tečkou
        if (str_starts_with($email, '.') || str_ends_with($email, '.')) {
            return false;
        }

        // Nesmí obsahovat dvě tečky za sebou
        if (str_contains($email, '..')) {
            return false;
        }

        // Kontrola nebezpečných znaků
        $dangerousChars = ['<', '>', '"', "'", '&', '\r', '\n', '\t', ' '];
        foreach ($dangerousChars as $char) {
            if (strpos($email, $char) !== false) {
                return false;
            }
        }

        // ✅ NOVÉ: Část za @ musí obsahovat alespoň jednu tečku
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return false;
        }

        $domain = $parts[1];
        if (empty($domain) || !str_contains($domain, '.')) {
            return false;
        }

        // Doména nesmí být pouze tečka nebo končit/začínat tečkou
        if ($domain === '.' || str_starts_with($domain, '.') || str_ends_with($domain, '.')) {
            return false;
        }

        return true;
    }

    /**
     * Validuje telefonní číslo
     */
    public static function validatePhoneNumber(string $phone): bool
    {
        // Sanitizace - odstranění všeho kromě číslic, mezer, pomlček a závorek
        $cleanPhone = preg_replace('/[^0-9\s\-\+\(\)]/', '', $phone);

        // Kontrola minimální a maximální délky
        $digitCount = preg_replace('/[^0-9]/', '', $cleanPhone);
        if (strlen($digitCount) < 9 || strlen($digitCount) > 15) {
            return false;
        }

        return true;
    }

    /**
     * Sanitizuje telefonní číslo
     */
    public static function sanitizePhoneNumber(string $phone): string
    {
        // Odstranění nebezpečných znaků, ponechání pouze čísel, mezer, pomlček a závorek
        return preg_replace('/[^0-9\s\-\+\(\)]/', '', trim($phone));
    }

    /**
     * Validuje uživatelské jméno
     */
    public static function validateUsername(string $username): array
    {
        $errors = [];

        // Délka
        if (strlen($username) < 3) {
            $errors[] = 'Uživatelské jméno musí mít alespoň 3 znaky.';
        }

        if (strlen($username) > 50) {
            $errors[] = 'Uživatelské jméno může mít maximálně 50 znaků.';
        }

        // Povolené znaky (alfanumerické + podtržítko + pomlčka)
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            $errors[] = 'Uživatelské jméno může obsahovat pouze písmena bez diakritiky (a-z, A-Z), číslice, podtržítka a pomlčky. Diakritika není povolena.';
        }

        // Nesmí začínat číslem
        if (preg_match('/^[0-9]/', $username)) {
            $errors[] = 'Uživatelské jméno nesmí začínat číslem.';
        }

        // ✅ NOVÉ: Blacklist zakázaných jmen
        $forbiddenUsernames = [
            'admin',
            'administrator',
            'root',
            'superuser',
            'super',
            'user',
            'test',
            'demo',
            'guest',
            'null',
            'undefined',
            'system',
            'support',
            'help',
            'info',
            'mail',
            'email',
            'webmaster',
            'postmaster',
            'noreply',
            'no-reply',
            'api',
            'www',
            'ftp',
            'smtp',
            'pop3',
            'imap',
            'sql',
            'database',
            'db',
            'backup',
            'config',
            'settings'
        ];

        if (in_array(strtolower($username), $forbiddenUsernames)) {
            $errors[] = 'Toto uživatelské jméno je zakázané z bezpečnostních důvodů.';
        }

        return $errors;
    }

    /**
     * Validuje heslo
     */
    public static function validatePassword(string $password): array
    {
        $errors = [];

        // Délka
        if (strlen($password) < 8) {
            $errors[] = 'Heslo musí mít alespoň 8 znaků.';
        }

        if (strlen($password) > 128) {
            $errors[] = 'Heslo může mít maximálně 128 znaků.';
        }

        // Alespoň jedno velké písmeno
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Heslo musí obsahovat alespoň jedno velké písmeno.';
        }

        // Alespoň jedno malé písmeno
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Heslo musí obsahovat alespoň jedno malé písmeno.';
        }

        // Alespoň jedna číslice
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Heslo musí obsahovat alespoň jednu číslici.';
        }

        // Alespoň jeden speciální znak
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
            $errors[] = 'Heslo musí obsahovat alespoň jeden speciální znak (!@#$%^&* atd.).';
        }

        return $errors;
    }

    /**
     * Validuje název společnosti nebo firmy
     */
    public static function validateCompanyName(string $name): array
    {
        $errors = [];

        // Sanitizace
        $name = trim($name);

        // Délka
        if (strlen($name) < 2) {
            $errors[] = 'Název společnosti musí mít alespoň 2 znaky.';
        }

        if (strlen($name) > 255) {
            $errors[] = 'Název společnosti může mít maximálně 255 znaků.';
        }

        // Kontrola nebezpečných tagů
        if ($name !== strip_tags($name)) {
            $errors[] = 'Název společnosti nesmí obsahovat HTML tagy.';
        }

        return $errors;
    }

    /**
     * Validuje IČO (Identifikační číslo organizace)
     */
    public static function validateICO(string $ico): bool
    {
        // Odstranění mezer a pomlček
        $ico = preg_replace('/[\s\-]/', '', $ico);

        // Musí být číslo
        if (!ctype_digit($ico)) {
            return false;
        }

        // Délka 8 znaků pro české IČO
        if (strlen($ico) !== 8) {
            return false;
        }

        // Kontrolní součet pro české IČO
        $sum = 0;
        for ($i = 0; $i < 7; $i++) {
            $sum += (int)$ico[$i] * (8 - $i);
        }

        $remainder = $sum % 11;
        if ($remainder < 2) {
            $checksum = $remainder;
        } else {
            $checksum = 11 - $remainder;
        }

        return (int)$ico[7] === $checksum;
    }

    /**
     * Validuje DIČ (Daňové identifikační číslo)
     */
    public static function validateDIC(string $dic): bool
    {
        // Odstranění mezer
        $dic = preg_replace('/\s/', '', $dic);

        // České DIČ: CZ + 8-10 číslic
        if (preg_match('/^CZ[0-9]{8,10}$/', $dic)) {
            return true;
        }

        // Slovenské DIČ: SK + 10 číslic
        if (preg_match('/^SK[0-9]{10}$/', $dic)) {
            return true;
        }

        // Jiné EU formáty - základní kontrola
        if (preg_match('/^[A-Z]{2}[0-9A-Z]+$/', $dic) && strlen($dic) >= 4 && strlen($dic) <= 15) {
            return true;
        }

        return false;
    }

    /**
     * Sanitizuje číslo faktury
     */
    public static function sanitizeInvoiceNumber(string $number): string
    {
        // Povolené znaky: písmena, číslice, pomlčka, lomítko
        return preg_replace('/[^a-zA-Z0-9\-\/]/', '', trim($number));
    }

    /**
     * Validuje částku (cenu)
     */
    public static function validateAmount(string $amount): bool
    {
        // Převod desetinné čárky na tečku
        $amount = str_replace(',', '.', $amount);

        // Kontrola formátu
        if (!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/', $amount)) {
            return false;
        }

        // Kontrola rozsahu (maximálně 999 999 999.99)
        $numericAmount = (float)$amount;
        if ($numericAmount < 0 || $numericAmount > 999999999.99) {
            return false;
        }

        return true;
    }

    /**
     * Sanitizuje částku
     */
    public static function sanitizeAmount(string $amount): string
    {
        // Převod čárky na tečku a odstranění nečíselných znaků kromě tečky
        $amount = str_replace(',', '.', $amount);
        $amount = preg_replace('/[^0-9.]/', '', $amount);

        // Zajištění pouze jedné desetinné tečky
        $parts = explode('.', $amount);
        if (count($parts) > 2) {
            $amount = $parts[0] . '.' . $parts[1];
        }

        return $amount;
    }

    /**
     * Validuje PSČ
     */
    public static function validatePostalCode(string $postalCode, string $country = 'CZ'): bool
    {
        $postalCode = preg_replace('/\s/', '', $postalCode);

        switch (strtoupper($country)) {
            case 'CZ':
                // České PSČ: 5 číslic nebo 3 číslice mezera 2 číslice
                return preg_match('/^[0-9]{5}$/', $postalCode) === 1;

            case 'SK':
                // Slovenské PSČ: 5 číslic nebo 3 číslice mezera 2 číslice
                return preg_match('/^[0-9]{5}$/', $postalCode) === 1;

            default:
                // Obecná kontrola: 3-10 alfanumerických znaků
                return preg_match('/^[0-9A-Z]{3,10}$/', strtoupper($postalCode)) === 1;
        }
    }

    /**
     * ✅ VYLEPŠENO: Kompletní sanitizace všech vstupních dat z formuláře
     */
    public static function sanitizeFormData(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // ✅ NOVÉ: Detekce XSS pokusů pro logování
                if (self::detectXssAttempt($value)) {
                    // Zde by se mělo logovat do SecurityLogger
                    error_log("XSS pokus detekován v poli '{$key}': " . self::safeLogString($value));
                }

                // Speciální zacházení pro různé typy polí
                switch ($key) {
                    case 'email':
                        // ✅ OPRAVENO: Pouze trim, bez FILTER_SANITIZE_EMAIL
                        $sanitized[$key] = trim($value);
                        break;

                    case 'phone':
                        $sanitized[$key] = self::sanitizePhoneNumber($value);
                        break;

                    case 'invoice_number':
                        $sanitized[$key] = self::sanitizeInvoiceNumber($value);
                        break;

                    case 'total':
                    case 'amount':
                    case 'price':
                        $sanitized[$key] = self::sanitizeAmount($value);
                        break;

                    case 'password':
                    case 'currentPassword':
                    case 'passwordVerify':
                        // Hesla se nesanitizují, pouze validují
                        $sanitized[$key] = $value;
                        break;

                    case 'url':
                    case 'website':
                        $sanitized[$key] = self::sanitizeUrl($value);
                        break;

                    default:
                        $sanitized[$key] = self::sanitizeString($value);
                        break;
                }
            } elseif (is_array($value)) {
                // Rekurzivní sanitizace pro vícerozměrná pole
                $sanitized[$key] = self::sanitizeFormData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * ✅ NOVÉ: Hromadná sanitizace pole dat s možností označit rich text pole
     */
    public static function sanitizeArray(array $data, array $richTextFields = []): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                if (in_array($key, $richTextFields)) {
                    $sanitized[$key] = self::sanitizeRichText($value);
                } else {
                    $sanitized[$key] = self::sanitizeString($value);
                }
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value, $richTextFields);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    // =====================================================================
    // ✅ NOVÉ: Privátní pomocné metody pro XSS ochranu
    // =====================================================================

    /**
     * Odstranění nebezpečných XSS výrazů
     */
    private static function removeXssPatterns(string $input): string
    {
        foreach (self::$xssPatterns as $pattern) {
            $input = preg_replace($pattern, '', $input);
        }
        return $input;
    }

    /**
     * Odstranění všech HTML atributů (ponechá pouze tagy)
     */
    private static function removeHtmlAttributes(string $input): string
    {
        return preg_replace('/<([a-zA-Z0-9]+)[^>]*>/', '<$1>', $input);
    }

    /**
     * ✅ NOVÁ: Kompletní validace nahraného souboru
     * @param FileUpload $file Nahraný soubor
     * @param string $allowedType Typ: 'image', 'document', 'archive'
     * @param int $maxSize Maximální velikost v bytech (0 = bez limitu)
     * @return array Seznam chyb (prázdný = vše OK)
     */
    public static function validateFileUpload(FileUpload $file, string $allowedType = 'image', int $maxSize = 0): array
    {
        $errors = [];

        try {
            // 1. Základní kontroly
            if (!$file->isOk()) {
                $errors[] = self::getFileUploadErrorMessage($file->getError());
                return $errors; // Pokud soubor není OK, další kontroly nemají smysl
            }

            if ($file->getSize() === 0) {
                $errors[] = 'Soubor je prázdný.';
                return $errors;
            }

            // 2. Kontrola velikosti souboru
            if ($maxSize > 0 && $file->getSize() > $maxSize) {
                $errors[] = sprintf('Soubor je příliš velký. Maximální velikost je %s.', self::formatBytes($maxSize));
            }

            // 3. Kontrola názvu souboru
            $filename = $file->getName();
            if (empty($filename)) {
                $errors[] = 'Název souboru je prázdný.';
                return $errors;
            }

            // 4. Kontrola nebezpečných přípon
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($extension, self::$dangerousExtensions, true)) {
                $errors[] = 'Typ souboru není povolen z bezpečnostních důvodů.';
            }

            // 5. Kontrola MIME typu podle client
            $clientMimeType = $file->getContentType();
            $allowedMimes = self::getAllowedMimeTypes($allowedType);

            if (!in_array($clientMimeType, $allowedMimes, true)) {
                $errors[] = sprintf(
                    'Nepovolený typ souboru: %s. Povolené typy: %s',
                    $clientMimeType,
                    implode(', ', $allowedMimes)
                );
            }

            // 6. Kontrola skutečného MIME typu (magic bytes)
            $realMimeType = self::getRealMimeType($file->getTemporaryFile());
            if ($realMimeType && !in_array($realMimeType, $allowedMimes, true)) {
                $errors[] = 'Soubor neodpovídá deklarovanému typu (možný pokus o podvržení).';
            }

            // 7. Specifické kontroly podle typu
            switch ($allowedType) {
                case 'image':
                    $imageErrors = self::validateImageFile($file);
                    $errors = array_merge($errors, $imageErrors);
                    break;

                case 'archive':
                    $archiveErrors = self::validateArchiveFile($file);
                    $errors = array_merge($errors, $archiveErrors);
                    break;
            }

            // 8. Virus scan (pokud je dostupný)
            $virusErrors = self::performVirusScan($file->getTemporaryFile());
            $errors = array_merge($errors, $virusErrors);

            // 9. Kontrola XSS v názvu souboru
            $cleanFilename = self::sanitizeString($filename);
            if ($cleanFilename !== $filename) {
                $errors[] = 'Název souboru obsahuje nebezpečné znaky.';
            }
        } catch (\Exception $e) {
            $errors[] = 'Chyba při validaci souboru: ' . $e->getMessage();
        }

        return $errors;
    }

    /**
     * ✅ NOVÁ: Získání skutečného MIME typu pomocí magic bytes
     */
    private static function getRealMimeType(string $filepath): ?string
    {
        if (!function_exists('finfo_open')) {
            return null; // finfo rozšíření není dostupné
        }

        try {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo === false) {
                return null;
            }

            $mimeType = finfo_file($finfo, $filepath);
            finfo_close($finfo);

            return $mimeType !== false ? $mimeType : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * ✅ NOVÁ: Validace obrázku
     */
    private static function validateImageFile(FileUpload $file): array
    {
        $errors = [];

        try {
            // Kontrola pomocí getimagesize - ověří skutečnou strukturu obrázku
            $imageInfo = getimagesize($file->getTemporaryFile());

            if ($imageInfo === false) {
                $errors[] = 'Soubor není platný obrázek.';
                return $errors;
            }

            // Kontrola rozměrů (max 4096x4096 px)
            [$width, $height] = $imageInfo;
            if ($width > 4096 || $height > 4096) {
                $errors[] = 'Obrázek je příliš velký (max. 4096x4096 pixelů).';
            }

            if ($width < 1 || $height < 1) {
                $errors[] = 'Neplatné rozměry obrázku.';
            }

            // Kontrola počtu barev (prevence problematických obrázků)
            if (isset($imageInfo['bits']) && $imageInfo['bits'] > 32) {
                $errors[] = 'Nepodporovaný formát obrázku.';
            }
        } catch (\Exception $e) {
            $errors[] = 'Chyba při validaci obrázku.';
        }

        return $errors;
    }

    /**
     * ✅ NOVÁ: Validace archivního souboru
     */
    private static function validateArchiveFile(FileUpload $file): array
    {
        $errors = [];

        try {
            $extension = strtolower(pathinfo($file->getName(), PATHINFO_EXTENSION));

            // Základní kontrola ZIP archivů
            if ($extension === 'zip') {
                $zip = new \ZipArchive();
                $result = $zip->open($file->getTemporaryFile());

                if ($result !== true) {
                    $errors[] = 'ZIP archiv je poškozen nebo neplatný.';
                    return $errors;
                }

                // Kontrola počtu souborů (max 1000)
                if ($zip->numFiles > 1000) {
                    $errors[] = 'ZIP archiv obsahuje příliš mnoho souborů (max. 1000).';
                }

                // Kontrola názvů souborů v archivu
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);

                    // Kontrola nebezpečných cest
                    if (strpos($filename, '../') !== false || strpos($filename, '..\\') !== false) {
                        $errors[] = 'ZIP archiv obsahuje nebezpečné cesty k souborům.';
                        break;
                    }

                    // Kontrola nebezpečných přípon
                    $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    if (in_array($fileExtension, self::$dangerousExtensions, true)) {
                        $errors[] = sprintf('ZIP archiv obsahuje nebezpečný soubor: %s', $filename);
                        break;
                    }
                }

                $zip->close();
            }
        } catch (\Exception $e) {
            $errors[] = 'Chyba při validaci archivu.';
        }

        return $errors;
    }

    /**
     * ✅ OPRAVENÉ: Virus scan pomocí dostupných nástrojů
     */
    private static function performVirusScan(string $filepath): array
    {
        $errors = [];

        try {
            // 1. ClamAV přes PHP extension (pokud je dostupné)
            if (function_exists('clamav_scan_file')) {

                // Definujeme konstanty pokud nejsou definované
                if (!defined('CL_CLEAN')) {
                    define('CL_CLEAN', 0);
                }
                if (!defined('CL_VIRUS')) {
                    define('CL_VIRUS', 1);
                }

                try {
                    $functionName = 'clamav_scan_file';
                    $scanResult = $functionName($filepath);

                    // Kontrola výsledku
                    if ($scanResult !== CL_CLEAN) {
                        $errors[] = 'Soubor obsahuje malware nebo virus (ClamAV PHP extension).';
                    }

                    // Pokud PHP extension funguje, vrátíme výsledek
                    return $errors;
                } catch (\Exception $e) {
                    // PHP extension selhalo, zkusíme command line
                    error_log('ClamAV PHP extension failed: ' . $e->getMessage());
                }
            }

            // 2. ClamAV přes command line (pokud je dostupné)
            if (self::isClamAVAvailable()) {
                try {
                    $output = [];
                    $returnCode = 0;

                    // Spustíme clamscan s timeout 30 sekund
                    $command = 'timeout 30 clamscan --no-summary --infected --quiet ' . escapeshellarg($filepath) . ' 2>&1';
                    exec($command, $output, $returnCode);

                    // ClamAV return codes:
                    // 0 = čistý soubor
                    // 1 = virus nalezen
                    // 2+ = chyba

                    if ($returnCode === 1) {
                        $errors[] = 'Soubor obsahuje malware nebo virus (ClamAV command line).';
                    } elseif ($returnCode > 1) {
                        // Chyba při skenování, ale neblokujeme upload
                        error_log('ClamAV scan error (code ' . $returnCode . '): ' . implode(' ', $output));
                    }

                    // Pokud command line funguje, vrátíme výsledek
                    return $errors;
                } catch (\Exception $e) {
                    error_log('ClamAV command line failed: ' . $e->getMessage());
                }
            }

            // 3. Základní heuristická kontrola jako fallback
            $heuristicErrors = self::performHeuristicScan($filepath);
            $errors = array_merge($errors, $heuristicErrors);
        } catch (\Exception $e) {
            // Pokud se celý virus scan nepodaří, pouze logujeme chybu
            // NEBLOKUJEME upload - bezpečnost je i v dalších kontrolách
            error_log('Virus scan completely failed: ' . $e->getMessage());
        }

        return $errors;
    }

    /**
     * ✅ VYLEPŠENÉ: Kontrola dostupnosti ClamAV s timeout
     */
    private static function isClamAVAvailable(): bool
    {
        try {
            $output = [];
            $returnCode = 0;

            // Zkusíme najít clamscan s timeout
            exec('timeout 5 which clamscan 2>/dev/null', $output, $returnCode);

            return $returnCode === 0 && !empty($output);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * ✅ VYLEPŠENÉ: Základní heuristická kontrola souboru
     */
    private static function performHeuristicScan(string $filepath): array
    {
        $errors = [];

        try {
            // Kontrola velikosti souboru (ochrana proti DoS)
            $fileSize = filesize($filepath);
            if ($fileSize === false || $fileSize > 50 * 1024 * 1024) { // 50MB limit pro scan
                return $errors; // Příliš velký soubor, přeskočit heuristickou kontrolu
            }

            // Přečíst prvních 2KB souboru pro analýzu
            $handle = fopen($filepath, 'rb');
            if ($handle === false) {
                return $errors;
            }

            $header = fread($handle, 2048);
            fclose($handle);

            if ($header === false || strlen($header) === 0) {
                return $errors;
            }

            // Kontrola podezřelých binárních vzorů a signatur
            $suspiciousPatterns = [
                // Executable signatures
                '/MZ.{0,100}PE/' => 'Soubor obsahuje Windows executable signaturu',
                '/\x7fELF/' => 'Soubor obsahuje Linux executable signaturu',

                // Script injections v obrázcích
                '/\xff\xd8\xff.{0,20}(<\?php|<script|javascript:)/i' => 'JPEG obsahuje podezřelý script',
                '/GIF8[79]a.{0,20}(<\?php|<script|javascript:)/i' => 'GIF obsahuje podezřelý script',
                '/\x89PNG.{0,20}(<\?php|<script|javascript:)/i' => 'PNG obsahuje podezřelý script',

                // HTML/JavaScript v binárních souborech
                '/(<html|<script|<iframe|javascript:|vbscript:)/i' => 'Binární soubor obsahuje web kód',

                // Podezřelé PHP tagy
                '/(<\?php|<\?=|\?>)/' => 'Soubor obsahuje PHP kód',

                // Base64 encoded scripts (časté u malware)
                '/eval\s*\(\s*base64_decode/i' => 'Podezřelý Base64 encoded kód',

                // Common malware strings
                '/(malware|trojan|backdoor|shell|payload)/i' => 'Podezřelé řetězce v souboru',
            ];

            foreach ($suspiciousPatterns as $pattern => $message) {
                if (preg_match($pattern, $header)) {
                    $errors[] = $message;
                    break; // Stačí první nalezená hrozba
                }
            }

            // Dodatečná kontrola: podezřelě vysoký podíl neASCII znaků
            $nonAsciiCount = 0;
            $headerLength = strlen($header);

            for ($i = 0; $i < $headerLength; $i++) {
                $byte = ord($header[$i]);
                if ($byte > 127 && $byte < 160) { // Podezřelý rozsah
                    $nonAsciiCount++;
                }
            }

            // Pokud je více než 30% podezřelých bytů, může jít o obfuskovaný kód
            if ($headerLength > 100 && ($nonAsciiCount / $headerLength) > 0.3) {
                $errors[] = 'Soubor obsahuje neobvykle vysoký podíl podezřelých bytů';
            }
        } catch (\Exception $e) {
            // Heuristická kontrola selhala, ale neblokujeme upload
            error_log('Heuristic scan failed: ' . $e->getMessage());
        }

        return $errors;
    }

    /**
     * ✅ NOVÁ: Získání povolených MIME typů podle kategorie
     */
    private static function getAllowedMimeTypes(string $type): array
    {
        switch ($type) {
            case 'image':
                return self::$allowedImageMimes;
            case 'document':
                return self::$allowedDocumentMimes;
            case 'archive':
                return self::$allowedArchiveMimes;
            default:
                return self::$allowedImageMimes; // Výchozí: obrázky
        }
    }

    /**
     * ✅ NOVÁ: Získání chybové zprávy pro upload error
     */
    private static function getFileUploadErrorMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'Soubor překračuje maximální povolenou velikost na serveru.';
            case UPLOAD_ERR_FORM_SIZE:
                return 'Soubor překračuje maximální velikost specifikovanou ve formuláři.';
            case UPLOAD_ERR_PARTIAL:
                return 'Soubor byl nahrán pouze částečně.';
            case UPLOAD_ERR_NO_FILE:
                return 'Nebyl vybrán žádný soubor.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Chybí dočasný adresář pro upload.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Soubor se nepodařilo zapsat na disk.';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload souboru byl zastaven rozšířením PHP.';
            default:
                return 'Nastala neznámá chyba při nahrávání souboru.';
        }
    }

    /**
     * ✅ NOVÁ: Formátování velikosti v bytech
     */
    private static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' B';
        }
    }

    /**
     * ✅ NOVÁ: Bezpečné generování názvu souboru
     */
    public static function generateSafeFilename(string $originalName, string $prefix = ''): string
    {
        // Získat příponu
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Sanitizace původního názvu
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $basename = self::sanitizeString($basename);
        $basename = preg_replace('/[^a-zA-Z0-9\-_]/', '', $basename);

        // Omezit délku
        if (strlen($basename) > 50) {
            $basename = substr($basename, 0, 50);
        }

        // Pokud není název validní, použijeme timestamp
        if (empty($basename)) {
            $basename = 'file_' . time();
        }

        // Sestavit finální název
        $filename = $prefix . $basename . '_' . uniqid() . '.' . $extension;

        return $filename;
    }

    /**
     * ✅ NOVÉ: Pokročilá validace ZIP souborů pro moduly
     */
    public static function validateZipFileUpload(FileUpload $file, int $maxFileSize = 10485760): array // 10MB default
    {
        $errors = [];

        // Základní kontroly souboru
        if (!$file->isOk()) {
            $errors[] = 'Soubor nebyl úspěšně nahrán.';
            return $errors;
        }

        // Kontrola velikosti souboru
        if ($file->getSize() > $maxFileSize) {
            $maxSizeMB = round($maxFileSize / 1048576, 2);
            $errors[] = 'Soubor je příliš velký. Maximální velikost je ' . $maxSizeMB . ' MB.';
        }

        if ($file->getSize() === 0) {
            $errors[] = 'Soubor je prázdný.';
        }

        // Kontrola MIME typu
        $allowedMimeTypes = [
            'application/zip',
            'application/x-zip-compressed',
            'multipart/x-zip'
        ];

        if (!in_array($file->getContentType(), $allowedMimeTypes)) {
            $errors[] = 'Neplatný typ souboru. Povoleny jsou pouze ZIP soubory.';
        }

        // Kontrola přípony souboru
        $filename = $file->getName();
        if (!preg_match('/\.zip$/i', $filename)) {
            $errors[] = 'Soubor musí mít příponu .zip.';
        }

        // ✅ KLÍČOVÁ KONTROLA: Magic bytes validace pro ZIP
        if ($file->getTemporaryFile()) {
            $handle = fopen($file->getTemporaryFile(), 'rb');
            if ($handle) {
                $magicBytes = fread($handle, 4);
                fclose($handle);

                // ZIP magic bytes: PK (0x504B)
                if (substr($magicBytes, 0, 2) !== "PK") {
                    $errors[] = 'Soubor není platný ZIP archiv.';
                }
            }
        }

        return $errors;
    }

    /**
     * ✅ NOVÉ: Bezpečná validace ZIP obsahu před extrakcí
     */
    public static function validateZipContents(string $zipPath): array
    {
        $errors = [];

        try {
            $zip = new \ZipArchive();
            $result = $zip->open($zipPath);

            if ($result !== TRUE) {
                $errors[] = 'Nepodařilo se otevřít ZIP soubor (kód: ' . $result . ').';
                return $errors;
            }

            $numFiles = $zip->numFiles;
            $totalSize = 0;
            $hasModuleJson = false;

            // Kontrola každého souboru v archivu
            for ($i = 0; $i < $numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $filename = $stat['name'];
                $size = $stat['size'];

                // ✅ BEZPEČNOST: Kontrola path traversal
                if (strpos($filename, '../') !== false || strpos($filename, '..\\') !== false) {
                    $errors[] = 'ZIP obsahuje nebezpečné cesty (path traversal): ' . $filename;
                    continue;
                }

                // ✅ BEZPEČNOST: Kontrola na absolútní cesty
                if (strpos($filename, '/') === 0 || preg_match('/^[a-zA-Z]:/', $filename)) {
                    $errors[] = 'ZIP obsahuje absolutní cestu: ' . $filename;
                    continue;
                }

                // ✅ BEZPEČNOST: Kontrola délky názvu souboru
                if (strlen($filename) > 255) {
                    $errors[] = 'Příliš dlouhý název souboru: ' . substr($filename, 0, 50) . '...';
                    continue;
                }

                // ✅ BEZPEČNOST: Kontrola nebezpečných souborů
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                $dangerousExtensions = ['exe', 'bat', 'cmd', 'scr', 'pif', 'com', 'vbs', 'jar'];

                if (in_array(strtolower($extension), $dangerousExtensions)) {
                    $errors[] = 'ZIP obsahuje nebezpečný soubor: ' . $filename;
                    continue;
                }

                // ✅ NOVÉ: Speciální kontrola pro JS soubory
                if (strtolower($extension) === 'js') {
                    // JS soubory jsou v modulech OK, ale kontrolujeme obsah na nebezpečné vzory
                    $jsContent = $zip->getFromIndex($i);
                    if ($jsContent !== false) {
                        $suspiciousPatterns = [
                            'eval\s*\(',
                            'new\s+Function\s*\(',
                            'document\.write\s*\(',
                            'window\.location\s*=',
                            'innerHTML\s*=.*<script',
                        ];

                        foreach ($suspiciousPatterns as $pattern) {
                            if (preg_match('/' . $pattern . '/i', $jsContent)) {
                                $errors[] = 'JS soubor obsahuje podezřelý kód: ' . $filename;
                                continue 2;
                            }
                        }
                    }
                }

                // Kontrola na module.json
                if (basename($filename) === 'module.json') {
                    $hasModuleJson = true;
                }

                $totalSize += $size;
            }

            $zip->close();

            // ✅ KONTROLA: Povinný module.json
            if (!$hasModuleJson) {
                $errors[] = 'ZIP neobsahuje povinný soubor module.json.';
            }

            // ✅ KONTROLA: Maximální celková velikost rozbalených souborů (zip bomb ochrana)
            $maxUncompressedSize = 100 * 1024 * 1024; // 100MB
            if ($totalSize > $maxUncompressedSize) {
                $totalSizeMB = round($totalSize / 1048576, 2);
                $maxSizeMB = round($maxUncompressedSize / 1048576, 2);
                $errors[] = 'Rozbalený obsah ZIP je příliš velký (' . $totalSizeMB . ' MB). Maximum je ' . $maxSizeMB . ' MB.';
            }
        } catch (\Exception $e) {
            $errors[] = 'Chyba při analýze ZIP souboru: ' . $e->getMessage();
        }

        return $errors;
    }

    /**
     * ✅ NOVÉ: Generování bezpečného názvu pro ZIP soubor
     */
    public static function generateSafeZipFilename(string $originalName, string $prefix = 'module_'): string
    {
        // Odstranit příponu a nebezpečné znaky
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
        $baseName = trim($baseName, '_-');

        // Pokud je název prázdný nebo moc krátký
        if (strlen($baseName) < 3) {
            $baseName = 'module';
        }

        // Omezit délku
        $baseName = substr($baseName, 0, 50);

        // Přidat timestamp a náhodný řetězec
        $timestamp = date('Y-m-d_H-i-s');
        $random = substr(uniqid(), -6);

        return $prefix . $baseName . '_' . $timestamp . '_' . $random . '.zip';
    }
}
