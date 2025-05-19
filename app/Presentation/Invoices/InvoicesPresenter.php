<?php

namespace App\Presentation\Invoices;

use Nette;
use Nette\Application\UI\Form;
use App\Model\InvoicesManager;
use App\Model\ClientsManager;
use App\Model\CompanyManager;
use App\Model\QrPaymentService;
use TCPDF;

class InvoicesPresenter extends Nette\Application\UI\Presenter
{
    /** @var InvoicesManager */
    private $invoicesManager;

    /** @var ClientsManager */
    private $clientsManager;

    /** @var CompanyManager */
    private $companyManager;

    /** @var QrPaymentService */
    private $qrPaymentService;

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

            $this->template->client = $manualClient;
        }

        $this->template->invoiceItems = $this->invoicesManager->getInvoiceItems($id);
        $this->template->company = $this->companyManager->getCompanyInfo();
    }

    public function actionDelete(int $id): void
    {
        $this->invoicesManager->delete($id);
        $this->flashMessage('Faktura byla úspěšně smazána', 'success');
        $this->redirect('default');
    }

    public function actionPdf(int $id): void
    {
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

        // Nastavení barev
        $primaryColor = array(70, 130, 180); // Steel Blue
        $secondaryColor = array(230, 230, 230); // Light Gray
        $textColor = array(50, 50, 50); // Dark Gray

        // Nastavení fontu
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

        // ------------------------------------------------
        // ZÁHLAVÍ FAKTURY
        // ------------------------------------------------

// Logo společnosti
if ($company->logo && file_exists(WWW_DIR . '/uploads/logo/' . $company->logo) && $invoice->show_logo) {
    $pdf->Image(WWW_DIR . '/uploads/logo/' . $company->logo, 15, 15, 40);
    $headerStartY = 15;
} else {
    // Pokud logo neexistuje nebo nemá být zobrazeno, zobrazíme název společnosti stylizovaně
    $pdf->SetFont('dejavusans', 'B', 20);
    $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
    $pdf->Cell(0, 10, $company->name, 0, 1, 'L');
    $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
    $headerStartY = 25;
}

        // Informace o faktuře
        $pdf->SetXY(120, $headerStartY);
        $pdf->SetFont('dejavusans', 'B', 18);
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->Cell(0, 10, 'FAKTURA ' . $invoice->number, 0, 1, 'R');
        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetXY(120, $headerStartY + 12);
        $pdf->Cell(0, 6, 'Datum vystavení: ' . date('d.m.Y', strtotime($invoice->issue_date)), 0, 1, 'R');
        $pdf->SetXY(120, $headerStartY + 18);
        $pdf->Cell(0, 6, 'Datum splatnosti: ' . date('d.m.Y', strtotime($invoice->due_date)), 0, 1, 'R');
        $pdf->SetXY(120, $headerStartY + 24);
        $pdf->Cell(0, 6, 'Forma úhrady: ' . $invoice->payment_method, 0, 1, 'R');

        // Stav faktury
        $pdf->SetXY(120, $headerStartY + 30);
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(30, 6, 'Stav faktury:', 0, 0, 'R');

        // Nastavení barvy podle stavu faktury
        if ($invoice->status == 'paid') {
            $pdf->SetTextColor(0, 128, 0); // Zelená pro zaplacenou fakturu
            $statusText = 'ZAPLACENO';
        } elseif ($invoice->status == 'overdue') {
            $pdf->SetTextColor(255, 0, 0); // Červená pro fakturu po splatnosti
            $statusText = 'PO SPLATNOSTI';
        } else {
            $pdf->SetTextColor(255, 165, 0); // Oranžová pro vystavenou fakturu
            $statusText = 'VYSTAVENO';
        }

        $pdf->Cell(40, 6, $statusText, 0, 1, 'R');
        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

        // ------------------------------------------------
        // SEKCE S ADRESAMI
        // ------------------------------------------------
        $boxStartY = $headerStartY + 45;

        // Nastavení pozadí pro adresní boxy
        $pdf->SetFillColor($secondaryColor[0], $secondaryColor[1], $secondaryColor[2]);

        // Box pro dodavatele
        $pdf->RoundedRect(15, $boxStartY, 80, 40, 2, '1111', 'DF', array(), $secondaryColor);

        // Box pro odběratele
        $pdf->RoundedRect(105, $boxStartY, 80, 40, 2, '1111', 'DF', array(), $secondaryColor);

        // Nadpisy boxů
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);

        $pdf->SetXY(15, $boxStartY + 2);
        $pdf->Cell(80, 6, 'DODAVATEL', 0, 0, 'C');

        $pdf->SetXY(105, $boxStartY + 2);
        $pdf->Cell(80, 6, 'ODBĚRATEL', 0, 1, 'C');

        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        $pdf->SetFont('dejavusans', '', 9);

        // Informace o dodavateli
        $pdf->SetXY(18, $boxStartY + 10);
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(0, 5, $company->name, 0, 1);
        $pdf->SetFont('dejavusans', '', 9);

        $pdf->SetXY(18, $boxStartY + 15);
        $pdf->MultiCell(75, 4, $company->address . "\n" . $company->zip . ' ' . $company->city . "\n" . $company->country, 0, 'L');

        $pdf->SetXY(18, $boxStartY + 28);
        $pdf->Cell(0, 4, 'IČ: ' . $company->ic, 0, 1);

        if ($company->dic) {
            $pdf->SetXY(18, $boxStartY + 32);
            $pdf->Cell(0, 4, 'DIČ: ' . $company->dic, 0, 1);
        }

        // Informace o odběrateli
        $pdf->SetXY(108, $boxStartY + 10);
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(0, 5, $client->name, 0, 1);
        $pdf->SetFont('dejavusans', '', 9);

        $pdf->SetXY(108, $boxStartY + 15);
        $pdf->MultiCell(75, 4, $client->address . "\n" . $client->zip . ' ' . $client->city . "\n" . $client->country, 0, 'L');

        if ($client->ic) {
            $pdf->SetXY(108, $boxStartY + 28);
            $pdf->Cell(0, 4, 'IČ: ' . $client->ic, 0, 1);
        }

        if ($client->dic) {
            $pdf->SetXY(108, $boxStartY + 32);
            $pdf->Cell(0, 4, 'DIČ: ' . $client->dic, 0, 1);
        }

        // ------------------------------------------------
        // SEKCE S POLOŽKAMI FAKTURY
        // ------------------------------------------------
        $itemsStartY = $boxStartY + 50;

        // Nadpis sekce
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->SetXY(15, $itemsStartY);
        $pdf->Cell(0, 10, 'POLOŽKY FAKTURY', 0, 1, 'L');
        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

        // Hlavička tabulky
        $pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('dejavusans', 'B', 9);

        $pdf->SetXY(15, $itemsStartY + 12);

        if ($isVatPayer) {
            // Plátce DPH - zobrazení včetně DPH
            $pdf->Cell(80, 7, 'Položka', 1, 0, 'C', true);
            $pdf->Cell(20, 7, 'Množství', 1, 0, 'C', true);
            $pdf->Cell(25, 7, 'Cena/jedn.', 1, 0, 'C', true);
            $pdf->Cell(15, 7, 'DPH %', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'Celkem s DPH', 1, 1, 'C', true);
        } else {
            // Neplátce DPH - zjednodušené zobrazení bez DPH
            $pdf->Cell(145, 7, 'Předmět fakturace', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'Částka', 1, 1, 'C', true);
        }

        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        $pdf->SetFont('dejavusans', '', 9);

        $y = $itemsStartY + 19;
        $totalAmount = 0;
        $rowCounter = 0;

        foreach ($invoiceItems as $item) {
            // Střídání barvy pozadí pro lepší čitelnost
            if ($rowCounter % 2 == 0) {
                $pdf->SetFillColor(245, 245, 245);
            } else {
                $pdf->SetFillColor(255, 255, 255);
            }

            if ($isVatPayer) {
                $pdf->SetXY(15, $y);
                $pdf->Cell(80, 6, $item->name, 1, 0, 'L', true);
                $pdf->Cell(20, 6, $item->quantity . ' ' . $item->unit, 1, 0, 'C', true);
                $pdf->Cell(25, 6, number_format($item->price, 2, ',', ' ') . ' Kč', 1, 0, 'R', true);
                $pdf->Cell(15, 6, $item->vat . ' %', 1, 0, 'C', true);
                $pdf->Cell(30, 6, number_format($item->total, 2, ',', ' ') . ' Kč', 1, 1, 'R', true);
            } else {
                $pdf->SetXY(15, $y);
                $pdf->Cell(145, 6, $item->name, 1, 0, 'L', true);
                $pdf->Cell(30, 6, number_format($item->total, 2, ',', ' ') . ' Kč', 1, 1, 'R', true);
            }

            $y += 6;
            $totalAmount += $item->total;
            $rowCounter++;
        }

        // Celková částka k úhradě
        $pdf->SetFont('dejavusans', 'B', 11);
        $pdf->SetXY(110, $y + 5);
        $pdf->Cell(50, 8, 'Celkem k úhradě:', 0, 0, 'R');
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->Cell(30, 8, number_format($totalAmount, 2, ',', ' ') . ' Kč', 0, 1, 'R');
        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

        // ------------------------------------------------
        // SEKCE S DODATEČNÝMI INFORMACEMI
        // ------------------------------------------------
        $footerStartY = $y + 20;

        // Dodavatel je/není plátce DPH
        if (!$isVatPayer) {
            $pdf->SetXY(15, $footerStartY);
            $pdf->SetFont('dejavusans', 'I', 9);
            $pdf->Cell(0, 5, 'Dodavatel není plátcem DPH.', 0, 1);
            $footerStartY += 5;
        }

        // Poznámky
        if ($invoice->note) {
            $pdf->SetXY(15, $footerStartY);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->Cell(0, 5, 'Poznámka:', 0, 1);

            $pdf->SetXY(15, $footerStartY + 5);
            $pdf->SetFont('dejavusans', 'I', 9);
            $pdf->MultiCell(110, 5, $invoice->note, 0, 'L');
            $footerStartY += 15;
        }

        // Platební údaje
        $pdf->SetXY(15, $footerStartY);
        $pdf->SetFont('dejavusans', 'B', 9);
        $pdf->Cell(0, 5, 'Platební údaje:', 0, 1);

        $pdf->SetXY(15, $footerStartY + 5);
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->Cell(0, 5, 'Bankovní spojení: ' . $company->bank_name, 0, 1);

        $pdf->SetXY(15, $footerStartY + 10);
        $pdf->Cell(0, 5, 'Číslo účtu: ' . $company->bank_account, 0, 1);

        $pdf->SetXY(15, $footerStartY + 15);
        $variableSymbol = str_replace('/', '', $invoice->number);
        $pdf->Cell(0, 5, 'Variabilní symbol: ' . $variableSymbol, 0, 1);

// Podpis
if ($company->signature && file_exists(WWW_DIR . '/uploads/signature/' . $company->signature) && $invoice->show_signature) {
    $pdf->Image(WWW_DIR . '/uploads/signature/' . $company->signature, 15, $footerStartY + 25, 40);
    
    $pdf->SetXY(15, $footerStartY + 45);
    $pdf->SetFont('dejavusans', '', 8);
    $pdf->Cell(40, 5, 'Podpis dodavatele', 0, 1, 'C');
}

        // Generování QR kódu pro platbu, pokud je požadován
        if ($invoice->qr_payment && $company->bank_account) {
            try {
                // Přidáme QR kód přímo do PDF
                $this->qrPaymentService->addQrPaymentToPdf(
                    $pdf,
                    150, // X souřadnice
                    $footerStartY + 5, // Y souřadnice 
                    40, // Šířka
                    40, // Výška
                    $company->bank_account,
                    $totalAmount,
                    $variableSymbol,
                    'Faktura ' . $invoice->number
                );
            } catch (\Exception $e) {
                // Pokud se nepodaří vygenerovat QR kód, přidáme alespoň platební údaje v textové podobě
                $pdf->SetXY(130, $footerStartY + 10);
                $pdf->SetFont('dejavusans', 'B', 9);
                $pdf->Cell(0, 5, 'PRO RYCHLOU PLATBU POUŽIJTE:', 0, 1);

                $pdf->SetXY(130, $footerStartY + 15);
                $pdf->SetFont('dejavusans', '', 9);
                $pdf->Cell(0, 5, 'Částka: ' . number_format($totalAmount, 2, ',', ' ') . ' Kč', 0, 1);

                $pdf->SetXY(130, $footerStartY + 20);
                $pdf->Cell(0, 5, 'Účet: ' . $company->bank_account, 0, 1);

                $pdf->SetXY(130, $footerStartY + 25);
                $pdf->Cell(0, 5, 'VS: ' . $variableSymbol, 0, 1);
            }
        }

        // ------------------------------------------------
        // PATIČKA FAKTURY
        // ------------------------------------------------
        $pdf->SetY(265);
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, 'Faktura byla vytvořena v systému ' . $company->name . '.', 0, 1, 'C');
        $pdf->Cell(0, 5, 'Děkujeme Vám za důvěru a těšíme se na další spolupráci.', 0, 1, 'C');

        // Výstup PDF
        $pdf->Output('faktura-' . $invoice->number . '.pdf', 'D');

        $this->terminate();
    }

    public function createComponentInvoiceForm(): Form
    {
        $form = new Form;

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
        $form->addHidden('user_id', 1);

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
            $invoiceData['client_id'] = 0; // Nastavíme 0 jako indikátor ručně zadaného klienta
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

    public function renderDefault(?string $search = null): void
    {
        // Kontrola faktur po splatnosti
        $this->invoicesManager->checkOverdueDates();

        // Získání faktur (případně filtrovaných)
        $this->template->invoices = $this->invoicesManager->getAll($search);
        $this->template->search = $search;
    }

    /**
     * Akce pro označení faktury jako zaplacené
     * @param int $id ID faktury
     */
    public function handleMarkAsPaid(int $id): void
    {
        $today = new \DateTime();

        $this->invoicesManager->updateStatus($id, 'paid', $today->format('Y-m-d'));
        $this->flashMessage('Faktura byla označena jako zaplacená', 'success');
        $this->redirect('this');
    }

    /**
     * Akce pro označení faktury jako vystavené (reset stavu)
     * @param int $id ID faktury
     */
    public function handleMarkAsCreated(int $id): void
    {
        $this->invoicesManager->updateStatus($id, 'created');
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
