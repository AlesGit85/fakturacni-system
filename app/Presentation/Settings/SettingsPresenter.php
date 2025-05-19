<?php

namespace App\Presentation\Settings;

use Nette;
use Nette\Application\UI\Form;
use App\Model\CompanyManager;

class SettingsPresenter extends Nette\Application\UI\Presenter
{
    /** @var CompanyManager */
    private $companyManager;

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

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'companyFormSucceeded'];

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
