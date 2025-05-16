<?php

declare(strict_types=1);

namespace App\Presentation\Odberatel;

use Nette;
use Nette\Application\UI\Form;

final class OdberatelPresenter extends Nette\Application\UI\Presenter
{
    /** @var string */
    private $configFile;

    public function __construct()
    {
        parent::__construct();
        $this->configFile = __DIR__ . '/../../../config/fakturaci/odberatele.json';
    }

    protected function startup(): void
    {
        parent::startup();
        
        // Zajistíme, že adresář pro konfiguraci existuje
        if (!is_dir(dirname($this->configFile))) {
            mkdir(dirname($this->configFile), 0755, true);
        }
    }

    public function renderDefault(): void
    {
        // Inicializace pole odběratelů
        if (file_exists($this->configFile)) {
            $odberatele = json_decode(file_get_contents($this->configFile), true);
            if (!is_array($odberatele)) {
                $odberatele = [];
            }
        } else {
            $odberatele = [];
            // Vytvoření prázdného souboru
            file_put_contents($this->configFile, json_encode($odberatele, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        
        // Implementace vyhledávání
        $searchTerm = $this->getParameter('search');
        $filteredOdberatele = $odberatele;
        
        if (!empty($searchTerm)) {
            $filteredOdberatele = array_filter($odberatele, function($odberatel) use ($searchTerm) {
                return (
                    stripos($odberatel['nazev'], $searchTerm) !== false ||
                    stripos($odberatel['adresa'], $searchTerm) !== false ||
                    stripos($odberatel['ico'], $searchTerm) !== false ||
                    stripos($odberatel['dic'], $searchTerm) !== false ||
                    (isset($odberatel['kontakt']['jmeno']) && stripos($odberatel['kontakt']['jmeno'], $searchTerm) !== false) ||
                    (isset($odberatel['kontakt']['email']) && stripos($odberatel['kontakt']['email'], $searchTerm) !== false)
                );
            });
        }
        
        // Abecední řazení odběratelů
        usort($filteredOdberatele, function($a, $b) {
            return strcasecmp($a['nazev'], $b['nazev']);
        });
        
        $this->template->odberatele = $filteredOdberatele;
        $this->template->searchTerm = $searchTerm;
    }
    
    public function renderAdd(): void
    {
        // Stránka pro přidání nového odběratele
    }
    
    public function renderEdit(int $id): void
    {
        // Načtení dat pro editaci
        $odberatele = $this->loadOdberatele();
        $editData = null;
        
        foreach ($odberatele as $odberatel) {
            if ($odberatel['id'] == $id) {
                $editData = $odberatel;
                break;
            }
        }
        
        if (!$editData) {
            $this->flashMessage('Odběratel nebyl nalezen', 'error');
            $this->redirect('default');
        }
        
        $this->template->editData = $editData;
        
        // Předvyplnění formuláře
        if (!$this->isPost()) {
            $defaults = $editData;
            $defaults['kontakt_jmeno'] = $editData['kontakt']['jmeno'] ?? '';
            $defaults['kontakt_telefon'] = $editData['kontakt']['telefon'] ?? '';
            $defaults['kontakt_email'] = $editData['kontakt']['email'] ?? '';
            $defaults['edit_id'] = $editData['id']; // Důležité pro identifikaci editovaného záznamu
            
            $this['odberatelForm']->setDefaults($defaults);
        }
    }
    
    public function actionDelete(int $id): void
    {
        $odberatele = $this->loadOdberatele();
        
        foreach ($odberatele as $key => $odberatel) {
            if ($odberatel['id'] == $id) {
                unset($odberatele[$key]);
                break;
            }
        }
        
        // Přeindexování pole a uložení do souboru
        $odberatele = array_values($odberatele);
        file_put_contents($this->configFile, json_encode($odberatele, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        $this->flashMessage('Odběratel byl úspěšně smazán', 'success');
        $this->redirect('default');
    }
    
    protected function createComponentOdberatelForm(): Form
    {
        $form = new Form;
        
        $form->addText('nazev', 'Název společnosti / Jméno a příjmení')
            ->setRequired('Zadejte název')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addTextArea('adresa', 'Adresa')
            ->setRequired('Zadejte adresu')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('rows', 4);
            
        $form->addText('ico', 'IČO')
            ->setRequired('Zadejte IČO')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addText('dic', 'DIČ')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addText('kontakt_jmeno', 'Jméno a příjmení')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addText('kontakt_telefon', 'Telefon')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addEmail('kontakt_email', 'E-mail')
            ->setHtmlAttribute('class', 'form-control');
            
        $form->addHidden('edit_id');
            
        $form->addSubmit('save', 'Uložit odběratele')
            ->setHtmlAttribute('class', 'btn btn-primary');
            
        $form->onSuccess[] = [$this, 'odberatelFormSucceeded'];
        
        return $form;
    }
    
    public function odberatelFormSucceeded(Form $form, array $values): void
    {
        $odberatele = $this->loadOdberatele();
        
        // Data odběratele
        $odberatelData = [
            'nazev' => $values['nazev'],
            'adresa' => $values['adresa'],
            'ico' => $values['ico'],
            'dic' => $values['dic'],
            'kontakt' => [
                'jmeno' => $values['kontakt_jmeno'],
                'telefon' => $values['kontakt_telefon'],
                'email' => $values['kontakt_email']
            ]
        ];
        
        // Editace nebo přidání nového odběratele
        if (!empty($values['edit_id'])) {
            // Editace existujícího odběratele
            $id = (int) $values['edit_id'];
            $found = false;
            
            foreach ($odberatele as $key => $odberatel) {
                if ($odberatel['id'] == $id) {
                    $odberatele[$key] = array_merge(['id' => $id], $odberatelData);
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $this->flashMessage('Odběratel nebyl nalezen', 'error');
                $this->redirect('default');
            }
            
            $this->flashMessage('Odběratel byl úspěšně upraven', 'success');
        } else {
            // Přidání nového odběratele
            $maxId = 0;
            foreach ($odberatele as $odberatel) {
                if (isset($odberatel['id']) && $odberatel['id'] > $maxId) {
                    $maxId = $odberatel['id'];
                }
            }
            
            $newId = $maxId + 1;
            $odberatele[] = array_merge(['id' => $newId], $odberatelData);
            
            $this->flashMessage('Odběratel byl úspěšně přidán', 'success');
        }
        
        // Abecední řazení odběratelů
        usort($odberatele, function($a, $b) {
            return strcasecmp($a['nazev'], $b['nazev']);
        });
        
        // Uložení do souboru
        file_put_contents($this->configFile, json_encode($odberatele, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        $this->redirect('default');
    }
    
    private function loadOdberatele(): array
    {
        if (file_exists($this->configFile)) {
            $odberatele = json_decode(file_get_contents($this->configFile), true);
            if (!is_array($odberatele)) {
                return [];
            }
            return $odberatele;
        }
        return [];
    }
}