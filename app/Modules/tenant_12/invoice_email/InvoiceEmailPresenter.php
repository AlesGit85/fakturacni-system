<?php

declare(strict_types=1);

namespace Modules\Tenant12\InvoiceEmail;

use Nette;
use App\Presentation\BasePresenter;
use App\Model\InvoicesManager;
use App\Model\ClientsManager;
use App\Model\CompanyManager;
use App\Model\EmailService;
use App\Model\QrPaymentService;
use TCPDF;

/**
 * Modul pro odesílání faktur emailem
 */
final class InvoiceEmailPresenter extends BasePresenter
{
    /** @var InvoicesManager */
    private $invoicesManager;

    /** @var ClientsManager */
    private $clientsManager;

    /** @var CompanyManager */
    private $companyManager;

    /** @var EmailService */
    private $emailService;

    /** @var QrPaymentService */
    private $qrPaymentService;

    // Pouze účetní a admin mohou odesílat faktury
    protected array $requiredRoles = ['accountant', 'admin'];

    public function __construct(
        InvoicesManager $invoicesManager,
        ClientsManager $clientsManager,
        CompanyManager $companyManager,
        EmailService $emailService,
        QrPaymentService $qrPaymentService
    ) {
        $this->invoicesManager = $invoicesManager;
        $this->clientsManager = $clientsManager;
        $this->companyManager = $companyManager;
        $this->emailService = $emailService;
        $this->qrPaymentService = $qrPaymentService;
    }

    /**
     * Nastavení tenant kontextu
     */
    public function startup(): void
    {
        parent::startup();

        // Nastavíme tenant kontext v manažerech
        $this->invoicesManager->setTenantContext(
            $this->getCurrentTenantId(),
            $this->isSuperAdmin()
        );

        $this->clientsManager->setTenantContext(
            $this->getCurrentTenantId(),
            $this->isSuperAdmin()
        );

        $this->companyManager->setTenantContext(
            $this->getCurrentTenantId(),
            $this->isSuperAdmin()
        );
    }

    /**
     * Handler pro odesílání faktury emailem
     */
    public function actionSend(int $invoiceId): void
    {
        try {
            // Načtení faktury
            $invoice = $this->invoicesManager->getById($invoiceId);
            if (!$invoice) {
                throw new \Exception('Faktura nebyla nalezena.');
            }

            // Načtení údajů o klientovi (automaticky dešifrované)
            if (!$invoice->manual_client) {
                $client = $this->clientsManager->getById($invoice->client_id);
            } else {
                // Pro ručně zadaného klienta vytvoříme objekt
                $client = new \stdClass();
                $client->name = $invoice->client_name;
                $client->address = $invoice->client_address;
                $client->city = $invoice->client_city;
                $client->zip = $invoice->client_zip;
                $client->country = $invoice->client_country;
                $client->ic = $invoice->client_ic;
                $client->dic = $invoice->client_dic;
                $client->email = '';
                $client->phone = '';
                $client->bank_account = '';
            }

            // Kontrola, zda klient má email
            if (empty($client->email)) {
                throw new \Exception('Klient nemá zadaný email. Nelze odeslat fakturu.');
            }

            // Načtení firemních údajů (automaticky dešifrované)
            $company = $this->companyManager->getCompanyInfo();
            if (!$company) {
                throw new \Exception('Firemní údaje nejsou vyplněny.');
            }

            // Kontrola, zda firma má email
            if (empty($company->email)) {
                throw new \Exception('Ve firemních údajích není zadán email pro odesílání faktur.');
            }

            // Vygenerování PDF do dočasného souboru
            $tempDir = dirname(__DIR__, 3) . '/temp';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $pdfPath = $tempDir . '/invoice-' . $invoice->id . '-' . time() . '.pdf';

            // Generování PDF
            $this->generateInvoicePdf($invoice, $client, $company, $pdfPath);

            // Kontrola, zda PDF bylo vytvořeno
            if (!file_exists($pdfPath)) {
                throw new \Exception('Nepodařilo se vygenerovat PDF faktury.');
            }

            // Odeslání emailu s fakturou
            $this->emailService->sendInvoiceEmail($invoice, $client, $company, $pdfPath);

            // Smazání dočasného PDF souboru
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }

            $this->flashMessage('Faktura byla úspěšně odeslána emailem na adresu: ' . $client->email, 'success');
        } catch (\Exception $e) {
            $this->flashMessage('Chyba při odesílání faktury: ' . $e->getMessage(), 'danger');
        }

        // Přesměrování zpět na detail faktury
        $this->redirect(':Invoices:show', $invoiceId);
    }

    /**
     * Vygeneruje PDF faktury a uloží ho do souboru
     */
    private function generateInvoicePdf($invoice, $client, $company, string $outputPath): void
    {
        // Načtení položek faktury
        $invoiceItems = $this->invoicesManager->getInvoiceItems($invoice->id);
        $isVatPayer = $company ? $company->vat_payer : false;

        // Vytvoření PDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Nastavení PDF dokumentu
        $pdf->SetCreator($company->name);
        $pdf->SetAuthor($company->name);
        $pdf->SetTitle('Faktura ' . $invoice->number);
        $pdf->SetSubject('Faktura ' . $invoice->number);

        // Odstranění hlavičky a patičky
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Přidání stránky
        $pdf->AddPage();

        // Nastavení okrajů
        $pdf->SetMargins(15, 15, 15);

        // Převod HEX barev na RGB hodnoty
        function hex2rgb($hex)
        {
            $hex = str_replace('#', '', $hex);
            if (strlen($hex) == 3) {
                $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
                $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
                $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
            } else {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
            }
            return array($r, $g, $b);
        }

        // Pomocná funkce: Inteligentní zalamování textu na max. počet znaků
        function wrapText($text, $maxLength = 100)
        {
            // Pokud je text prázdný, vrátíme prázdné pole
            if (empty($text)) {
                return [];
            }

            // Nahradíme všechny možné varianty znaků nového řádku za standardní PHP \n
            $text = str_replace(["\r\n", "\r"], "\n", $text);

            // Rozdělíme text podle ručně zadaných nových řádků
            $manualLines = explode("\n", $text);
            $wrappedLines = [];

            foreach ($manualLines as $line) {
                // Pokud je řádek kratší než max délka, přidáme ho celý
                if (mb_strlen($line) <= $maxLength) {
                    $wrappedLines[] = $line;
                    continue;
                }

                // Pokud je delší, musíme ho zalomit
                $words = explode(' ', $line);
                $currentLine = '';

                foreach ($words as $word) {
                    // Zkusíme přidat slovo k aktuálnímu řádku
                    $testLine = $currentLine === '' ? $word : $currentLine . ' ' . $word;

                    // Pokud by přesáhl max délku
                    if (mb_strlen($testLine) > $maxLength) {
                        // Uložíme současný řádek (pokud není prázdný)
                        if ($currentLine !== '') {
                            $wrappedLines[] = $currentLine;
                        }
                        // Začneme nový řádek
                        $currentLine = $word;

                        // Pokud i samotné slovo je delší než max délka, rozdělíme ho
                        if (mb_strlen($word) > $maxLength) {
                            $wrappedLines[] = mb_substr($word, 0, $maxLength);
                            $currentLine = '';
                        }
                    } else {
                        $currentLine = $testLine;
                    }
                }

                // Přidáme poslední řádek, pokud není prázdný
                if ($currentLine !== '') {
                    $wrappedLines[] = $currentLine;
                }
            }

            return $wrappedLines;
        }

        // Získání barev z nastavení firmy nebo použití výchozích
        $headingColorHex = $company->invoice_heading_color ?? '#cacaca';
        $trapezoidBgColorHex = $company->invoice_trapezoid_bg_color ?? '#cacaca';
        $trapezoidTextColorHex = $company->invoice_trapezoid_text_color ?? '#000000';
        $labelsColorHex = $company->invoice_labels_color ?? '#cacaca';
        $footerColorHex = $company->invoice_footer_color ?? '#393b41';

        // Převod HEX na RGB
        $headingColor = hex2rgb($headingColorHex);
        $trapezoidBgColor = hex2rgb($trapezoidBgColorHex);
        $trapezoidTextColor = hex2rgb($trapezoidTextColorHex);
        $labelsColor = hex2rgb($labelsColorHex);
        $footerColor = hex2rgb($footerColorHex);

        // Definice barev
        $textColor = array(50, 50, 50);    // Tmavě šedá pro text

        // Nastavení fontu
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

        // ------------------------------------------------
        // ZÁHLAVÍ FAKTURY - lichoběžník
        // ------------------------------------------------

        // Zvětšení mezery mezi horním okrajem a obsahem
        $headerStartY = 20; // Zvýšeno z 15 na 20mm pro více prostoru nahoře

        // Šířka a výška faktura textu pro výpočet centrování loga
        $fakturaTextHeight = 10; // Výška textu "FAKTURA"

        // Logo společnosti - nyní vertikálně vycentrované
        if ($company->logo && file_exists(WWW_DIR . '/uploads/logo/' . $company->logo) && $invoice->show_logo) {
            // Získáme rozměry loga
            $logoInfo = getimagesize(WWW_DIR . '/uploads/logo/' . $company->logo);
            $logoWidth = 40; // Šířka loga v mm
            $logoHeight = 15; // Předpokládaná výška loga v mm

            if ($logoInfo) {
                // Výpočet poměru stran a skutečné výšky pro zachování poměru stran
                $logoRatio = $logoInfo[1] / $logoInfo[0];
                $logoHeight = $logoWidth * $logoRatio;
            }

            // Výpočet Y pozice pro vertikální centrování loga s textem "FAKTURA"
            $logoY = $headerStartY + ($fakturaTextHeight - $logoHeight) / 2;

            // Vykreslení loga vycentrovaného vertikálně
            $pdf->Image(WWW_DIR . '/uploads/logo/' . $company->logo, 15, $logoY, $logoWidth);
        } else {
            // Pokud logo neexistuje nebo nemá být zobrazeno, zobrazíme název společnosti stylizovaně
            $pdf->SetFont('dejavusans', 'B', 20);
            $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
            $pdf->Cell(0, 10, $company->name, 0, 1, 'L');
            $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        }

        // Nadpis FAKTURA s číslem
        $pageWidth = 180; // dostupná šířka po odečtení okrajů
        $fakturaText = 'FAKTURA';
        $fakturaWidth = 50;
        $vsWidth = 60;
        $emptyWidth = $pageWidth - $fakturaWidth - $vsWidth;

        // Nastavení Y pozice pro text "FAKTURA" - stejná jako headerStartY
        $pdf->SetY($headerStartY);
        $pdf->SetX(15);

        $pdf->SetFont('dejavusans', '', 24);
        $pdf->SetTextColor($headingColor[0], $headingColor[1], $headingColor[2]);
        $pdf->Cell($emptyWidth, 10, '', 0, 0, 'L');
        $pdf->Cell($fakturaWidth, 10, $fakturaText, 0, 0, 'R');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('dejavusans', 'B', 24);
        $pdf->Cell($vsWidth, 10, $invoice->number, 0, 1, 'L');

        // Šedý lichoběžník v záhlaví
        $pdf->Ln(5);
        $startY = $pdf->GetY();
        $endY = $startY + 95; // Konec lichoběžníku

        // Pozice QR kódu
        $qrWidth = 40;
        $qrHeight = 40;
        $qrX = 150;
        $blockMiddleY = ($startY + $endY) / 2;
        $qrY = $blockMiddleY - ($qrHeight / 2);

        // Souřadnice pro lichoběžník
        $p0x = 0;
        $p0y = $startY;
        $p1x = 195;
        $p1y = $startY;
        $p2x = $qrX;
        $p2y = $endY;
        $p3x = 0;
        $p3y = $endY;

        // Kreslení pozadí lichoběžníku s uživatelskou barvou
        $pdf->SetFillColor($trapezoidBgColor[0], $trapezoidBgColor[1], $trapezoidBgColor[2]);
        $points = array($p0x, $p0y, $p1x, $p1y, $p2x, $p2y, $p3x, $p3y);
        $pdf->Polygon($points, 'F');

        // ------------------------------------------------
        // PLATEBNÍ ÚDAJE
        // ------------------------------------------------

        // Nastavení pro tabulku platebních údajů
        $leftMargin = 20;
        $labelWidth = 36;
        $valueWidth = 75;
        $rowHeight = 5.5;
        $labelRightPadding = 20;
        $valueX = $leftMargin + $labelWidth + $labelRightPadding - 20;

        // Nastavení barvy textu v lichoběžníku
        $pdf->SetTextColor($trapezoidTextColor[0], $trapezoidTextColor[1], $trapezoidTextColor[2]);

        // Prosím o zaplacení
        $pdf->SetXY($leftMargin, $startY + 10);
        $pdf->SetFont('dejavusans', '', 11);
        $pdf->Cell(100, 5, 'Prosím o zaplacení', 0, 1, 'L');

        // Částka - velká
        $pdf->SetXY($leftMargin, $startY + 16);
        $pdf->SetFont('dejavusans', 'B', 28);
        $pdf->Cell(100, 10, number_format($invoice->total, 0, ',', ' ') . ' Kč', 0, 1, 'L');

        // Mezera před platebními údaji
        $pdf->Ln(5);

        // Převod způsobu platby na správný tvar pro PDF
        $paymentMethodText = '';
        switch ($invoice->payment_method) {
            case 'Bankovní převod':
                $paymentMethodText = 'bankovním převodem';
                break;
            case 'Hotovost':
                $paymentMethodText = 'v hotovosti';
                break;
            case 'Karta':
            case 'Platební karta':
                $paymentMethodText = 'platební kartou';
                break;
            default:
                $paymentMethodText = strtolower($invoice->payment_method);
                break;
        }

        // Forma úhrady - nyní s dynamickým textem
        $pdf->SetXY($leftMargin, $pdf->GetY());
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($labelWidth, $rowHeight, 'Forma úhrady:', 0, 0, 'L');
        $pdf->SetX($valueX);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($valueWidth, $rowHeight, $paymentMethodText, 0, 1, 'L');

        // Bankovní údaje zobrazujeme pouze pro bankovní převod
        if ($invoice->payment_method === 'Bankovní převod') {
            // Číslo účtu
            $pdf->SetXY($leftMargin, $pdf->GetY());
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->Cell($labelWidth, $rowHeight, 'Číslo účtu:', 0, 0, 'L');
            $pdf->SetX($valueX);
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->Cell($valueWidth, $rowHeight, $company->bank_account, 0, 1, 'L');

            // Mezera před variabilním symbolem
            $pdf->Ln(2);

            // Variabilní symbol
            $pdf->SetXY($leftMargin, $pdf->GetY());
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->Cell($labelWidth, $rowHeight, 'Variabilní symbol:', 0, 0, 'L');
            $pdf->SetX($valueX);
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->Cell($valueWidth, $rowHeight, $invoice->number, 0, 1, 'L');

            // Mezera před dalšími údaji  
            $pdf->Ln(2);

            // Banka
            $pdf->SetXY($leftMargin, $pdf->GetY());
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->Cell($labelWidth, $rowHeight, 'Banka:', 0, 0, 'L');
            $pdf->SetX($valueX);
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->Cell($valueWidth, $rowHeight, $company->bank_name, 0, 1, 'L');
        } else {
            // Pro jiné způsoby platby přidáme jen malou mezeru
            $pdf->Ln(2);
        }

        // QR kód pro platbu
        if ($invoice->qr_payment) {
            $cleanVs = preg_replace('/\D/', '', $invoice->number);
            // Přidáme QR kód přímo do PDF
            $this->qrPaymentService->addQrPaymentToPdf(
                $pdf,
                $qrX,
                $qrY,
                $qrWidth,
                $qrHeight,
                $company->bank_account,
                $invoice->total,
                $cleanVs,
                'Faktura ' . $invoice->number
            );
        }

        // ------------------------------------------------
        // SEKCE S DODAVATELEM A ODBĚRATELEM
        // ------------------------------------------------

        // Začátek sekce - pod lichoběžníkem
        $pdf->SetY($endY + 5);

        // Obnovení černé barvy pro běžný text
        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

        // Dodavatel - bez lomítka, barva z nastavení
        $pdf->SetTextColor($labelsColor[0], $labelsColor[1], $labelsColor[2]);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(90, 8, 'Dodavatel', 0, 0, 'L');

        // Odběratel - bez lomítka, barva z nastavení
        $pdf->Cell(90, 8, 'Odběratel', 0, 1, 'L');

        // Údaje dodavatele - černá barva
        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(90, 6, $company->name, 0, 0, 'L');

        // Údaje odběratele
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(90, 6, $client->name, 0, 1, 'L');

        // Pokračování dodavatele
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(90, 6, $company->address . ', ' . $company->zip . ' ' . $company->city, 0, 0, 'L');

        // Pokračování odběratele
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(90, 6, $client->address . ', ' . $client->zip . ' ' . $client->city, 0, 1, 'L');

        // IČO dodavatele
        $pdf->Cell(90, 6, 'IČO: ' . $company->ic, 0, 0, 'L');

        // IČO odběratele (pokud existuje)
        $clientIcText = '';
        if (!empty($client->ic)) {
            $clientIcText = 'IČO: ' . $client->ic;
        }
        $pdf->Cell(90, 6, $clientIcText, 0, 1, 'L');

        // DIČ dodavatele nebo "Nejsem plátce DPH"
        if ($isVatPayer && !empty($company->dic)) {
            $pdf->Cell(90, 6, 'DIČ: ' . $company->dic, 0, 0, 'L');
        } else {
            $pdf->Cell(90, 6, 'Nejsem plátce DPH.', 0, 0, 'L');
        }

        // DIČ odběratele (pokud existuje)
        $clientDicText = '';
        if (!empty($client->dic)) {
            $clientDicText = 'DIČ: ' . $client->dic;
        }
        $pdf->Cell(90, 6, $clientDicText, 0, 1, 'L');

        // ------------------------------------------------
        // SEKCE S POLOŽKAMI FAKTURY
        // ------------------------------------------------

        // Fakturuji Vám za - barva z nastavení
        $pdf->Ln(5);
        $pdf->SetTextColor($labelsColor[0], $labelsColor[1], $labelsColor[2]);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(180, 8, 'Fakturuji Vám za', 0, 1, 'L');

        // Položky faktury - obnovení černé barvy
        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        $pdf->SetFont('dejavusans', '', 10);

        // Použití funkce wrapText pro inteligentní zalamování na max 100 znaků
        foreach ($invoiceItems as $key => $item) {
            // Zalomení názvu položky na max 100 znaků
            $nameLines = wrapText($item->name, 100);

            // Vykreslíme každý řádek názvu s vlastní spodní linkou
            foreach ($nameLines as $index => $line) {
                $pdf->Cell(180, 8, $line, 'B', 1, 'L');
            }

            // Pokud je popis, přidáme ho na další řádek
            if (!empty($item->description)) {
                $pdf->SetFont('dejavusans', 'I', 9);

                // Zalomení popisu na max 100 znaků
                $descLines = wrapText($item->description, 100);

                foreach ($descLines as $descLine) {
                    $pdf->Cell(180, 6, $descLine, 0, 1, 'L');
                }

                $pdf->SetFont('dejavusans', '', 10);
            }
        }

        // Celkem zaplaťte
        $pdf->Ln(5);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(100, 8, '', 0, 0, 'L');
        $pdf->Cell(30, 8, 'Celkem zaplaťte:', 0, 0, 'R');
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(50, 8, number_format($invoice->total, 0, ',', ' ') . ' Kč', 0, 1, 'L');

        // ------------------------------------------------
        // PODPIS SPOLEČNOSTI
        // ------------------------------------------------

        // Zobrazení podpisu, pokud existuje a má být zobrazen
        if ($company->signature && file_exists(WWW_DIR . '/uploads/signature/' . $company->signature) && $invoice->show_signature) {
            $pdf->Ln(15); // Mezera před podpisem

            // Popisek "Podpis:" - stejná barva jako ostatní štítky
            $pdf->SetTextColor($labelsColor[0], $labelsColor[1], $labelsColor[2]);
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->Cell(180, 8, 'Podpis:', 0, 1, 'L');

            // Malá mezera mezi popiskem a podpisem
            $pdf->Ln(2);

            // Získáme rozměry podpisu
            $signatureInfo = getimagesize(WWW_DIR . '/uploads/signature/' . $company->signature);
            $signatureWidth = 60; // Šířka podpisu v mm
            $signatureHeight = 20; // Předpokládaná výška podpisu v mm

            if ($signatureInfo) {
                // Výpočet poměru stran a skutečné výšky pro zachování poměru stran
                $signatureRatio = $signatureInfo[1] / $signatureInfo[0];
                $signatureHeight = $signatureWidth * $signatureRatio;

                // Omezíme maximální výšku podpisu
                if ($signatureHeight > 30) {
                    $signatureHeight = 30;
                    $signatureWidth = 30 / $signatureRatio;
                }
            }

            // Pozice podpisu - zarovnáno vlevo s malým odsazením
            $signatureX = 20;
            $signatureY = $pdf->GetY();

            // Vykreslení podpisu
            $pdf->Image(WWW_DIR . '/uploads/signature/' . $company->signature, $signatureX, $signatureY, $signatureWidth, $signatureHeight);

            // Posuneme Y pozici za podpis
            $pdf->SetY($signatureY + $signatureHeight + 5);

            // Obnovíme černou barvu textu pro další obsah
            $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        }

        // Přidáme poznámku, pokud existuje
        if (!empty($invoice->note)) {
            $pdf->Ln(10);
            $pdf->SetFont('dejavusans', 'I', 10);
            $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
            $pdf->Cell(30, 8, 'Poznámka:', 0, 0, 'L');

            // Pro správné zobrazení víceřádkového textu poznámky
            $pdf->Ln(10);
            $pdf->SetX(20);

            // Vyčistíme text poznámky stejným způsobem
            $cleanNote = str_replace(["\r\n", "\r"], "\n", $invoice->note);
            $noteLines = explode("\n", $cleanNote);

            foreach ($noteLines as $noteLine) {
                $pdf->Cell(160, 6, $noteLine, 0, 1, 'L');
            }
        }

        // ------------------------------------------------
        // MARKETINGOVÝ TEXT NAD PATIČKOU
        // ------------------------------------------------

        // Vypočítáme pozici, aby byl text nad lichoběžníkem
        $pdf->Ln(15);
        $pdf->SetFont('dejavusans', 'I', 9);
        $pdf->SetTextColor(150, 150, 150); // Šedá barva pro marketingový text
        $pdf->Cell(0, 6, 'Vystaveno systémem QRdoklad – protože fakturovat nemusí být otrava.', 0, 1, 'C');

        // ------------------------------------------------
        // PATIČKA FAKTURY
        // ------------------------------------------------

        // Vypnutí automatického zalomení stránky
        $pdf->SetAutoPageBreak(false);

        // Definice rozměrů pro patičku
        $pageWidth = 210; // A4 šířka
        $pageHeight = 297; // A4 výška
        $footerHeight = 20; // Výška patičky

        // Souřadnice pro patičku
        $footerStartY = $pageHeight - $footerHeight;
        $footerEndY = $pageHeight;

        // Souřadnice pro lichoběžník patičky
        $rightEdge = $pageWidth;
        $leftEdgeTop = 70; // 2/3 šířky od pravého okraje nahoře
        $leftEdgeBottom = 50; // Širší dole - zkosení doleva

        $p0x = $rightEdge;
        $p0y = $footerStartY;
        $p1x = $leftEdgeTop;
        $p1y = $footerStartY;
        $p2x = $leftEdgeBottom;
        $p2y = $footerEndY;
        $p3x = $rightEdge;
        $p3y = $footerEndY;

        // Kreslení tmavého pozadí patičky s uživatelskou barvou
        $pdf->SetFillColor($footerColor[0], $footerColor[1], $footerColor[2]);
        $points = array($p0x, $p0y, $p1x, $p1y, $p2x, $p2y, $p3x, $p3y);
        $pdf->Polygon($points, 'F');

        // Text pro patičku - bílá barva
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('dejavusans', '', 9);

        // Výpočet pozic pro sloupce v patičce
        $colWidth = 60;
        $col1X = $leftEdgeTop + 15;
        $col2X = $col1X + $colWidth + 10;
        $verticalMiddle = $footerStartY + ($footerHeight / 2) - 5;

        // První sloupec - jméno a telefon
        $pdf->SetXY($col1X, $verticalMiddle);
        $pdf->Cell($colWidth, 6, $company->name, 0, 1, 'L');
        $pdf->SetXY($col1X, $verticalMiddle + 6);
        $pdf->Cell($colWidth, 6, $company->phone, 0, 1, 'L');

        // Druhý sloupec - email
        $pdf->SetXY($col2X, $verticalMiddle);
        $pdf->Cell($colWidth, 6, 'Email:', 0, 1, 'L');
        $pdf->SetXY($col2X, $verticalMiddle + 6);
        $pdf->Cell($colWidth, 6, $company->email, 0, 1, 'L');

        // Výstup PDF do souboru
        $pdf->Output($outputPath, 'F');
    }

    public function renderDefault(): void
    {
        // Prázdná metoda - modul funguje přes handlery
    }

    /**
     * Statická metoda pro hook - vrací HTML tlačítka pro detail faktury
     */
    public static function getInvoiceDetailAction($invoice, $presenter): ?string
    {
        // Kontrola oprávnění - pouze účetní a admin
        $user = $presenter->getUser();
        if (!$user->isInRole('accountant') && !$user->isInRole('admin')) {
            return null;
        }

        // OPRAVENO: Link jde na handler v InvoicesPresenter
        $link = $presenter->link('sendInvoiceEmail!', ['invoiceId' => $invoice->id]);

        return '<a href="' . htmlspecialchars($link) . '" class="btn btn-success">
                <i class="bi bi-envelope"></i> Odeslat emailem
            </a>';
    }
}
