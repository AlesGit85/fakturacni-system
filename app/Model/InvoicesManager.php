<?php

namespace App\Model;

use Nette;

class InvoicesManager
{
    use Nette\SmartObject;

    /** @var Nette\Database\Explorer */
    private $database;

    public function __construct(Nette\Database\Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Získá všechny faktury s informacemi o klientech
     */
    public function getAll()
    {
        return $this->database->table('invoices')
            ->order('id DESC');
    }

    // Pokud by výše uvedená metoda nefungovala, můžeš použít alternativní verzi:
    public function getAllAlternative()
    {
        $invoices = $this->database->table('invoices')->order('id DESC');
        // Data klientů lze pak v presenteru získat pomocí related()
        // Např.: $invoice->ref('client_id')
        return $invoices;
    }

    /**
     * Získá fakturu podle ID včetně položek
     */
    public function getById($id)
    {
        return $this->database->table('invoices')->get($id);
    }

    /**
     * Získá položky faktury
     */
    public function getInvoiceItems($invoiceId)
    {
        return $this->database->table('invoice_items')
            ->where('invoice_id', $invoiceId)
            ->order('id ASC');
    }

    /**
     * Přidá nebo aktualizuje fakturu
     */
    public function save($data, $id = null)
    {
        // Konverze stdClass na pole
        if ($data instanceof \stdClass) {
            $data = (array) $data;
        }

        if ($id) {
            return $this->database->table('invoices')->where('id', $id)->update($data);
        } else {
            return $this->database->table('invoices')->insert($data);
        }
    }

    /**
     * Přidá nebo aktualizuje položku faktury
     */
    public function saveItem($data, $id = null)
    {
        // Konverze stdClass na pole
        if ($data instanceof \stdClass) {
            $data = (array) $data;
        }

        // Ošetření prázdných číselných hodnot
        if (isset($data['price']) && $data['price'] === '') {
            $data['price'] = 0;
        }

        if (isset($data['quantity']) && $data['quantity'] === '') {
            $data['quantity'] = 1;
        }

        if (isset($data['vat']) && $data['vat'] === '') {
            $data['vat'] = 0;
        }

        if (isset($data['total']) && $data['total'] === '') {
            $data['total'] = 0;
        }

        if ($id) {
            return $this->database->table('invoice_items')->where('id', $id)->update($data);
        } else {
            return $this->database->table('invoice_items')->insert($data);
        }
    }

    /**
     * Smaže fakturu
     */
    public function delete($id)
    {
        // Nejprve smažeme položky faktury
        $this->database->table('invoice_items')->where('invoice_id', $id)->delete();
        // Poté smažeme fakturu
        return $this->database->table('invoices')->where('id', $id)->delete();
    }

    /**
     * Smaže položku faktury
     */
    public function deleteItem($id)
    {
        return $this->database->table('invoice_items')->where('id', $id)->delete();
    }

    /**
     * Vygeneruje nové číslo faktury ve formátu RRRRMM####
     */
    public function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = $year . $month;

        $lastInvoice = $this->database->table('invoices')
            ->where('number LIKE ?', "$prefix%")
            ->order('id DESC')
            ->limit(1)
            ->fetch();

        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->number, 6)); // Posun o znak kvůli měsíci navíc
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . sprintf('%04d', $newNumber);
    }

    /**
     * Aktualizuje celkovou částku faktury
     */
    public function updateInvoiceTotal($invoiceId)
    {
        $total = $this->database->table('invoice_items')
            ->where('invoice_id', $invoiceId)
            ->sum('total');

        return $this->database->table('invoices')
            ->where('id', $invoiceId)
            ->update(['total' => $total]);
    }

    /**
     * Smaže všechny položky faktury
     */
    public function deleteInvoiceItems($invoiceId)
    {
        return $this->database->table('invoice_items')->where('invoice_id', $invoiceId)->delete();
    }
}
