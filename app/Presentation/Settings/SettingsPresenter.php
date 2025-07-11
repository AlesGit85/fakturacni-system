<?php

namespace App\Presentation\Settings;

use Nette;
use Nette\Application\UI\Form;
use App\Model\CompanyManager;
use App\Presentation\BasePresenter;

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

    public function createComponentCompanyForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        // Základní údaje firmy
        $form->addText('name', 'Název společnosti:')
            ->setRequired('Zadejte název společnosti');

        $form->addTextArea('address', 'Adresa:')
            ->setRequired('Zadejte adresu')
            ->setHtmlAttribute('rows', 3);

        $form->addText('city', 'Město:')
            ->setRequired('Zadejte město');

        $form->addText('zip', 'PSČ:')
            ->setRequired('Zadejte PSČ');

        $form->addText('country', 'Země:')
            ->setRequired('Zadejte zemi')
            ->setDefaultValue('Česká republika');

        $form->addText('ic', 'IČO:')
            ->setRequired('Zadejte IČO');

        // DIČ s podmínkou
        $form->addCheckbox('vat_payer', 'Jsem plátce DPH');

        $form->addText('dic', 'DIČ:')
            ->setHtmlAttribute('placeholder', 'Vyplňte pouze jako plátce DPH');

        // Bankovní údaje
        $form->addText('bank_account', 'Číslo účtu:')
            ->setHtmlAttribute('placeholder', 'např. 123456789/0100');

        $form->addText('bank_name', 'Název banky:')
            ->setHtmlAttribute('placeholder', 'např. Komerční banka');

        // Kontaktní údaje
        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte e-mail');

        $form->addText('phone', 'Telefon:')
            ->setHtmlAttribute('placeholder', '+420 123 456 789');

        // Nahrávání souborů
        $form->addUpload('logo', 'Logo společnosti:')
            ->setHtmlAttribute('accept', 'image/*')
            ->addRule(Form::IMAGE, 'Logo musí být obrázek');

        $form->addUpload('signature', 'Podpis:')
            ->setHtmlAttribute('accept', 'image/*')
            ->addRule(Form::IMAGE, 'Podpis musí být obrázek');

        // Barvy pro faktury - podle vašeho schématu
        $form->addText('invoice_heading_color', 'Barva nadpisu faktury:')
            ->setHtmlAttribute('type', 'color')
            ->setHtmlAttribute('class', 'form-control form-control-color')
            ->setDefaultValue('#B1D235');

        $form->addText('invoice_trapezoid_bg_color', 'Barva pozadí lichoběžníku:')
            ->setHtmlAttribute('type', 'color')
            ->setHtmlAttribute('class', 'form-control form-control-color')
            ->setDefaultValue('#B1D235');

        $form->addText('invoice_trapezoid_text_color', 'Barva textu v lichoběžníku:')
            ->setHtmlAttribute('type', 'color')
            ->setHtmlAttribute('class', 'form-control form-control-color')
            ->setDefaultValue('#212529');

        $form->addText('invoice_labels_color', 'Barva štítků (Dodavatel, Odběratel, apod.):')
            ->setHtmlAttribute('type', 'color')
            ->setHtmlAttribute('class', 'form-control form-control-color')
            ->setDefaultValue('#95B11F');

        $form->addText('invoice_footer_color', 'Barva patičky:')
            ->setHtmlAttribute('type', 'color')
            ->setHtmlAttribute('class', 'form-control form-control-color')
            ->setDefaultValue('#6c757d');

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'companyFormSucceeded'];

        // Nastavení výchozích hodnot z databáze
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

    public function companyFormSucceeded(Form $form, \stdClass $data): void
    {
        $values = (array) $data;

        // Zpracování vat_payer checkboxu (už je v $data)
        $values['vat_payer'] = (bool) $data->vat_payer;
        
        // Pokud není plátce DPH, vymažeme DIČ
        if (!$values['vat_payer']) {
            $values['dic'] = null;
        }

        // Validace DIČ pokud je plátce DPH
        if ($values['vat_payer'] && empty($values['dic'])) {
            $this->flashMessage('Při zaškrtnutí "Jsem plátce DPH" je nutné vyplnit DIČ', 'error');
            return;
        }

        // Zpracování nahrávání logo
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

        // Zpracování nahrávání podpisu
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

        // Validace HEX barev
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

        try {
            $this->companyManager->save($values);
            $this->flashMessage('Firemní údaje byly úspěšně uloženy', 'success');
        } catch (\Exception $e) {
            $this->flashMessage('Chyba při ukládání: ' . $e->getMessage(), 'error');
        }
        
        $this->redirect('default');
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
     * Zpracování nahrávání souborů
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

        // Vytvoření upload adresáře, pokud neexistuje
        $uploadDir = WWW_DIR . '/www/uploads/' . $type;
        
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
        $originalName = $file->getName();
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
     * Smaže logo společnosti
     */
    public function handleDeleteLogo(): void
    {
        $company = $this->companyManager->getCompanyInfo();

        if ($company && $company->logo) {
            $logoPath = WWW_DIR . '/www/uploads/logo/' . $company->logo;

            // Smažeme fyzický soubor
            if (file_exists($logoPath)) {
                unlink($logoPath);
            }

            // Smažeme odkaz v databázi
            $this->companyManager->save(['logo' => null]);

            $this->flashMessage('Logo bylo úspěšně smazáno', 'success');
        } else {
            $this->flashMessage('Logo nebylo nalezeno', 'error');
        }

        $this->redirect('default');
    }

    /**
     * Smaže podpis společnosti
     */
    public function handleDeleteSignature(): void
    {
        $company = $this->companyManager->getCompanyInfo();

        if ($company && $company->signature) {
            $signaturePath = WWW_DIR . '/www/uploads/signature/' . $company->signature;

            // Smažeme fyzický soubor
            if (file_exists($signaturePath)) {
                unlink($signaturePath);
            }

            // Smažeme odkaz v databázi
            $this->companyManager->save(['signature' => null]);

            $this->flashMessage('Podpis byl úspěšně smazán', 'success');
        } else {
            $this->flashMessage('Podpis nebyl nalezen', 'error');
        }

        $this->redirect('default');
    }
}