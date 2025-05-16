<?php

declare(strict_types=1);

namespace App\Presentation\Dodavatel;

use Nette;
use Nette\Application\UI\Form;

final class DodavatelPresenter extends Nette\Application\UI\Presenter
{
    /** @var string */
    private $configDir;
    
    /** @var string */
    private $uploadsDir;

    public function __construct()
    {
        parent::__construct();
        $this->configDir = __DIR__ . '/../../../config/fakturaci';
        $this->uploadsDir = __DIR__ . '/../../../www/uploads/loga';
    }

    protected function startup(): void
    {
        parent::startup();
        
        // Zajistíme, že adresář pro konfiguraci existuje
        if (!is_dir($this->configDir)) {
            mkdir($this->configDir, 0755, true);
        }
        
        // Zajistíme, že adresář pro loga existuje
        if (!is_dir($this->uploadsDir)) {
            mkdir($this->uploadsDir, 0755, true);
        }
    }

    public function renderDefault(): void
    {
        $configFile = $this->configDir . '/dodavatel.json';
        
        // Výchozí hodnoty pro dodavatele
        $defaultDodavatel = [
            'nazev' => 'Aleš Zita',
            'adresa' => '503 46, Librantice 167',
            'ico' => '87894912',
            'dic' => '',
            'ucet' => '2695541004/5500',
            'banka' => 'Raiffeisenbank a.s.',
            'swift' => 'RZBCCZPP',
            'iban' => 'CZ3655000000002695541004',
            'plátce_dph' => false,
            'telefon' => '+420 703 985 390',
            'email' => 'a.zita@post.cz',
            'web' => '',
            'logo' => '',
            'zivnost_1' => 'Úřad příslušný podle §71 odst.2 živnostenského zákona:',
            'zivnost_2' => 'Magistrát města Hradec Králové'
        ];
        
        // Načtení existující konfigurace nebo použití výchozích hodnot
        if (file_exists($configFile)) {
            $dodavatel = json_decode(file_get_contents($configFile), true);
            // Zajištění, že existují všechny potřebné klíče
            $dodavatel = array_merge($defaultDodavatel, $dodavatel);
        } else {
            $dodavatel = $defaultDodavatel;
            // Vytvoření konfiguračního souboru s výchozími hodnotami
            file_put_contents($configFile, json_encode($dodavatel, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        
        $this->template->dodavatel = $dodavatel;
    }
    
    protected function createComponentDodavatelForm(): Form
    {
        $form = new Form;
        
        $form->addText('nazev', 'Jméno a příjmení / Název společnosti')
            ->setRequired('Zadejte název')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addTextArea('adresa', 'Adresa')
            ->setRequired('Zadejte adresu')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addText('ico', 'IČO')
            ->setRequired('Zadejte IČO')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addText('dic', 'DIČ')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addCheckbox('platce_dph', 'Jsem plátce DPH');
            
        $form->addText('ucet', 'Číslo účtu (ve formátu číslo/kód_banky)')
            ->setRequired('Zadejte číslo účtu')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addText('banka', 'Název banky')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addText('swift', 'BIC/SWIFT')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addText('iban', 'IBAN')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addText('telefon', 'Telefon')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addEmail('email', 'Email')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addText('web', 'Web')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addUpload('logo_upload', 'Logo')
            ->setHtmlAttribute('accept', 'image/*')
            ->addRule(Form::IMAGE, 'Soubor musí být obrázek (JPEG, PNG, GIF)')
            ->setRequired(false);
            
        $form->addCheckbox('odstranit_logo', 'Odstranit logo');
            
        $form->addText('zivnost_1', '§ citace')
            ->setHtmlAttribute('readonly', 'readonly')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addText('zivnost_2', 'Živnostenský úřad')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addSubmit('save', 'Uložit údaje')
            ->setHtmlAttribute('class', 'btn btn-primary');
            
        $form->onSuccess[] = [$this, 'dodavatelFormSucceeded'];
        
        // Naplnění formuláře hodnotami z šablony
        if ($this->template->dodavatel) {
            $form->setDefaults($this->template->dodavatel);
        }
        
        return $form;
    }
    
    public function dodavatelFormSucceeded(Form $form, array $values): void
    {
        $configFile = $this->configDir . '/dodavatel.json';
        
        // Zpracování loga
        $logoPath = $this->template->dodavatel['logo'] ?? ''; // Výchozí hodnota
        
        $logoUpload = $values['logo_upload'];
        if ($logoUpload->isOk()) {
            // Vytvoření unikátního názvu souboru
            $fileName = 'logo_' . time() . '.' . pathinfo($logoUpload->getName(), PATHINFO_EXTENSION);
            $destinationPath = $this->uploadsDir . '/' . $fileName;
            
            // Přesun souboru
            $logoUpload->move($destinationPath);
            
            // Aktualizace cesty k logu
            $logoPath = 'uploads/loga/' . $fileName;
            
            // Odstranění starého loga, pokud existuje
            if (!empty($this->template->dodavatel['logo']) && 
                $this->template->dodavatel['logo'] != $logoPath && 
                file_exists(__DIR__ . '/../../../www/' . $this->template->dodavatel['logo'])) {
                unlink(__DIR__ . '/../../../www/' . $this->template->dodavatel['logo']);
            }
        } elseif ($values['odstranit_logo'] && !empty($logoPath)) {
            // Odstranění loga, pokud bylo požadováno
            if (file_exists(__DIR__ . '/../../../www/' . $logoPath)) {
                unlink(__DIR__ . '/../../../www/' . $logoPath);
            }
            $logoPath = '';
        }
        
        // Vytvoření pole s údaji dodavatele
        $dodavatel = [
            'nazev' => $values['nazev'],
            'adresa' => $values['adresa'],
            'ico' => $values['ico'],
            'dic' => $values['dic'],
            'ucet' => $values['ucet'],
            'banka' => $values['banka'],
            'swift' => $values['swift'],
            'iban' => $values['iban'],
            'plátce_dph' => $values['platce_dph'],
            'telefon' => $values['telefon'],
            'email' => $values['email'],
            'web' => $values['web'],
            'logo' => $logoPath,
            'zivnost_1' => $values['zivnost_1'],
            'zivnost_2' => $values['zivnost_2']
        ];
        
        // Uložení do souboru
        file_put_contents($configFile, json_encode($dodavatel, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        $this->flashMessage('Údaje dodavatele byly úspěšně uloženy', 'success');
        $this->redirect('this');
    }
}