<?php

namespace App\Model;

use Nette;
use DateTime;

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
     * Získá všechny faktury, řazené podle čísla sestupně
     * 
     * @param string|null $search Hledaný výraz
     * @return Nette\Database\Table\Selection
     */
    public function getAll($search = null)
    {
        $query = $this->database->table('invoices');
        
        // Aplikujeme vyhledávání, pokud je zadáno
        if ($search) {
            $query->where('number LIKE ? OR client_name LIKE ? OR total LIKE ?', 
                "%$search%", "%$search%", "%$search%");
        }
        
        // Řazení podle čísla faktury sestupně (nejnovější první)
        return $query->order('number DESC');
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

    /**
     * Aktualizuje stav faktury
     * 
     * @param int $id ID faktury
     * @param string $status Nový stav ('created', 'paid', 'overdue')
     * @param string|null $paymentDate Datum zaplacení (pro stav 'paid')
     * @return bool
     */
    public function updateStatus($id, $status, $paymentDate = null)
    {
        $data = ['status' => $status];
        
        if ($status === 'paid' && $paymentDate) {
            $data['payment_date'] = $paymentDate;
        } elseif ($status !== 'paid') {
            // Při změně stavu na jiný než 'paid' vymažeme datum platby
            $data['payment_date'] = null;
        }
        
        return $this->database->table('invoices')
            ->where('id', $id)
            ->update($data);
    }

    /**
     * Kontroluje a aktualizuje stav faktur po splatnosti
     * 
     * @return int Počet aktualizovaných faktur
     */
    public function checkOverdueDates()
    {
        $today = new DateTime();
        
        // Najdeme faktury, které jsou po splatnosti a nejsou označeny jako 'overdue' nebo 'paid'
        $result = $this->database->table('invoices')
            ->where('due_date < ?', $today->format('Y-m-d'))
            ->where('status', 'created')
            ->update(['status' => 'overdue']);
            
        return $result;
    }
    
    /**
     * Získá statistiky faktur
     * 
     * @return array Statistiky faktur
     */
    public function getStatistics()
    {
        $total = $this->database->table('invoices')->count();
        $paid = $this->database->table('invoices')->where('status', 'paid')->count();
        $overdue = $this->database->table('invoices')->where('status', 'overdue')->count();
        $unpaidAmount = $this->database->table('invoices')
            ->where('status != ?', 'paid')
            ->sum('total') ?? 0;
        
        return [
            'totalCount' => $total,
            'paidCount' => $paid,
            'overdueCount' => $overdue,
            'unpaidAmount' => $unpaidAmount
        ];
    }
}