<?php

namespace App\Presentation\Invoices;

use Nette;
use Nette\Application\UI\Form;
use App\Model\InvoicesManager;
use App\Model\ClientsManager;
use App\Model\CompanyManager;
use TCPDF;

class InvoicesPresenter extends Nette\Application\UI\Presenter
{
    /** @var InvoicesManager */
    private $invoicesManager;

    /** @var ClientsManager */
    private $clientsManager;

    /** @var CompanyManager */
    private $companyManager;

    public function __construct(InvoicesManager $invoicesManager, ClientsManager $clientsManager, CompanyManager $companyManager)
    {
        $this->invoicesManager = $invoicesManager;
        $this->clientsManager = $clientsManager;
        $this->companyManager = $companyManager;
    }

    public function renderDefault(): void
    {
        $this->template->invoices = $this->invoicesManager->getAll();
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

        // Nastavení fontu
        $pdf->SetFont('dejavusans', '', 10);

        // Logo společnosti
        if ($company->logo && file_exists(WWW_DIR . '/uploads/logo/' . $company->logo)) {
            $pdf->Image(WWW_DIR . '/uploads/logo/' . $company->logo, 10, 10, 50);
        }

        // Informace o faktuře
        $pdf->SetXY(130, 10);
        $pdf->SetFont('dejavusans', 'B', 16);
        $pdf->Cell(0, 10, 'FAKTURA ' . $invoice->number, 0, 1, 'R');

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetXY(130, 20);
        $pdf->Cell(0, 6, 'Datum vystavení: ' . date('d.m.Y', strtotime($invoice->issue_date)), 0, 1, 'R');
        $pdf->SetXY(130, 26);
        $pdf->Cell(0, 6, 'Datum splatnosti: ' . date('d.m.Y', strtotime($invoice->due_date)), 0, 1, 'R');
        $pdf->SetXY(130, 32);
        $pdf->Cell(0, 6, 'Forma úhrady: ' . $invoice->payment_method, 0, 1, 'R');

        // Oddělovací čára
        $pdf->Line(10, 45, 200, 45);

        // Dodavatel a odběratel
        $pdf->SetXY(10, 50);
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(90, 6, 'Dodavatel:', 0, 0);
        $pdf->Cell(90, 6, 'Odběratel:', 0, 1);

        $pdf->SetFont('dejavusans', '', 10);

        // Dodavatel
        $pdf->SetXY(10, 58);
        $pdf->Cell(90, 6, $company->name, 0, 1);
        $pdf->SetXY(10, 64);
        $pdf->Cell(90, 6, $company->address, 0, 1);
        $pdf->SetXY(10, 70);
        $pdf->Cell(90, 6, $company->zip . ' ' . $company->city, 0, 1);
        $pdf->SetXY(10, 76);
        $pdf->Cell(90, 6, 'IČ: ' . $company->ic, 0, 1);

        if ($company->dic) {
            $pdf->SetXY(10, 82);
            $pdf->Cell(90, 6, 'DIČ: ' . $company->dic, 0, 1);
        }

        // Odběratel
        $pdf->SetXY(100, 58);
        $pdf->Cell(90, 6, $client->name, 0, 1);
        $pdf->SetXY(100, 64);
        $pdf->Cell(90, 6, $client->address, 0, 1);
        $pdf->SetXY(100, 70);
        $pdf->Cell(90, 6, $client->zip . ' ' . $client->city, 0, 1);

        if ($client->ic) {
            $pdf->SetXY(100, 76);
            $pdf->Cell(90, 6, 'IČ: ' . $client->ic, 0, 1);
        }

        if ($client->dic) {
            $pdf->SetXY(100, 82);
            $pdf->Cell(90, 6, 'DIČ: ' . $client->dic, 0, 1);
        }

        // Oddělovací čára
        $pdf->Line(10, 95, 200, 95);

        // Položky faktury - různé zobrazení podle typu dodavatele
        if ($isVatPayer) {
            // Plátce DPH - zobrazení včetně DPH
            $pdf->SetXY(10, 100);
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->Cell(80, 6, 'Položka', 1, 0, 'C');
            $pdf->Cell(20, 6, 'Množství', 1, 0, 'C');
            $pdf->Cell(25, 6, 'Cena/jedn.', 1, 0, 'C');
            $pdf->Cell(15, 6, 'DPH %', 1, 0, 'C');
            $pdf->Cell(30, 6, 'Celkem s DPH', 1, 1, 'C');

            $pdf->SetFont('dejavusans', '', 9);
            $y = 106;
            $totalAmount = 0;

            foreach ($invoiceItems as $item) {
                $pdf->SetXY(10, $y);
                $pdf->Cell(80, 6, $item->name, 1, 0);
                $pdf->Cell(20, 6, $item->quantity . ' ' . $item->unit, 1, 0, 'C');
                $pdf->Cell(25, 6, number_format($item->price, 2, ',', ' ') . ' Kč', 1, 0, 'R');
                $pdf->Cell(15, 6, $item->vat . ' %', 1, 0, 'C');
                $pdf->Cell(30, 6, number_format($item->total, 2, ',', ' ') . ' Kč', 1, 1, 'R');
                $y += 6;
                $totalAmount += $item->total;
            }
        } else {
            // Neplátce DPH - zjednodušené zobrazení bez DPH
            $pdf->SetXY(10, 100);
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->Cell(140, 6, 'Předmět fakturace', 1, 0, 'C');
            $pdf->Cell(30, 6, 'Částka', 1, 1, 'C');

            $pdf->SetFont('dejavusans', '', 9);
            $y = 106;
            $totalAmount = 0;

            foreach ($invoiceItems as $item) {
                $pdf->SetXY(10, $y);
                $pdf->Cell(140, 6, $item->name, 1, 0);
                $pdf->Cell(30, 6, number_format($item->total, 2, ',', ' ') . ' Kč', 1, 1, 'R');
                $y += 6;
                $totalAmount += $item->total;
            }
        }

        // Celková částka
        $pdf->SetXY(140, $y + 5);
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(30, 6, 'Celkem k úhradě:', 0, 0, 'R');
        $pdf->Cell(30, 6, number_format($totalAmount, 2, ',', ' ') . ' Kč', 0, 1, 'R');

        // Dodavatel je/není plátce DPH
        if (!$isVatPayer) {
            $pdf->SetXY(10, $y + 10);
            $pdf->SetFont('dejavusans', 'I', 9);
            $pdf->Cell(0, 5, 'Dodavatel není plátcem DPH.', 0, 1);
        }

        // Poznámky
        if ($invoice->note) {
            $pdf->SetXY(10, $y + 15);
            $pdf->SetFont('dejavusans', 'I', 9);
            $pdf->MultiCell(0, 5, 'Poznámka: ' . $invoice->note, 0, 'L');
        }

        // Bankovní údaje
        $pdf->SetXY(10, $y + 25);
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->Cell(0, 5, 'Bankovní spojení: ' . $company->bank_name, 0, 1);
        $pdf->SetXY(10, $y + 30);
        $pdf->Cell(0, 5, 'Číslo účtu: ' . $company->bank_account, 0, 1);
        $pdf->SetXY(10, $y + 35);
        $pdf->Cell(0, 5, 'Variabilní symbol: ' . str_replace('/', '', $invoice->number), 0, 1);

        // Informace o platbě (náhrada za QR kód)
        if ($invoice->qr_payment && $company->bank_account) {
            $pdf->SetXY(150, $y + 25);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->Cell(0, 5, 'PLATEBNÍ ÚDAJE', 0, 1);

            $pdf->SetXY(150, $y + 32);
            $pdf->SetFont('dejavusans', '', 9);
            $pdf->Cell(0, 5, 'Částka: ' . number_format($totalAmount, 2, ',', ' ') . ' Kč', 0, 1);

            $pdf->SetXY(150, $y + 37);
            $pdf->Cell(0, 5, 'Účet: ' . $company->bank_account, 0, 1);

            $pdf->SetXY(150, $y + 42);
            $pdf->Cell(0, 5, 'VS: ' . str_replace('/', '', $invoice->number), 0, 1);
        }

        // Podpis
        if ($company->signature && file_exists(WWW_DIR . '/uploads/signature/' . $company->signature)) {
            $pdf->Image(WWW_DIR . '/uploads/signature/' . $company->signature, 20, $y + 45, 40);
        }

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

        $form->addCheckbox('qr_payment', 'Generovat QR kód pro platbu')
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
}
