<?php

declare(strict_types=1);

namespace App\Security;

use Nette;

/**
 * Bezpečnostní validátor pro vstupní data
 * Chrání proti XSS, zajišťuje validaci a sanitizaci vstupů
 */
class SecurityValidator
{
    use Nette\SmartObject;

    /**
     * Sanitizuje vstupní řetězec proti XSS útokům
     */
    public static function sanitizeString(string $input): string
    {
        // Odstranění nebezpečných tagů a atributů
        $input = strip_tags($input);
        
        // Převod HTML entit
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Odstranění přebytečných mezer
        $input = trim($input);
        
        return $input;
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
            $errors[] = 'Uživatelské jméno může obsahovat pouze písmena, číslice, podtržítka a pomlčky.';
        }
        
        // Nesmí začínat číslem
        if (preg_match('/^[0-9]/', $username)) {
            $errors[] = 'Uživatelské jméno nesmí začínat číslem.';
        }
        
        // ✅ NOVÉ: Blacklist zakázaných jmen
        $forbiddenUsernames = [
            'admin', 'administrator', 'root', 'superuser', 'super', 'user',
            'test', 'demo', 'guest', 'null', 'undefined', 'system', 'support',
            'help', 'info', 'mail', 'email', 'webmaster', 'postmaster',
            'noreply', 'no-reply', 'api', 'www', 'ftp', 'smtp', 'pop3',
            'imap', 'sql', 'database', 'db', 'backup', 'config', 'settings'
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
                return preg_match('/^[0-9]{5}$/', $postalCode);
                
            case 'SK':
                // Slovenské PSČ: 5 číslic nebo 3 číslice mezera 2 číslice
                return preg_match('/^[0-9]{5}$/', $postalCode);
                
            default:
                // Obecná kontrola: 3-10 alfanumerických znaků
                return preg_match('/^[0-9A-Z]{3,10}$/', strtoupper($postalCode));
        }
    }

    /**
     * Kompletní sanitizace všech vstupních dat z formuláře
     */
    public static function sanitizeFormData(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
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
                        
                    default:
                        $sanitized[$key] = self::sanitizeString($value);
                        break;
                }
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
}