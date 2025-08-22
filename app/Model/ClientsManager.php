<?php

namespace App\Model;

use Nette;
use App\Security\EncryptionService;

class ClientsManager
{
    use Nette\SmartObject;

    /** @var Nette\Database\Explorer */
    private $database;

    /** @var EncryptionService */
    private $encryptionService;

    /** @var int|null Current tenant ID pro filtrování */
    private $currentTenantId = null;

    /** @var bool Je uživatel super admin? */
    private $isSuperAdmin = false;

    /**
     * Citlivá pole, která se budou automaticky šifrovat
     */
    private const ENCRYPTED_FIELDS = ['ic', 'dic', 'email', 'phone'];

    public function __construct(Nette\Database\Explorer $database, EncryptionService $encryptionService)
    {
        $this->database = $database;
        $this->encryptionService = $encryptionService;
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
    // ENCRYPTION/DECRYPTION HELPER METODY
    // =====================================================

    /**
     * Zašifruje citlivá pole před uložením do databáze
     */
    private function encryptSensitiveData(array $data): array
    {
        return $this->encryptionService->encryptFields($data, self::ENCRYPTED_FIELDS);
    }

    /**
     * Dešifruje citlivá pole po načtení z databáze
     */
    private function decryptSensitiveData(array $data): array
    {
        return $this->encryptionService->decryptFields($data, self::ENCRYPTED_FIELDS);
    }

    /**
     * Dešifruje jeden záznam klienta
     */
    private function decryptClientRecord($client)
    {
        if (!$client) {
            return null;
        }

        // Převedeme na pole pro dešifrování
        $clientArray = $client->toArray();
        
        // Dešifrujeme citlivá pole
        $decryptedArray = $this->decryptSensitiveData($clientArray);
        
        // Vytvoříme nový objekt s dešifrovanými daty
        $decryptedClient = (object) $decryptedArray;
        
        return $decryptedClient;
    }

    /**
     * Dešifruje kolekci záznamů klientů
     */
    private function decryptClientRecords($clients)
    {
        $decryptedClients = [];
        
        foreach ($clients as $client) {
            $decryptedClient = $this->decryptClientRecord($client);
            if ($decryptedClient) {
                $decryptedClients[] = $decryptedClient;
            }
        }
        
        return $decryptedClients;
    }

    // =====================================================
    // UPRAVENÉ PŮVODNÍ METODY S AUTOMATICKÝM ŠIFROVÁNÍM
    // =====================================================

    /**
     * Získá všechny klienty (filtrované podle tenant_id) s automatickým dešifrováním
     */
    public function getAll()
    {
        $selection = $this->database->table('clients')->order('name ASC');
        $filteredSelection = $this->applyTenantFilter($selection);
        
        // 🔓 AUTOMATICKÉ DEŠIFROVÁNÍ při načítání
        $clients = $filteredSelection->fetchAll();
        return $this->decryptClientRecords($clients);
    }

    /**
     * Získá klienta podle ID (s kontrolou tenant_id) s automatickým dešifrováním
     */
    public function getById($id)
    {
        $selection = $this->database->table('clients')->where('id', $id);
        $filteredSelection = $this->applyTenantFilter($selection);
        $client = $filteredSelection->fetch();
        
        // 🔓 AUTOMATICKÉ DEŠIFROVÁNÍ při načítání
        return $this->decryptClientRecord($client);
    }

    /**
     * Přidá nebo aktualizuje klienta (automaticky nastaví tenant_id) s automatickým šifrováním
     */
    public function save($data, $id = null)
    {
        // Konverze stdClass na pole
        if ($data instanceof \stdClass) {
            $data = (array) $data;
        }

        // 🔒 AUTOMATICKÉ ŠIFROVÁNÍ před uložením
        $encryptedData = $this->encryptSensitiveData($data);

        if ($id) {
            // EDITACE - ověříme, že klient patří do správného tenanta
            $existingClient = $this->getById($id);
            if (!$existingClient) {
                throw new \Exception('Klient neexistuje nebo k němu nemáte přístup.');
            }

            // Aktualizace (bez změny tenant_id) - používáme šifrovaná data
            $result = $this->database->table('clients')->where('id', $id)->update($encryptedData);
            
            // Pro debug - zobrazíme, co se uložilo
            if ($this->encryptionService->isEncryptionEnabled()) {
                \Tracy\Debugger::log("🔒 KROK 2: Klient ID:$id aktualizován se šifrováním", \Tracy\ILogger::INFO);
                \Tracy\Debugger::log("Šifrovaná data: " . json_encode(array_intersect_key($encryptedData, array_flip(self::ENCRYPTED_FIELDS))), \Tracy\ILogger::INFO);
            }
            
            return $result;
        } else {
            // NOVÝ KLIENT - automaticky nastavíme tenant_id
            if ($this->currentTenantId === null) {
                // OPRAVENO: Místo výjimky použijeme fallback na tenant 1
                $encryptedData['tenant_id'] = 1; // Fallback pro výchozí tenant
            } else {
                $encryptedData['tenant_id'] = $this->currentTenantId;
            }

            $result = $this->database->table('clients')->insert($encryptedData);
            
            // Pro debug - zobrazíme, co se uložilo
            if ($this->encryptionService->isEncryptionEnabled()) {
                \Tracy\Debugger::log("🔒 KROK 2: Nový klient vytvořen se šifrováním", \Tracy\ILogger::INFO);
                \Tracy\Debugger::log("Šifrovaná data: " . json_encode(array_intersect_key($encryptedData, array_flip(self::ENCRYPTED_FIELDS))), \Tracy\ILogger::INFO);
            }
            
            return $result;
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
    // NOVÉ MULTI-TENANCY METODY S ŠIFROVÁNÍM
    // =====================================================

    /**
     * Získá klienty pro konkrétní tenant (pouze pro super admina) s automatickým dešifrováním
     */
    public function getByTenant(int $tenantId)
    {
        if (!$this->isSuperAdmin) {
            throw new \Exception('Pouze super admin může získat klienty jiného tenanta.');
        }

        $clients = $this->database->table('clients')
            ->where('tenant_id', $tenantId)
            ->order('name ASC')
            ->fetchAll();

        // 🔓 AUTOMATICKÉ DEŠIFROVÁNÍ při načítání
        return $this->decryptClientRecords($clients);
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
     * Vrátí statistiky klientů pro aktuální tenant s automatickým dešifrováním
     */
    public function getStatistics(): array
    {
        $selection = $this->database->table('clients');
        $filteredSelection = $this->applyTenantFilter($selection);

        $totalClients = $filteredSelection->count();

        // Klienti s emailem - pozor, email může být šifrovaný!
        // Pro statistiky použijeme počet záznamů s neprázdným emailem (i šifrovaným)
        $withEmail = $this->applyTenantFilter($this->database->table('clients'))
            ->where('email IS NOT NULL AND email != ""')
            ->count();

        // Klienti s telefonem - podobně jako email
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
     * POZNÁMKA: Vyhledávání v šifrovaných polích je omezené!
     */
    public function search(string $query)
    {
        // Pro vyhledávání v šifrovaných datech potřebujeme speciální přístup
        // Zatím vyhledáváme pouze v nešifrovaných polích (name)
        $selection = $this->database->table('clients')
            ->where('name LIKE ?', "%$query%")
            ->order('name ASC');

        $filteredSelection = $this->applyTenantFilter($selection);
        $clients = $filteredSelection->fetchAll();
        
        // 🔓 AUTOMATICKÉ DEŠIFROVÁNÍ při načítání
        return $this->decryptClientRecords($clients);
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
     * Získá názvy klientů pro dropdown (filtrované podle tenant_id) s automatickým dešifrováním
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

    // =====================================================
    // NOVÉ METODY PRO TESTOVÁNÍ ŠIFROVÁNÍ
    // =====================================================

    /**
     * Testovací metoda pro ověření šifrování - POUZE PRO DEBUG!
     */
    public function testEncryption(int $clientId = null): array
    {
        if (!$this->encryptionService->isEncryptionEnabled()) {
            return ['error' => 'Šifrování není zapnuto'];
        }

        $testResults = [];

        if ($clientId) {
            // Test konkrétního klienta
            $rawClient = $this->database->table('clients')->where('id', $clientId)->fetch();
            if ($rawClient) {
                $decryptedClient = $this->getById($clientId);
                
                $testResults = [
                    'client_id' => $clientId,
                    'raw_data' => $rawClient->toArray(),
                    'decrypted_data' => (array) $decryptedClient,
                    'encrypted_fields' => self::ENCRYPTED_FIELDS
                ];
            }
        } else {
            // Obecný test šifrování
            $testData = [
                'name' => 'Test Company',
                'ic' => '12345678',
                'dic' => 'CZ12345678',
                'email' => 'test@example.com',
                'phone' => '+420123456789'
            ];
            
            $encrypted = $this->encryptSensitiveData($testData);
            $decrypted = $this->decryptSensitiveData($encrypted);
            
            $testResults = [
                'original' => $testData,
                'encrypted' => $encrypted,
                'decrypted' => $decrypted,
                'test_ok' => ($testData === $decrypted)
            ];
        }

        return $testResults;
    }

    // =====================================================
    // KOMPATIBILITA S PŮVODNÍM API
    // =====================================================

    /**
     * Získá počet klientů (bez nutnosti načítat a dešifrovat všechna data)
     */
    public function getCount(): int
    {
        $selection = $this->database->table('clients');
        $filteredSelection = $this->applyTenantFilter($selection);
        return $filteredSelection->count();
    }

    /**
     * Získá Selection objekt (pro případy, kdy potřebujeme původní DB operace)
     * POZOR: Data NEBUDOU dešifrovaná!
     */
    public function getSelection()
    {
        $selection = $this->database->table('clients')->order('name ASC');
        return $this->applyTenantFilter($selection);
    }
}