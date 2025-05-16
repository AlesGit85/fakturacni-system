<?php

namespace App\Helpers;

class CzechQrHelpers
{
    /**
     * Převod českého čísla účtu na IBAN formát
     * @param string $accountNumber Číslo účtu
     * @param string $bankCode Kód banky
     * @return string IBAN
     */
    public static function accountToIBAN($accountNumber, $bankCode)
    {
        // Odstranění mezer a dalších znaků
        $accountNumber = preg_replace('/\D/', '', $accountNumber);
        $bankCode = preg_replace('/\D/', '', $bankCode);
        
        // Pro český IBAN musí být account number doplněno na 16 znaků zleva nulami
        $accountNumber = str_pad($accountNumber, 16, '0', STR_PAD_LEFT);
        $bankCode = str_pad($bankCode, 4, '0', STR_PAD_LEFT);
        
        // BBAN = bank code (4 znaky) + account number (16 znaků)
        $bban = $bankCode . $accountNumber;
        
        // Převod na numerickou hodnotu pro výpočet kontrolní sumy
        $countryCode = 'CZ';
        $numericCountry = '1235'; // CZ → C=12, Z=35
        $ibanCheckString = $bban . $numericCountry . '00';

        // Výpočet kontrolního čísla (mod 97)
        $mod = bcmod($ibanCheckString, '97');
        $checkDigits = 98 - $mod;
        
        // Sestavení kompletního IBAN
        $iban = $countryCode . str_pad($checkDigits, 2, '0', STR_PAD_LEFT) . $bban;

        return $iban;
    }

    /**
     * Vytvoření QR kódu s platebními údaji ve formátu SPAYD
     * @param string $account Číslo účtu ve formátu 'číslo/kód_banky'
     * @param float $amount Částka k úhradě
     * @param string $vs Variabilní symbol
     * @param string $message Zpráva pro příjemce
     * @return string SPAYD řetězec
     */
    public static function generateCzechSpayd($account, $amount, $vs = '', $message = '')
    {
        $accountParts = explode('/', $account);
        if (count($accountParts) != 2) {
            return "ERROR: Nesprávný formát čísla účtu";
        }

        $accountNumber = trim($accountParts[0]);
        $bankCode = trim($accountParts[1]);

        $iban = self::accountToIBAN($accountNumber, $bankCode);

        $spayd = 'SPD*1.0';
        $spayd .= '*ACC:' . $iban;
        $spayd .= '*AM:' . number_format((float)$amount, 2, '.', '');
        $spayd .= '*CC:CZK';

        if (!empty($vs)) {
            $spayd .= '*X-VS:' . $vs;
        }

        if (!empty($message)) {
            $message = str_replace(['*', '\n'], ['', ' '], $message);
            $spayd .= '*MSG:' . $message;
        }

        return $spayd;
    }
    
    /**
     * Přidá QR kód pro platbu na PDF dokument
     * @param \TCPDF $pdf Instance PDF dokumentu
     * @param float $x X souřadnice QR kódu
     * @param float $y Y souřadnice QR kódu
     * @param float $w Šířka QR kódu
     * @param float $h Výška QR kódu
     * @param string $account Číslo účtu ve formátu 'číslo/kód_banky'
     * @param float $amount Částka k úhradě
     * @param string $vs Variabilní symbol
     * @param string $message Zpráva pro příjemce
     * @return void
     */
    public static function addCzechQRPayment($pdf, $x, $y, $w, $h, $account, $amount, $vs = '', $message = '')
    {
        // Generování SPAYD řetězce pro QR kód
        $spayd = self::generateCzechSpayd($account, $amount, $vs, $message);
        
        // Nejprve vykreslíme bílý obdélník s rámečkem jako podklad pro QR kód
        $pdf->SetFillColor(255, 255, 255); // Bílá barva
        $pdf->SetDrawColor(190, 190, 190); // Světle šedá barva pro rámeček
        
        // Přidáme malé odsazení kolem QR kódu
        $padding = 2;
        $pdf->Rect($x - $padding, $y - $padding, $w + ($padding * 2), $h + ($padding * 2), 'DF', array('all' => array('width' => 0.5, 'color' => array(190, 190, 190))));
        
        // Vytvoření QR kódu pomocí TCPDF
        $style = array(
            'border' => false,
            'vpadding' => 0,
            'hpadding' => 0,
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false,
            'module_width' => 1,
            'module_height' => 1
        );
        
        // Přidání QR kódu do PDF
        $pdf->write2DBarcode($spayd, 'QRCODE,M', $x, $y, $w, $h, $style, 'N');
    }
}