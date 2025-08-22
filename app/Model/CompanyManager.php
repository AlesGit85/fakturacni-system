<?php

namespace App\Model;

use Nette;
use App\Security\EncryptionService;

class CompanyManager
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
    private const ENCRYPTED_FIELDS = ['ic', 'dic', 'email', 'phone', 'bank_account'];

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
     * Dešifruje jeden záznam společnosti
     */
    private function decryptCompanyRecord($company)
    {
        if (!$company) {
            return null;
        }

        // Převedeme na pole pro dešifrování
        $companyArray = $company->toArray();
        
        // Dešifrujeme citlivá pole
        $decryptedArray = $this->decryptSensitiveData($companyArray);
        
        // Vytvoříme nový objekt s dešifrovanými daty
        $decryptedCompany = (object) $decryptedArray;
        
        return $decryptedCompany;
    }

    // =====================================================
    // UPRAVENÉ PŮVODNÍ METODY S AUTOMATICKÝM ŠIFROVÁNÍM
    // =====================================================

    /**
     * Získá firemní údaje (filtrované podle tenant_id) s automatickým dešifrováním
     */
    public function getCompanyInfo()
    {
        $selection = $this->database->table('company_info');
        $filteredSelection = $this->applyTenantFilter($selection);
        $company = $filteredSelection->fetch();
        
        // 🔓 AUTOMATICKÉ DEŠIFROVÁNÍ při načítání
        return $this->decryptCompanyRecord($company);
    }

    /**
     * Aktualizuje firemní údaje (automaticky nastaví tenant_id) s automatickým šifrováním
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

        // 🔒 AUTOMATICKÉ ŠIFROVÁNÍ před uložením
        $encryptedData = $this->encryptSensitiveData($data);

        $company = $this->getCompanyInfoRaw(); // Použijeme raw verzi pro kontrolu existence
        if ($company) {
            // EDITACE - aktualizujeme existující záznam (bez změny tenant_id)
            $result = $this->database->table('company_info')->where('id', $company->id)->update($encryptedData);
            
            // Pro debug - zobrazíme, co se uložilo
            if ($this->encryptionService->isEncryptionEnabled()) {
                \Tracy\Debugger::log("🔒 KROK 3: Firemní údaje aktualizovány se šifrováním", \Tracy\ILogger::INFO);
                \Tracy\Debugger::log("Šifrovaná data: " . json_encode(array_intersect_key($encryptedData, array_flip(self::ENCRYPTED_FIELDS))), \Tracy\ILogger::INFO);
            }
            
            return $result;
        } else {
            // NOVÁ SPOLEČNOST - automaticky nastavíme tenant_id
            if ($this->currentTenantId === null) {
                // Fallback pro výchozí tenant
                $encryptedData['tenant_id'] = 1;
            } else {
                $encryptedData['tenant_id'] = $this->currentTenantId;
            }

            $result = $this->database->table('company_info')->insert($encryptedData);
            
            // Pro debug - zobrazíme, co se uložilo
            if ($this->encryptionService->isEncryptionEnabled()) {
                \Tracy\Debugger::log("🔒 KROK 3: Nové firemní údaje vytvořeny se šifrováním", \Tracy\ILogger::INFO);
                \Tracy\Debugger::log("Šifrovaná data: " . json_encode(array_intersect_key($encryptedData, array_flip(self::ENCRYPTED_FIELDS))), \Tracy\ILogger::INFO);
            }
            
            return $result;
        }
    }

    /**
     * Získá RAW firemní údaje (bez dešifrování) - pro interní použití
     */
    private function getCompanyInfoRaw()
    {
        $selection = $this->database->table('company_info');
        $filteredSelection = $this->applyTenantFilter($selection);
        return $filteredSelection->fetch();
    }

    // =====================================================
    // NOVÉ MULTI-TENANCY METODY S ŠIFROVÁNÍM
    // =====================================================

    /**
     * Získá údaje společnosti pro konkrétní tenant (pouze pro super admina) s automatickým dešifrováním
     */
    public function getByTenant(int $tenantId)
    {
        if (!$this->isSuperAdmin) {
            throw new \Exception('Pouze super admin může získat údaje společnosti jiného tenanta.');
        }

        $company = $this->database->table('company_info')
            ->where('tenant_id', $tenantId)
            ->fetch();

        // 🔓 AUTOMATICKÉ DEŠIFROVÁNÍ při načítání
        return $this->decryptCompanyRecord($company);
    }

    /**
     * Přesune společnost do jiného tenanta (pouze pro super admina)
     */
    public function moveToTenant(int $companyId, int $newTenantId): bool
    {
        if (!$this->isSuperAdmin) {
            throw new \Exception('Pouze super admin může přesouvat společnosti mezi tenancy.');
        }

        $result = $this->database->table('company_info')
            ->where('id', $companyId)
            ->update(['tenant_id' => $newTenantId]);

        return $result > 0;
    }

    /**
     * Zkontroluje, zda má tenant nastavenou společnost
     */
    public function hasCompanyInfo(): bool
    {
        return $this->getCompanyInfoRaw() !== null;
    }

    /**
     * Získá základní údaje o společnosti pro aktuální tenant s automatickým dešifrováním
     */
    public function getBasicInfo(): array
    {
        $company = $this->getCompanyInfo(); // Používáme dešifrovanou verzi
        
        if (!$company) {
            return [
                'name' => '',
                'ic' => '',
                'dic' => '',
                'vat_payer' => false,
                'configured' => false
            ];
        }

        return [
            'name' => $company->name ?? '',
            'ic' => $company->ic ?? '',
            'dic' => $company->dic ?? '',
            'vat_payer' => (bool)($company->vat_payer ?? false),
            'configured' => !empty($company->name) && !empty($company->ic)
        ];
    }

    // =====================================================
    // HELPER METODY PRO KOMPATIBILITU
    // =====================================================

    /**
     * Zkontroluje, zda společnost existuje a uživatel k ní má přístup
     */
    public function exists(): bool
    {
        return $this->getCompanyInfoRaw() !== null;
    }

    // =====================================================
    // NOVÉ METODY PRO TESTOVÁNÍ ŠIFROVÁNÍ
    // =====================================================

    /**
     * Testovací metoda pro ověření šifrování firemních údajů - POUZE PRO DEBUG!
     */
    public function testEncryption(): array
    {
        if (!$this->encryptionService->isEncryptionEnabled()) {
            return ['error' => 'Šifrování není zapnuto'];
        }

        $currentCompany = $this->getCompanyInfoRaw();
        
        if ($currentCompany) {
            // Test skutečných firemních dat
            $decryptedCompany = $this->getCompanyInfo();
            
            return [
                'company_exists' => true,
                'raw_data' => $currentCompany->toArray(),
                'decrypted_data' => (array) $decryptedCompany,
                'encrypted_fields' => self::ENCRYPTED_FIELDS
            ];
        } else {
            // Obecný test šifrování
            $testData = [
                'name' => 'Test Company s.r.o.',
                'ic' => '87654321',
                'dic' => 'CZ87654321',
                'email' => 'info@testcompany.cz',
                'phone' => '+420987654321',
                'bank_account' => '987654321/0100'
            ];
            
            $encrypted = $this->encryptSensitiveData($testData);
            $decrypted = $this->decryptSensitiveData($encrypted);
            
            return [
                'company_exists' => false,
                'original' => $testData,
                'encrypted' => $encrypted,
                'decrypted' => $decrypted,
                'test_ok' => ($testData === $decrypted)
            ];
        }
    }
}