<?php

namespace App\Model;

use Nette;

class ClientsManager
{
    use Nette\SmartObject;

    /** @var Nette\Database\Explorer */
    private $database;

    public function __construct(Nette\Database\Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Získá všechny klienty
     */
    public function getAll()
    {
        return $this->database->table('clients')->order('name ASC');
    }

    /**
     * Získá klienta podle ID
     */
    public function getById($id)
    {
        return $this->database->table('clients')->get($id);
    }

    /**
     * Přidá nebo aktualizuje klienta
     */
    public function save($data, $id = null)
    {
        // Konverze stdClass na pole
        if ($data instanceof \stdClass) {
            $data = (array) $data;
        }

        if ($id) {
            return $this->database->table('clients')->where('id', $id)->update($data);
        } else {
            return $this->database->table('clients')->insert($data);
        }
    }

    /**
     * Smaže klienta
     */
    public function delete($id)
    {
        return $this->database->table('clients')->where('id', $id)->delete();
    }
}
