<?php

declare(strict_types=1);

namespace App\Presentation\Tenants;

use Nette;
use Nette\Application\UI\Form;
use App\Model\TenantManager;
use App\Model\AresService;
use App\Presentation\BasePresenter;
use Tracy\ILogger;

final class TenantsPresenter extends BasePresenter
{
    /** @var TenantManager */
    private $tenantManager;

    /** @var AresService */
    private $aresService;

    /** @var ILogger */
    private $logger;

    // Pouze super admin má přístup k správě tenantů
    protected array $requiredRoles = [];

    public function __construct(
        TenantManager $tenantManager,
        AresService $aresService,
        ILogger $logger
    ) {
        $this->tenantManager = $tenantManager;
        $this->aresService = $aresService;
        $this->logger = $logger;
    }

    public function startup(): void
    {
        parent::startup();
        
        // Kontrola super admin oprávnění pro všechny akce
        if (!$this->isSuperAdmin()) {
            $this->flashMessage('Pouze super admin může spravovat tenanty.', 'danger');
            $this->redirect('Home:default');
        }
    }

    public function renderDefault(): void
    {
        $this->template->tenants = $this->tenantManager->getAllTenantsWithStats();
        $this->template->dashboardStats = $this->tenantManager->getDashboardStats();
    }

    public function renderAdd(): void
    {
        // Příprava pro šablonu
        $this->template->pageTitle = 'Vytvořit nový tenant';
        
        // Přidáme URL pro ARES lookup do šablony
        $this->template->aresLookupUrl = $this->link('aresLookup!');
    }

    public function actionDeactivate(int $id): void
    {
        $superAdminId = $this->getUser()->getId();
        $reason = $this->getParameter('reason') ?? 'Deaktivace super adminem';
        
        if ($this->tenantManager->deactivateTenant($id, $superAdminId, $reason)) {
            $this->flashMessage('Tenant byl úspěšně deaktivován.', 'success');
        } else {
            $this->flashMessage('Chyba při deaktivaci tenanta.', 'danger');
        }
        
        $this->redirect('default');
    }

    public function actionActivate(int $id): void
    {
        $superAdminId = $this->getUser()->getId();
        
        if ($this->tenantManager->activateTenant($id, $superAdminId)) {
            $this->flashMessage('Tenant byl úspěšně aktivován.', 'success');
        } else {
            $this->flashMessage('Chyba při aktivaci tenanta.', 'danger');
        }
        
        $this->redirect('default');
    }

    public function actionDelete(int $id): void
    {
        // Získáme důvod z parametru
        $reason = $this->getParameter('reason');
        if (!$reason) {
            $this->flashMessage('Je nutné uvést důvod smazání tenanta.', 'danger');
            $this->redirect('default');
        }

        $superAdminId = $this->getUser()->getId();
        
        if ($this->tenantManager->deleteTenant($id, $superAdminId, $reason)) {
            $this->flashMessage('Tenant byl úspěšně smazán. VŠECHNA DATA BYLA ZTRACENA!', 'warning');
        } else {
            $this->flashMessage('Chyba při mazání tenanta.', 'danger');
        }
        
        $this->redirect('default');
    }

    /**
     * Vyhledá firmu v ARESu podle IČO - pro tenant formulář
     */
    public function handleAresLookup(): void
    {
        try {
            // Agresivní čištění všech output bufferů
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Ručně nastavíme content type header
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            
            // Kontrola oprávnění - pouze super admin může vytvářet tenanty
            if (!$this->isSuperAdmin()) {
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
            
            // Načtení dat z ARESu
            $result = $this->aresService->getCompanyInfo($ico);
            
            if ($result) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                echo json_encode(['error' => 'Firma s tímto IČO nebyla v ARESu nalezena.']);
            }
            
        } catch (\Exception $e) {
            // Logování chyby
            $this->logger->log("ARES Lookup Error (Tenants): " . $e->getMessage(), ILogger::ERROR);
            echo json_encode(['error' => 'Došlo k chybě při komunikaci s ARESem: ' . $e->getMessage()]);
        }
        
        exit;
    }

    /**
     * Formulář pro vytvoření nového tenanta
     */
    protected function createComponentCreateTenantForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        // Základní údaje tenanta
        $form->addGroup('Základní údaje tenanta');
        
        $form->addText('name', 'Název tenanta:')
            ->setRequired('Zadejte název tenanta')
            ->setHtmlAttribute('placeholder', 'např. Firma ABC s.r.o.')
            ->setHtmlAttribute('class', 'form-control');

        $form->addText('domain', 'Doména (volitelné):')
            ->setHtmlAttribute('placeholder', 'např. firma-abc.cz')
            ->setHtmlAttribute('class', 'form-control');

        // Firemní údaje
        $form->addGroup('Údaje společnosti');
        
        $form->addText('company_name', 'Název společnosti:')
            ->setRequired('Zadejte název společnosti')
            ->setHtmlAttribute('class', 'form-control');

        $form->addText('ic', 'IČO:')
            ->setHtmlAttribute('placeholder', '12345678')
            ->setHtmlAttribute('class', 'form-control')
            ->addRule(Form::PATTERN, 'IČO musí obsahovat 8 číslic', '[0-9]{8}');

        $form->addText('dic', 'DIČ:')
            ->setHtmlAttribute('placeholder', 'CZ12345678')
            ->setHtmlAttribute('class', 'form-control');

        $form->addCheckbox('vat_payer', 'Plátce DPH')
            ->setHtmlAttribute('class', 'form-check-input');

        $form->addText('phone', 'Telefon:')
            ->setHtmlAttribute('placeholder', '+420 123 456 789')
            ->setHtmlAttribute('class', 'form-control');

        $form->addTextArea('address', 'Adresa:')
            ->setHtmlAttribute('rows', 2)
            ->setHtmlAttribute('placeholder', 'Ulice a číslo popisné')
            ->setHtmlAttribute('class', 'form-control');

        $form->addText('city', 'Město:')
            ->setHtmlAttribute('placeholder', 'Praha')
            ->setHtmlAttribute('class', 'form-control');

        $form->addText('zip', 'PSČ:')
            ->setHtmlAttribute('placeholder', '110 00')
            ->setHtmlAttribute('class', 'form-control')
            ->addRule(Form::PATTERN, 'PSČ musí mít formát XXX XX', '\d{3}\s?\d{2}');

        $form->addText('country', 'Země:')
            ->setDefaultValue('Česká republika')
            ->setHtmlAttribute('class', 'form-control');

        // Admin údaje
        $form->addGroup('Administrátor tenanta');
        
        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Zadejte uživatelské jméno')
            ->setHtmlAttribute('class', 'form-control')
            ->addRule(Form::MIN_LENGTH, 'Uživatelské jméno musí mít alespoň %d znaků', 3);

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte e-mailovou adresu')
            ->setHtmlAttribute('class', 'form-control');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Zadejte heslo')
            ->setHtmlAttribute('class', 'form-control')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 6);

        $form->addPassword('password_confirm', 'Ověření hesla:')
            ->setRequired('Zadejte heslo znovu pro ověření')
            ->setHtmlAttribute('class', 'form-control')
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password']);

        $form->addText('first_name', 'Jméno:')
            ->setRequired('Zadejte jméno')
            ->setHtmlAttribute('class', 'form-control');

        $form->addText('last_name', 'Příjmení:')
            ->setRequired('Zadejte příjmení')
            ->setHtmlAttribute('class', 'form-control');

        // Tlačítka
        $form->addSubmit('send', 'Vytvořit tenant')
            ->setHtmlAttribute('class', 'btn btn-primary');

        $form->addSubmit('cancel', 'Zrušit')
            ->setValidationScope([])
            ->setHtmlAttribute('class', 'btn btn-secondary');

        $form->onSuccess[] = [$this, 'createTenantFormSucceeded'];

        return $form;
    }

    public function createTenantFormSucceeded(Form $form, \stdClass $data): void
    {
        // Kontrola, zda bylo kliknuto na zrušit
        if ($form->isSubmitted('cancel')) {
            $this->redirect('default');
        }

        $tenantData = [
            'name' => $data->name,
            'domain' => $data->domain ?: null,
            'settings' => []
        ];

        $adminData = [
            'username' => $data->username,
            'email' => $data->email,
            'password' => $data->password,
            'first_name' => $data->first_name,
            'last_name' => $data->last_name
        ];

        $companyData = [
            'company_name' => $data->company_name,
            'ic' => $data->ic ?: '',
            'dic' => $data->dic ?: '',
            'vat_payer' => $data->vat_payer,
            'phone' => $data->phone ?: '',
            'address' => $data->address ?: '',
            'city' => $data->city ?: '',
            'zip' => $data->zip ?: '',
            'country' => $data->country ?: 'Česká republika'
        ];

        // Vytvoření tenanta
        $result = $this->tenantManager->createTenant($tenantData, $adminData, $companyData);

        if ($result['success']) {
            $this->flashMessage($result['message'], 'success');
            $this->flashMessage("Admin uživatel: {$adminData['username']}, heslo bylo nastaveno podle zadání.", 'info');
            $this->redirect('default');
        } else {
            $this->flashMessage('Chyba při vytváření tenanta: ' . $result['message'], 'danger');
        }
    }

    /**
     * Formulář pro potvrzení smazání tenanta
     */
    protected function createComponentDeleteTenantForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        $form->addHidden('tenant_id');

        $form->addTextArea('reason', 'Důvod smazání:')
            ->setRequired('Je nutné uvést důvod smazání tenanta')
            ->setHtmlAttribute('rows', 4)
            ->setHtmlAttribute('placeholder', 'Uveďte detailní důvod, proč tenant mažete...')
            ->setHtmlAttribute('class', 'form-control');

        $form->addText('confirmation', 'Pro potvrzení napište: SMAZAT')
            ->setRequired('Pro potvrzení napište slovo SMAZAT')
            ->setHtmlAttribute('class', 'form-control')
            ->addRule(Form::EQUAL, 'Pro smazání musíte napsat přesně slovo SMAZAT', 'SMAZAT');

        $form->addSubmit('send', 'SMAZAT TENANT')
            ->setHtmlAttribute('class', 'btn btn-danger btn-lg');

        $form->onSuccess[] = [$this, 'deleteTenantFormSucceeded'];

        return $form;
    }

    public function deleteTenantFormSucceeded(Form $form, \stdClass $data): void
    {
        $tenantId = (int) $data->tenant_id;
        $reason = $data->reason;

        if ($tenantId <= 0) {
            $this->flashMessage('Neplatné ID tenanta.', 'danger');
            $this->redirect('default');
        }

        // Přesměrování na akci delete s parametry
        $this->redirect('delete', ['id' => $tenantId, 'reason' => $reason]);
    }
}