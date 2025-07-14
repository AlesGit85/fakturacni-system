<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use App\Security\SecurityLogger;

/**
 * Správce tenantů pro multi-tenancy systém
 */
class TenantManager
{
    use Nette\SmartObject;

    /** @var Nette\Database\Explorer */
    private $database;

    /** @var SecurityLogger */
    private $securityLogger;

    /** @var Passwords */
    private $passwords;

    /** @var string */
    private $modulesDir;

    /** @var string */
    private $wwwModulesDir;

    public function __construct(
        Nette\Database\Explorer $database,
        SecurityLogger $securityLogger,
        Passwords $passwords
    ) {
        $this->database = $database;
        $this->securityLogger = $securityLogger;
        $this->passwords = $passwords;
        
        // Nastavení cest k modulům
        $this->modulesDir = dirname(__DIR__) . '/Modules';
        $this->wwwModulesDir = dirname(__DIR__, 2) . '/www/Modules';
    }

    /**
     * Vytvoří nového tenanta s kompletním setupem
     */
    public function createTenant(array $tenantData, array $adminData, array $companyData = []): array
    {
        try {
            $this->database->beginTransaction();

            // 1. Kontrola unikátnosti
            $existingTenant = $this->database->table('tenants')->where('name', $tenantData['name'])->fetch();
            if ($existingTenant) {
                throw new \Exception("Tenant s názvem '{$tenantData['name']}' již existuje.");
            }

            $existingUser = $this->database->table('users')->where('username', $adminData['username'])->fetch();
            if ($existingUser) {
                throw new \Exception("Uživatel s username '{$adminData['username']}' již existuje.");
            }

            $existingEmail = $this->database->table('users')->where('email', $adminData['email'])->fetch();
            if ($existingEmail) {
                throw new \Exception("Uživatel s emailem '{$adminData['email']}' již existuje.");
            }

            // 2. Vytvoření tenanta
            $tenant = $this->database->table('tenants')->insert([
                'name' => $tenantData['name'],
                'domain' => $tenantData['domain'] ?? null,
                'status' => 'active',
                'created_at' => new \DateTime(),
                'settings' => json_encode($tenantData['settings'] ?? [])
            ]);

            $tenantId = $tenant->id;

            // 3. Vytvoření admin uživatele pro tento tenant
            $hashedPassword = $this->passwords->hash($adminData['password']);
            
            $adminUser = $this->database->table('users')->insert([
                'username' => $adminData['username'],
                'email' => $adminData['email'],
                'password' => $hashedPassword,
                'first_name' => $adminData['first_name'],
                'last_name' => $adminData['last_name'],
                'role' => 'admin',
                'tenant_id' => $tenantId,
                'is_super_admin' => 0,
                'created_at' => new \DateTime()
            ]);

            // 4. Vytvoření firemních údajů
            $this->database->table('company_info')->insert([
                'name' => $companyData['company_name'] ?? $tenantData['name'],
                'ic' => $companyData['ic'] ?? '',
                'dic' => $companyData['dic'] ?? '',
                'email' => $adminData['email'],
                'phone' => $companyData['phone'] ?? '',
                'address' => $companyData['address'] ?? '',
                'city' => $companyData['city'] ?? '',
                'zip' => $companyData['zip'] ?? '',
                'country' => $companyData['country'] ?? 'Česká republika',
                'vat_payer' => $companyData['vat_payer'] ?? false,
                'tenant_id' => $tenantId
            ]);

            // 5. Vytvoření adresářové struktury pro moduly
            $this->createTenantDirectories($tenantId);

            // 6. Zkopírování základních modulů
            $this->setupDefaultModules($tenantId, $adminUser->id);

            $this->database->commit();

            // 7. Logování vytvoření tenanta
            $this->securityLogger->logSecurityEvent(
                'tenant_creation',
                "Tenant '{$tenantData['name']}' (ID: $tenantId) byl vytvořen s admin uživatelem '{$adminData['username']}' (ID: {$adminUser->id})"
            );

            return [
                'success' => true,
                'tenant_id' => $tenantId,
                'admin_user_id' => $adminUser->id,
                'message' => "Tenant '{$tenantData['name']}' byl úspěšně vytvořen"
            ];

        } catch (\Exception $e) {
            $this->database->rollback();
            
            $this->securityLogger->logSecurityEvent(
                'tenant_creation_failed',
                "Chyba při vytváření tenanta: " . $e->getMessage()
            );

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Vytvoří adresářovou strukturu pro nový tenant
     */
    private function createTenantDirectories(int $tenantId): void
    {
        $directories = [
            $this->modulesDir . '/tenant_' . $tenantId,
            $this->wwwModulesDir . '/tenant_' . $tenantId
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Nastaví základní moduly pro nový tenant
     */
    private function setupDefaultModules(int $tenantId, int $adminUserId): void
    {
        // Zkopírujeme základní moduly z tenant_1 (pokud existují)
        $defaultModulesDir = $this->modulesDir . '/tenant_1';
        $newTenantDir = $this->modulesDir . '/tenant_' . $tenantId;
        $defaultWwwDir = $this->wwwModulesDir . '/tenant_1';
        $newWwwDir = $this->wwwModulesDir . '/tenant_' . $tenantId;

        if (is_dir($defaultModulesDir)) {
            $this->copyDirectory($defaultModulesDir, $newTenantDir);
        }

        if (is_dir($defaultWwwDir)) {
            $this->copyDirectory($defaultWwwDir, $newWwwDir);
        }

        // Registrujeme moduly v databázi
        $this->registerModulesFromFilesystem($tenantId, $adminUserId);
    }

    /**
     * Registruje moduly z filesystému do databáze
     */
    private function registerModulesFromFilesystem(int $tenantId, int $userId): void
    {
        $tenantModulesDir = $this->modulesDir . '/tenant_' . $tenantId;
        
        if (!is_dir($tenantModulesDir)) {
            return;
        }

        $moduleDirectories = array_diff(scandir($tenantModulesDir), ['.', '..']);
        
        foreach ($moduleDirectories as $moduleDir) {
            $moduleInfoFile = $tenantModulesDir . '/' . $moduleDir . '/module.json';
            
            if (file_exists($moduleInfoFile)) {
                $moduleInfo = json_decode(file_get_contents($moduleInfoFile), true);
                
                if ($moduleInfo && isset($moduleInfo['id'])) {
                    // Zkontrolujeme, zda už záznam existuje
                    $existingModule = $this->database->table('user_modules')
                        ->where('user_id', $userId)
                        ->where('module_id', $moduleInfo['id'])
                        ->fetch();
                    
                    if (!$existingModule) {
                        $this->database->table('user_modules')->insert([
                            'user_id' => $userId,
                            'tenant_id' => $tenantId,
                            'module_id' => $moduleInfo['id'],
                            'module_name' => $moduleInfo['name'] ?? $moduleInfo['id'],
                            'module_version' => $moduleInfo['version'] ?? '1.0.0',
                            'module_path' => 'tenant_' . $tenantId . '/' . $moduleInfo['id'],
                            'is_active' => true,
                            'installed_at' => new \DateTime(),
                            'installed_by' => $userId
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Pomocná metoda pro kopírování adresářů
     */
    private function copyDirectory(string $source, string $destination): void
    {
        if (!is_dir($source)) return;

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $files = scandir($source);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $sourcePath = $source . '/' . $file;
            $destPath = $destination . '/' . $file;

            if (is_dir($sourcePath)) {
                $this->copyDirectory($sourcePath, $destPath);
            } else {
                copy($sourcePath, $destPath);
            }
        }
    }

    /**
     * Získá všechny tenanty s detailními statistikami
     */
    public function getAllTenantsWithStats(): array
    {
        $tenants = [];

        foreach ($this->database->table('tenants')->order('name ASC') as $tenant) {
            // Základní statistiky
            $stats = [
                'users_count' => $this->database->table('users')->where('tenant_id', $tenant->id)->count(),
                'invoices_count' => $this->database->table('invoices')->where('tenant_id', $tenant->id)->count(),
                'clients_count' => $this->database->table('clients')->where('tenant_id', $tenant->id)->count(),
                'modules_count' => $this->database->table('user_modules')->where('tenant_id', $tenant->id)->count(),
                'total_revenue' => $this->database->table('invoices')
                    ->where('tenant_id', $tenant->id)
                    ->where('status', 'paid')
                    ->sum('total') ?: 0,
                'unpaid_amount' => $this->database->table('invoices')
                    ->where('tenant_id', $tenant->id)
                    ->where('status', ['created', 'overdue'])
                    ->sum('total') ?: 0
            ];

            // Informace o admin uživateli (hlavní kontakt)
            $adminUser = $this->database->table('users')
                ->where('tenant_id', $tenant->id)
                ->where('role', 'admin')
                ->order('created_at ASC') // První vytvořený admin
                ->fetch();

            // Informace o společnosti
            $company = $this->database->table('company_info')
                ->where('tenant_id', $tenant->id)
                ->fetch();

            // Poslední aktivita
            $lastActivity = $this->database->table('users')
                ->where('tenant_id', $tenant->id)
                ->where('last_login IS NOT NULL')
                ->order('last_login DESC')
                ->fetch();

            $tenants[] = [
                'tenant' => $tenant->toArray(),
                'stats' => $stats,
                'admin_user' => $adminUser ? $adminUser->toArray() : null,
                'company' => $company ? $company->toArray() : null,
                'last_activity' => $lastActivity ? $lastActivity->last_login : null
            ];
        }

        return $tenants;
    }

    /**
     * Získá statistiky pro dashboard
     */
    public function getDashboardStats(): array
    {
        $totalRevenue = $this->database->table('invoices')
            ->where('status', 'paid')
            ->sum('total') ?: 0;

        $totalUnpaid = $this->database->table('invoices')
            ->where('status', ['created', 'overdue'])
            ->sum('total') ?: 0;

        $activeUsersLast30Days = $this->database->table('users')
            ->where('last_login > ?', new \DateTime('-30 days'))
            ->where('is_super_admin', 0)
            ->count();

        return [
            'total_tenants' => $this->database->table('tenants')->count(),
            'active_tenants' => $this->database->table('tenants')->where('status', 'active')->count(),
            'total_users' => $this->database->table('users')->where('is_super_admin', 0)->count(),
            'total_invoices' => $this->database->table('invoices')->count(),
            'total_revenue' => $totalRevenue,
            'total_unpaid' => $totalUnpaid,
            'active_users_30d' => $activeUsersLast30Days,
            'monthly_growth' => $this->getMonthlyGrowth()
        ];
    }

    /**
     * Spočítá měsíční růst tenantů
     */
    private function getMonthlyGrowth(): float
    {
        $currentMonth = $this->database->table('tenants')
            ->where('MONTH(created_at) = ? AND YEAR(created_at) = ?', date('m'), date('Y'))
            ->count();

        $lastMonth = $this->database->table('tenants')
            ->where('MONTH(created_at) = ? AND YEAR(created_at) = ?', 
                date('m', strtotime('-1 month')), 
                date('Y', strtotime('-1 month'))
            )
            ->count();

        if ($lastMonth == 0) return $currentMonth > 0 ? 100 : 0;
        
        return (($currentMonth - $lastMonth) / $lastMonth) * 100;
    }

    /**
     * Deaktivuje tenant
     */
    public function deactivateTenant(int $tenantId, int $superAdminId, string $reason = ''): bool
    {
        try {
            $tenant = $this->database->table('tenants')->get($tenantId);
            if (!$tenant) {
                return false;
            }

            $this->database->table('tenants')
                ->where('id', $tenantId)
                ->update(['status' => 'inactive']);

            $this->securityLogger->logSecurityEvent(
                'tenant_deactivation',
                "Tenant '{$tenant->name}' (ID: $tenantId) byl deaktivován super adminem (ID: $superAdminId). Důvod: $reason"
            );

            return true;

        } catch (\Exception $e) {
            $this->securityLogger->logSecurityEvent(
                'tenant_deactivation_failed',
                "Chyba při deaktivaci tenanta ID $tenantId: " . $e->getMessage()
            );
            return false;
        }
    }

    /**
     * Aktivuje tenant
     */
    public function activateTenant(int $tenantId, int $superAdminId): bool
    {
        try {
            $tenant = $this->database->table('tenants')->get($tenantId);
            if (!$tenant) {
                return false;
            }

            $this->database->table('tenants')
                ->where('id', $tenantId)
                ->update(['status' => 'active']);

            $this->securityLogger->logSecurityEvent(
                'tenant_activation',
                "Tenant '{$tenant->name}' (ID: $tenantId) byl aktivován super adminem (ID: $superAdminId)"
            );

            return true;

        } catch (\Exception $e) {
            $this->securityLogger->logSecurityEvent(
                'tenant_activation_failed',
                "Chyba při aktivaci tenanta ID $tenantId: " . $e->getMessage()
            );
            return false;
        }
    }

    /**
     * Smaže tenant (NEBEZPEČNÉ - pouze pro super admina)
     */
    public function deleteTenant(int $tenantId, int $superAdminId, string $reason): bool
    {
        try {
            $tenant = $this->database->table('tenants')->get($tenantId);
            if (!$tenant) {
                return false;
            }

            // Kontrola, že tenant není tenant 1 (default)
            if ($tenantId === 1) {
                throw new \Exception('Nelze smazat výchozí tenant.');
            }

            $this->database->beginTransaction();

            // Smazání všech souvisejících dat
            $this->database->table('user_modules')->where('tenant_id', $tenantId)->delete();
            $this->database->table('users')->where('tenant_id', $tenantId)->delete();
            $this->database->table('invoice_items')
                ->where('invoice_id IN', $this->database->table('invoices')->where('tenant_id', $tenantId)->select('id'))
                ->delete();
            $this->database->table('invoices')->where('tenant_id', $tenantId)->delete();
            $this->database->table('clients')->where('tenant_id', $tenantId)->delete();
            $this->database->table('company_info')->where('tenant_id', $tenantId)->delete();
            $this->database->table('tenants')->where('id', $tenantId)->delete();

            // Smazání adresářů s moduly
            $this->removeTenantDirectories($tenantId);

            $this->database->commit();

            $this->securityLogger->logSecurityEvent(
                'tenant_deletion',
                "Tenant '{$tenant->name}' (ID: $tenantId) byl SMAZÁN super adminem (ID: $superAdminId). Důvod: $reason"
            );

            return true;

        } catch (\Exception $e) {
            $this->database->rollback();
            
            $this->securityLogger->logSecurityEvent(
                'tenant_deletion_failed',
                "Chyba při mazání tenanta ID $tenantId: " . $e->getMessage()
            );
            return false;
        }
    }

    /**
     * Smaže adresáře tenanta
     */
    private function removeTenantDirectories(int $tenantId): void
    {
        $directories = [
            $this->modulesDir . '/tenant_' . $tenantId,
            $this->wwwModulesDir . '/tenant_' . $tenantId
        ];

        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $this->removeDirectory($dir);
            }
        }
    }

    /**
     * Rekurzivně smaže adresář
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;

        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}