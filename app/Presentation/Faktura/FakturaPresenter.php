<?php

declare(strict_types=1);

namespace App\Presentation\Faktura;

use Nette;
use Nette\Application\UI\Form;

final class FakturaPresenter extends Nette\Application\UI\Presenter
{
    /** @var string */
    private $configDir;
    
    /** @var string */
    private $fakturaDir;

    public function __construct()
    {
        parent::__construct();
        $this->configDir = __DIR__ . '/../../../config/fakturaci';
        $this->fakturaDir = __DIR__ . '/../../../faktury';
    }

    protected function startup(): void
    {
        parent::startup();
        
        // Zajistíme, že adresáře existují
        if (!is_dir($this->configDir)) {
            mkdir($this->configDir, 0755, true);
        }
        
        if (!is_dir($this->fakturaDir)) {
            mkdir($this->fakturaDir, 0755, true);
        }
    }

    public function renderDefault(): void
    {
        // Načtení seznamu odběratelů
        $odberateleFile = $this->configDir . '/odberatele.json';
        if (file_exists($odberateleFile)) {
            $odberatele = json_decode(file_get_contents($odberateleFile), true);
            if (!is_array($odberatele)) {
                $odberatele = [];
            }
        } else {
            $odberatele = [];
        }
        
        // Abecední řazení odběratelů
        usort($odberatele, function ($a, $b) {
            return strcasecmp($a['nazev'], $b['nazev']);
        });
        
        // Načtení údajů o dodavateli
        $dodavatelFile = $this->configDir . '/dodavatel.json';
        if (file_exists($dodavatelFile)) {
            $dodavatel = json_decode(file_get_contents($dodavatelFile), true);
        } else {
            // Výchozí hodnoty, pokud konfigurace neexistuje
            $dodavatel = [
                'nazev' => 'Aleš Zita',
                'adresa' => '503 46, Librantice 167',
                'ico' => '87894912',
                'dic' => '',
                'ucet' => '2695541004/5500',
                'banka' => 'Raiffeisenbank a.s.',
                'swift' => 'RZBCCZPP',
                'iban' => 'CZ3655000000002695541004',
                'plátce_dph' => false,
                'telefon' => '+420 703 985 390',
                'email' => 'a.zita@post.cz',
                'web' => '',
                'zivnost_1' => 'Úřad příslušný podle §71 odst.2 živnostenského zákona:',
                'zivnost_2' => 'Magistrát města Hradec Králové'
            ];
        }
        
        $this->template->odberatele = $odberatele;
        $this->template->dodavatel = $dodavatel;
        $this->template->defaultVS = $this->generujVS();
    }
    
    protected function createComponentFakturaForm(): Form
    {
        $form = new Form;
        
        // Získat seznam odběratelů pro select
        $odberatele = $this->loadOdberatele();
        $odberateleOptions = ['' => '-- Vyberte odběratele --'];
        foreach ($odberatele as $odberatel) {
            $odberateleOptions[$odberatel['id']] = $odberatel['nazev'];
        }
        
        $form->addSelect('odberatel_id', 'Vyberte odběratele', $odberateleOptions)
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('id', 'odberatel-select');
            
        $form->addCheckbox('manual_toggle', 'Zadat odběratele ručně')
            ->setHtmlAttribute('id', 'manual-toggle');
            
        $form->addTextArea('odberatel', 'Odběratel (název a adresa)')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('id', 'odberatel')
            ->setHtmlAttribute('rows', 6)
            ->setRequired('Zadejte údaje o odběrateli');
            
        $form->addText('castka', 'Částka (Kč)')
            ->setHtmlType('number')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('min', 0)
            ->setHtmlAttribute('step', 1)
            ->setRequired('Zadejte částku');
            
        $form->addText('vs', 'Variabilní symbol')
            ->setHtmlAttribute('class', 'form-control')
            ->setDefaultValue($this->generujVS())
            ->setRequired('Zadejte variabilní symbol');
            
        $form->addText('splatnost', 'Datum splatnosti')
            ->setHtmlType('date')
            ->setHtmlAttribute('class', 'form-control')
            ->setDefaultValue(date('Y-m-d', strtotime('+14 days')))
            ->setRequired('Zadejte datum splatnosti');
            
        $form->addText('predmet1', 'Předmět fakturace 1')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired('Zadejte alespoň jeden předmět fakturace');
            
        $form->addText('predmet2', 'Předmět fakturace 2 (volitelné)')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addText('predmet3', 'Předmět fakturace 3 (volitelné)')
            ->setHtmlAttribute('class', 'form-control');
            
        // Získat info, zda má dodavatel logo
        $dodavatel = $this->loadDodavatel();
        $maLogo = !empty($dodavatel['logo']) && file_exists(__DIR__ . '/../../../www/' . $dodavatel['logo']);
            
        if ($maLogo) {
            $form->addCheckbox('pridat_logo', 'Přidat logo na fakturu')
                ->setHtmlAttribute('id', 'pridat_logo');
        }
            
        $form->addSubmit('generate', 'Vygenerovat fakturu (PDF)')
            ->setHtmlAttribute('class', 'btn btn-primary');
            
        $form->onSuccess[] = [$this, 'fakturaFormSucceeded'];
        
        return $form;
    }

public function fakturaFormSucceeded(Form $form, array $values): void
{
    try {
        // Načtení údajů o dodavateli
        $dodavatel = $this->loadDodavatel();
        
        // Validace a sanitizace vstupů
        $odberatel = $values['odberatel']; // Ponecháme české znaky
        $odberatel = str_replace(["\r\n", "\r"], "\n", $odberatel); // Normalizace konců řádků

        $castka = filter_var($values['castka'], FILTER_VALIDATE_FLOAT) ?: 0;
        $vs = preg_replace('/\D/', '', $values['vs']); // pouze číslice
        $splatnost = $values['splatnost'];

        $predmet1 = $values['predmet1']; // První položka (povinná)
        $predmet2 = $values['predmet2'] ?? ''; // Nepovinná položka
        $predmet3 = $values['predmet3'] ?? ''; // Nepovinná položka

        // Formátování pro zobrazení
        $castka_zobrazeni = number_format($castka, 0, ',', ' ') . ' Kč';
        $datum_vystaveni = date('j. n. Y');
        $datum_splatnosti = date('j. n. Y', strtotime($splatnost));

        // Vytvoření PDF pomocí TCPDF
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Nastavení dokumentu - metadata
        $pdf->SetCreator('Fakturační systém');
        $pdf->SetAuthor($dodavatel['nazev']);
        $pdf->SetTitle('Faktura č. ' . $vs);
        $pdf->SetSubject('Faktura č. ' . $vs);
        $pdf->SetKeywords('Faktura, ' . $vs);

        // Nastavení hlavičky a patičky
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Nastavení výchozího fontu - DejaVu Sans pro dobrou podporu češtiny
        $pdf->SetFont('dejavusans', '', 10);

        // Nastavení okrajů [mm] - levý, horní, pravý
        $pdf->SetMargins(15, 15, 15);

        // Automatické zalomení stránky
        $pdf->SetAutoPageBreak(true, 15);

        // Přidání stránky
        $pdf->AddPage();

        // Přidání loga, pokud je požadováno
        $pouzit_logo = isset($values['pridat_logo']) && $values['pridat_logo'] && 
                      !empty($dodavatel['logo']) && file_exists(__DIR__ . '/../../../www/' . $dodavatel['logo']);
        
        if ($pouzit_logo) {
            $logoPath = __DIR__ . '/../../../www/' . $dodavatel['logo'];
            $logoExtension = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));

            // Zjistíme velikost obrázku
            $logoInfo = getimagesize($logoPath);
            if ($logoInfo) {
                $logoWidth = $logoInfo[0];
                $logoHeight = $logoInfo[1];

                // Lepší způsob výpočtu rozměrů pro zachování kvality
                $maxLogoWidth = 40; // v mm - menší než předtím, aby bylo logo ostřejší
                $maxLogoHeight = 15; // v mm

                // Převod z px na mm (přibližně)
                $pxToMm = 0.264583; // 1px = 0.264583mm
                $logoWidthMm = $logoWidth * $pxToMm;
                $logoHeightMm = $logoHeight * $pxToMm;

                // Výpočet poměru stran
                $scaleX = $maxLogoWidth / $logoWidthMm;
                $scaleY = $maxLogoHeight / $logoHeightMm;
                $scale = min($scaleX, $scaleY);

                // Pokud je měřítko menší než 1, obrázek je třeba zmenšit
                if ($scale < 1) {
                    $finalWidth = $logoWidthMm * $scale;
                    $finalHeight = $logoHeightMm * $scale;
                } else {
                    // Jinak zachováme původní rozměry (v mm)
                    $finalWidth = $logoWidthMm;
                    $finalHeight = $logoHeightMm;
                }

                // Umístění loga vlevo nahoře s lepší kvalitou
                if ($logoExtension === 'svg') {
                    // Pro SVG soubory
                    $pdf->ImageSVG($logoPath, 15, 15, $finalWidth, $finalHeight);
                } else {
                    // Pro rastrové soubory s vyšší kvalitou
                    $pdf->Image($logoPath, 15, 15, $finalWidth, $finalHeight, '', '', '', false, 300);
                }
            }
        }

        // Barvy pro fakturační položky - změna na #cacaca
        $greyColor = array(202, 202, 202); // Barva #cacaca

        // Nadpis FAKTURA a číslo
        $pageWidth = 180; // dostupná šířka v mm po odečtení okrajů
        $fakturaText = 'FAKTURA';
        $fakturaWidth = 50; // šířka buňky pro "FAKTURA"
        $vsWidth = 60; // šířka buňky pro číslo faktury
        $emptyWidth = $pageWidth - $fakturaWidth - $vsWidth; // zbývající šířka

        // Nastavíme barvu a font pro "FAKTURA"
        $pdf->SetFont('dejavusans', '', 24);
        $pdf->SetTextColor($greyColor[0], $greyColor[1], $greyColor[2]);

        // První buňka - prázdná pro odsazení zleva
        $pdf->Cell($emptyWidth, 10, '', 0, 0, 'L');

        // Druhá buňka - text "FAKTURA" zarovnaný doprava
        $pdf->Cell($fakturaWidth, 10, $fakturaText, 0, 0, 'R');

        // Třetí buňka - číslo faktury (VS) zarovnané doleva
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('dejavusans', 'B', 24);
        $pdf->Cell($vsWidth, 10, $vs, 0, 1, 'L');

        // Šedý blok v záhlaví
        $pdf->Ln(5);
        $startY = $pdf->GetY();
        $endY = $startY + 95; // Konec lichoběžníku

        // Pozice QR kódu - menší a zarovnaný na střed vertikálně
        $qrWidth = 40; // Menší QR kód
        $qrHeight = 40; // Menší QR kód
        $qrX = 150; // Posunuto více doprava

        // Výpočet Y pozice pro střed bloku
        $blockMiddleY = ($startY + $endY) / 2;
        $qrY = $blockMiddleY - ($qrHeight / 2); // Zarovnání na střed

        // Výpočet souřadnic pro lichoběžník
        $p0x = 0; // Bod začíná od levého okraje stránky
        $p0y = $startY;
        $p1x = 195;
        $p1y = $startY;
        $p2x = $qrX; // Zkosení směřuje k levému okraji QR kódu
        $p2y = $endY;
        $p3x = 0; // Levá strana je rovná
        $p3y = $endY;

        // Kreslení šedého pozadí - lichoběžník se zkosením vpravo
        $pdf->SetFillColor($greyColor[0], $greyColor[1], $greyColor[2]);
        $points = array(
            $p0x, $p0y,  // levý horní roh
            $p1x, $p1y,  // pravý horní roh
            $p2x, $p2y,  // pravý dolní roh (zkosený)
            $p3x, $p3y   // levý dolní roh
        );
        $pdf->Polygon($points, 'F');

        // Nastavení pro tabulku platebních údajů
        $leftMargin = 20; // Levý okraj pro všechny položky
        $labelWidth = 36;
        $valueWidth = 75;
        $rowHeight = 5.5; // Výška řádku
        $labelRightPadding = 20; // Odsazení mezi popiskem a hodnotou

        // Prosím o zaplacení - zarovnáno s ostatními položkami
        $pdf->SetXY($leftMargin, $startY + 10);
        $pdf->SetFont('dejavusans', '', 11);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(100, 5, 'Prosím o zaplacení', 0, 1, 'L');

        // Částka - velká, zarovnáno s ostatními položkami
        $pdf->SetXY($leftMargin, $startY + 16);
        $pdf->SetFont('dejavusans', 'B', 28);
        $pdf->Cell(100, 10, $castka_zobrazeni, 0, 1, 'L');

        // Vložení mezery před platební údaje
        $pdf->Ln(5);

        // Pozice X pro hodnoty (zarovnané)
        $valueX = $leftMargin + $labelWidth + $labelRightPadding - 20;

        // Forma úhrady
        $pdf->SetXY($leftMargin, $pdf->GetY());
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($labelWidth, $rowHeight, 'Forma úhrady:', 0, 0, 'L');
        $pdf->SetX($valueX);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($valueWidth, $rowHeight, 'bankovním převodem', 0, 1, 'L');

        // Číslo účtu
        $pdf->SetXY($leftMargin, $pdf->GetY());
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($labelWidth, $rowHeight, 'Číslo účtu:', 0, 0, 'L');
        $pdf->SetX($valueX);
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell($valueWidth, $rowHeight, $dodavatel['ucet'], 0, 1, 'L');

        // Mezera před variabilním symbolem
        $pdf->Ln(2);

        // Variabilní symbol
        $pdf->SetXY($leftMargin, $pdf->GetY());
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($labelWidth, $rowHeight, 'Variabilní symbol:', 0, 0, 'L');
        $pdf->SetX($valueX);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($valueWidth, $rowHeight, $vs, 0, 1, 'L');

        // Datum vystavení
        $pdf->SetXY($leftMargin, $pdf->GetY());
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($labelWidth, $rowHeight, 'Datum vystavení:', 0, 0, 'L');
        $pdf->SetX($valueX);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($valueWidth, $rowHeight, $datum_vystaveni, 0, 1, 'L');

        // Datum splatnosti
        $pdf->SetXY($leftMargin, $pdf->GetY());
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($labelWidth, $rowHeight, 'Datum splatnosti:', 0, 0, 'L');
        $pdf->SetX($valueX);
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell($valueWidth, $rowHeight, $datum_splatnosti, 0, 1, 'L');

        // Vložení mezery před další údaje
        $pdf->Ln(2);

        // Banka
        $pdf->SetXY($leftMargin, $pdf->GetY());
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($labelWidth, $rowHeight, 'Banka:', 0, 0, 'L');
        $pdf->SetX($valueX);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($valueWidth, $rowHeight, $dodavatel['banka'], 0, 1, 'L');

        // BIC/SWIFT
        $pdf->SetXY($leftMargin, $pdf->GetY());
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($labelWidth, $rowHeight, 'BIC/SWIFT:', 0, 0, 'L');
        $pdf->SetX($valueX);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($valueWidth, $rowHeight, $dodavatel['swift'], 0, 1, 'L');

        // IBAN
        $pdf->SetXY($leftMargin, $pdf->GetY());
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($labelWidth, $rowHeight, 'IBAN:', 0, 0, 'L');
        $pdf->SetX($valueX);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($valueWidth, $rowHeight, $dodavatel['iban'], 0, 1, 'L');

        // QR kód na pravé straně hlavičky
        \App\Helpers\CzechQrHelpers::addCzechQRPayment(
            $pdf, $qrX, $qrY, $qrWidth, $qrHeight, 
            $dodavatel['ucet'], $castka, $vs, 'Faktura ' . $vs
        );

        // Začátek sekce s dodavatelem a odběratelem - pod lichoběžníkem
        $pdf->SetY($endY + 5);

        // Dodavatel - barva #cacaca
        $pdf->SetTextColor($greyColor[0], $greyColor[1], $greyColor[2]);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(90, 8, 'Dodavatel', 0, 0, 'L');

        // Odběratel - barva #cacaca
        $pdf->Cell(90, 8, 'Odběratel', 0, 1, 'L');

        // Údaje dodavatele
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(90, 6, $dodavatel['nazev'], 0, 0, 'L');

        // Údaje odběratele - rozdělení textu na řádky
        $odberatelLines = explode("\n", $odberatel);
        $pdf->SetFont('dejavusans', 'B', 10);
        if (isset($odberatelLines[0])) {
            // První řádek - název firmy
            $pdf->Cell(90, 6, $odberatelLines[0], 0, 1, 'L');
        } else {
            $pdf->Cell(90, 6, '', 0, 1, 'L');
        }

        // Pokračování dodavatele
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(90, 6, $dodavatel['adresa'], 0, 0, 'L');

        // Funkce pro zalamování dlouhého textu
        $wrapText = function($text, $maxLen = 40) {
            if (strlen($text) <= $maxLen) {
                return $text;
            }
            // Hledáme poslední mezeru před limitem
            $cutPoint = strrpos(substr($text, 0, $maxLen), ' ');
            if ($cutPoint === false) {
                $cutPoint = $maxLen;
            }
            return substr($text, 0, $cutPoint) . "\n" .
                $wrapText(substr($text, $cutPoint + 1), $maxLen);
        };

        // Zpracování adresy odběratele s kontrolou délky řádků
        $adresaLines = [];
        for ($i = 1; $i < count($odberatelLines); $i++) {
            if (isset($odberatelLines[$i]) && !empty($odberatelLines[$i])) {
                // Kontrola délky řádku a případné zalamování
                if (strlen($odberatelLines[$i]) > 40) {
                    $wrapped = $wrapText($odberatelLines[$i], 40);
                    $wrappedLines = explode("\n", $wrapped);
                    foreach ($wrappedLines as $line) {
                        $adresaLines[] = $line;
                    }
                } else {
                    $adresaLines[] = $odberatelLines[$i];
                }
            }
        }

        // Vykreslení adresy odběratele
        $lineCount = 0;
        foreach ($adresaLines as $line) {
            if ($lineCount === 0) {
                // První řádek jsme již vypsali výše (druhý řádek vstupu)
                $pdf->Cell(90, 6, $line, 0, 1, 'L');
            } else {
                $pdf->Cell(90, 6, '', 0, 0, 'L');
                $pdf->Cell(90, 6, $line, 0, 1, 'L');
            }
            $lineCount++;

            // Omezíme počet řádků na 5 (máme už jeden řádek z názvu firmy)
            if ($lineCount >= 5) {
                break;
            }
        }

        // Doplníme prázdné řádky, pokud je třeba
        while ($lineCount < 5) {
            $pdf->Cell(90, 6, '', 0, 0, 'L');
            $pdf->Cell(90, 6, '', 0, 1, 'L');
            $lineCount++;
        }

        $pdf->Cell(90, 6, 'IČO: ' . $dodavatel['ico'], 0, 0, 'L');
        $pdf->Cell(90, 6, '', 0, 1, 'L');

        if ($dodavatel['platce_dph']) {
            $pdf->Cell(90, 6, 'DIČ: ' . $dodavatel['dic'], 0, 0, 'L');
        } else {
            $pdf->Cell(90, 6, 'Nejsem plátce DPH.', 0, 0, 'L');
        }
        $pdf->Cell(90, 6, '', 0, 1, 'L');

        // Živnostenský úřad
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->Cell(180, 4, $dodavatel['zivnost_1'], 0, 1, 'L');
        $pdf->Cell(180, 4, $dodavatel['zivnost_2'], 0, 1, 'L');

        // Fakturuji Vám za
        $pdf->Ln(5);
        $pdf->SetTextColor($greyColor[0], $greyColor[1], $greyColor[2]);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(180, 8, 'Fakturuji Vám za', 0, 1, 'L');

        // Předměty fakturace
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('dejavusans', '', 10);

        // První položka (povinná)
        $pdf->Cell(180, 8, $predmet1, 'B', 1, 'L');

        // Druhá položka (nepovinná)
        if (!empty($predmet2)) {
            $pdf->Cell(180, 8, $predmet2, 'B', 1, 'L');
        } else {
            $pdf->Cell(180, 8, '', 'B', 1, 'L');
        }

        // Třetí položka (nepovinná)
        if (!empty($predmet3)) {
            $pdf->Cell(180, 8, $predmet3, 'B', 1, 'L');
        } else {
            $pdf->Cell(180, 8, '', 'B', 1, 'L');
        }

        // Celkem zaplaťte
        $pdf->Ln(5);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(100, 8, '', 0, 0, 'L');
        $pdf->Cell(30, 8, 'Celkem zaplaťte: ', 0, 0, 'R');
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(50, 8, $castka_zobrazeni, 0, 1, 'L');

        // Zápatí faktury
        $footerColor = array(57, 59, 65); // #393b41
        $pageWidth = 210;
        $pageHeight = 297;
        $footerHeight = 20;
        $footerStartY = $pageHeight - $footerHeight;
        $footerEndY = $pageHeight;
        $rightEdge = $pageWidth;
        $leftEdgeTop = 70;
        $leftEdgeBottom = 50;

        // Vypnutí automatického zalomení stránky
        $pdf->SetAutoPageBreak(false);

        // Kreslení tmavého pozadí zápatí
        $pdf->SetFillColor($footerColor[0], $footerColor[1], $footerColor[2]);
        $points = array(
            $rightEdge, $footerStartY,
            $leftEdgeTop, $footerStartY,
            $leftEdgeBottom, $footerEndY,
            $rightEdge, $footerEndY
        );
        $pdf->Polygon($points, 'F');

        // Text v zápatí - bílá barva
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('dejavusans', '', 9);

        $colWidth = 60;
        $col1X = $leftEdgeTop + 15;
        $col2X = $col1X + $colWidth + 10;
        $verticalMiddle = $footerStartY + ($footerHeight / 2) - 5;

        // První sloupec - jméno, příjmení a telefon
        $pdf->SetXY($col1X, $verticalMiddle);
        $pdf->Cell($colWidth, 6, $dodavatel['nazev'], 0, 1, 'L');
        $pdf->SetXY($col1X, $verticalMiddle + 6);
        $pdf->Cell($colWidth, 6, $dodavatel['telefon'], 0, 1, 'L');

        // Druhý sloupec - email
        $pdf->SetXY($col2X, $verticalMiddle);
        $pdf->Cell($colWidth, 6, 'Email:', 0, 1, 'L');
        $pdf->SetXY($col2X, $verticalMiddle + 6);
        $pdf->Cell($colWidth, 6, $dodavatel['email'], 0, 1, 'L');

        // Generování názvu souboru a cesty k němu
        $pdfFileName = 'faktura_' . $vs . '.pdf';
        $pdfFilePath = $this->fakturaDir . '/' . $pdfFileName;

        // Uložení PDF na disk
        $pdf->Output($pdfFilePath, 'F');

        // Uložení metadat faktury pro pozdější vyhledávání a filtrování
        $metadataFile = $this->configDir . '/faktury_metadata.json';
        $fakturaData = [
            'vs' => $vs,
            'soubor' => $pdfFileName,
            'datum_vystaveni' => $datum_vystaveni,
            'datum_splatnosti' => $datum_splatnosti,
            'castka' => $castka,
            'odberatel' => trim(explode("\n", $odberatel)[0]),
            'zaplaceno' => false
        ];

        // Načtení existujících metadat nebo vytvoření nových
        if (file_exists($metadataFile)) {
            $metadata = json_decode(file_get_contents($metadataFile), true) ?: [];
        } else {
            $metadata = [];
        }

        // Uložení/aktualizace záznamu pro tuto fakturu
        $metadata[$vs] = $fakturaData;

        // Uložení metadat do souboru
        file_put_contents($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Stažení PDF
        $this->sendResponse(new \Nette\Application\Responses\FileResponse(
            $pdfFilePath,
            $pdfFileName,
            'application/pdf'
        ));
        
    } catch (\Exception $e) {
        // Zachycení případných chyb při generování PDF
        $this->flashMessage('Došlo k chybě při generování PDF: ' . $e->getMessage(), 'error');
        $this->redirect('this');
    }
}
    
    private function generujVS(): string
    {
        $dnesniDatum = date('Ymd');
        $posledniCislo = 0;
        
        // Kontrola, zda složka existuje
        if (is_dir($this->fakturaDir)) {
            // Procházení všech souborů ve složce faktur
            $soubory = scandir($this->fakturaDir);
            foreach ($soubory as $soubor) {
                // Hledáme faktury z dnešního dne - formát názvu faktura_YYYYMMDDXX.pdf
                if (preg_match('/faktura_' . $dnesniDatum . '(\d{2})\.pdf$/', $soubor, $matches)) {
                    $cislo = intval($matches[1]);
                    if ($cislo > $posledniCislo) {
                        $posledniCislo = $cislo;
                    }
                }
            }
        }
        
        // Inkrementace čísla pro další fakturu
        $noveCislo = $posledniCislo + 1;
        
        // Vrátíme datum a dvoumístné číslo (01, 02, atd.)
        return $dnesniDatum . sprintf('%02d', $noveCislo);
    }
    
    private function loadOdberatele(): array
    {
        $odberateleFile = $this->configDir . '/odberatele.json';
        if (file_exists($odberateleFile)) {
            $odberatele = json_decode(file_get_contents($odberateleFile), true);
            if (!is_array($odberatele)) {
                return [];
            }
            return $odberatele;
        }
        return [];
    }
    
    private function loadDodavatel(): array
    {
        $dodavatelFile = $this->configDir . '/dodavatel.json';
        if (file_exists($dodavatelFile)) {
            $dodavatel = json_decode(file_get_contents($dodavatelFile), true);
            if (!is_array($dodavatel)) {
                return [];
            }
            return $dodavatel;
        }
        return [];
    }
}