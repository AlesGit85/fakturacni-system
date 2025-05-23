<?php

namespace App\Presentation\Clients;

use Nette;
use Nette\Application\UI\Form;
use App\Model\ClientsManager;
use App\Presentation\BasePresenter;

class ClientsPresenter extends BasePresenter
{
    /** @var ClientsManager */
    private $clientsManager;

    /** @var Nette\Database\Explorer */
    private $database;

    // Základní role pro přístup k presenteru
    protected array $requiredRoles = ['readonly', 'accountant', 'admin'];
    
    // Konkrétní role pro konkrétní akce
    protected array $actionRoles = [
        'default' => ['readonly', 'accountant', 'admin'], // Seznam klientů mohou vidět všichni
        'show' => ['readonly', 'accountant', 'admin'], // Detail klienta mohou vidět všichni
        'add' => ['accountant', 'admin'], // Přidat klienta může jen účetní a admin
        'edit' => ['accountant', 'admin'], // Upravit klienta může jen účetní a admin
        'delete' => ['admin'], // Smazat klienta může jen admin
    ];

    public function __construct(
        ClientsManager $clientsManager,
        Nette\Database\Explorer $database
    ) {
        $this->clientsManager = $clientsManager;
        $this->database = $database;
    }

    public function renderDefault(): void
    {
        $this->template->clients = $this->clientsManager->getAll();
    }

    public function renderShow(int $id): void
    {
        $client = $this->clientsManager->getById($id);

        if (!$client) {
            $this->error('Klient nebyl nalezen');
        }

        $this->template->client = $client;
    }

    protected function createComponentClientForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addText('name', 'Název společnosti:')
            ->setRequired('Zadejte název společnosti');

        $form->addTextArea('address', 'Adresa:')
            ->setRequired('Zadejte adresu');

        $form->addText('city', 'Město:')
            ->setRequired('Zadejte město');

        $form->addText('zip', 'PSČ:')
            ->setRequired('Zadejte PSČ');

        $form->addText('country', 'Země:')
            ->setRequired('Zadejte zemi')
            ->setDefaultValue('Česká republika');

        $form->addText('contact_person', 'Kontaktní osoba:');
        $form->addText('ic', 'IČ:');
        $form->addText('dic', 'DIČ:');
        $form->addEmail('email', 'E-mail:');
        $form->addText('phone', 'Telefon:');

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'clientFormSucceeded'];

        return $form;
    }

    public function clientFormSucceeded(Form $form, \stdClass $data): void
    {
        $id = $this->getParameter('id');

        if ($id) {
            $this->clientsManager->save($data, $id);
            $this->flashMessage('Klient byl úspěšně aktualizován', 'success');
        } else {
            $this->clientsManager->save($data);
            $this->flashMessage('Klient byl úspěšně přidán', 'success');
        }

        $this->redirect('default');
    }

    public function actionEdit(int $id): void
    {
        $client = $this->clientsManager->getById($id);

        if (!$client) {
            $this->error('Klient nebyl nalezen');
        }

        $this['clientForm']->setDefaults($client);
    }

    public function actionDelete(int $id): void
    {
        // Kontrola oprávnění pro mazání - pouze admin
        if (!$this->isAdmin()) {
            $this->flashMessage('Pouze administrátoři mohou mazat klienty.', 'danger');
            $this->redirect('show', $id);
        }

        // Nejprve zkontrolujeme, zda klient existuje
        $client = $this->clientsManager->getById($id);

        if (!$client) {
            $this->error('Klient nebyl nalezen');
        }

        // Kontrola, zda má klient faktury
        $invoiceCount = $this->database->table('invoices')
            ->where('client_id', $id)
            ->count();

        if ($invoiceCount > 0) {
            // Vytvořit srozumitelnou hlášku pro uživatele
            if ($invoiceCount == 1) {
                $message = "Klient '{$client->name}' má 1 fakturu v systému a nelze ho smazat. Nejprve musíte smazat tuto fakturu.";
            } else {
                $message = "Klient '{$client->name}' má {$invoiceCount} faktur v systému a nelze ho smazat. Nejprve musíte smazat tyto faktury.";
            }

            $message .= " <a href='" . $this->link('Invoices:default') . "'>Přejít na faktury</a>.";

            $this->flashMessage($message, 'danger');
            $this->redirect('show', $id); // Přesměrování na detail klienta
            return;
        }

        // Pokud nemá faktury, můžeme ho smazat
        $this->clientsManager->delete($id);
        $this->flashMessage("Klient '{$client->name}' byl úspěšně smazán", 'success');
        $this->redirect('default');
    }

    /**
     * Zpřístupní databázi pro šablony
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Zkontroluje, zda má klient faktury
     * @param int $clientId ID klienta
     * @return int Počet faktur klienta
     */
    public function getClientInvoiceCount(int $clientId): int
    {
        return $this->database->table('invoices')
            ->where('client_id', $clientId)
            ->count();
    }
}