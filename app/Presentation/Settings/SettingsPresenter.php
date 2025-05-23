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

    // Jen admin může přistupovat k nastavení
    protected array $requiredRoles = ['admin'];
    
    // Konkrétní role pro konkrétní akce
    protected array $actionRoles = [
        'default' => ['admin'], // K nastavení má přístup jen admin
        'deleteLogo' => ['admin'], // Smazat logo může jen admin
        'deleteSignature' => ['admin'], // Smazat podpis může jen admin
    ];

    public function __construct(CompanyManager $companyManager)
    {
        $this->companyManager = $companyManager;
    }

    public function renderDefault(): void
    {
        $this->template->company = $this->companyManager->getCompanyInfo();
    }

    public function createComponentCompanyForm(): Form
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

        $form->addText('ic', 'IČ:')
            ->setRequired('Zadejte IČ');

        // Checkbox pro plátce DPH
        $form->addCheckbox('vat_payer', 'Jsem plátce DPH');

        // DIČ
        $form->addText('dic', 'DIČ:')
            ->setRequired(false)
            ->setNullable();

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte e-mail');

        $form->addText('phone', 'Telefon:')
            ->setRequired('Zadejte telefon');

        $form->addText('bank_account', 'Číslo účtu:')
            ->setRequired('Zadejte číslo účtu');

        $form->addText('bank_name', 'Název banky:')
            ->setRequired('Zadejte název banky');

        $form->addUpload('logo', 'Logo společnosti:')
            ->setRequired(false)
            ->addRule(Form::IMAGE, 'Logo musí být ve formátu JPEG, PNG nebo GIF')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost souboru je 2 MB', 2 * 1024 * 1024);

        $form->addUpload('signature', 'Podpis:')
            ->setRequired(false)
            ->addRule(Form::IMAGE, 'Podpis musí být ve formátu JPEG, PNG nebo GIF')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost souboru je 2 MB', 2 * 1024 * 1024);

        // Barvy faktury
        $form->addText('invoice_heading_color', 'Barva nadpisu "FAKTURA":')
            ->setHtmlAttribute('type', 'color')
            ->setHtmlAttribute('class', 'form-control form-control-color');

        $form->addText('invoice_trapezoid_bg_color', 'Barva pozadí lichoběžníku:')
            ->setHtmlAttribute('type', 'color')
            ->setHtmlAttribute('class', 'form-control form-control-color');

        $form->addText('invoice_trapezoid_text_color', 'Barva textu v lichoběžníku:')
            ->setHtmlAttribute('type', 'color')
            ->setHtmlAttribute('class', 'form-control form-control-color');

        $form->addText('invoice_labels_color', 'Barva popisků (Dodavatel, Odběratel, atd.):')
            ->setHtmlAttribute('type', 'color')
            ->setHtmlAttribute('class', 'form-control form-control-color');

        $form->addText('invoice_footer_color', 'Barva patičky:')
            ->setHtmlAttribute('type', 'color')
            ->setHtmlAttribute('class', 'form-control form-control-color');

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'companyFormSucceeded'];

        // Nastavení výchozích hodnot z databáze
        $company = $this->companyManager->getCompanyInfo();
        if ($company) {
            $defaults = (array) $company;
            
            // Nastavení výchozích barev, pokud nejsou v databázi
            $defaults['invoice_heading_color'] = $company->invoice_heading_color ?? '#cacaca';
            $defaults['invoice_trapezoid_bg_color'] = $company->invoice_trapezoid_bg_color ?? '#cacaca';
            $defaults['invoice_trapezoid_text_color'] = $company->invoice_trapezoid_text_color ?? '#000000';
            $defaults['invoice_labels_color'] = $company->invoice_labels_color ?? '#cacaca';
            $defaults['invoice_footer_color'] = $company->invoice_footer_color ?? '#393b41';
            
            $form->setDefaults($defaults);
        } else {
            // Výchozí hodnoty pro nový záznam
            $form->setDefaults([
                'invoice_heading_color' => '#cacaca',
                'invoice_trapezoid_bg_color' => '#cacaca',
                'invoice_trapezoid_text_color' => '#000000',
                'invoice_labels_color' => '#cacaca',
                'invoice_footer_color' => '#393b41'
            ]);
        }

        return $form;
    }

    public function companyFormSucceeded(Form $form, \stdClass $data): void
    {
        $values = (array) $data;

        // Zpracování nahrávání logo
        if ($data->logo && $data->logo->isOk()) {
            $logoName = $this->processUploadedFile($data->logo, 'logo');
            $values['logo'] = $logoName;
        } else {
            unset($values['logo']);
        }

        // Zpracování nahrávání podpisu
        if ($data->signature && $data->signature->isOk()) {
            $signatureName = $this->processUploadedFile($data->signature, 'signature');
            $values['signature'] = $signatureName;
        } else {
            unset($values['signature']);
        }

        $this->companyManager->save($values);
        $this->flashMessage('Firemní údaje byly úspěšně uloženy', 'success');
        $this->redirect('default');
    }

    private function processUploadedFile(Nette\Http\FileUpload $file, string $type): string
    {
        $uploadDir = WWW_DIR . '/uploads/' . $type;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . '.' . $file->getImageFileExtension();
        $file->move($uploadDir . '/' . $fileName);

        return $fileName;
    }

    /**
     * Smaže logo společnosti
     */
    public function handleDeleteLogo(): void
    {
        $company = $this->companyManager->getCompanyInfo();

        if ($company && $company->logo) {
            $logoPath = WWW_DIR . '/uploads/logo/' . $company->logo;

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
            $signaturePath = WWW_DIR . '/uploads/signature/' . $company->signature;

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