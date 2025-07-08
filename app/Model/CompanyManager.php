<?php

namespace App\Model;

use Nette;

class CompanyManager
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
     * Získá firemní údaje (filtrované podle tenant_id)
     */
    public function getCompanyInfo()
    {
        $selection = $this->database->table('company_info');
        $filteredSelection = $this->applyTenantFilter($selection);
        return $filteredSelection->fetch();
    }

    /**
     * Aktualizuje firemní údaje (automaticky nastaví tenant_id)
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
            // EDITACE - aktualizujeme existující záznam (bez změny tenant_id)
            return $this->database->table('company_info')->where('id', $company->id)->update($data);
        } else {
            // NOVÁ SPOLEČNOST - automaticky nastavíme tenant_id
            if ($this->currentTenantId === null) {
                // Fallback pro výchozí tenant
                $data['tenant_id'] = 1;
            } else {
                $data['tenant_id'] = $this->currentTenantId;
            }

            return $this->database->table('company_info')->insert($data);
        }
    }

    // =====================================================
    // NOVÉ MULTI-TENANCY METODY
    // =====================================================

    /**
     * Získá údaje společnosti pro konkrétní tenant (pouze pro super admina)
     */
    public function getByTenant(int $tenantId)
    {
        if (!$this->isSuperAdmin) {
            throw new \Exception('Pouze super admin může získat údaje společnosti jiného tenanta.');
        }

        return $this->database->table('company_info')
            ->where('tenant_id', $tenantId)
            ->fetch();
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
        return $this->getCompanyInfo() !== null;
    }

    /**
     * Získá základní údaje o společnosti pro aktuální tenant
     */
    public function getBasicInfo(): array
    {
        $company = $this->getCompanyInfo();
        
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
        return $this->getCompanyInfo() !== null;
    }
}