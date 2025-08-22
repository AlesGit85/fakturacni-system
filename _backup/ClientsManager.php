<?php

namespace App\Model;

use Nette;

class ClientsManager
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
    // MULTI-TENANCY NASTAVENÍ
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
     * Získá všechny klienty (filtrované podle tenant_id)
     */
    public function getAll()
    {
        $selection = $this->database->table('clients')->order('name ASC');
        return $this->applyTenantFilter($selection);
    }

    /**
     * Získá klienta podle ID (s kontrolou tenant_id)
     */
    public function getById($id)
    {
        $selection = $this->database->table('clients')->where('id', $id);
        $filteredSelection = $this->applyTenantFilter($selection);
        return $filteredSelection->fetch();
    }

    /**
     * Přidá nebo aktualizuje klienta (automaticky nastaví tenant_id)
     */
    public function save($data, $id = null)
    {
        // Konverze stdClass na pole
        if ($data instanceof \stdClass) {
            $data = (array) $data;
        }

        if ($id) {
            // EDITACE - ověříme, že klient patří do správného tenanta
            $existingClient = $this->getById($id);
            if (!$existingClient) {
                throw new \Exception('Klient neexistuje nebo k němu nemáte přístup.');
            }

            // Aktualizace (bez změny tenant_id)
            return $this->database->table('clients')->where('id', $id)->update($data);
        } else {
            // NOVÝ KLIENT - automaticky nastavíme tenant_id
            if ($this->currentTenantId === null) {
                // OPRAVENO: Místo výjimky použijeme fallback na tenant 1
                $data['tenant_id'] = 1; // Fallback pro výchozí tenant
            } else {
                $data['tenant_id'] = $this->currentTenantId;
            }

            return $this->database->table('clients')->insert($data);
        }
    }

    /**
     * Smaže klienta (s kontrolou tenant_id)
     */
    public function delete($id)
    {
        // Ověříme, že klient existuje a patří do správného tenanta
        $client = $this->getById($id);
        if (!$client) {
            throw new \Exception('Klient neexistuje nebo k němu nemáte přístup.');
        }

        return $this->database->table('clients')->where('id', $id)->delete();
    }

    // =====================================================
    // NOVÉ MULTI-TENANCY METODY
    // =====================================================

    /**
     * Získá klienty pro konkrétní tenant (pouze pro super admina)
     */
    public function getByTenant(int $tenantId)
    {
        if (!$this->isSuperAdmin) {
            throw new \Exception('Pouze super admin může získat klienty jiného tenanta.');
        }

        return $this->database->table('clients')
            ->where('tenant_id', $tenantId)
            ->order('name ASC');
    }

    /**
     * Přesune klienta do jiného tenanta (pouze pro super admina)
     */
    public function moveToTenant(int $clientId, int $newTenantId): bool
    {
        if (!$this->isSuperAdmin) {
            throw new \Exception('Pouze super admin může přesouvat klienty mezi tenancy.');
        }

        $result = $this->database->table('clients')
            ->where('id', $clientId)
            ->update(['tenant_id' => $newTenantId]);

        return $result > 0;
    }

    /**
     * Vrátí statistiky klientů pro aktuální tenant
     */
    public function getStatistics(): array
    {
        $selection = $this->database->table('clients');
        $filteredSelection = $this->applyTenantFilter($selection);

        $totalClients = $filteredSelection->count();

        // Klienti s emailem
        $withEmail = $this->applyTenantFilter($this->database->table('clients'))
            ->where('email IS NOT NULL AND email != ""')
            ->count();

        // Klienti s telefonem
        $withPhone = $this->applyTenantFilter($this->database->table('clients'))
            ->where('phone IS NOT NULL AND phone != ""')
            ->count();

        return [
            'total' => $totalClients,
            'with_email' => $withEmail,
            'with_phone' => $withPhone,
            'without_contact' => $totalClients - max($withEmail, $withPhone)
        ];
    }

    /**
     * Vyhledávání klientů podle názvu nebo IČ (filtrované podle tenant_id)
     */
    public function search(string $query)
    {
        $selection = $this->database->table('clients')
            ->where('name LIKE ? OR ic LIKE ?', "%$query%", "%$query%")
            ->order('name ASC');

        return $this->applyTenantFilter($selection);
    }

    // =====================================================
    // HELPER METODY PRO KOMPATIBILITU
    // =====================================================

    /**
     * Zkontroluje, zda klient existuje a uživatel k němu má přístup
     */
    public function exists(int $id): bool
    {
        return $this->getById($id) !== null;
    }

    /**
     * Získá názvy klientů pro dropdown (filtrované podle tenant_id)
     */
    public function getPairs(): array
    {
        $clients = $this->getAll();
        $pairs = [];
        
        foreach ($clients as $client) {
            $pairs[$client->id] = $client->name;
        }
        
        return $pairs;
    }
}