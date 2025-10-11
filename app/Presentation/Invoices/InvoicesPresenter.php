<?php

namespace App\Presentation\Invoices;

use Nette;
use Nette\Application\UI\Form;
use App\Model\InvoicesManager;
use App\Model\ClientsManager;
use App\Model\CompanyManager;
use App\Model\QrPaymentService;
use App\Presentation\BasePresenter;
use TCPDF;

class InvoicesPresenter extends BasePresenter
{
    /** @var InvoicesManager */
    private $invoicesManager;

    /** @var ClientsManager */
    private $clientsManager;

    /** @var CompanyManager */
    private $companyManager;

    /** @var QrPaymentService */
    private $qrPaymentService;

    /** @var \App\Model\ModuleManager */
    private $moduleManager;

    /** @var \App\Model\EmailService */
    private $emailService;

    // Všichni přihlášení uživatelé mají základní přístup k fakturám
    protected array $requiredRoles = ['readonly', 'accountant', 'admin'];

    // Konkrétní role pro jednotlivé akce
    protected array $actionRoles = [
        'default' => ['readonly', 'accountant', 'admin'], // Seznam faktur mohou vidět všichni
        'show' => ['readonly', 'accountant', 'admin'], // Detail faktury mohou vidět všichni
        'pdf' => ['readonly', 'accountant', 'admin'], // PDF může stáhnout každý
        'add' => ['accountant', 'admin'], // Přidat fakturu může účetní a admin
        'edit' => ['accountant', 'admin'], // Upravit fakturu může účetní a admin
        'delete' => ['admin'], // Smazat fakturu může jen admin
        'markAsPaid' => ['accountant', 'admin'], // Označit jako zaplacenou může účetní a admin
        'markAsCreated' => ['accountant', 'admin'], // Zrušit zaplacení může účetní a admin
    ];

    public function __construct(
        InvoicesManager $invoicesManager,
        ClientsManager $clientsManager,
        CompanyManager $companyManager,
        QrPaymentService $qrPaymentService,
        \App\Model\ModuleManager $moduleManager,
        \App\Model\EmailService $emailService
    ) {
        $this->invoicesManager = $invoicesManager;
        $this->clientsManager = $clientsManager;
        $this->companyManager = $companyManager;
        $this->qrPaymentService = $qrPaymentService;
        $this->moduleManager = $moduleManager;
        $this->emailService = $emailService;
    }

    /**
     * MULTI-TENANCY: Nastavení tenant kontextu po spuštění presenteru
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

        // PŘIDÁNO: CompanyManager také potřebuje tenant kontext pro zobrazení faktur
        $this->companyManager->setTenantContext(
            $this->getCurrentTenantId(),
            $this->isSuperAdmin()
        );
    }

    public function renderAdd(): void
    {
        $company = $this->companyManager->getCompanyInfo();
        $this->template->isVatPayer = $company ? $company->vat_payer : false;
    }

    public function renderShow(int $id): void
    {
        $invoice = $this->invoicesManager->getById($id);

        if (!$invoice) {
            $this->error('Faktura nebyla nalezena');
        }

        $this->template->invoice = $invoice;

        if (!$invoice->manual_client) {
            $this->template->client = $this->clientsManager->getById($invoice->client_id);
        } else {
            $manualClient = new \stdClass();
            $manualClient->name = $invoice->client_name;
            $manualClient->address = $invoice->client_address;
            $manualClient->city = $invoice->client_city;
            $manualClient->zip = $invoice->client_zip;
            $manualClient->country = $invoice->client_country;
            $manualClient->ic = $invoice->client_ic;
            $manualClient->dic = $invoice->client_dic;
            $manualClient->email = '';
            $manualClient->phone = '';
            $manualClient->bank_account = '';
            $this->template->client = $manualClient;
        }

        $this->template->invoiceItems = $this->invoicesManager->getInvoiceItems($id);
        $this->template->company = $this->companyManager->getCompanyInfo();

        // Načtení akcí z aktivních modulů
        $moduleActions = $this->getModuleInvoiceActions($invoice);

        $this->template->moduleInvoiceActions = $moduleActions;
    }

    public function actionDelete(int $id): void
    {
        // Kontrola oprávnění je už v actionRoles - pouze admin
        $this->invoicesManager->delete($id);
        $this->flashMessage('Faktura byla úspěšně smazána', 'success');
        $this->redirect('default');
    }

    public function actionPdf(int $id): void
    {
        // Čištění output bufferů
        while (ob_get_level()) {
            ob_end_clean();
        }

        $invoice = $this->invoicesManager->getById($id);

        if (!$invoice) {
            $this->error('Faktura nebyla nalezena');
        }

        // Získání údajů o klientovi - buď z databáze, nebo z faktury pro ručně zadané
        if (!$invoice->manual_client) {
            $client = $this->clientsManager->getById($invoice->client_id);
        } else {
            // Pro ručně zadaného klienta vytvoříme objekt s údaji z faktury
            $client = new \stdClass();
            $client->name = $invoice->client_name;
            $client->address = $invoice->client_address;
            $client->city = $invoice->client_city;
            $client->zip = $invoice->client_zip;
            $client->country = $invoice->client_country;
            $client->ic = $invoice->client_ic;
            $client->dic = $invoice->client_dic;
            // Přidáme chybějící vlastnosti i zde pro PDF
            $client->email = '';
            $client->phone = '';
            $client->bank_account = '';
        }

        $invoiceItems = $this->invoicesManager->getInvoiceItems($id);
        $company = $this->companyManager->getCompanyInfo();
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

        // ✅ NOVÁ POMOCNÁ FUNKCE: Inteligentní zalamování textu na max. počet znaků
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

        // ✅ OPRAVENO: Převod způsobu platby na správný tvar pro PDF
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

        // ✅ VYLEPŠENO: Bankovní údaje zobrazujeme pouze pro bankovní převod
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

        // ✅ UPRAVENO: Použití funkce wrapText pro inteligentní zalamování na max 100 znaků
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

        // Výstup PDF
        $pdf->Output('faktura-' . $invoice->number . '.pdf', 'D');
        $this->terminate();
    }

    public function createComponentInvoiceForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        // ✅ Anti-spam ochrana
        $this->addAntiSpamProtectionToForm($form);

        // ✅ OPRAVENO: Načtení preferencí na začátku metody
        $lastInvoice = $this->invoicesManager->getLastUserInvoice($this->getUser()->getId());

        // Výchozí hodnoty - pokud uživatel ještě nemá žádnou fakturu, použije se výchozí nastavení
        $defaultQrPayment = $lastInvoice ? (bool)$lastInvoice->qr_payment : true;
        $defaultShowLogo = $lastInvoice ? (bool)$lastInvoice->show_logo : true;
        $defaultShowSignature = $lastInvoice ? (bool)$lastInvoice->show_signature : false;
        $defaultPaymentMethod = $lastInvoice ? $lastInvoice->payment_method : 'Bankovní převod';

        // Přepínač mezi existujícím a ručně zadaným klientem
        $clientTypeRadio = $form->addRadioList('client_type', 'Klient:', [
            'existing' => 'Vybrat existujícího klienta',
            'manual' => 'Zadat ručně'
        ])->setDefaultValue('existing');

        // Existující klient - výběr
        $clients = $this->clientsManager->getPairs();
        $form->addSelect('client_id', 'Vyberte klienta:', $clients)
            ->setPrompt('Vyberte klienta')
            ->setRequired(false)
            ->setOption('id', 'existing-client-select')
            ->addConditionOn($clientTypeRadio, Form::EQUAL, 'existing')
            ->setRequired('Vyberte klienta');

        // Ruční zadání klienta - formulář
        $form->addText('client_name', 'Název klienta:')
            ->setOption('id', 'manual-client-name')
            ->addConditionOn($clientTypeRadio, Form::EQUAL, 'manual')
            ->setRequired('Zadejte název klienta');

        $form->addTextArea('client_address', 'Adresa:')
            ->setOption('id', 'manual-client-address')
            ->addConditionOn($clientTypeRadio, Form::EQUAL, 'manual')
            ->setRequired('Zadejte adresu');

        $form->addText('client_city', 'Město:')
            ->setOption('id', 'manual-client-city')
            ->addConditionOn($clientTypeRadio, Form::EQUAL, 'manual')
            ->setRequired('Zadejte město');

        $form->addText('client_zip', 'PSČ:')
            ->setOption('id', 'manual-client-zip')
            ->addConditionOn($clientTypeRadio, Form::EQUAL, 'manual')
            ->setRequired('Zadejte PSČ');

        $form->addText('client_country', 'Země:')
            ->setOption('id', 'manual-client-country')
            ->setDefaultValue('Česká republika')
            ->addConditionOn($clientTypeRadio, Form::EQUAL, 'manual')
            ->setRequired('Zadejte zemi');

        $form->addText('client_ic', 'IČ:')
            ->setOption('id', 'manual-client-ic');

        $form->addText('client_dic', 'DIČ:')
            ->setOption('id', 'manual-client-dic');

        // Fakturační údaje
        $form->addText('number', 'Číslo faktury:')
            ->setRequired('Zadejte číslo faktury')
            ->setDefaultValue($this->invoicesManager->generateInvoiceNumber());

        $form->addText('issue_date', 'Datum vystavení:')
            ->setRequired('Zadejte datum vystavení')
            ->setDefaultValue(date('Y-m-d'))
            ->setHtmlType('date');

        $form->addText('due_date', 'Datum splatnosti:')
            ->setRequired('Zadejte datum splatnosti')
            ->setDefaultValue(date('Y-m-d', strtotime('+14 days')))
            ->setHtmlType('date');

        // ✅ OPRAVENO: Způsob platby s výchozí hodnotou přímo při vytváření
        $form->addSelect('payment_method', 'Způsob platby:', [
            'Bankovní převod' => 'Bankovní převod',
            'Hotovost' => 'Hotovost',
            'Karta' => 'Platební karta',
        ])
            ->setRequired('Vyberte způsob platby')
            ->setDefaultValue($defaultPaymentMethod);

        // ✅ OPRAVENO: Možnosti zobrazení s prázdnými labely pro moderní design
        $form->addCheckbox('qr_payment', '')
            ->setDefaultValue($defaultQrPayment);

        $form->addCheckbox('show_logo', '')
            ->setDefaultValue($defaultShowLogo);

        $form->addCheckbox('show_signature', '')
            ->setDefaultValue($defaultShowSignature);

        $form->addTextArea('note', 'Poznámka:')
            ->setHtmlAttribute('rows', 3);

        // Skrytá položka pro uživatele
        $form->addHidden('user_id', $this->getUser()->getId());

        // Odeslání formuláře
        $form->addSubmit('send', 'Uložit fakturu');

        $form->onSuccess[] = [$this, 'invoiceFormSucceeded'];

        return $form;
    }

    public function invoiceFormSucceeded(Form $form, \stdClass $data): void
    {
        $id = $this->getParameter('id');

        // Kontrola, zda jsou zadány položky faktury
        $items = $this->getHttpRequest()->getPost('items');

        if (!$items) {
            $form->addError('Faktura musí obsahovat alespoň jednu položku.');
            return;
        }

        // Příprava dat pro uložení faktury
        $invoiceData = [
            'number' => $data->number,
            'issue_date' => $data->issue_date,
            'due_date' => $data->due_date,
            'payment_method' => $data->payment_method,
            'qr_payment' => $data->qr_payment,
            'show_logo' => $data->show_logo, // Nová položka
            'show_signature' => $data->show_signature, // Nová položka
            'note' => $data->note,
            'user_id' => $data->user_id,
            'manual_client' => ($data->client_type === 'manual'),
        ];

        // Nastavení klienta podle zvoleného typu
        if ($data->client_type === 'existing') {
            $invoiceData['client_id'] = $data->client_id;
        } else {
            // Vytvoříme nový záznam v tabulce klientů pro ručně zadaného klienta
            $clientData = [
                'name' => $data->client_name,
                'address' => $data->client_address,
                'city' => $data->client_city,
                'zip' => $data->client_zip,
                'country' => $data->client_country,
                'ic' => $data->client_ic,
                'dic' => $data->client_dic,
                'email' => '', // Prázdná výchozí hodnota pro povinné pole
                'phone' => '', // Prázdná výchozí hodnota pro povinné pole
            ];

            $newClient = $this->clientsManager->save($clientData);

            // Použijeme ID nově vytvořeného klienta
            $invoiceData['client_id'] = $newClient->id;

            // Zachováme informaci o ručně zadaném klientovi a jeho údajích
            $invoiceData['manual_client'] = true;
            $invoiceData['client_name'] = $data->client_name;
            $invoiceData['client_address'] = $data->client_address;
            $invoiceData['client_city'] = $data->client_city;
            $invoiceData['client_zip'] = $data->client_zip;
            $invoiceData['client_country'] = $data->client_country;
            $invoiceData['client_ic'] = $data->client_ic;
            $invoiceData['client_dic'] = $data->client_dic;
        }

        // Vytvoření nebo aktualizace faktury
        if ($id) {
            $invoice = $this->invoicesManager->save($invoiceData, $id);
            $invoiceId = $id;
            $this->flashMessage('Faktura byla úspěšně aktualizována', 'success');
        } else {
            $invoice = $this->invoicesManager->save($invoiceData);
            $invoiceId = $invoice->id;
            $this->flashMessage('Faktura byla úspěšně vytvořena', 'success');
        }

        // Vymazání starých položek při editaci
        if ($id) {
            $this->invoicesManager->deleteInvoiceItems($id);
        }

        // Uložení položek faktury
        foreach ($items as $item) {
            $item['invoice_id'] = $invoiceId;
            $this->invoicesManager->saveItem($item);
        }

        // Aktualizace celkové částky faktury
        $this->invoicesManager->updateInvoiceTotal($invoiceId);

        $this->redirect('show', $invoiceId);
    }

    public function actionEdit(int $id): void
    {
        $invoice = $this->invoicesManager->getById($id);

        if (!$invoice) {
            $this->error('Faktura nebyla nalezena');
        }

        // Připravíme data formuláře
        $defaults = (array) $invoice;

        // Nastavíme typ klienta a jeho údaje
        if ($invoice->manual_client) {
            $defaults['client_type'] = 'manual';

            // Pro ručně zadaného klienta načteme aktuální údaje z tabulky clients
            if ($invoice->client_id) {
                $client = $this->clientsManager->getById($invoice->client_id);
                if ($client) {
                    $defaults['client_name'] = $client->name;
                    $defaults['client_address'] = $client->address;
                    $defaults['client_city'] = $client->city;
                    $defaults['client_zip'] = $client->zip;
                    $defaults['client_country'] = $client->country;
                    $defaults['client_ic'] = $client->ic;
                    $defaults['client_dic'] = $client->dic;
                }
            }
        } else {
            $defaults['client_type'] = 'existing';
            // Pro existujícího klienta nastavíme client_id
            $defaults['client_id'] = $invoice->client_id;
        }

        $this['invoiceForm']->setDefaults($defaults);
        $this->template->invoice = $invoice;
        $this->template->invoiceItems = $this->invoicesManager->getInvoiceItems($id);

        $company = $this->companyManager->getCompanyInfo();
        $this->template->isVatPayer = $company ? $company->vat_payer : false;
    }

    // Je potřeba implementovat metodu pro mazání položek faktury
    public function actionDeleteItem(int $invoiceId, int $itemId): void
    {
        $this->invoicesManager->deleteItem($itemId);
        $this->invoicesManager->updateInvoiceTotal($invoiceId);
        $this->flashMessage('Položka byla úspěšně smazána', 'success');
        $this->redirect('edit', $invoiceId);
    }

    public function renderDefault(?string $filter = null, ?string $search = null, ?int $client = null): void
    {
        // Kontrola faktur po splatnosti - pouze pro účetní a admin
        if ($this->isAccountant()) {
            $this->invoicesManager->checkOverdueDates();
        }

        // Příprava dotazu
        $query = $this->invoicesManager->getAll(null, null, $search);

        // Aplikace filtru podle stavu
        if ($filter) {
            $query->where('status', $filter);
        }

        // Aplikace filtru podle klienta
        if ($client) {
            $query->where('client_id', $client);

            // Získáme název klienta pro zobrazení v šabloně
            $clientName = $this->clientsManager->getById($client)->name ?? null;
            $this->template->clientFilter = $clientName;
        }

        // Nastavení proměnných pro šablonu
        $this->template->invoices = $query;
        $this->template->filter = $filter;
        $this->template->search = $search;
        $this->template->client = $client;
    }

    /**
     * Akce pro označení faktury jako zaplacené
     * @param int $id ID faktury
     */
    public function handleMarkAsPaid(int $id): void
    {
        // Explicitní kontrola oprávnění - pouze účetní a admin
        if (!$this->isAccountant()) {
            $this->flashMessage('Nemáte oprávnění označovat faktury jako zaplacené.', 'danger');
            $this->redirect('this');
        }

        $this->invoicesManager->markAsPaid($id);
        $this->flashMessage('Faktura byla označena jako zaplacená', 'success');
        $this->redirect('this');
    }

    /**
     * Akce pro označení faktury jako vystavené (reset stavu)
     * @param int $id ID faktury
     */
    public function handleMarkAsCreated(int $id): void
    {
        // Explicitní kontrola oprávnění - pouze účetní a admin
        if (!$this->isAccountant()) {
            $this->flashMessage('Nemáte oprávnění měnit stav faktur.', 'danger');
            $this->redirect('this');
        }

        $this->invoicesManager->markAsCreated($id);
        $this->flashMessage('Faktura byla označena jako vystavená', 'success');
        $this->redirect('this');
    }

    /**
     * Formulář pro vyhledávání
     */
    protected function createComponentSearchForm(): Nette\Application\UI\Form
    {
        $form = new Nette\Application\UI\Form;

        $form->addText('search', 'Hledat:')
            ->setHtmlAttribute('placeholder', 'Číslo faktury, klient, částka...');

        $form->addSubmit('send', 'Vyhledat');

        $form->onSuccess[] = function (Nette\Application\UI\Form $form, \stdClass $values) {
            $this->redirect('default', $values->search);
        };

        return $form;
    }

    /**
     * Získá akce (tlačítka) z aktivních modulů pro detail faktury
     */
    private function getModuleInvoiceActions($invoice): array
    {
        $actions = [];

        // Načteme aktivní moduly pro aktuálního uživatele
        $activeModules = $this->moduleManager->getActiveModulesForUser(
            $this->getUser()->getId()
        );

        foreach ($activeModules as $moduleId => $moduleInfo) {
            // Pokusíme se načíst presenter modulu s tenant-specific namespace
            $presenterClass = $this->getModulePresenterClass($moduleId, $moduleInfo);

            if ($presenterClass && method_exists($presenterClass, 'getInvoiceDetailAction')) {
                // Modul má metodu pro invoice detail akce
                try {
                    $action = $presenterClass::getInvoiceDetailAction($invoice, $this);
                    if ($action) {
                        $actions[] = $action;
                        \Tracy\Debugger::barDump($action, "Action from: $moduleId");
                    }
                } catch (\Exception $e) {
                    // DEBUG: Vypíšeme chybu
                    \Tracy\Debugger::barDump($e->getMessage(), "Error in: $moduleId");
                    continue;
                }
            }
        }

        return $actions;
    }

    /**
     * Získá třídu presenteru modulu s podporou tenant-specific namespace
     */
    private function getModulePresenterClass(string $moduleId, array $moduleInfo): ?string
    {
        // Získání tenant_id z moduleInfo
        $tenantId = $moduleInfo['tenant_id'] ?? 1;

        // Získáme název složky z physical_path místo použití moduleId
        $physicalPath = $moduleInfo['physical_path'] ?? null;
        if (!$physicalPath || !is_dir($physicalPath)) {
            return null;
        }

        // Získáme název složky (poslední část cesty)
        $folderName = basename($physicalPath);

        // Převod názvu složky (např. invoice_email) na CamelCase pro presenter
        $parts = explode('_', $folderName);
        $presenterName = implode('', array_map('ucfirst', $parts));

        // Vytvoření tenant-specific namespace BEZ App\ na začátku!
        $namespace = 'Modules\\Tenant' . $tenantId . '\\' . $presenterName;
        $presenterClass = $namespace . '\\' . $presenterName . 'Presenter';

        return class_exists($presenterClass) ? $presenterClass : null;
    }

    /**
     * Handler pro odesílání faktury emailem
     */
    public function handleSendInvoiceEmail(int $invoiceId): void
    {
        try {
            // Kontrola oprávnění - pouze účetní a admin
            if (!$this->isAccountant()) {
                $this->flashMessage('Nemáte oprávnění odesílat faktury emailem.', 'danger');
                $this->redirect('show', $invoiceId);
            }

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

            // Načtení firemních údajů
            $company = $this->companyManager->getCompanyInfo();
            if (!$company) {
                throw new \Exception('Firemní údaje nejsou vyplněny.');
            }

            // Kontrola, zda firma má email
            if (empty($company->email)) {
                throw new \Exception('Ve firemních údajích není zadán email pro odesílání faktur.');
            }

            // Vygenerování PDF do dočasného souboru
            $tempDir = dirname(__DIR__, 2) . '/temp';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $pdfPath = $tempDir . '/invoice-' . $invoice->id . '-' . time() . '.pdf';

            // Generování PDF (použijeme existující actionPdf logiku)
            $this->generateInvoicePdfForEmail($invoice, $client, $company, $pdfPath);

            // Kontrola, zda PDF bylo vytvořeno
            if (!file_exists($pdfPath)) {
                throw new \Exception('Nepodařilo se vygenerovat PDF faktury.');
            }

            // Odeslání emailu s fakturou
            // Odeslání emailu pomocí EmailService
            $this->emailService->sendInvoiceEmail($invoice, $client, $company, $pdfPath);

            // Smazání dočasného PDF souboru
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }

            $this->flashMessage('Faktura byla úspěšně odeslána emailem na adresu: ' . $client->email, 'success');
        } catch (\Exception $e) {
            $this->flashMessage('Chyba při odesílání faktury: ' . $e->getMessage(), 'danger');
        }

        $this->redirect('show', $invoiceId);
    }

    /**
     * Pomocná metoda pro generování PDF faktury pro email
     * (zjednodušená verze actionPdf)
     */
    private function generateInvoicePdfForEmail($invoice, $client, $company, string $outputPath): void
    {
        // Načtení položek faktury
        $invoiceItems = $this->invoicesManager->getInvoiceItems($invoice->id);
        $isVatPayer = $company ? $company->vat_payer : false;

        // Vytvoření PDF
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

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
        $hex2rgb = function ($hex) {
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
        };

        // Barevné schéma
        $primaryColor = $hex2rgb('#B1D235');
        $secondaryColor = $hex2rgb('#95B11F');
        $grayColor = $hex2rgb('#6c757d');
        $blackColor = $hex2rgb('#212529');
        $footerColor = $blackColor;

        // Pomocná funkce pro zalamování textu
        $smartWrap = function ($text, $maxLength = 60) {
            if (mb_strlen($text) <= $maxLength) {
                return $text;
            }
            $words = explode(' ', $text);
            $lines = [];
            $currentLine = '';
            foreach ($words as $word) {
                if (mb_strlen($currentLine . ' ' . $word) <= $maxLength) {
                    $currentLine .= ($currentLine ? ' ' : '') . $word;
                } else {
                    if ($currentLine) {
                        $lines[] = $currentLine;
                    }
                    $currentLine = $word;
                }
            }
            if ($currentLine) {
                $lines[] = $currentLine;
            }
            return implode("\n", $lines);
        };

        // ZÁHLAVÍ
        $logoPath = dirname(__DIR__, 2) . '/www/static/images/logo.png';
        $headerHeight = 35;

        $pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->Rect(0, 0, 210, $headerHeight, 'F');

        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 15, 8, 40, 0, 'PNG');
        }

        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('dejavusans', 'B', 24);
        $pdf->SetXY(120, 12);
        $pdf->Cell(70, 10, 'FAKTURA', 0, 0, 'R');

        $pdf->SetFont('dejavusans', '', 12);
        $pdf->SetXY(120, 24);
        $pdf->Cell(70, 6, $invoice->number, 0, 0, 'R');

        // DODAVATEL A ODBĚRATEL
        $startY = $headerHeight + 15;
        $pdf->SetTextColor($blackColor[0], $blackColor[1], $blackColor[2]);

        $pdf->SetFont('dejavusans', 'B', 11);
        $pdf->SetXY(15, $startY);
        $pdf->Cell(85, 6, 'Dodavatel', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetXY(15, $startY + 8);
        $pdf->MultiCell(85, 5, $smartWrap($company->name, 40), 0, 'L');

        $currentY = $pdf->GetY();
        $pdf->SetXY(15, $currentY);
        $pdf->Cell(85, 5, $company->address, 0, 1, 'L');
        $pdf->SetX(15);
        $pdf->Cell(85, 5, $company->zip . ' ' . $company->city, 0, 1, 'L');
        $pdf->SetX(15);
        $pdf->Cell(85, 5, 'IČO: ' . $company->ic, 0, 1, 'L');
        if ($isVatPayer) {
            $pdf->SetX(15);
            $pdf->Cell(85, 5, 'DIČ: ' . $company->dic, 0, 1, 'L');
        }

        $pdf->SetFont('dejavusans', 'B', 11);
        $pdf->SetXY(110, $startY);
        $pdf->Cell(85, 6, 'Odběratel', 0, 1, 'L');

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetXY(110, $startY + 8);
        $pdf->MultiCell(85, 5, $smartWrap($client->name, 40), 0, 'L');

        $currentY = $pdf->GetY();
        $pdf->SetXY(110, $currentY);
        $pdf->Cell(85, 5, $client->address, 0, 1, 'L');
        $pdf->SetX(110);
        $pdf->Cell(85, 5, $client->zip . ' ' . $client->city, 0, 1, 'L');
        if (!empty($client->ic)) {
            $pdf->SetX(110);
            $pdf->Cell(85, 5, 'IČO: ' . $client->ic, 0, 1, 'L');
        }
        if (!empty($client->dic)) {
            $pdf->SetX(110);
            $pdf->Cell(85, 5, 'DIČ: ' . $client->dic, 0, 1, 'L');
        }

        // ÚDAJE O FAKTUŘE
        $infoY = $startY + 55;
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->SetXY(15, $infoY);
        $pdf->Cell(85, 6, 'Číslo faktury: ', 0, 0, 'L');
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(85, 6, $invoice->number, 0, 1, 'L');

        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->SetX(15);
        $pdf->Cell(85, 6, 'Datum vystavení: ', 0, 0, 'L');
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(85, 6, $invoice->issue_date->format('d.m.Y'), 0, 1, 'L');

        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->SetX(15);
        $pdf->Cell(85, 6, 'Datum splatnosti: ', 0, 0, 'L');
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(85, 6, $invoice->due_date->format('d.m.Y'), 0, 1, 'L');

        if (!empty($invoice->variable_symbol)) {
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->SetX(15);
            $pdf->Cell(85, 6, 'Variabilní symbol: ', 0, 0, 'L');
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->Cell(85, 6, $invoice->variable_symbol, 0, 1, 'L');
        }

        // TABULKA POLOŽEK
        $tableY = $infoY + 35;
        $pdf->SetY($tableY);

        $pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('dejavusans', 'B', 10);

        if ($isVatPayer) {
            $pdf->Cell(70, 8, 'Položka', 1, 0, 'L', true);
            $pdf->Cell(20, 8, 'Množství', 1, 0, 'C', true);
            $pdf->Cell(35, 8, 'Cena/jedn.', 1, 0, 'R', true);
            $pdf->Cell(20, 8, 'DPH %', 1, 0, 'C', true);
            $pdf->Cell(35, 8, 'Celkem', 1, 1, 'R', true);
        } else {
            $pdf->Cell(90, 8, 'Položka', 1, 0, 'L', true);
            $pdf->Cell(30, 8, 'Množství', 1, 0, 'C', true);
            $pdf->Cell(40, 8, 'Cena/jedn.', 1, 0, 'R', true);
            $pdf->Cell(40, 8, 'Celkem', 1, 1, 'R', true);
        }

        $pdf->SetTextColor($blackColor[0], $blackColor[1], $blackColor[2]);
        $pdf->SetFont('dejavusans', '', 9);

        foreach ($invoiceItems as $item) {
            $itemTotal = $item->quantity * $item->price;

            if ($isVatPayer) {
                $pdf->Cell(70, 7, $smartWrap($item->description, 35), 1, 0, 'L');
                $pdf->Cell(20, 7, number_format($item->quantity, 2, ',', ' '), 1, 0, 'C');
                $pdf->Cell(35, 7, number_format($item->price, 2, ',', ' ') . ' Kč', 1, 0, 'R');
                $pdf->Cell(20, 7, $item->vat . '%', 1, 0, 'C');
                $pdf->Cell(35, 7, number_format($itemTotal, 2, ',', ' ') . ' Kč', 1, 1, 'R');
            } else {
                $pdf->Cell(90, 7, $smartWrap($item->description, 45), 1, 0, 'L');
                $pdf->Cell(30, 7, number_format($item->quantity, 2, ',', ' '), 1, 0, 'C');
                $pdf->Cell(40, 7, number_format($item->price, 2, ',', ' ') . ' Kč', 1, 0, 'R');
                $pdf->Cell(40, 7, number_format($itemTotal, 2, ',', ' ') . ' Kč', 1, 1, 'R');
            }
        }

        // CELKOVÁ ČÁSTKA
        $pdf->Ln(5);
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(145, 10, 'Celková částka k úhradě:', 0, 0, 'R');
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->Cell(35, 10, number_format($invoice->total, 2, ',', ' ') . ' Kč', 0, 1, 'R');

        // QR KÓD
        $pdf->SetTextColor($blackColor[0], $blackColor[1], $blackColor[2]);
        $pdf->Ln(10);
        $qrSize = 50;

        // QR kód přidáme přímo do PDF pomocí addQrPaymentToPdf
        $cleanVs = preg_replace('/\D/', '', $invoice->number);
        $this->qrPaymentService->addQrPaymentToPdf(
            $pdf,
            15,                     // x pozice
            $pdf->GetY(),           // y pozice  
            $qrSize,                // šířka
            $qrSize,                // výška
            $company->bank_account,
            $invoice->total,
            $cleanVs,
            'Faktura ' . $invoice->number
        );

        // Posuneme kurzor pod QR kód
        $pdf->SetY($pdf->GetY() + $qrSize + 5);

        $pdf->SetFont('dejavusans', '', 9);
        $pdf->SetXY(70, $pdf->GetY());
        $pdf->MultiCell(120, 5, "Číslo účtu: " . $company->bank_account . "\nVariabilní symbol: " . ($invoice->variable_symbol ?? 'N/A') . "\nČástka: " . number_format($invoice->total, 2, ',', ' ') . " Kč", 0, 'L');

        // POZNÁMKA
        if (!empty($invoice->note)) {
            $pdf->Ln(10);
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->Cell(0, 6, 'Poznámka:', 0, 1, 'L');
            $pdf->SetFont('dejavusans', '', 9);
            $pdf->MultiCell(0, 5, $invoice->note, 0, 'L');
        }

        // PATIČKA
        $pdf->SetAutoPageBreak(false);

        $pageWidth = 210;
        $pageHeight = 297;
        $footerHeight = 20;

        $footerStartY = $pageHeight - $footerHeight;
        $footerEndY = $pageHeight;

        $rightEdge = $pageWidth;
        $leftEdgeTop = 70;
        $leftEdgeBottom = 50;

        $p0x = $rightEdge;
        $p0y = $footerStartY;
        $p1x = $leftEdgeTop;
        $p1y = $footerStartY;
        $p2x = $leftEdgeBottom;
        $p2y = $footerEndY;
        $p3x = $rightEdge;
        $p3y = $footerEndY;

        $pdf->SetFillColor($footerColor[0], $footerColor[1], $footerColor[2]);
        $points = array($p0x, $p0y, $p1x, $p1y, $p2x, $p2y, $p3x, $p3y);
        $pdf->Polygon($points, 'F');

        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('dejavusans', '', 9);

        $colWidth = 60;
        $col1X = $leftEdgeTop + 15;
        $col2X = $col1X + $colWidth + 10;
        $verticalMiddle = $footerStartY + ($footerHeight / 2) - 5;

        $pdf->SetXY($col1X, $verticalMiddle);
        $pdf->Cell($colWidth, 6, $company->name, 0, 1, 'L');
        $pdf->SetXY($col1X, $verticalMiddle + 6);
        $pdf->Cell($colWidth, 6, $company->phone, 0, 1, 'L');

        $pdf->SetXY($col2X, $verticalMiddle);
        $pdf->Cell($colWidth, 6, 'Email:', 0, 1, 'L');
        $pdf->SetXY($col2X, $verticalMiddle + 6);
        $pdf->Cell($colWidth, 6, $company->email, 0, 1, 'L');

        // Výstup PDF do souboru
        $pdf->Output($outputPath, 'F');
    }
}
