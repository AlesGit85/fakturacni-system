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
        QrPaymentService $qrPaymentService
    ) {
        $this->invoicesManager = $invoicesManager;
        $this->clientsManager = $clientsManager;
        $this->companyManager = $companyManager;
        $this->qrPaymentService = $qrPaymentService;
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
            // Pro existujícího klienta použijeme data z tabulky klientů
            $this->template->client = $this->clientsManager->getById($invoice->client_id);
        } else {
            // Pro ručně zadaného klienta vytvoříme objekt s údaji z faktury
            $manualClient = new \stdClass();
            $manualClient->name = $invoice->client_name;
            $manualClient->address = $invoice->client_address;
            $manualClient->city = $invoice->client_city;
            $manualClient->zip = $invoice->client_zip;
            $manualClient->country = $invoice->client_country;
            $manualClient->ic = $invoice->client_ic;
            $manualClient->dic = $invoice->client_dic;
            // Přidáme chybějící vlastnosti s prázdnými hodnotami
            $manualClient->email = '';
            $manualClient->phone = '';
            $manualClient->bank_account = '';

            $this->template->client = $manualClient;
        }

        $this->template->invoiceItems = $this->invoicesManager->getInvoiceItems($id);
        $this->template->company = $this->companyManager->getCompanyInfo();
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

        // Datum vystavení
        $pdf->SetXY($leftMargin, $pdf->GetY());
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($labelWidth, $rowHeight, 'Datum vystavení:', 0, 0, 'L');
        $pdf->SetX($valueX);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($valueWidth, $rowHeight, date('d. m. Y', strtotime($invoice->issue_date)), 0, 1, 'L');

        // Datum splatnosti
        $pdf->SetXY($leftMargin, $pdf->GetY());
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($labelWidth, $rowHeight, 'Datum splatnosti:', 0, 0, 'L');
        $pdf->SetX($valueX);
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell($valueWidth, $rowHeight, date('d. m. Y', strtotime($invoice->due_date)), 0, 1, 'L');

        // Mezera před dalšími údaji
        $pdf->Ln(2);

        // Banka
        $pdf->SetXY($leftMargin, $pdf->GetY());
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($labelWidth, $rowHeight, 'Banka:', 0, 0, 'L');
        $pdf->SetX($valueX);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell($valueWidth, $rowHeight, $company->bank_name, 0, 1, 'L');

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

        foreach ($invoiceItems as $key => $item) {
            // Nahradíme všechny možné varianty znaků nového řádku za standardní PHP \n
            $cleanText = str_replace(["\r\n", "\r"], "\n", $item->name);

            // Rozdělíme text na jednotlivé řádky
            $lines = explode("\n", $cleanText);

            // Vykreslíme každý řádek s vlastní spodní linkou
            foreach ($lines as $index => $line) {
                $pdf->Cell(180, 8, $line, 'B', 1, 'L');
            }

            // Pokud je popis, přidáme ho na další řádek
            if (!empty($item->description)) {
                $pdf->SetFont('dejavusans', 'I', 9);

                // Vyčistíme i popis stejným způsobem
                $cleanDesc = str_replace(["\r\n", "\r"], "\n", $item->description);
                $descLines = explode("\n", $cleanDesc);

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

        // Přepínač mezi existujícím a ručně zadaným klientem
        $clientTypeRadio = $form->addRadioList('client_type', 'Klient:', [
            'existing' => 'Vybrat existujícího klienta',
            'manual' => 'Zadat ručně'
        ])->setDefaultValue('existing');

        // Existující klient - výběr
        $clients = $this->clientsManager->getAll()->fetchPairs('id', 'name');
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

        $form->addSelect('payment_method', 'Způsob platby:', [
            'Bankovní převod' => 'Bankovní převod',
            'Hotovost' => 'Hotovost',
            'Karta' => 'Platební karta',
        ])
            ->setRequired('Vyberte způsob platby');

        // Možnosti zobrazení - všechny checkboxy na jednom místě
        $form->addCheckbox('qr_payment', 'Generovat QR kód pro platbu')
            ->setDefaultValue(true);

        $form->addCheckbox('show_logo', 'Zobrazit logo na faktuře')
            ->setDefaultValue(true);

        $form->addCheckbox('show_signature', 'Zobrazit podpis na faktuře')
            ->setDefaultValue(true);

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

        // Nastavíme typ klienta
        if ($invoice->manual_client) {
            $defaults['client_type'] = 'manual';
        } else {
            $defaults['client_type'] = 'existing';
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
}