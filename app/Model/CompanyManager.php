<?php

namespace App\Model;

use Nette;

class CompanyManager
{
    use Nette\SmartObject;

    /** @var Nette\Database\Explorer */
    private $database;

    public function __construct(Nette\Database\Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Získá firemní údaje
     */
    public function getCompanyInfo()
    {
        return $this->database->table('company_info')->fetch();
    }

    /**
     * Aktualizuje firemní údaje
     */
    public function save($data)
    {
        // Konverze stdClass na pole
        if ($data instanceof \stdClass) {
            $data = (array) $data;
        }

        // Zajistíme, že null DIČ bude uloženo jako prázdný řetězec
        if (!isset($data['dic']) || $data['dic'] === null) {
            $data['dic'] = '';
        }

        $company = $this->getCompanyInfo();
        if ($company) {
            return $this->database->table('company_info')->where('id', $company->id)->update($data);
        } else {
            return $this->database->table('company_info')->insert($data);
        }
    }
}
