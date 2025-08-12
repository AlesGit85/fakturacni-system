<?php

namespace App\Presentation\Settings;

use Nette;
use Nette\Application\UI\Form;
use App\Model\CompanyManager;
use App\Presentation\BasePresenter;
use App\Security\SecurityValidator;

class SettingsPresenter extends BasePresenter
{
    /** @var CompanyManager */
    private $companyManager;

    // Pouze admin má přístup k nastavení
    protected array $requiredRoles = ['admin'];
    
    // Všechny akce v nastavení jsou pouze pro admina
    protected array $actionRoles = [
        'default' => ['admin'],
        'deleteLogo' => ['admin'],
        'deleteSignature' => ['admin'],
    ];

    // ✅ PŘIDEJ TYTO ŘÁDKY: Vypnout anti-spam pro upload formuláře
    protected bool $enableHoneypotProtection = false;
    protected bool $enableTimingProtection = false;

    public function __construct(CompanyManager $companyManager)
    {
        $this->companyManager = $companyManager;
    }

    /**
     * MULTI-TENANCY: Nastavení tenant kontextu po spuštění presenteru
     */
    public function startup(): void
    {
        parent::startup();
        
        // Nastavíme tenant kontext v CompanyManager
        $this->companyManager->setTenantContext(
            $this->getCurrentTenantId(),
            $this->isSuperAdmin()
        );
    }

    public function renderDefault(): void
    {
        $this->template->company = $this->companyManager->getCompanyInfo();
    }

    /**
     * ✅ XSS OCHRANA: Vytvoření formuláře pro firemní údaje s bezpečnostními filtry
     */
    public function createComponentCompanyForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        // ✅ Anti-spam ochrana
        $this->addAntiSpamProtectionToForm($form);

        // ========== Základní údaje firmy ==========
        $nameField = $form->addText('name', 'Název společnosti:')
            ->setRequired('Zadejte název společnosti')
            ->setHtmlAttribute('maxlength', 255);
        $this->addSecurityFilters($nameField, 'string');

        $addressField = $form->addTextArea('address', 'Adresa:')
            ->setRequired('Zadejte adresu')
            ->setHtmlAttribute('rows', 3)
            ->setHtmlAttribute('maxlength', 500);
        $this->addSecurityFilters($addressField, 'string');

        $cityField = $form->addText('city', 'Město:')
            ->setRequired('Zadejte město')
            ->setHtmlAttribute('maxlength', 100);
        $this->addSecurityFilters($cityField, 'string');

        $zipField = $form->addText('zip', 'PSČ:')
            ->setRequired('Zadejte PSČ')
            ->setHtmlAttribute('maxlength', 6);
        $this->addSecurityFilters($zipField, 'string');

        $countryField = $form->addText('country', 'Země:')
            ->setRequired('Zadejte zemi')
            ->setDefaultValue('Česká republika')
            ->setHtmlAttribute('maxlength', 100);
        $this->addSecurityFilters($countryField, 'string');

        $icField = $form->addText('ic', 'IČO:')
            ->setRequired('Zadejte IČO')
            ->setHtmlAttribute('maxlength', 8);
        $this->addSecurityFilters($icField, 'string');

        // ========== DIČ s podmínkou ==========
        $form->addCheckbox('vat_payer', 'Jsem plátce DPH');

        $dicField = $form->addText('dic', 'DIČ:')
            ->setHtmlAttribute('placeholder', 'Vyplňte pouze jako plátce DPH')
            ->setHtmlAttribute('maxlength', 15);
        $this->addSecurityFilters($dicField, 'string');

        // ========== Bankovní údaje ==========
        $bankAccountField = $form->addText('bank_account', 'Číslo účtu:')
            ->setHtmlAttribute('placeholder', 'např. 123456789/0100')
            ->setHtmlAttribute('maxlength', 50);
        $this->addSecurityFilters($bankAccountField, 'string');

        $bankNameField = $form->addText('bank_name', 'Název banky:')
            ->setHtmlAttribute('placeholder', 'např. Komerční banka')
            ->setHtmlAttribute('maxlength', 100);
        $this->addSecurityFilters($bankNameField, 'string');

        // ========== Kontaktní údaje ==========
        $emailField = $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte e-mail')
            ->setHtmlAttribute('maxlength', 254);
        $this->addSecurityFilters($emailField, 'email');

        $phoneField = $form->addText('phone', 'Telefon:')
            ->setHtmlAttribute('placeholder', '+420 123 456 789')
            ->setHtmlAttribute('maxlength', 20);
        $this->addSecurityFilters($phoneField, 'phone');

        // ========== Nahrávání souborů ==========
        $form->addUpload('logo', 'Logo společnosti:')
            ->setHtmlAttribute('accept', 'image/*')
            ->addRule(Form::IMAGE, 'Logo musí být obrázek');

        $form->addUpload('signature', 'Podpis:')
            ->setHtmlAttribute('accept', 'image/*')
            ->addRule(Form::IMAGE, 'Podpis musí být obrázek');

        // ========== Barvy pro faktury - podle vašeho schématu ==========
        $headingColorField = $form->addText('invoice_heading_color', 'Barva nadpisu faktury:')
            ->setHtmlAttribute('type', 'color')
            ->setHtmlAttribute('class', 'form-control form-control-color')
            ->setDefaultValue('#B1D235');
        
        $trapezoidBgColorField = $form->addText('invoice_trapezoid_bg_color', 'Barva pozadí lichoběžníku:')
            ->setHtmlAttribute('type', 'color')
            ->setHtmlAttribute('class', 'form-control form-control-color')
            ->setDefaultValue('#B1D235');

        $trapezoidTextColorField = $form->addText('invoice_trapezoid_text_color', 'Barva textu v lichoběžníku:')
            ->setHtmlAttribute('type', 'color')
            ->setHtmlAttribute('class', 'form-control form-control-color')
            ->setDefaultValue('#212529');

        $labelsColorField = $form->addText('invoice_labels_color', 'Barva štítků (Dodavatel, Odběratel, apod.):')
            ->setHtmlAttribute('type', 'color')
            ->setHtmlAttribute('class', 'form-control form-control-color')
            ->setDefaultValue('#95B11F');

        $footerColorField = $form->addText('invoice_footer_color', 'Barva patičky:')
            ->setHtmlAttribute('type', 'color')
            ->setHtmlAttribute('class', 'form-control form-control-color')
            ->setDefaultValue('#6c757d');

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'companyFormSucceeded'];

        // ========== Nastavení výchozích hodnot z databáze ==========
        $company = $this->companyManager->getCompanyInfo();
        if ($company) {
            $defaults = (array) $company;
            
            // Nastavení výchozích barev podle vašeho schématu, pokud nejsou v databázi
            $defaults['invoice_heading_color'] = $company->invoice_heading_color ?? '#B1D235';
            $defaults['invoice_trapezoid_bg_color'] = $company->invoice_trapezoid_bg_color ?? '#B1D235';
            $defaults['invoice_trapezoid_text_color'] = $company->invoice_trapezoid_text_color ?? '#212529';
            $defaults['invoice_labels_color'] = $company->invoice_labels_color ?? '#95B11F';
            $defaults['invoice_footer_color'] = $company->invoice_footer_color ?? '#6c757d';
            
            $form->setDefaults($defaults);
        } else {
            // Výchozí hodnoty pro nový záznam podle vašeho barevného schématu
            $form->setDefaults([
                'invoice_heading_color' => '#B1D235',
                'invoice_trapezoid_bg_color' => '#B1D235',
                'invoice_trapezoid_text_color' => '#212529',
                'invoice_labels_color' => '#95B11F',
                'invoice_footer_color' => '#6c757d'
            ]);
        }

        return $form;
    }

    /**
     * ✅ XSS OCHRANA: Zpracování formuláře s bezpečnostní kontrolou
     */
    public function companyFormSucceeded(Form $form, \stdClass $data): void
    {
        // ✅ XSS OCHRANA: Základní kontrola XSS pokusů ve formulářových datech
        $xssDetected = false;
        foreach ((array)$data as $key => $value) {
            if (is_string($value) && SecurityValidator::detectXssAttempt($value)) {
                $xssDetected = true;
                
                // Logování XSS pokusu
                $this->securityLogger->logSecurityEvent(
                    'xss_attempt_settings_form',
                    "XSS pokus v poli '{$key}' formuláře nastavení",
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

        // ✅ XSS OCHRANA: Sanitizace dat před zpracováním
        $values = [];
        foreach ((array)$data as $key => $value) {
            if (is_string($value)) {
                $values[$key] = SecurityValidator::sanitizeString($value);
            } else {
                $values[$key] = $value;
            }
        }

        // ✅ Zpracování vat_payer checkboxu
        $values['vat_payer'] = (bool) $data->vat_payer;
        
        // Pokud není plátce DPH, vymažeme DIČ
        if (!$values['vat_payer']) {
            $values['dic'] = null;
        }

        // ✅ Validace DIČ pokud je plátce DPH
        if ($values['vat_payer'] && empty($values['dic'])) {
            $this->flashMessage('Při zaškrtnutí "Jsem plátce DPH" je nutné vyplnit DIČ', 'error');
            return;
        }

        // ✅ NOVÉ: Dodatečná validace sanitizovaných dat
        $validationErrors = $this->validateCompanyData($values);
        if (!empty($validationErrors)) {
            foreach ($validationErrors as $error) {
                $this->flashMessage($error, 'danger');
            }
            return;
        }

        // ========== Zpracování nahrávání logo ==========
        if ($data->logo && $data->logo->isOk()) {
            try {
                $logoName = $this->processUploadedFile($data->logo, 'logo');
                if ($logoName) {
                    $values['logo'] = $logoName;
                } else {
                    $this->flashMessage('Chyba při nahrávání loga', 'error');
                    return;
                }
            } catch (\Exception $e) {
                $this->flashMessage('Chyba při nahrávání loga: ' . $e->getMessage(), 'error');
                return;
            }
        } else {
            unset($values['logo']);
        }

        // ========== Zpracování nahrávání podpisu ==========
        if ($data->signature && $data->signature->isOk()) {
            try {
                $signatureName = $this->processUploadedFile($data->signature, 'signature');
                if ($signatureName) {
                    $values['signature'] = $signatureName;
                } else {
                    $this->flashMessage('Chyba při nahrávání podpisu', 'error');
                    return;
                }
            } catch (\Exception $e) {
                $this->flashMessage('Chyba při nahrávání podpisu: ' . $e->getMessage(), 'error');
                return;
            }
        } else {
            unset($values['signature']);
        }

        // ========== Validace HEX barev ==========
        $colorFields = [
            'invoice_heading_color',
            'invoice_trapezoid_bg_color', 
            'invoice_trapezoid_text_color',
            'invoice_labels_color',
            'invoice_footer_color'
        ];

        foreach ($colorFields as $field) {
            if (isset($values[$field]) && !$this->isValidHexColor($values[$field])) {
                $this->flashMessage("Neplatná hodnota barvy pro pole: $field", 'error');
                return;
            }
        }

        // ========== Uložení do databáze ==========
        try {
            $this->companyManager->save($values);
            $this->flashMessage('Firemní údaje byly úspěšně uloženy', 'success');

            // Logování úspěšného uložení
            $this->securityLogger->logSecurityEvent(
                'company_settings_updated',
                "Firemní nastavení bylo aktualizováno uživatelem {$this->getUser()->getIdentity()->username}",
                ['user_id' => $this->getUser()->getId()]
            );

            $this->redirect('default');
        } catch (Nette\Application\AbortException $e) {
            // ✅ OPRAVA: AbortException (redirect) necháme projít!
            throw $e;
        } catch (\Exception $e) {
            $this->flashMessage('Chyba při ukládání: ' . $e->getMessage(), 'error');

            // Logování chyby
            $this->securityLogger->logSecurityEvent(
                'company_settings_save_error',
                "Chyba při ukládání firemních nastavení: " . $e->getMessage(),
                [
                    'user_id' => $this->getUser()->getId(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            );
        }
    }

    /**
     * ✅ NOVÉ: Validace firemních dat
     */
    private function validateCompanyData(array $data): array
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

        // Validace emailu
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

        // Validace názvu společnosti
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

    /**
     * Validace HEX barvy
     * @param string $color
     * @return bool
     */
    private function isValidHexColor(string $color): bool
    {
        return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color);
    }

    /**
     * ✅ XSS OCHRANA: Bezpečné zpracování nahrávání souborů
     */
    private function processUploadedFile(Nette\Http\FileUpload $file, string $type): ?string
    {
        // Kontrola, zda je soubor v pořádku
        if (!$file->isOk()) {
            throw new \Exception('Soubor nebyl úspěšně nahrán (error: ' . $file->getError() . ')');
        }

        // Kontrola, zda je to opravdu obrázek
        if (!$file->isImage()) {
            throw new \Exception('Soubor musí být obrázek');
        }

        // ✅ XSS OCHRANA: Sanitizace názvu souboru
        $originalName = SecurityValidator::sanitizeString($file->getName());

        // Vytvoření upload adresáře, pokud neexistuje
        $uploadDir = WWW_DIR . '/web/uploads/' . $type;
        
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new \Exception('Nepodařilo se vytvořit adresář pro nahrávání: ' . $uploadDir);
            }
        }

        // Kontrola, zda je adresář zapisovatelný
        if (!is_writable($uploadDir)) {
            throw new \Exception('Adresář pro nahrávání není zapisovatelný: ' . $uploadDir);
        }

        // Získání přípony souboru bezpečným způsobem
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        // Kontrola povolených přípon
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception('Nepodporovaný formát obrázku. Povolené formáty: ' . implode(', ', $allowedExtensions));
        }

        // Vytvoření jedinečného názvu souboru
        $fileName = uniqid() . '_' . time() . '.' . $extension;
        $fullPath = $uploadDir . '/' . $fileName;
        
        // Pokus o přesunutí souboru
        try {
            $file->move($fullPath);
        } catch (\Exception $e) {
            throw new \Exception('Nepodařilo se uložit soubor: ' . $e->getMessage());
        }

        // Ověření, že se soubor skutečně uložil
        if (!file_exists($fullPath)) {
            throw new \Exception('Soubor se nepodařilo úspěšně uložit');
        }

        // Kontrola velikosti nahraného souboru
        $fileSize = filesize($fullPath);
        if ($fileSize === false || $fileSize === 0) {
            unlink($fullPath); // Smažeme prázdný soubor
            throw new \Exception('Nahraný soubor je prázdný');
        }

        return $fileName;
    }

    /**
     * ✅ XSS OCHRANA: Bezpečné smazání loga společnosti
     */
    public function handleDeleteLogo(): void
    {
        try {
            $company = $this->companyManager->getCompanyInfo();

            if ($company && $company->logo) {
                // ✅ XSS OCHRANA: Sanitizace cesty k souboru
                $logoPath = WWW_DIR . '/web/uploads/logo/' . basename($company->logo);

                // Smažeme fyzický soubor
                if (file_exists($logoPath)) {
                    unlink($logoPath);
                }

                // Smažeme odkaz v databázi
                $this->companyManager->save(['logo' => null]);

                $this->flashMessage('Logo bylo úspěšně smazáno', 'success');

                // Logování akce
                $this->securityLogger->logSecurityEvent(
                    'company_logo_deleted',
                    "Logo společnosti bylo smazáno uživatelem {$this->getUser()->getIdentity()->username}",
                    ['user_id' => $this->getUser()->getId()]
                );
            } else {
                $this->flashMessage('Logo nebylo nalezeno', 'error');
            }

            $this->redirect('default');
        } catch (Nette\Application\AbortException $e) {
            // AbortException (redirect) necháme projít
            throw $e;
        } catch (\Exception $e) {
            $this->flashMessage('Chyba při mazání loga: ' . $e->getMessage(), 'error');
            $this->redirect('default');
        }
    }

    /**
     * ✅ XSS OCHRANA: Bezpečné smazání podpisu společnosti
     */
    public function handleDeleteSignature(): void
    {
        try {
            $company = $this->companyManager->getCompanyInfo();

            if ($company && $company->signature) {
                // ✅ XSS OCHRANA: Sanitizace cesty k souboru
                $signaturePath = WWW_DIR . '/web/uploads/signature/' . basename($company->signature);

                // Smažeme fyzický soubor
                if (file_exists($signaturePath)) {
                    unlink($signaturePath);
                }

                // Smažeme odkaz v databázi
                $this->companyManager->save(['signature' => null]);

                $this->flashMessage('Podpis byl úspěšně smazán', 'success');

                // Logování akce
                $this->securityLogger->logSecurityEvent(
                    'company_signature_deleted',
                    "Podpis společnosti byl smazán uživatelem {$this->getUser()->getIdentity()->username}",
                    ['user_id' => $this->getUser()->getId()]
                );
            } else {
                $this->flashMessage('Podpis nebyl nalezen', 'error');
            }

            $this->redirect('default');
        } catch (Nette\Application\AbortException $e) {
            // AbortException (redirect) necháme projít
            throw $e;
        } catch (\Exception $e) {
            $this->flashMessage('Chyba při mazání podpisu: ' . $e->getMessage(), 'error');
            $this->redirect('default');
        }
    }
}