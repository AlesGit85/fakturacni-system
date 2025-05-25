<?php

namespace App\Presentation\Clients;

use Nette;
use App\Model\AresService;
use Nette\Application\Responses\JsonResponse;
use App\Model\ClientsManager;
use Nette\Application\UI\Form;
use App\Presentation\BasePresenter;
use Tracy\ILogger;

class ClientsPresenter extends BasePresenter
{
    /** @var ClientsManager */
    private $clientsManager;

    /** @var Nette\Database\Explorer */
    private $database;

    /** @var AresService */
    private $aresService;

    /** @var ILogger */
    private $logger;

    // Základní role pro přístup k presenteru
    protected array $requiredRoles = ['readonly', 'accountant', 'admin'];
    
    // Konkrétní role pro konkrétní akce
    protected array $actionRoles = [
        'default' => ['readonly', 'accountant', 'admin'], // Seznam klientů mohou vidět všichni
        'show' => ['readonly', 'accountant', 'admin'], // Detail klienta mohou vidět všichni
        'add' => ['accountant', 'admin'], // Přidat klienta může jen účetní a admin
        'edit' => ['accountant', 'admin'], // Upravit klienta může jen účetní a admin
        'delete' => ['admin'], // Smazat klienta může jen admin
        'aresLookup' => ['accountant', 'admin'], // ARES lookup může jen účetní a admin
    ];

    public function __construct(
        ClientsManager $clientsManager, 
        Nette\Database\Explorer $database, 
        AresService $aresService,
        ILogger $logger
    ) {
        $this->clientsManager = $clientsManager;
        $this->database = $database;
        $this->aresService = $aresService;
        $this->logger = $logger;
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

    public function renderAdd(): void
    {
        // Přidáme URL pro ARES lookup do šablony
        $this->template->aresLookupUrl = $this->link('aresLookup!');
    }

    public function renderEdit(int $id): void
    {
        $client = $this->clientsManager->getById($id);

        if (!$client) {
            $this->error('Klient nebyl nalezen');
        }

        $this['clientForm']->setDefaults($client);
        
        // Přidáme URL pro ARES lookup do šablony
        $this->template->aresLookupUrl = $this->link('aresLookup!');
    }

    protected function createComponentClientForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        // === ARES a identifikační údaje (první) ===
        $form->addText('ic', 'IČ:')
            ->setHtmlAttribute('placeholder', 'Zadejte IČ a klikněte na načíst z ARESu');

        $form->addText('name', 'Název společnosti:')
            ->setRequired('Zadejte název společnosti');

        $form->addText('dic', 'DIČ:')
            ->setHtmlAttribute('placeholder', 'Volitelné - vyplní se automaticky z ARESu');

        // === Adresa ===
        $form->addTextArea('address', 'Adresa:')
            ->setRequired('Zadejte adresu')
            ->setHtmlAttribute('rows', 2);

        $form->addText('city', 'Město:')
            ->setRequired('Zadejte město');

        $form->addText('zip', 'PSČ:')
            ->setRequired('Zadejte PSČ')
            ->setHtmlAttribute('placeholder', '12345');

        $form->addText('country', 'Země:')
            ->setRequired('Zadejte zemi')
            ->setDefaultValue('Česká republika');

        // === Kontaktní údaje ===
        $form->addText('contact_person', 'Kontaktní osoba:')
            ->setHtmlAttribute('placeholder', 'Jméno kontaktní osoby');

        $form->addEmail('email', 'E-mail:')
            ->setHtmlAttribute('placeholder', 'email@firma.cz');

        $form->addText('phone', 'Telefon:')
            ->setHtmlAttribute('placeholder', '+420 123 456 789');

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
            // Vytvoření srozumitelné hlášky s správnou gramatikou
            $invoiceText = $this->getInvoiceCountText($invoiceCount);
            
            if ($invoiceCount == 1) {
                $message = "Klient '{$client->name}' má {$invoiceText} v systému a nelze ho smazat. Nejprve musíte smazat tuto fakturu.";
            } else {
                $message = "Klient '{$client->name}' má {$invoiceText} v systému a nelze ho smazat. Nejprve musíte smazat tyto faktury.";
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

    /**
     * Vyhledá firmu v ARESu podle IČO
     * AresService už vždy vrací data, takže nemusíme mít vlastní fallback
     */
    public function handleAresLookup(): void
    {
        // Získáme IČO z GET parametrů
        $ico = $this->getHttpRequest()->getQuery('ico');
        
        if (!$ico) {
            $this->sendJson(['error' => 'Nebylo zadáno IČO']);
            return;
        }
        
        // Logování požadavku
        $this->logger->log("ARES lookup požadavek pro IČO: $ico", ILogger::INFO);
        
        // AresService vždy vrací data (buď z ARESu nebo testovací)
        // Takže nepotřebujeme try-catch ani vlastní fallback
        $data = $this->aresService->getCompanyDataByIco($ico);
        
        // Kontrola, zda máme validní data
        if (!isset($data['name']) || empty(trim($data['name']))) {
            $this->logger->log("AresService vrátil nevalidní data pro IČO: $ico", ILogger::ERROR);
            $this->sendJson(['error' => 'Nepodařilo se načíst data pro zadané IČO']);
            return;
        }
        
        $this->logger->log("ARES úspěšně vrátil data pro IČO: $ico, firma: " . $data['name'], ILogger::INFO);
        
        // Pošleme data jako JSON
        $this->sendJson($data);
    }
}