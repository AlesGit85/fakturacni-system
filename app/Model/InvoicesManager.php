<?php

namespace App\Model;

use Nette;

class InvoicesManager
{
    use Nette\SmartObject;

    /** @var Nette\Database\Explorer */
    private $database;

    /** @var int|null Current tenant ID pro filtrování */
    private $currentTenantId = null;

    /** @var bool Je uživatel super admin? */
    private $isSuperAdmin = false;

    public function __construct(Nette\Database\Explorer $database)
    {
        $this->database = $database;
    }

    // =====================================================
    // MULTI-TENANCY NASTAVENÍ (NOVÉ)
    // =====================================================

    /**
     * Nastaví current tenant ID pro filtrování dat
     * Volá se z BasePresenter nebo jiných služeb
     */
    public function setTenantContext(?int $tenantId, bool $isSuperAdmin = false): void
    {
        $this->currentTenantId = $tenantId;
        $this->isSuperAdmin = $isSuperAdmin;
    }

    /**
     * Aplikuje tenant filtr na databázový dotaz
     */
    private function applyTenantFilter(Nette\Database\Table\Selection $selection): Nette\Database\Table\Selection
    {
        // Super admin vidí všechna data
        if ($this->isSuperAdmin) {
            return $selection;
        }

        // Ostatní uživatelé vidí pouze data svého tenanta
        if ($this->currentTenantId !== null) {
            return $selection->where('tenant_id', $this->currentTenantId);
        }

        // Pokud nemá tenant_id, nevidí nic (fallback bezpečnost)
        return $selection->where('1 = 0');
    }

    // =====================================================
    // PŮVODNÍ METODY S MULTI-TENANCY ROZŠÍŘENÍM
    // =====================================================

    /**
     * Získá všechny faktury (filtrované podle tenant_id)
     */
    public function getAll($limit = null, $offset = null, $search = null)
    {
        $query = $this->database->table('invoices');
        
        // Aplikace tenant filtru
        $query = $this->applyTenantFilter($query);
        
        // Vyhledávání
        if (!empty($search)) {
            $query = $query->where('number LIKE ? OR client_name LIKE ? OR total LIKE ?', 
                "%$search%", "%$search%", "%$search%");
        }
        
        // Řazení podle čísla faktury sestupně (nejnovější první)
        $query = $query->order('number DESC');
        
        // Limit a offset
        if ($limit !== null) {
            $query = $query->limit($limit, $offset);
        }
        
        return $query;
    }

    /**
     * Získá fakturu podle ID (s kontrolou tenant_id)
     */
    public function getById($id)
    {
        $selection = $this->database->table('invoices')->where('id', $id);
        $filteredSelection = $this->applyTenantFilter($selection);
        return $filteredSelection->fetch();
    }

    /**
     * Získá položky faktury (s kontrolou, že faktura patří tenantu)
     */
    public function getInvoiceItems($invoiceId)
    {
        // Nejprve ověříme, že faktura patří do správného tenanta
        $invoice = $this->getById($invoiceId);
        if (!$invoice) {
            return []; // Faktura neexistuje nebo k ní nemáme přístup
        }

        return $this->database->table('invoice_items')
            ->where('invoice_id', $invoiceId)
            ->order('id ASC');
    }

    /**
     * Přidá nebo aktualizuje fakturu (automaticky nastaví tenant_id)
     */
    public function save($data, $id = null)
    {
        // Konverze stdClass na pole
        if ($data instanceof \stdClass) {
            $data = (array) $data;
        }

        if ($id) {
            // EDITACE - ověříme, že faktura patří do správného tenanta
            $existingInvoice = $this->getById($id);
            if (!$existingInvoice) {
                throw new \Exception('Faktura neexistuje nebo k ní nemáte přístup.');
            }

            // Aktualizace (bez změny tenant_id)
            return $this->database->table('invoices')->where('id', $id)->update($data);
        } else {
            // NOVÁ FAKTURA - automaticky nastavíme tenant_id
            if ($this->currentTenantId === null) {
                // Fallback pro výchozí tenant
                $data['tenant_id'] = 1;
            } else {
                $data['tenant_id'] = $this->currentTenantId;
            }

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
     * Smaže fakturu (s kontrolou tenant_id)
     */
    public function delete($id)
    {
        // Ověříme, že faktura existuje a patří do správného tenanta
        $invoice = $this->getById($id);
        if (!$invoice) {
            throw new \Exception('Faktura neexistuje nebo k ní nemáte přístup.');
        }

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
     * Smaže všechny položky faktury (při editaci)
     */
    public function deleteInvoiceItems($invoiceId)
    {
        // Ověříme, že faktura patří do správného tenanta
        $invoice = $this->getById($invoiceId);
        if (!$invoice) {
            throw new \Exception('Faktura neexistuje nebo k ní nemáte přístup.');
        }

        return $this->database->table('invoice_items')->where('invoice_id', $invoiceId)->delete();
    }

    /**
     * Vygeneruje nové číslo faktury ve formátu RRRRMM#### (filtrované podle tenant_id)
     */
    public function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = $year . $month;

        // Hledáme posledního fakturu v aktuálním tenantu
        $selection = $this->database->table('invoices')
            ->where('number LIKE ?', $prefix . '%')
            ->order('number DESC');

        $filteredSelection = $this->applyTenantFilter($selection);
        $lastInvoice = $filteredSelection->fetch();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Vrátí statistiky faktur pro aktuální tenant
     */
    public function getStatistics(): array
    {
        $selection = $this->database->table('invoices');
        $filteredSelection = $this->applyTenantFilter($selection);

        $totalInvoices = $filteredSelection->count();
        
        // Faktury podle statusu
        $unpaid = $this->applyTenantFilter($this->database->table('invoices'))
            ->where('status', 'created')
            ->count();
            
        $paid = $this->applyTenantFilter($this->database->table('invoices'))
            ->where('status', 'paid')
            ->count();
            
        $overdue = $this->applyTenantFilter($this->database->table('invoices'))
            ->where('status', 'overdue')
            ->count();

        // Celková suma
        $totalAmount = $this->applyTenantFilter($this->database->table('invoices'))
            ->sum('total') ?: 0;

        // Suma nezaplacených
        $unpaidAmount = $this->applyTenantFilter($this->database->table('invoices'))
            ->where('status', 'created')
            ->sum('total') ?: 0;

        return [
            'total' => $totalInvoices,
            'unpaid' => $unpaid,
            'paid' => $paid,
            'overdue' => $overdue,
            'total_amount' => $totalAmount,
            'unpaid_amount' => $unpaidAmount
        ];
    }

    /**
     * Označí fakturu jako zaplacenou
     */
    public function markAsPaid($id)
    {
        // Ověříme, že faktura patří do správného tenanta
        $invoice = $this->getById($id);
        if (!$invoice) {
            throw new \Exception('Faktura neexistuje nebo k ní nemáte přístup.');
        }

        return $this->database->table('invoices')
            ->where('id', $id)
            ->update([
                'status' => 'paid',
                'paid_date' => new \DateTime()
            ]);
    }

    /**
     * Označí fakturu jako nevytvořenou (zruší zaplacení)
     */
    public function markAsCreated($id)
    {
        // Ověříme, že faktura patří do správného tenanta
        $invoice = $this->getById($id);
        if (!$invoice) {
            throw new \Exception('Faktura neexistuje nebo k ní nemáte přístup.');
        }

        return $this->database->table('invoices')
            ->where('id', $id)
            ->update([
                'status' => 'created',
                'paid_date' => null
            ]);
    }

    /**
     * Kontrola faktur po splatnosti a automatické označení
     */
    public function checkOverdueDates()
    {
        $today = new \DateTime();
        
        $overdueInvoices = $this->applyTenantFilter($this->database->table('invoices'))
            ->where('status', 'created')
            ->where('due_date < ?', $today->format('Y-m-d'));

        foreach ($overdueInvoices as $invoice) {
            $this->database->table('invoices')
                ->where('id', $invoice->id)
                ->update(['status' => 'overdue']);
        }
    }

    /**
     * Aktualizuje celkovou částku faktury na základě položek
     */
    public function updateInvoiceTotal($invoiceId)
    {
        // Ověříme, že faktura patří do správného tenanta
        $invoice = $this->getById($invoiceId);
        if (!$invoice) {
            throw new \Exception('Faktura neexistuje nebo k ní nemáte přístup.');
        }

        $items = $this->database->table('invoice_items')
            ->where('invoice_id', $invoiceId);

        $total = 0;
        foreach ($items as $item) {
            $total += $item->total;
        }

        $this->database->table('invoices')
            ->where('id', $invoiceId)
            ->update(['total' => $total]);
    }

    // =====================================================
    // NOVÉ MULTI-TENANCY METODY
    // =====================================================

    /**
     * Získá faktury pro konkrétní tenant (pouze pro super admina)
     */
    public function getByTenant(int $tenantId)
    {
        if (!$this->isSuperAdmin) {
            throw new \Exception('Pouze super admin může získat faktury jiného tenanta.');
        }

        return $this->database->table('invoices')
            ->where('tenant_id', $tenantId)
            ->order('number DESC');
    }

    /**
     * Přesune fakturu do jiného tenanta (pouze pro super admina)
     */
    public function moveToTenant(int $invoiceId, int $newTenantId): bool
    {
        if (!$this->isSuperAdmin) {
            throw new \Exception('Pouze super admin může přesouvat faktury mezi tenancy.');
        }

        $result = $this->database->table('invoices')
            ->where('id', $invoiceId)
            ->update(['tenant_id' => $newTenantId]);

        return $result > 0;
    }

    /**
     * Vyhledávání faktur podle čísla nebo klienta (filtrované podle tenant_id)
     */
    public function search(string $query)
    {
        $selection = $this->database->table('invoices')
            ->where('number LIKE ? OR client_name LIKE ?', "%$query%", "%$query%")
            ->order('number DESC');

        return $this->applyTenantFilter($selection);
    }

    // =====================================================
    // HELPER METODY PRO KOMPATIBILITU
    // =====================================================

    /**
     * Zkontroluje, zda faktura existuje a uživatel k ní má přístup
     */
    public function exists(int $id): bool
    {
        return $this->getById($id) !== null;
    }

    /**
     * Získá počet klientových faktur (s kontrolou tenant přístupu)
     */
    public function getClientInvoiceCount(int $clientId): int
    {
        return $this->applyTenantFilter($this->database->table('invoices'))
            ->where('client_id', $clientId)
            ->count();
    }
}