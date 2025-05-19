<?php

namespace App\Model;

use TCPDF;
use Exception;

class QrPaymentService
{
    /**
     * Přidá QR kód pro platbu přímo do PDF
     *
     * @param TCPDF $pdf Instance PDF dokumentu
     * @param float $x X-ová souřadnice pro QR kód
     * @param float $y Y-ová souřadnice pro QR kód 
     * @param float $w Šířka QR kódu
     * @param float $h Výška QR kódu
     * @param string $accountNumber Číslo účtu
     * @param float $amount Částka
     * @param string $variableSymbol Variabilní symbol
     * @param string $message Zpráva příjemci
     * @return void
     */
    public function addQrPaymentToPdf(
        TCPDF $pdf,
        float $x,
        float $y,
        float $w,
        float $h,
        string $accountNumber,
        float $amount,
        string $variableSymbol = '',
        string $message = ''
    ): void {
        // Generování SPAYD řetězce pro QR kód
        $spayd = $this->generateCzechSpayd($accountNumber, $amount, $variableSymbol, $message);
        
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
        
        // Přidání popisku nad QR kód
        $currentY = $pdf->GetY();
        $currentX = $pdf->GetX();
        $pdf->SetXY($x, $y - 10);
        $pdf->SetFont('dejavusans', 'B', 9);
        $pdf->Cell($w, 5, 'QR kód pro platbu', 0, 0, 'C');
        $pdf->SetXY($currentX, $currentY);
    }
    
    /**
     * Vytvoření QR kódu s platebními údaji ve formátu SPAYD
     * 
     * @param string $account Číslo účtu ve formátu 'číslo/kód_banky'
     * @param float $amount Částka k úhradě
     * @param string $vs Variabilní symbol
     * @param string $message Zpráva pro příjemce
     * @return string SPAYD řetězec
     */
    private function generateCzechSpayd($account, $amount, $vs = '', $message = '') {
        // Ujistíme se, že máme účet ve formátu číslo/kód
        if (strpos($account, '/') === false) {
            throw new Exception("Nesprávný formát čísla účtu, očekává se ve formátu 'číslo/kód_banky'");
        }
        
        $accountParts = explode('/', $account);
        $accountNumber = trim($accountParts[0]);
        $bankCode = trim($accountParts[1]);

        $iban = $this->accountToIBAN($accountNumber, $bankCode);

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
     * Převod českého čísla účtu na IBAN formát
     * 
     * @param string $accountNumber Číslo účtu
     * @param string $bankCode Kód banky
     * @return string IBAN
     */
    private function accountToIBAN($accountNumber, $bankCode) {
        // Kontrola, jestli jde o účet s předčíslím nebo bez
        if (strpos($accountNumber, '-') !== false) {
            list($prefix, $number) = explode('-', $accountNumber);
        } else {
            $prefix = '';
            $number = $accountNumber;
        }
        
        // Odstranění mezer a dalších znaků
        $number = preg_replace('/\D/', '', $number);
        $prefix = preg_replace('/\D/', '', $prefix);
        $bankCode = preg_replace('/\D/', '', $bankCode);
        
        // Pro český IBAN musí být account number složeno z předčíslí a čísla
        // a doplněno na 16 znaků zleva nulami
        if (!empty($prefix)) {
            $prefix = str_pad($prefix, 6, '0', STR_PAD_LEFT);
        } else {
            $prefix = '000000'; // Není-li předčíslí, doplníme nulami
        }
        
        $number = str_pad($number, 10, '0', STR_PAD_LEFT);
        $bankCode = str_pad($bankCode, 4, '0', STR_PAD_LEFT);
        
        // BBAN = bank code (4 znaky) + account number (16 znaků)
        $bban = $bankCode . $prefix . $number;
        
        // Převod na numerickou hodnotu pro výpočet kontrolní sumy
        $countryCode = 'CZ';
        $numericCountry = '1235'; // CZ → C=12, Z=35
        $ibanCheckString = $bban . $numericCountry . '00';

        // Výpočet kontrolního čísla (mod 97)
        if (function_exists('bcmod')) {
            $mod = bcmod($ibanCheckString, '97');
        } else {
            // Pro případ, že není k dispozici bcmod
            $mod = $this->modulo($ibanCheckString, 97);
        }
        
        $checkDigits = 98 - $mod;
        
        // Sestavení kompletního IBAN
        $iban = $countryCode . str_pad($checkDigits, 2, '0', STR_PAD_LEFT) . $bban;

        return $iban;
    }
    
    /**
     * Alternativní implementace modulo pro velká čísla bez extension bcmath
     * 
     * @param string $dividend Dělenec
     * @param int $divisor Dělitel
     * @return int Zbytek po dělení
     */
    private function modulo($dividend, $divisor) {
        $mod = 0;
        for ($i = 0; $i < strlen($dividend); $i++) {
            $mod = ($mod * 10 + (int)$dividend[$i]) % $divisor;
        }
        return $mod;
    }
}