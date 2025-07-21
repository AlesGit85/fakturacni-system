<?php

namespace App\Presentation\Clients;

use Nette;
use Tracy\ILogger;
use App\Model\AresService;
use App\Model\ClientsManager;
use Nette\Application\UI\Form;
use App\Presentation\BasePresenter;
use App\Security\SecurityValidator;
use Nette\Application\Responses\JsonResponse;

class ClientsPresenter extends BasePresenter
{
    /** @var ClientsManager */
    private $clientsManager;

    /** @var AresService */
    private $aresService;

    /** @var ILogger */
    private $logger;

    // Všichni přihlášení uživatelé mají základní přístup ke klientům
    protected array $requiredRoles = ['readonly', 'accountant', 'admin'];

    // Konkrétní role pro jednotlivé akce
    protected array $actionRoles = [
        'default' => ['readonly', 'accountant', 'admin'], // Seznam klientů mohou vidět všichni
        'show' => ['readonly', 'accountant', 'admin'], // Detail klienta mohou vidět všichni
        'add' => ['accountant', 'admin'], // Přidat klienta může účetní a admin
        'edit' => ['accountant', 'admin'], // Upravit klienta může účetní a admin
        'delete' => ['admin'], // Smazat klienta může jen admin
        'aresLookup' => ['accountant', 'admin'], // ARES lookup může účetní a admin (pro vytváření/editaci)
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

    /**
     * MULTI-TENANCY: Nastavení tenant kontextu po spuštění presenteru
     */
    public function startup(): void
    {
        parent::startup();

        // Nastavíme tenant kontext v ClientsManager
        $this->clientsManager->setTenantContext(
            $this->getCurrentTenantId(),
            $this->isSuperAdmin()
        );
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

    /**
     * ✅ PLNĚ ZABEZPEČENÝ formulář pro klienty s pokročilými validacemi
     */
    protected function createComponentClientForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        // === ARES a identifikační údaje (první) ===
        $icField = $form->addText('ic', 'IČ:')
            ->setHtmlAttribute('placeholder', 'Zadejte IČ a klikněte na načíst z ARESu')
            ->setHtmlAttribute('maxlength', 8);
        $this->addSecurityFilters($icField, 'string');
        $this->addSecurityValidation($icField, 'ico');

        $nameField = $form->addText('name', 'Název společnosti:')
            ->setRequired('Zadejte název společnosti')
            ->setHtmlAttribute('maxlength', 255);
        $this->addSecurityFilters($nameField, 'string');
        $this->addSecurityValidation($nameField, 'company_name');

        $dicField = $form->addText('dic', 'DIČ:')
            ->setHtmlAttribute('placeholder', 'Volitelné - vyplní se automaticky z ARESu')
            ->setHtmlAttribute('maxlength', 15);
        $this->addSecurityFilters($dicField, 'string');
        $this->addSecurityValidation($dicField, 'dic');

        // === Adresa ===
        $addressField = $form->addTextArea('address', 'Adresa:')
            ->setRequired('Zadejte adresu')
            ->setHtmlAttribute('rows', 2)
            ->setHtmlAttribute('maxlength', 500);
        $this->addSecurityFilters($addressField, 'string');

        $cityField = $form->addText('city', 'Město:')
            ->setRequired('Zadejte město')
            ->setHtmlAttribute('maxlength', 100);
        $this->addSecurityFilters($cityField, 'string');

        $zipField = $form->addText('zip', 'PSČ:')
            ->setRequired('Zadejte PSČ')
            ->setHtmlAttribute('placeholder', '12345')
            ->setHtmlAttribute('maxlength', 6);
        $this->addSecurityFilters($zipField, 'string');
        // ✅ NOVÉ: Validace PSČ
        $zipField->addRule(function ($control) {
            $value = $control->getValue();
            return empty($value) || SecurityValidator::validatePostalCode($value, 'CZ');
        }, 'Zadejte platné PSČ (např. 12345).');

        $countryField = $form->addText('country', 'Země:')
            ->setRequired('Zadejte zemi')
            ->setDefaultValue('Česká republika')
            ->setHtmlAttribute('maxlength', 100);
        $this->addSecurityFilters($countryField, 'string');

        // === Kontaktní údaje ===
        $contactField = $form->addText('contact_person', 'Kontaktní osoba:')
            ->setHtmlAttribute('placeholder', 'Jméno kontaktní osoby')
            ->setHtmlAttribute('maxlength', 100);
        $this->addSecurityFilters($contactField, 'string');

        $emailField = $form->addEmail('email', 'E-mail:')
            ->setHtmlAttribute('placeholder', 'email@firma.cz')
            ->setHtmlAttribute('maxlength', 254); // RFC limit
        $this->addSecurityFilters($emailField, 'email');
        $this->addSecurityValidation($emailField, 'email');

        $phoneField = $form->addText('phone', 'Telefon:')
            ->setHtmlAttribute('placeholder', '+420 123 456 789')
            ->setHtmlAttribute('maxlength', 20);
        $this->addSecurityFilters($phoneField, 'phone');
        $this->addSecurityValidation($phoneField, 'phone');

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'clientFormSucceeded'];

        return $form;
    }

    /**
     * ✅ PLNĚ ZABEZPEČENÉ zpracování formuláře s detailním logováním
     */
    public function clientFormSucceeded(Form $form, \stdClass $data): void
    {
        // ✅ NOVÉ: Kontrola XSS pokusů
        if ($this->hasXssAttempts()) {
            $attempts = $this->getXssAttempts();

            $this->flashMessage(
                'Formulář obsahuje nebezpečný obsah. Zkontrolujte zadané údaje.',
                'danger'
            );

            // Podrobné logování pro admina
            $this->securityLogger->logSecurityEvent(
                'form_xss_blocked',
                'Formulář klienta byl zablokován kvůli XSS pokusu',
                [
                    'attempts' => $attempts,
                    'user_id' => $this->getUser()->getId(),
                    'client_id' => $this->getParameter('id'),
                    'form_data_preview' => array_map(function ($value) {
                        return is_string($value) ? SecurityValidator::safeLogString($value, 30) : $value;
                    }, (array)$data)
                ]
            );

            return; // Zastavíme zpracování
        }

        // ✅ Sanitizace dat před uložením
        $sanitizedData = $this->sanitizeFormData((array)$data);

        // ✅ NOVÉ: Dodatečná validace sanitizovaných dat
        $validationErrors = $this->validateClientData($sanitizedData);
        if (!empty($validationErrors)) {
            foreach ($validationErrors as $error) {
                $this->flashMessage($error, 'danger');
            }
            return;
        }

        $id = $this->getParameter('id');

        try {
            if ($id) {
                $this->clientsManager->save((object)$sanitizedData, $id);
                $this->flashMessage('Klient byl úspěšně aktualizován', 'success');

                // Logování úspěšné aktualizace
                $this->securityLogger->logSecurityEvent(
                    'client_updated',
                    "Klient ID:{$id} byl aktualizován uživatelem {$this->getUser()->getIdentity()->username}",
                    ['client_id' => $id, 'user_id' => $this->getUser()->getId()]
                );
            } else {
                $newClient = $this->clientsManager->save((object)$sanitizedData);
                $newClientId = $newClient->id ?? 'unknown';

                $this->flashMessage('Klient byl úspěšně přidán', 'success');

                // Logování úspěšného vytvoření
                $this->securityLogger->logSecurityEvent(
                    'client_created',
                    "Nový klient byl vytvořen uživatelem {$this->getUser()->getIdentity()->username}",
                    ['client_id' => $newClientId, 'user_id' => $this->getUser()->getId()]
                );
            }

            $this->redirect('default');
        } catch (\Exception $e) {
            $this->flashMessage('Chyba při ukládání klienta: ' . $e->getMessage(), 'danger');

            // Logování chyby
            $this->securityLogger->logSecurityEvent(
                'client_save_error',
                "Chyba při ukládání klienta: " . $e->getMessage(),
                [
                    'user_id' => $this->getUser()->getId(),
                    'client_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            );
        }
    }

    /**
     * ✅ NOVÉ: Dodatečná validace dat klienta
     */
    private function validateClientData(array $data): array
    {
        $errors = [];

        // Validace IČO
        if (!empty($data['ic']) && !SecurityValidator::validateICO($data['ic'])) {
            $errors[] = 'IČO má neplatný formát nebo kontrolní součet.';
        }

        // Validace DIČ
        if (!empty($data['dic']) && !SecurityValidator::validateDIC($data['dic'])) {
            $errors[] = 'DIČ má neplatný formát.';
        }

        // Validace emailu
        if (!empty($data['email']) && !SecurityValidator::validateEmail($data['email'])) {
            $errors[] = 'E-mailová adresa má neplatný formát.';
        }

        // Validace telefonu
        if (!empty($data['phone']) && !SecurityValidator::validatePhoneNumber($data['phone'])) {
            $errors[] = 'Telefonní číslo má neplatný formát.';
        }

        // Validace názvu společnosti
        if (!empty($data['name'])) {
            $nameErrors = SecurityValidator::validateCompanyName($data['name']);
            $errors = array_merge($errors, $nameErrors);
        }

        // Validace PSČ
        if (!empty($data['zip']) && !SecurityValidator::validatePostalCode($data['zip'], 'CZ')) {
            $errors[] = 'PSČ má neplatný formát.';
        }

        return $errors;
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
        // Kontrola oprávnění je už v actionRoles - pouze admin

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
     * VRÁCENA PŮVODNÍ FUNKČNÍ VERZE
     */
    public function handleAresLookup(): void
    {
        try {
            // Agresivní čištění všech output bufferů (řeší problém s již odeslanými headers)
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Ručně nastavíme content type header
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }

            // Explicitní kontrola oprávnění - pouze účetní a admin
            if (!$this->isAccountant()) {
                echo json_encode(['error' => 'Nemáte oprávnění pro vyhledávání v ARESu.']);
                exit;
            }

            // Získáme IČO z GET parametrů
            $ico = $this->getHttpRequest()->getQuery('ico');

            if (!$ico) {
                echo json_encode(['error' => 'Nebylo zadáno IČO']);
                exit;
            }

            // Validace IČO
            $ico = trim($ico);
            if (!preg_match('/^\d{7,8}$/', $ico)) {
                echo json_encode(['error' => 'Neplatné IČO. Zadejte 7 nebo 8 číslic.']);
                exit;
            }

            // PŮVODNÍ VOLÁNÍ - getCompanyInfo (alias který vrací null při neúspěchu)
            $result = $this->aresService->getCompanyInfo($ico);

            if ($result) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                echo json_encode(['error' => 'Firma s tímto IČO nebyla v ARESu nalezena.']);
            }
        } catch (\Exception $e) {
            // Logování chyby
            $this->logger->log("ARES Lookup Error: " . $e->getMessage(), \Tracy\ILogger::ERROR);
            echo json_encode(['error' => 'Došlo k chybě při komunikaci s ARESem: ' . $e->getMessage()]);
        }

        exit;
    }
}
