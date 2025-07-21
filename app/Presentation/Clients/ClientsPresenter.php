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
     * ✅ XSS OCHRANA: Vytvoření formuláře pro klienta s bezpečnostními filtry
     */
    public function createComponentClientForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        // === ARES a identifikační údaje (první) ===
        $icField = $form->addText('ic', 'IČ:')
            ->setHtmlAttribute('placeholder', 'Zadejte IČ a klikněte na načíst z ARESu')
            ->setHtmlAttribute('maxlength', 8);
        $this->addSecurityFilters($icField, 'string');

        $nameField = $form->addText('name', 'Název společnosti:')
            ->setRequired('Zadejte název společnosti')
            ->setHtmlAttribute('maxlength', 255);
        $this->addSecurityFilters($nameField, 'string');

        $dicField = $form->addText('dic', 'DIČ:')
            ->setHtmlAttribute('placeholder', 'Volitelné - vyplní se automaticky z ARESu')
            ->setHtmlAttribute('maxlength', 15);
        $this->addSecurityFilters($dicField, 'string');

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
        // ✅ Základní validace PSČ
        $zipField->addRule(function ($control) {
            $value = preg_replace('/\s/', '', $control->getValue());
            return empty($value) || preg_match('/^\d{5}$/', $value);
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

        $phoneField = $form->addText('phone', 'Telefon:')
            ->setHtmlAttribute('placeholder', '+420 123 456 789')
            ->setHtmlAttribute('maxlength', 20);
        $this->addSecurityFilters($phoneField, 'phone');

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'clientFormSucceeded'];

        return $form;
    }

    /**
     * ✅ XSS OCHRANA: Zpracování formuláře s bezpečnostní kontrolou
     */
    public function clientFormSucceeded(Form $form, \stdClass $data): void
    {
        // ✅ XSS OCHRANA: Základní kontrola XSS pokusů ve formulářových datech
        $xssDetected = false;
        foreach ((array)$data as $key => $value) {
            if (is_string($value) && SecurityValidator::detectXssAttempt($value)) {
                $xssDetected = true;
                
                // Logování XSS pokusu
                $this->securityLogger->logSecurityEvent(
                    'xss_attempt_client_form',
                    "XSS pokus v poli '{$key}' formuláře klienta",
                    [
                        'field' => $key,
                        'client_ip' => $this->getHttpRequest()->getRemoteAddress(),
                        'user_id' => $this->getUser()->getId(),
                        'value_preview' => SecurityValidator::safeLogString($value, 50)
                    ]
                );
                break;
            }
        }

        if ($xssDetected) {
            $this->flashMessage(
                'Formulář obsahuje nebezpečný obsah (HTML/JavaScript kód). Zkontrolujte zadané údaje a odešlete formulář znovu.',
                'danger'
            );
            return;
        }

        // ✅ XSS OCHRANA: Sanitizace dat před uložením
        $sanitizedData = [];
        foreach ((array)$data as $key => $value) {
            if (is_string($value)) {
                $sanitizedData[$key] = SecurityValidator::sanitizeString($value);
            } else {
                $sanitizedData[$key] = $value;
            }
        }

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
                // EDITACE - save() vrací počet aktualizovaných řádků, ne objekt
                $this->clientsManager->save((object)$sanitizedData, $id);
                $this->flashMessage('Klient byl úspěšně aktualizován', 'success');

                // Logování úspěšné aktualizace
                $this->securityLogger->logSecurityEvent(
                    'client_updated',
                    "Klient ID:{$id} byl aktualizován uživatelem {$this->getUser()->getIdentity()->username}",
                    ['client_id' => $id, 'user_id' => $this->getUser()->getId()]
                );
                
                $this->redirect('default');
            } else {
                // NOVÝ KLIENT
                $newClient = $this->clientsManager->save((object)$sanitizedData);
                $newClientId = $newClient->id ?? 'unknown';

                $this->flashMessage('Klient byl úspěšně přidán', 'success');

                // Logování úspěšného vytvoření
                $this->securityLogger->logSecurityEvent(
                    'client_created',
                    "Nový klient byl vytvořen uživatelem {$this->getUser()->getIdentity()->username}",
                    ['client_id' => $newClientId, 'user_id' => $this->getUser()->getId()]
                );
                
                $this->redirect('default');
            }
        } catch (Nette\Application\AbortException $e) {
            // ✅ OPRAVA: AbortException (redirect) necháme projít!
            throw $e;
        } catch (\Exception $e) {
            // Pouze skutečné chyby
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
     * ✅ NOVÉ: Zjednodušená validace dat klienta
     */
    private function validateClientData(array $data): array
    {
        $errors = [];

        // Validace IČO - základní formát
        if (!empty($data['ic'])) {
            $ic = preg_replace('/\D/', '', $data['ic']); // Pouze číslice
            if (strlen($ic) < 7 || strlen($ic) > 8) {
                $errors[] = 'IČO musí mít 7 nebo 8 číslic.';
            }
        }

        // Validace DIČ - základní formát
        if (!empty($data['dic'])) {
            $dic = trim($data['dic']);
            if (!preg_match('/^(CZ)?[0-9]{8,12}$/', $dic)) {
                $errors[] = 'DIČ má neplatný formát.';
            }
        }

        // Validace emailu - jen pokud je vyplněn
        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'E-mailová adresa má neplatný formát.';
            }
        }

        // Validace PSČ - základní formát pro ČR
        if (!empty($data['zip'])) {
            $zip = preg_replace('/\s/', '', $data['zip']); // Odstranit mezery
            if (!preg_match('/^\d{5}$/', $zip)) {
                $errors[] = 'PSČ musí mít formát 12345.';
            }
        }

        // Validace názvu společnosti - nesmí být prázdný a nesmí obsahovat jen mezery
        if (!empty($data['name'])) {
            $name = trim($data['name']);
            if (strlen($name) < 2) {
                $errors[] = 'Název společnosti musí mít alespoň 2 znaky.';
            }
            if (strlen($name) > 255) {
                $errors[] = 'Název společnosti je příliš dlouhý (max. 255 znaků).';
            }
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
     * Vytvoří správný tvar slova "faktura" podle počtu
     * @param int $count Počet faktur
     * @return string Správný tvar
     */
    public function getInvoiceCountText(int $count): string
    {
        if ($count == 1) {
            return "1 fakturu";
        } elseif ($count >= 2 && $count <= 4) {
            return "{$count} faktury";
        } else {
            return "{$count} faktur";
        }
    }

    /**
     * Vyhledá firmu v ARESu podle IČO
     * ✅ PŮVODNÍ FUNKČNÍ VERZE s XSS ochranou
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

            // ✅ XSS OCHRANA: Sanitizace IČO
            $ico = SecurityValidator::sanitizeInvoiceNumber(trim($ico));
            
            // Validace IČO
            if (!preg_match('/^\d{7,8}$/', $ico)) {
                echo json_encode(['error' => 'Neplatné IČO. Zadejte 7 nebo 8 číslic.']);
                exit;
            }

            // PŮVODNÍ VOLÁNÍ - getCompanyInfo (alias který vrací null při neúspěchu)
            $result = $this->aresService->getCompanyInfo($ico);

            if ($result) {
                // ✅ XSS OCHRANA: Sanitizace dat z ARES před odesláním
                $sanitizedResult = SecurityValidator::sanitizeFormData((array)$result);
                echo json_encode(['success' => true, 'data' => $sanitizedResult]);
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