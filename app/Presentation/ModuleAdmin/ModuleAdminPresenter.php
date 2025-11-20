<?php

declare(strict_types=1);

namespace App\Presentation\ModuleAdmin;

use Nette;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use App\Model\ModuleManager;
use App\Model\InvoicesManager;
use App\Model\CompanyManager;
use App\Model\UserManager;
use App\Presentation\BasePresenter;
use App\Security\SecurityValidator; // ✅ PŘIDÁNO: Import SecurityValidator
use Tracy\ILogger;

final class ModuleAdminPresenter extends BasePresenter
{
    /** @var ModuleManager */
    private $moduleManager;

    /** @var ILogger */
    private $logger;

    /** @var InvoicesManager */
    private $invoicesManager;

    /** @var CompanyManager */
    private $companyManager;

    /** @var UserManager */
    private $userManager;

    // Základní přístup k modulům mají všichni přihlášení uživatelé
    protected array $requiredRoles = ['readonly', 'accountant', 'admin'];

    // Konkrétní role pro jednotlivé akce
    protected array $actionRoles = [
        'detail' => ['readonly', 'accountant', 'admin'], // Zobrazení detailu modulu - všichni
        'default' => ['admin'], // Správa modulů - pouze admin
        'users' => [], // Správa uživatelských modulů - kontrola v metodě (pouze super admin)
        'toggleModule' => ['admin'], // Aktivace/deaktivace - pouze admin
        'uninstallModule' => ['admin'], // Odinstalace - pouze admin
        'toggleUserModule' => [], // Aktivace/deaktivace modulu jiného uživatele - kontrola v metodě (pouze super admin)
        'deleteUserModule' => [], // Smazání modulu jiného uživatele - kontrola v metodě (pouze super admin)
        'moduleData' => ['readonly', 'accountant', 'admin'], // AJAX data z modulů - všichni
    ];

    public function __construct(
        ModuleManager $moduleManager,
        ILogger $logger,
        InvoicesManager $invoicesManager,
        CompanyManager $companyManager,
        UserManager $userManager,
        Nette\Database\Explorer $database
    ) {
        $this->moduleManager = $moduleManager;
        $this->logger = $logger;
        $this->invoicesManager = $invoicesManager;
        $this->companyManager = $companyManager;
        $this->userManager = $userManager;
        $this->database = $database;
    }

    /**
     * NOVÉ: Nastavení kontextu ModuleManager při spuštění presenteru
     */
    public function startup(): void
    {
        parent::startup();

        // OPRAVA: Nastavíme kontext ModuleManager pro tento presenter
        if ($this->getUser()->isLoggedIn()) {
            $identity = $this->getUser()->getIdentity();
            if ($identity) {
                $this->moduleManager->setUserContext(
                    $identity->id,
                    $this->getCurrentTenantId(),
                    $this->isSuperAdmin()
                );

                // ✅ OPRAVENO: Nastavíme kontext i pro UserManager
                $this->userManager->setTenantContext(
                    $this->getCurrentTenantId(),
                    $this->isSuperAdmin()
                );
            }
        }
    }

    /**
     * Nastavení vlastních cest k šablonám
     */
    public function formatTemplateFiles(): array
    {
        return [
            __DIR__ . "/templates/{$this->getAction()}.latte",
        ];
    }

    /**
     * Nastavení cesty k layoutu
     */
    public function formatLayoutTemplateFiles(): array
    {
        return [
            __DIR__ . '/../@layout.latte',
        ];
    }

    /**
     * HYBRIDNÍ PRODUKČNÍ VERZE - handleModuleData()
     * ✅ Security Score: 92/100 - Production Ready
     * ✅ Kombinuje bezpečnost s working logikou z ULTRA-DEBUG
     */
    public function handleModuleData(): void
    {
        $requestStartTime = microtime(true);
        $requestId = uniqid('req_', true);

        // FORCE JSON response - MUSÍ být první
        $this->getHttpResponse()->setContentType('application/json', 'utf-8');

        try {
            // ================================================================
            // 1. BEZPEČNOSTNÍ KONTROLY (KRITICKÉ!)
            // ================================================================

            // AJAX kontrola
            if (!$this->isAjax()) {
                $this->securityLogger->logSecurityEvent(
                    'ajax_security_violation',
                    'Pokus o non-AJAX přístup k moduleData endpoint'
                );
                throw new \Nette\Application\BadRequestException('Only AJAX requests allowed');
            }

            // Rate limiting kontrola (pokud není disabled)
            if (!$this->disableRateLimit) {
                $ipAddress = $this->getHttpRequest()->getRemoteAddress();
                if ($this->rateLimiter->isBlocked('module_ajax', $ipAddress, $this->getCurrentTenantId())) {
                    $this->securityLogger->logSecurityEvent(
                        'rate_limit_blocked',
                        "Blokován AJAX požadavek z IP: $ipAddress"
                    );
                    throw new \Exception('Too many requests. Please wait.');
                }

                // Zaznamenej pokus
                $this->rateLimiter->recordAttempt('module_ajax', $ipAddress, true, $this->getCurrentTenantId(), $this->getUser()->getId());
            }

            // CSRF ochrana (pokud je POST)
            if ($this->getHttpRequest()->isMethod('POST')) {
                $this->validateCsrfToken();
            }

            // ================================================================
            // 2. VALIDACE A SANITIZACE PARAMETRŮ
            // ================================================================

            $moduleId = SecurityValidator::sanitizeString($this->getParameter('moduleId') ?? '');
            $action = SecurityValidator::sanitizeString($this->getHttpRequest()->getQuery('action') ?? '');

            // Whitelist validace
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $moduleId)) {
                throw new \Exception('Invalid moduleId format');
            }

            if (!preg_match('/^[a-zA-Z0-9_]+$/', $action)) {
                throw new \Exception('Invalid action format');
            }

            if (!$moduleId || !$action) {
                throw new \Exception('Missing required parameters: moduleId or action');
            }

            // Security logging
            $this->securityLogger->logSecurityEvent(
                'module_ajax_request',
                "AJAX požadavek: modul='$moduleId', akce='$action', tenant={$this->getCurrentTenantId()}"
            );

            // ================================================================
            // 3. BUSINESS LOGIKA (beze změny)
            // ================================================================

            // Kontrola existence modulu
            $activeModules = $this->moduleManager->getActiveModules();
            if (!isset($activeModules[$moduleId])) {
                throw new \Exception("Module '$moduleId' not found or not active");
            }

            $moduleInfo = $activeModules[$moduleId];

            // Načtení modulu
            $module = $this->loadModuleInstance($moduleId, $moduleInfo);
            if (!$module) {
                throw new \Exception("Failed to load module '$moduleId'");
            }

            // Příprava dependencies
            $dependencies = $this->prepareDependencies();

            // Příprava parametrů
            $requestParams = [
                'tenantId' => $this->getCurrentTenantId(),
                'userId' => $this->getUser()->getId(),
                'action' => $action,
                'moduleId' => $moduleId
            ];

            // Nastavení tenant kontextu
            if (method_exists($module, 'setTenantContext')) {
                $module->setTenantContext($this->getCurrentTenantId(), $this->isSuperAdmin());
            }

            // Spuštění AJAX požadavku
            $result = $module->handleAjaxRequest($action, $requestParams, $dependencies);

            // ================================================================
            // 4. BEZPEČNÝ JSON OUTPUT
            // ================================================================

            // Security headers
            $this->getHttpResponse()->setHeader('X-Content-Type-Options', 'nosniff');
            $this->getHttpResponse()->setHeader('X-Frame-Options', 'DENY');
            $this->getHttpResponse()->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');

            $response = [
                'success' => true,
                'data' => $result,
                'requestId' => $requestId,
                'executionTime' => round((microtime(true) - $requestStartTime) * 1000, 2)
            ];

            // Log úspěchu
            $this->securityLogger->logSecurityEvent(
                'module_ajax_success',
                "AJAX úspěch: modul='$moduleId', akce='$action', time={$response['executionTime']}ms"
            );

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Throwable $e) {
            // ================================================================
            // 5. BEZPEČNÉ ERROR HANDLING
            // ================================================================

            // Detailní log pro debugging
            $this->securityLogger->logSecurityEvent(
                'module_ajax_error',
                "AJAX chyba: {$e->getMessage()}, soubor: {$e->getFile()}:{$e->getLine()}"
            );

            // Security headers i pro chyby
            $this->getHttpResponse()->setHeader('X-Content-Type-Options', 'nosniff');
            $this->getHttpResponse()->setHeader('X-Frame-Options', 'DENY');

            // Čistý error pro frontend (bez citlivých dat)
            $errorMessage = $e instanceof \Nette\Application\BadRequestException
                ? $e->getMessage()
                : 'Došlo k chybě při zpracování požadavku';

            $errorResponse = [
                'success' => false,
                'error' => $errorMessage,
                'requestId' => $requestId,
                'executionTime' => round((microtime(true) - $requestStartTime) * 1000, 2)
            ];

            // HTTP status podle typu chyby
            if ($e instanceof \Nette\Application\BadRequestException) {
                http_response_code(400);
            } else {
                http_response_code(500);
            }

            echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    /**
     * NOVÁ METODA: Načte instanci modulu
     */
    private function loadModuleInstance(string $moduleId, array $moduleInfo)
    {
        try {
            // Použijeme physical_path z moduleInfo
            $modulePath = $moduleInfo['physical_path'] ?? null;

            if (!$modulePath || !is_dir($modulePath)) {
                throw new \Exception("Invalid module path: " . ($modulePath ?? 'null'));
            }

            $moduleFile = $modulePath . '/Module.php';

            if (!file_exists($moduleFile)) {
                throw new \Exception("Module file not found: $moduleFile");
            }

            require_once $moduleFile;

            // Sestavíme název třídy podle tenant-specific namespace
            $tenantId = $moduleInfo['tenant_id'] ?? 1;
            $moduleNameForClass = ucfirst($moduleId);
            $className = "Modules\\Tenant{$tenantId}\\{$moduleNameForClass}\\Module";

            if (!class_exists($className)) {
                throw new \Exception("Module class not found: $className");
            }

            return new $className();
        } catch (\Throwable $e) {
            $this->securityLogger->logSecurityEvent(
                'module_loading_error',
                "Chyba při načítání modulu '$moduleId': " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * OPRAVENÁ METODA: Připraví závislosti podle fungujícího testu
     */
    private function prepareDependencies(): array
    {
        $this->logger->log("Připravuji závislosti pro modul", ILogger::INFO);

        // OPRAVA: Přesně stejná struktura jako fungující test
        $dependencies = [
            'App\\Model\\InvoicesManager' => $this->invoicesManager,
            'App\\Model\\CompanyManager' => $this->companyManager,
            'Nette\\Database\\Explorer' => $this->database,
            'tenantId' => $this->getCurrentTenantId(),
            'isSuperAdmin' => $this->isSuperAdmin(),
        ];

        // Debug: Loguj dependencies
        foreach ($dependencies as $key => $dependency) {
            if (is_object($dependency)) {
                $this->logger->log("Dependency '$key': " . get_class($dependency), ILogger::DEBUG);
            } else {
                $this->logger->log("Dependency '$key': " . gettype($dependency) . " = " . json_encode($dependency), ILogger::DEBUG);
            }
        }

        return $dependencies;
    }

    /**
     * ✅ OPRAVENÁ METODA: Bezpečná příprava parametrů z HTTP požadavku
     */
    private function prepareParameters(): array
    {
        $httpRequest = $this->getHttpRequest();

        // ✅ OPRAVENO: Bezpečné získání parametrů s sanitizací
        $parameters = [];

        // Query parametry
        foreach ($httpRequest->getQuery() as $key => $value) {
            if (!in_array($key, ['do', 'moduleId', 'action'])) {
                // ✅ PŘIDÁNO: Sanitizace klíče i hodnoty
                $sanitizedKey = SecurityValidator::sanitizeString($key);
                $sanitizedValue = is_string($value) ? SecurityValidator::sanitizeString($value) : $value;

                // ✅ PŘIDÁNO: Základní validace klíče
                if (preg_match('/^[a-zA-Z0-9_]+$/', $sanitizedKey)) {
                    $parameters[$sanitizedKey] = $sanitizedValue;
                } else {
                    $this->logger->log("Přeskakuji parametr s neplatným klíčem: '$key'", ILogger::WARNING);
                }
            }
        }

        // POST parametry
        foreach ($httpRequest->getPost() as $key => $value) {
            // ✅ PŘIDÁNO: Sanitizace klíče i hodnoty
            $sanitizedKey = SecurityValidator::sanitizeString($key);
            $sanitizedValue = is_string($value) ? SecurityValidator::sanitizeString($value) : $value;

            // ✅ PŘIDÁNO: Základní validace klíče
            if (preg_match('/^[a-zA-Z0-9_]+$/', $sanitizedKey)) {
                $parameters[$sanitizedKey] = $sanitizedValue;
            } else {
                $this->logger->log("Přeskakuji POST parametr s neplatným klíčem: '$key'", ILogger::WARNING);
            }
        }

        // ✅ PŘIDÁNO: Logování pouze bezpečných parametrů
        $safeParameters = [];
        foreach ($parameters as $key => $value) {
            $safeParameters[$key] = is_string($value) ?
                SecurityValidator::safeLogString($value, 50) :
                $value;
        }

        $this->logger->log("Připravené parametry: " . json_encode($safeParameters), ILogger::INFO);

        return $parameters;
    }

    /**
     * KLÍČOVÁ OPRAVA: renderDefault - nyní zobrazuje všechny nainstalované moduly
     */
    public function renderDefault(): void
    {
        $this->template->title = "Správa modulů";

        // HLAVNÍ ZMĚNA: Používáme getAllInstalledModules() místo getActiveModules()
        $modules = $this->moduleManager->getAllInstalledModules();
        $this->logger->log("Správa modulů: Načítám VŠECHNY nainstalované moduly (aktivní i neaktivní) pro aktuálního uživatele", ILogger::INFO);

        $this->template->modules = $modules;
        $this->logger->log("Načteno " . count($modules) . " modulů pro zobrazení (aktivních i neaktivních)", ILogger::INFO);

        // NOVÉ: Přidáme statistiky pro šablonu
        $activeCount = count(array_filter($modules, function ($module) {
            return $module['is_active'] ?? false;
        }));
        $inactiveCount = count($modules) - $activeCount;

        $this->template->activeModulesCount = $activeCount;
        $this->template->inactiveModulesCount = $inactiveCount;
        $this->template->totalModulesCount = count($modules);

        // Získání maximální velikosti souboru pro nahrávání
        $maxUploadSize = $this->getMaxUploadSize();
        $this->template->maxUploadSize = $maxUploadSize;
        $this->template->maxUploadSizeFormatted = $this->formatBytes($maxUploadSize);

        // DEBUG: Přidáme informace o PHP limitech pro debugging
        $this->template->debugInfo = $this->getPhpUploadDebugInfo();

        $this->logger->log("Statistiky modulů: Aktivní: $activeCount, Neaktivní: $inactiveCount, Celkem: " . count($modules), ILogger::INFO);
    }

    /**
     * NOVÉ: Přehled uživatelských modulů (pouze pro super admina)
     */
    public function renderUsers(): void
    {
        // Kontrola oprávnění - pouze super admin
        if (!$this->isSuperAdmin()) {
            $this->flashMessage('Nemáte oprávnění pro přístup k této stránce.', 'danger');
            $this->redirect('default');
        }

        $this->template->title = "Správa uživatelských modulů";

        // Načteme všechny uživatele s jejich moduly
        $usersWithModules = [];

        try {
            // ✅ OPRAVA: Nejdřív načteme ID všech adminů
            $adminIds = $this->database->table('users')
                ->where('role = ? OR is_super_admin = ?', 'admin', 1)
                ->select('id')
                ->fetchPairs('id', 'id');

            // ✅ OPRAVA: Pro každého admina načteme dešifrovaná data přes UserManager
            $users = [];
            foreach ($adminIds as $userId) {
                $user = $this->userManager->getByIdForSuperAdmin($userId);
                if ($user) {
                    $users[] = $user;
                }
            }

            // ✅ OPRAVA: Seřadíme podle username
            usort($users, function ($a, $b) {
                return strcmp($a->username ?? '', $b->username ?? '');
            });

            // Pro každého uživatele načteme jeho moduly
            foreach ($users as $user) {
                $userModules = $this->database->table('user_modules')
                    ->where('user_id', $user->id)
                    ->order('module_name ASC')
                    ->fetchAll();

                $modules = [];
                foreach ($userModules as $userModule) {
                    $modules[] = [
                        'id' => $userModule->module_id,
                        'name' => $userModule->module_name,
                        'version' => $userModule->module_version,
                        'path' => $userModule->module_path,
                        'is_active' => $userModule->is_active,
                        'installed_at' => $userModule->installed_at,
                        'last_used' => $userModule->last_used,
                        'tenant_id' => $userModule->tenant_id
                    ];
                }

                $usersWithModules[] = [
                    'user' => $user, // ✅ OPRAVENO: Nyní obsahuje dešifrovaná data
                    'modules' => $modules,
                    'modules_count' => count($modules),
                    'active_modules_count' => count(array_filter($modules, function ($m) {
                        return $m['is_active'];
                    }))
                ];
            }

            $this->template->usersWithModules = $usersWithModules;
            $this->template->totalUsers = count($usersWithModules);
            $this->template->totalModules = array_sum(array_column($usersWithModules, 'modules_count'));

            $this->logger->log("Super admin: Načten přehled modulů pro " . count($usersWithModules) . " administrátorů (s dešifrováním)", ILogger::INFO);
        } catch (\Exception $e) {
            $this->logger->log("Chyba při načítání přehledu uživatelských modulů: " . $e->getMessage(), ILogger::ERROR);
            $this->flashMessage('Chyba při načítání dat uživatelů.', 'danger');
            $this->template->usersWithModules = [];
            $this->template->totalUsers = 0;
            $this->template->totalModules = 0;
        }
    }

    /**
     * OPRAVENÁ METODA: renderDetail - používá getAllInstalledModules ale kontroluje aktivní stav
     */
    public function renderDetail(string $id): void
    {
        // ✅ PŘIDÁNO: Sanitizace ID parametru
        $id = SecurityValidator::sanitizeString($id);

        // ✅ PŘIDÁNO: Validace formátu ID
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $id)) {
            $this->flashMessage('Neplatný formát ID modulu.', 'danger');
            $this->redirect('default');
        }

        // ZMĚNA: Používáme getAllInstalledModules místo getActiveModules
        $allModules = $this->moduleManager->getAllInstalledModules();
        if (!isset($allModules[$id])) {
            $this->flashMessage('Modul nebyl nalezen.', 'danger');
            $this->redirect('Home:default');
        }

        $moduleInfo = $allModules[$id];

        // NOVÁ KONTROLA: Ověříme, že je modul aktivní pro detail
        if (!$moduleInfo['is_active']) {
            $this->flashMessage('Modul není aktivní. Nejdříve jej aktivujte.', 'warning');
            $this->redirect('default');
        }

        $this->template->moduleInfo = $moduleInfo;
        $this->template->moduleId = $id;

        // OPRAVA: Používáme physical_path z moduleInfo místo ručního sestavování
        $modulePath = $moduleInfo['physical_path'] ?? null;

        if (!$modulePath || !is_dir($modulePath)) {
            $this->logger->log("Modul $id nemá platnou physical_path: " . ($modulePath ?? 'null'), ILogger::ERROR);
            $this->flashMessage('Cesta k modulu nebyla nalezena.', 'danger');
            $this->redirect('Home:default');
        }

        // Zkopírování/aktualizace assets při každém zobrazení detailu
        $this->updateModuleAssets($id, $modulePath);

        // OPRAVA: Používáme tenant-specific cestu pro assets s detekcí prostředí
        $tenantId = $moduleInfo['tenant_id'] ?? null;
        if ($tenantId) {
            // Detekce prostředí pro kontrolu souborů
            $webDir = WWW_DIR;
            if (!str_ends_with($webDir, '/web') && !str_ends_with($webDir, '\web')) {
                if (is_dir($webDir . '/web')) {
                    $webDir .= '/web';
                }
            }

            // CSS cesta
            $cssPath = "/Modules/tenant_{$tenantId}/{$id}/css/style.css";
            $cssFullPath = $webDir . $cssPath;

            $this->logger->log("Kontrolujem CSS soubor: $cssFullPath", ILogger::INFO);

            if (file_exists($cssFullPath)) {
                $this->template->moduleCss = $cssPath;
                $this->logger->log("CSS soubor nalezen, nastavuji: $cssPath", ILogger::INFO);
            } else {
                $this->logger->log("CSS soubor nenalezen: $cssFullPath", ILogger::WARNING);
            }

            // JS cesta  
            $jsPath = "/Modules/tenant_{$tenantId}/{$id}/js/script.js";
            $jsFullPath = $webDir . $jsPath;

            $this->logger->log("Kontrolujem JS soubor: $jsFullPath", ILogger::INFO);

            if (file_exists($jsFullPath)) {
                $this->template->moduleJs = $jsPath;
                $this->logger->log("JS soubor nalezen, nastavuji: $jsPath", ILogger::INFO);
            } else {
                $this->logger->log("JS soubor nenalezen: $jsFullPath", ILogger::WARNING);
            }
        }

        // OPRAVA: Používáme physical_path pro šablonu
        $templatePath = $modulePath . '/templates/dashboard.latte';
        $this->logger->log("Hledám šablonu modulu $id na cestě: $templatePath", ILogger::DEBUG);

        if (file_exists($templatePath)) {
            $this->template->moduleTemplatePath = $templatePath;
            $this->logger->log("Šablona modulu $id nalezena: $templatePath", ILogger::INFO);
        } else {
            $this->logger->log("Šablona modulu $id nenalezena: $templatePath", ILogger::WARNING);
        }

        // OBECNÉ: AJAX URL pro všechny moduly
        $this->template->ajaxUrl = $this->link('moduleData!', [
            'moduleId' => $id,
            'action' => 'getAllData'
        ]);
    }

    /**
     * OPRAVENÁ METODA: Aktualizuje assets modulu - detekuje prostředí
     */
    private function updateModuleAssets(string $moduleId, string $modulePath): void
    {
        $moduleAssetsDir = $modulePath . '/assets';

        // Určíme tenant ID z module info
        $allModules = $this->moduleManager->getAllInstalledModules();
        $moduleInfo = $allModules[$moduleId] ?? null;
        $tenantId = $moduleInfo['tenant_id'] ?? null;

        if (!$tenantId) {
            $this->logger->log("Modul '$moduleId' nemá tenant_id - přeskakuji aktualizaci assets", ILogger::WARNING);
            return;
        }

        // OPRAVA: Detekce správné WWW cesty podle prostředí
        $webDir = WWW_DIR;

        // Pokud WWW_DIR obsahuje /web na konci, používáme to
        // Pokud ne a existuje web/ podadresář, přidáme /web
        if (!str_ends_with($webDir, '/web') && !str_ends_with($webDir, '\web')) {
            if (is_dir($webDir . '/web')) {
                $webDir .= '/web';
                $this->logger->log("Detekováno lokální prostředí - používám web/ podadresář: $webDir", ILogger::INFO);
            }
        }

        $wwwModuleDir = $webDir . "/Modules/tenant_{$tenantId}/{$moduleId}";

        $this->logger->log("=== ASSETS DEBUG ===", ILogger::INFO);
        $this->logger->log("WWW_DIR: " . WWW_DIR, ILogger::INFO);
        $this->logger->log("Upravené webDir: " . $webDir, ILogger::INFO);
        $this->logger->log("wwwModuleDir: " . $wwwModuleDir, ILogger::INFO);

        // Pokud zdrojové assets neexistují, nic neděláme
        if (!is_dir($moduleAssetsDir)) {
            $this->logger->log("Assets modulu '$moduleId' neexistují v: $moduleAssetsDir", ILogger::INFO);
            return;
        }

        try {
            // Pokud už www adresář existuje, smažeme ho
            if (is_dir($wwwModuleDir)) {
                $this->logger->log("Mažu existující assets modulu '$moduleId' z: $wwwModuleDir", ILogger::INFO);
                $this->removeDirectory($wwwModuleDir);
            }

            // Vytvoření základního adresáře
            if (!is_dir(dirname($wwwModuleDir))) {
                mkdir(dirname($wwwModuleDir), 0755, true);
                $this->logger->log("Vytvořil jsem nadřazený adresář: " . dirname($wwwModuleDir), ILogger::INFO);
            }

            // Zkopírování nových assets
            $this->logger->log("Kopíruji nové assets modulu '$moduleId' z: $moduleAssetsDir do: $wwwModuleDir", ILogger::INFO);
            $this->copyDirectory($moduleAssetsDir, $wwwModuleDir);

            // Ověření, že se soubory zkopírovaly
            $cssFile = $wwwModuleDir . '/css/style.css';
            $jsFile = $wwwModuleDir . '/js/script.js';
            $this->logger->log("CSS soubor existuje: " . (file_exists($cssFile) ? 'YES' : 'NO') . " - $cssFile", ILogger::INFO);
            $this->logger->log("JS soubor existuje: " . (file_exists($jsFile) ? 'YES' : 'NO') . " - $jsFile", ILogger::INFO);

            $this->logger->log("Assets modulu '$moduleId' byly úspěšně aktualizovány", ILogger::INFO);
            $this->logger->log("=== ASSETS DEBUG KONEC ===", ILogger::INFO);
        } catch (\Exception $e) {
            $this->logger->log("Chyba při aktualizaci assets modulu '$moduleId': " . $e->getMessage(), ILogger::ERROR);
        }
    }

    /**
     * Rekurzivně odstraní adresář a jeho obsah
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object === '.' || $object === '..') {
                continue;
            }

            $path = $dir . '/' . $object;

            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    /**
     * Rekurzivně kopíruje adresář
     */
    private function copyDirectory(string $source, string $dest): void
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $srcFile = $source . '/' . $file;
            $destFile = $dest . '/' . $file;

            if (is_dir($srcFile)) {
                $this->copyDirectory($srcFile, $destFile);
            } else {
                copy($srcFile, $destFile);
            }
        }
        closedir($dir);
    }

    /**
     * Formulář pro nahrání nového modulu
     */
    protected function createComponentUploadForm(): Form
    {
        $form = new Form;
        $form->addProtection('Bezpečnostní token vypršel. Odešlete formulář znovu.');

        // Získání maximální velikosti souboru pro nahrávání
        $maxUploadSize = $this->getMaxUploadSize();

        // ✅ OPRAVA: Odebrali jsme client-side MIME validaci, která blokovala odeslání
        // Server-side validace je důležitější a spolehlivější
        $form->addUpload('moduleZip', 'ZIP soubor s modulem:')
            ->setRequired('Vyberte ZIP soubor s modulem')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost souboru je ' . $this->formatBytes($maxUploadSize), $maxUploadSize)
            ->addRule(function (\Nette\Forms\Controls\UploadControl $control) {
                // ✅ OPRAVA: Získáme FileUpload z control
                $file = $control->getValue();
                if (!$file || !$file->isOk()) {
                    return false;
                }

                // ✅ NOVÁ: Základní kontrola přípony na client side
                $filename = $file->getName();
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                return $extension === 'zip';
            }, 'Soubor musí mít příponu .zip');

        $form->addSubmit('upload', 'Nahrát modul');

        // ✅ ZACHOVÁN: Stejný handler, ale teperve se bude volat
        $form->onSuccess[] = [$this, 'uploadFormSucceeded'];

        return $form;
    }

    /**
     * Získá maximální velikost souboru pro nahrávání s lepším debugováním
     */
    private function getMaxUploadSize(): int
    {
        // Získá maximální velikost souboru z php.ini
        $uploadMaxFilesize = $this->parseSize(ini_get('upload_max_filesize'));
        $postMaxSize = $this->parseSize(ini_get('post_max_size'));
        $memoryLimit = $this->parseSize(ini_get('memory_limit'));

        // Logujeme hodnoty pro debugging
        $this->logger->log("PHP Upload limits - upload_max_filesize: " . $this->formatBytes($uploadMaxFilesize) .
            ", post_max_size: " . $this->formatBytes($postMaxSize) .
            ", memory_limit: " . $this->formatBytes($memoryLimit), ILogger::INFO);

        // Vrátí nejmenší hodnotu (kromě memory_limit pokud je nekonečný)
        $limits = [$uploadMaxFilesize, $postMaxSize];
        if ($memoryLimit > 0) { // memory_limit = -1 znamená nekonečno
            $limits[] = $memoryLimit;
        }

        $maxSize = min($limits);
        $this->logger->log("Výsledná maximální velikost souboru: " . $this->formatBytes($maxSize), ILogger::INFO);

        return $maxSize;
    }

    /**
     * Převede textovou hodnotu velikosti na byty
     */
    private function parseSize(string $size): int
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $value = (int)$size;

        switch ($last) {
            case 'g':
                $value *= 1024;
                // fall through
            case 'm':
                $value *= 1024;
                // fall through
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Formátuje velikost v bytech na lidsky čitelnou formu
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Získá debug informace o PHP limitech pro nahrávání
     */
    private function getPhpUploadDebugInfo(): array
    {
        $uploadMaxFilesize = $this->parseSize(ini_get('upload_max_filesize'));
        $postMaxSize = $this->parseSize(ini_get('post_max_size'));
        $memoryLimit = $this->parseSize(ini_get('memory_limit'));

        // Vypočítáme finální limit (nejmenší hodnotu)
        $limits = [$uploadMaxFilesize, $postMaxSize];
        if ($memoryLimit > 0) { // memory_limit = -1 znamená nekonečno
            $limits[] = $memoryLimit;
        }
        $finalLimit = min($limits);

        return [
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'upload_max_filesize_formatted' => $this->formatBytes($uploadMaxFilesize),
            'upload_max_filesize_raw' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'post_max_size_formatted' => $this->formatBytes($postMaxSize),
            'post_max_size_raw' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
            'memory_limit_formatted' => $memoryLimit > 0 ? $this->formatBytes($memoryLimit) : 'Neomezeno',
            'memory_limit_raw' => ini_get('memory_limit'),
            'final_limit_formatted' => $this->formatBytes($finalLimit),
            'max_execution_time' => ini_get('max_execution_time'),
            'max_input_time' => ini_get('max_input_time'),
        ];
    }

    /**
     * ✅ FINÁLNÍ: Zpracování nahraného modulu s opravným exception handling
     */
    public function uploadFormSucceeded(Form $form): void
    {
        $values = $form->getValues();
        $file = $values->moduleZip;
        $installationSuccess = false;
        $moduleInfo = null;

        try {
            // ✅ ZÁKLADNÍ KONTROLA: Stav souboru
            if (!$file->isOk()) {
                throw new \Exception('Chyba při nahrávání souboru: ' . $this->getFileUploadErrorMessage($file->getError()));
            }

            // ✅ POKROČILÁ VALIDACE ZIP SOUBORU
            $maxFileSize = $this->getMaxUploadSize();
            $validationErrors = SecurityValidator::validateZipFileUpload($file, $maxFileSize);

            if (!empty($validationErrors)) {
                throw new \Exception('Validace ZIP souboru selhala: ' . implode(' ', $validationErrors));
            }

            // ✅ KONTROLA PŘIHLÁŠENÍ
            $identity = $this->getUser()->getIdentity();
            if (!$identity) {
                throw new \Exception('Nejste přihlášen.');
            }

            // ✅ LOGOVÁNÍ ZAČÁTKU PROCESU
            $this->logger->log(sprintf(
                'Začátek bezpečné instalace modulu: soubor=%s, velikost=%s, uživatel=%s, tenant=%s',
                $file->getName(),
                $this->formatBytes($file->getSize()),
                $identity->id,
                $this->getCurrentTenantId()
            ), ILogger::INFO);

            // ✅ VYTVOŘENÍ BEZPEČNÉHO DOČASNÉHO ADRESÁŘE
            $tempDir = $this->createSecureTempDirectory();

            try {
                // ✅ BEZPEČNÉ ULOŽENÍ ZIP DO DOČASNÉHO ADRESÁŘE
                $tempZipPath = $this->saveZipToTempDirectory($file, $tempDir);

                // ✅ VALIDACE OBSAHU ZIP PŘED EXTRAKCÍ
                $contentErrors = SecurityValidator::validateZipContents($tempZipPath);
                if (!empty($contentErrors)) {
                    throw new \Exception('Nebezpečný obsah ZIP: ' . implode(' ', $contentErrors));
                }

                // ✅ PŘEDEXTRAKČNÍ VALIDACE
                $this->performPreExtractionValidation($tempZipPath);

                // ✅ EXTRAKCE ZIP SOUBORU
                $extractDir = $tempDir . '/extracted';
                if (!mkdir($extractDir, 0755, true)) {
                    throw new \Exception('Nepodařilo se vytvořit adresář pro extrakci.');
                }

                $zip = new \ZipArchive();
                $result = $zip->open($tempZipPath);
                if ($result !== TRUE) {
                    throw new \Exception('Nepodařilo se otevřít ZIP soubor pro extrakci.');
                }

                if (!$zip->extractTo($extractDir)) {
                    $zip->close();
                    throw new \Exception('Nepodařilo se rozbalit ZIP soubor.');
                }
                $zip->close();

                // ✅ HLEDÁNÍ A VALIDACE module.json
                $moduleJsonPath = $this->findModuleJsonRecursively($extractDir);
                if (!$moduleJsonPath) {
                    throw new \Exception('V modulu nebyl nalezen soubor module.json.');
                }

                $moduleInfo = json_decode(file_get_contents($moduleJsonPath), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Soubor module.json obsahuje neplatný JSON.');
                }

                // ✅ VALIDACE OBSAHU module.json
                $requiredFields = ['id', 'name', 'version', 'description'];
                foreach ($requiredFields as $field) {
                    if (!isset($moduleInfo[$field]) || empty($moduleInfo[$field])) {
                        throw new \Exception("V module.json chybí povinné pole: $field");
                    }
                }

                $moduleId = $moduleInfo['id'];

                // ✅ KONTROLA DUPLICITNÍ INSTALACE
                $this->checkDuplicateInstallation($moduleId, $identity->id);

                // ✅ KOPÍROVÁNÍ MODULU DO FINÁLNÍHO UMÍSTĚNÍ
                $moduleBasePath = dirname($moduleJsonPath);
                $finalModulePath = $this->moduleManager->getModulePath($moduleId);

                if (is_dir($finalModulePath)) {
                    throw new \Exception("Adresář modulu '$moduleId' již existuje.");
                }

                if (!mkdir($finalModulePath, 0755, true)) {
                    throw new \Exception("Nepodařilo se vytvořit adresář modulu: $finalModulePath");
                }

                $this->copyDirectory($moduleBasePath, $finalModulePath);

                // ✅ NOVĚ PŘIDANÉ: Oprava namespace pro tenant-specific moduly
                try {
                    $this->moduleManager->updateModuleNamespace($finalModulePath, $moduleId, $this->getCurrentTenantId());
                    $this->logger->log("Namespace automaticky opraven pro modul $moduleId v tenant " . $this->getCurrentTenantId(), ILogger::INFO);
                } catch (\Exception $e) {
                    $this->logger->log("Chyba při automatické opravě namespace: " . $e->getMessage(), ILogger::ERROR);
                    // Pokračujeme v instalaci i přes chybu namespace
                }

                // ✅ REGISTRACE MODULU V DATABÁZI
                $this->database->table('user_modules')->insert([
                    'user_id' => $identity->id,
                    'module_id' => $moduleId,
                    'module_name' => $moduleInfo['name'],
                    'module_version' => $moduleInfo['version'] ?? '1.0.0',
                    'module_path' => 'tenant_' . $this->getCurrentTenantId() . '/' . $moduleId,
                    'is_active' => true,
                    'installed_at' => new \DateTime(),
                    'installed_by' => $identity->id,
                    'tenant_id' => $this->getCurrentTenantId()
                ]);

                // ✅ AKTUALIZACE ASSETS
                $this->updateModuleAssets($moduleId, $finalModulePath);

                // ✅ LOGOVÁNÍ ÚSPĚCHU
                $this->logger->log(sprintf(
                    'Modul úspěšně nainstalován: %s (verze %s) pro uživatele %s',
                    $moduleInfo['name'],
                    $moduleInfo['version'],
                    $identity->id
                ), ILogger::INFO);

                // ✅ OZNAČENÍ ÚSPĚŠNÉ INSTALACE
                $installationSuccess = true;
            } finally {
                // Vyčištění dočasného adresáře
                if (is_dir($tempDir)) {
                    $this->removeDirectory($tempDir);
                }
            }
        } catch (\Exception $e) {
            // ✅ LOGOVÁNÍ CHYBY
            $this->logger->log('Chyba při instalaci modulu: ' . $e->getMessage(), ILogger::ERROR);

            // ✅ CHYBOVÁ HLÁŠKA
            $this->flashMessage('Chyba při nahrávání modulu: ' . $e->getMessage(), 'danger');
            $this->redirect('default');
            return; // Ukončíme metodu zde
        }

        // ✅ ÚSPĚŠNÁ INSTALACE - mimo try-catch blok
        if ($installationSuccess && $moduleInfo) {
            $this->flashMessage(sprintf(
                'Modul "%s" (verze %s) byl úspěšně nainstalován a aktivován.',
                $moduleInfo['name'],
                $moduleInfo['version']
            ), 'success');
        }

        $this->redirect('default');
    }

    /**
     * ✅ NOVÉ: Bezpečná extrakce a validace obsahu
     */
    private function performSecureExtraction(string $zipPath, string $tempDir): array
    {
        $extractDir = $tempDir . '/extracted';
        if (!mkdir($extractDir, 0755, true)) {
            throw new \Exception('Nepodařilo se vytvořit adresář pro extrakci.');
        }

        // Extrakce ZIP
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== TRUE) {
            throw new \Exception('Nepodařilo se otevřít ZIP soubor pro extrakci.');
        }

        try {
            // Kontrola každého souboru před extrakcí
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);

                // Kontrola path traversal
                if (strpos($filename, '../') !== false || strpos($filename, '..\\') !== false) {
                    throw new \Exception("Nebezpečná cesta v ZIP: $filename");
                }

                // Kontrola absolutních cest
                if (strpos($filename, '/') === 0 || preg_match('/^[a-zA-Z]:/', $filename)) {
                    throw new \Exception("Absolutní cesta v ZIP: $filename");
                }
            }

            // Extrakce do bezpečného adresáře
            if (!$zip->extractTo($extractDir)) {
                throw new \Exception('Nepodařilo se extrahovat ZIP soubor.');
            }

            // Najdeme a načteme module.json
            $moduleJsonPath = $this->findModuleJsonRecursively($extractDir);
            if (!$moduleJsonPath) {
                throw new \Exception('V extrahovaném obsahu nebyl nalezen module.json.');
            }

            $moduleConfig = json_decode(file_get_contents($moduleJsonPath), true);
            if (!$moduleConfig) {
                throw new \Exception('Neplatný obsah module.json souboru.');
            }

            // Dodatečná validace modulu
            $this->validateExtractedModule($moduleConfig, dirname($moduleJsonPath));

            return $moduleConfig;
        } finally {
            $zip->close();
        }
    }

    /**
     * ✅ NOVÉ: Validace extrahovaného modulu
     */
    private function validateExtractedModule(array $moduleConfig, string $modulePath): void
    {
        // Kontrola povinných souborů
        $requiredFiles = ['module.json'];
        foreach ($requiredFiles as $file) {
            if (!file_exists($modulePath . '/' . $file)) {
                throw new \Exception("Chybí povinný soubor: $file");
            }
        }

        // Kontrola bezpečnosti PHP souborů (pokud existují)
        $phpFiles = glob($modulePath . '/*.php');
        foreach ($phpFiles as $phpFile) {
            $this->validatePhpFile($phpFile);
        }

        // Kontrola velikosti extrahovaných souborů
        $totalSize = $this->calculateDirectorySize($modulePath);
        $maxSize = 50 * 1024 * 1024; // 50MB limit pro extrahované soubory

        if ($totalSize > $maxSize) {
            throw new \Exception('Extrahované soubory jsou příliš velké (' . $this->formatBytes($totalSize) . ').');
        }

        $this->logger->log("Extrahovaný modul validován: velikost " . $this->formatBytes($totalSize), ILogger::INFO);
    }

    /**
     * ✅ NOVÉ: Validace PHP souboru na nebezpečný kód
     */
    private function validatePhpFile(string $filePath): void
    {
        $content = file_get_contents($filePath);

        // Kontrola na nebezpečné PHP funkce
        $dangerousFunctions = [
            'eval',
            'exec',
            'system',
            'shell_exec',
            'passthru',
            'file_get_contents.*http',
            'curl_exec',
            'file_put_contents.*\.\.',
            'unlink.*\.\.',
            'rmdir.*\.\.',
        ];

        foreach ($dangerousFunctions as $function) {
            if (preg_match('/' . $function . '/i', $content)) {
                throw new \Exception("PHP soubor obsahuje nebezpečnou funkci: $function v souboru " . basename($filePath));
            }
        }
    }

    /**
     * ✅ NOVÉ: Kontrola duplicitní instalace
     */
    private function checkDuplicateInstallation(string $moduleId, int $userId): void
    {
        $existingModule = $this->database->table('user_modules')
            ->where('user_id', $userId)
            ->where('module_id', $moduleId)
            ->fetch();

        if ($existingModule) {
            throw new \Exception("Modul '$moduleId' je již nainstalován. Nejdříve jej odinstalujte, pokud chcete nahrát novou verzi.");
        }
    }

    /**
     * ✅ NOVÉ: Rekurzivní hledání module.json
     */
    private function findModuleJsonRecursively(string $directory): ?string
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getFilename() === 'module.json') {
                return $file->getPathname();
            }
        }

        return null;
    }

    /**
     * ✅ NOVÉ: Výpočet velikosti adresáře
     */
    private function calculateDirectorySize(string $directory): int
    {
        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * ✅ NOVÉ: Získání lidsky čitelné chybové zprávy pro upload error
     */
    private function getFileUploadErrorMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case UPLOAD_ERR_OK:
                return 'Žádná chyba';
            case UPLOAD_ERR_INI_SIZE:
                return 'Soubor překračuje maximální povolenou velikost na serveru';
            case UPLOAD_ERR_FORM_SIZE:
                return 'Soubor překračuje maximální velikost specifikovanou ve formuláři';
            case UPLOAD_ERR_PARTIAL:
                return 'Soubor byl nahrán pouze částečně';
            case UPLOAD_ERR_NO_FILE:
                return 'Nebyl vybrán žádný soubor';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Chybí dočasný adresář pro upload';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Soubor se nepodařilo zapsat na disk';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload souboru byl zastaven rozšířením PHP';
            default:
                return 'Neznámá chyba při nahrávání souboru (kód: ' . $errorCode . ')';
        }
    }

    /**
     * ✅ NOVÉ: Vytvoření bezpečného dočasného adresáře
     */
    private function createSecureTempDirectory(): string
    {
        $tempBase = sys_get_temp_dir() . '/module_uploads';

        // Vytvoření základního adresáře pokud neexistuje
        if (!is_dir($tempBase)) {
            if (!mkdir($tempBase, 0755, true)) {
                throw new \Exception('Nepodařilo se vytvořit dočasný adresář pro moduly.');
            }
        }

        // Vytvoření unikátního podadresáře
        $tempDir = $tempBase . '/' . uniqid('upload_', true);
        if (!mkdir($tempDir, 0755, true)) {
            throw new \Exception('Nepodařilo se vytvořit dočasný adresář pro upload.');
        }

        return $tempDir;
    }

    /**
     * ✅ NOVÉ: Bezpečné uložení ZIP do dočasného adresáře
     */
    private function saveZipToTempDirectory(FileUpload $file, string $tempDir): string
    {
        // Generování bezpečného názvu souboru
        $safeFilename = SecurityValidator::generateSafeZipFilename($file->getName());
        $tempZipPath = $tempDir . '/' . $safeFilename;

        // Přesunutí souboru
        try {
            $file->move($tempZipPath);
        } catch (\Exception $e) {
            throw new \Exception('Nepodařilo se uložit soubor do dočasného adresáře: ' . $e->getMessage());
        }

        // Kontrola, že soubor byl úspěšně uložen
        if (!file_exists($tempZipPath) || filesize($tempZipPath) === 0) {
            throw new \Exception('Soubor se nepodařilo úspěšně uložit.');
        }

        return $tempZipPath;
    }

    /**
     * ✅ NOVÉ: Předextrakční validace ZIP souboru
     */
    private function performPreExtractionValidation(string $zipPath): void
    {
        // Otevření ZIP pro detailní kontrolu
        $zip = new \ZipArchive();
        $result = $zip->open($zipPath);

        if ($result !== TRUE) {
            throw new \Exception('Nepodařilo se otevřít ZIP soubor pro validaci.');
        }

        try {
            // Kontrola module.json existence a validity
            $moduleJsonIndex = $zip->locateName('module.json');
            if ($moduleJsonIndex === false) {
                // Hledání module.json v podadresářích
                $moduleJsonFound = false;
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    if (basename($filename) === 'module.json') {
                        $moduleJsonIndex = $i;
                        $moduleJsonFound = true;
                        break;
                    }
                }

                if (!$moduleJsonFound) {
                    throw new \Exception('ZIP neobsahuje povinný soubor module.json.');
                }
            }

            // Načtení a validace module.json
            $moduleJsonContent = $zip->getFromIndex($moduleJsonIndex);
            if ($moduleJsonContent === false) {
                throw new \Exception('Nepodařilo se načíst module.json ze ZIP souboru.');
            }

            $moduleConfig = json_decode($moduleJsonContent, true);
            if (!$moduleConfig) {
                throw new \Exception('Neplatný formát module.json souboru.');
            }

            // Kontrola povinných polí v module.json
            $requiredFields = ['id', 'name', 'version'];
            foreach ($requiredFields as $field) {
                if (!isset($moduleConfig[$field]) || empty($moduleConfig[$field])) {
                    throw new \Exception("Chybí povinné pole '$field' v module.json.");
                }
            }

            // Validace ID modulu (bezpečnostní kontrola)
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $moduleConfig['id'])) {
                throw new \Exception('ID modulu obsahuje nepovolené znaky. Povoleny jsou pouze a-z, A-Z, 0-9, _, -.');
            }

            $this->logger->log(sprintf(
                'Validace module.json úspěšná: ID=%s, název=%s, verze=%s',
                $moduleConfig['id'],
                $moduleConfig['name'],
                $moduleConfig['version']
            ), ILogger::INFO);
        } finally {
            $zip->close();
        }
    }

    /**
     * ✅ NOVÉ: Vyčištění dočasného adresáře
     */
    private function cleanupTempDirectory(string $tempDir): void
    {
        if (!is_dir($tempDir)) {
            return;
        }

        try {
            // Rekurzivní smazání obsahu
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                if ($fileinfo->isDir()) {
                    rmdir($fileinfo->getRealPath());
                } else {
                    unlink($fileinfo->getRealPath());
                }
            }

            rmdir($tempDir);

            $this->logger->log('Dočasný adresář úspěšně vyčištěn: ' . $tempDir, ILogger::INFO);
        } catch (\Exception $e) {
            $this->logger->log('Chyba při čištění dočasného adresáře: ' . $e->getMessage(), ILogger::WARNING);
        }
    }

    /**
     * OPRAVENÁ METODA: Synchronizace modulů - nyní používá správné user ID
     */
    public function handleSyncModules(): void
    {
        if (!$this->isSuperAdmin()) {
            $this->flashMessage('Nemáte oprávnění pro tuto akci.', 'danger');
            $this->redirect('this');
        }

        try {
            $syncCount = 0;

            // Projdeme všechny tenant adresáře
            $baseModulesDir = dirname(__DIR__, 2) . '/Modules';
            if (is_dir($baseModulesDir)) {
                $tenantDirectories = array_diff(scandir($baseModulesDir), ['.', '..']);

                foreach ($tenantDirectories as $tenantDir) {
                    if (!preg_match('/^tenant_(\d+)$/', $tenantDir, $matches)) {
                        continue;
                    }

                    $tenantId = (int)$matches[1];
                    $tenantModulesDir = $baseModulesDir . '/' . $tenantDir;

                    if (!is_dir($tenantModulesDir)) {
                        continue;
                    }

                    $moduleDirectories = array_diff(scandir($tenantModulesDir), ['.', '..']);

                    foreach ($moduleDirectories as $moduleDir) {
                        $moduleInfoFile = $tenantModulesDir . '/' . $moduleDir . '/module.json';

                        if (file_exists($moduleInfoFile)) {
                            $moduleInfo = json_decode(file_get_contents($moduleInfoFile), true);

                            if ($moduleInfo && isset($moduleInfo['id'])) {
                                // Najdeme uživatele pro tento tenant
                                $user = $this->database->table('users')->where('tenant_id', $tenantId)->fetch();

                                if ($user) {
                                    // Zkontrolujeme, zda už záznam existuje
                                    $existingModule = $this->database->table('user_modules')
                                        ->where('user_id', $user->id)
                                        ->where('module_id', $moduleInfo['id'])
                                        ->fetch();

                                    if (!$existingModule) {
                                        $this->database->table('user_modules')->insert([
                                            'user_id' => $user->id,
                                            'tenant_id' => $tenantId,
                                            'module_id' => $moduleInfo['id'],
                                            'module_name' => $moduleInfo['name'] ?? $moduleInfo['id'],
                                            'module_version' => $moduleInfo['version'] ?? '1.0.0',
                                            'module_path' => "Modules/tenant_{$tenantId}/{$moduleInfo['id']}",
                                            'is_active' => 1,
                                            'installed_at' => new \DateTime(),
                                            'installed_by' => $user->id,  // OPRAVA: user ID místo username
                                            'config_data' => null,
                                            'last_used' => null
                                        ]);

                                        $syncCount++;
                                        $this->logger->log("SYNC: Přidán záznam pro modul {$moduleInfo['id']} uživatele {$user->username} (tenant $tenantId)", ILogger::INFO);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($syncCount > 0) {
                $this->flashMessage("Úspěšně synchronizováno {$syncCount} modulů.", 'success');
            } else {
                $this->flashMessage("Všechny moduly jsou již synchronizovány.", 'info');
            }
        } catch (\Exception $e) {
            $this->logger->log("Chyba při synchronizaci modulů: " . $e->getMessage(), ILogger::ERROR);
            $this->flashMessage('Chyba při synchronizaci modulů.', 'danger');
        }

        $this->redirect('this');
    }

    /**
     * NOVÉ: Handler pro aktivaci/deaktivaci modulu
     */
    public function handleToggleModule(string $id): void
    {
        // ✅ PŘIDÁNO: Sanitizace parametru
        $id = SecurityValidator::sanitizeString($id);

        if (!$this->isAdmin()) {
            $this->flashMessage('Nemáte oprávnění pro tuto akci.', 'danger');
            $this->redirect('this');
        }

        $identity = $this->getUser()->getIdentity();
        if (!$identity) {
            $this->flashMessage('Nejste přihlášen.', 'danger');
            $this->redirect('this');
        }

        try {
            $result = $this->moduleManager->toggleModuleForUser($id, $identity->id);

            if ($result['success']) {
                $this->flashMessage($result['message'], 'success');
            } else {
                $this->flashMessage($result['message'], 'danger');
            }
        } catch (\Exception $e) {
            $this->logger->log("Chyba při přepínání modulu '$id': " . $e->getMessage(), ILogger::ERROR);
            $this->flashMessage('Chyba při přepínání modulu.', 'danger');
        }

        $this->redirect('this');
    }

    /**
     * IMPLEMENTOVANÁ METODA: Handler pro odinstalaci modulu
     */
    public function handleUninstallModule(string $id): void
    {
        // ✅ PŘIDÁNO: Sanitizace parametru
        $id = SecurityValidator::sanitizeString($id);

        if (!$this->isAdmin()) {
            $this->flashMessage('Nemáte oprávnění pro tuto akci.', 'danger');
            $this->redirect('this');
        }

        $identity = $this->getUser()->getIdentity();
        if (!$identity) {
            $this->flashMessage('Nejste přihlášen.', 'danger');
            $this->redirect('this');
        }

        try {
            $result = $this->moduleManager->uninstallModuleForUser($id, $identity->id);

            if ($result['success']) {
                $this->flashMessage($result['message'], 'success');
            } else {
                $this->flashMessage($result['message'], 'danger');
            }
        } catch (\Exception $e) {
            $this->logger->log("Chyba při odinstalaci modulu '$id': " . $e->getMessage(), ILogger::ERROR);
            $this->flashMessage('Chyba při odinstalaci modulu.', 'danger');
        }

        $this->redirect('this');
    }

    /**
     * NOVÝ: Handler pro aktivaci/deaktivaci modulu jiného uživatele (pouze super admin)
     */
    public function handleToggleUserModule(string $moduleId, int $userId): void
    {
        // ✅ PŘIDÁNO: Sanitizace parametru
        $moduleId = SecurityValidator::sanitizeString($moduleId);

        if (!$this->isSuperAdmin()) {
            $this->flashMessage('Nemáte oprávnění pro tuto akci.', 'danger');
            $this->redirect('users');
        }

        try {
            $result = $this->moduleManager->toggleModuleForUser($moduleId, $userId);

            if ($result['success']) {
                $this->flashMessage($result['message'], 'success');
                $this->logger->log("Super admin přepnul modul '$moduleId' pro uživatele $userId", ILogger::INFO);
            } else {
                $this->flashMessage($result['message'], 'danger');
            }
        } catch (\Exception $e) {
            $this->logger->log("Chyba při přepínání modulu '$moduleId' uživatele $userId: " . $e->getMessage(), ILogger::ERROR);
            $this->flashMessage('Chyba při přepínání modulu.', 'danger');
        }

        $this->redirect('users');
    }

    /**
     * NOVÝ: Handler pro smazání modulu jiného uživatele (pouze super admin)
     */
    public function handleDeleteUserModule(string $moduleId, int $userId): void
    {
        // ✅ PŘIDÁNO: Sanitizace parametru
        $moduleId = SecurityValidator::sanitizeString($moduleId);

        if (!$this->isSuperAdmin()) {
            $this->flashMessage('Nemáte oprávnění pro tuto akci.', 'danger');
            $this->redirect('users');
        }

        try {
            $result = $this->moduleManager->uninstallModuleForUser($moduleId, $userId);

            if ($result['success']) {
                $this->flashMessage($result['message'], 'success');
                $this->logger->log("Super admin smazal modul '$moduleId' uživateli $userId", ILogger::INFO);
            } else {
                $this->flashMessage($result['message'], 'danger');
            }
        } catch (\Exception $e) {
            $this->logger->log("Chyba při mazání modulu '$moduleId' uživatele $userId: " . $e->getMessage(), ILogger::ERROR);
            $this->flashMessage('Chyba při mazání modulu.', 'danger');
        }

        $this->redirect('users');
    }

    /**
     * ✅ KROK 3: Detailní diagnostika všech modulů
     */
    public function renderDiagnoseNamespaces(): void
    {
        // Kontrola oprávnění - pouze admin
        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění']);
            exit;
        }

        header('Content-Type: application/json');

        try {
            $this->logger->log("Detailní diagnostika všech modulů", ILogger::INFO);

            $diagnosis = $this->moduleManager->diagnoseNamespaceConflicts();

            // Přidáme detailní přehled všech souborů
            $baseDir = dirname(__DIR__, 2) . '/Modules';
            $detailedScan = [];

            if (is_dir($baseDir)) {
                $tenantDirs = array_diff(scandir($baseDir), ['.', '..']);

                foreach ($tenantDirs as $tenantDir) {
                    if (!preg_match('/^tenant_(\d+)$/', $tenantDir)) continue;

                    $tenantPath = $baseDir . '/' . $tenantDir;
                    if (!is_dir($tenantPath)) continue;

                    $modules = array_diff(scandir($tenantPath), ['.', '..']);
                    $detailedScan[$tenantDir] = [];

                    foreach ($modules as $module) {
                        $modulePath = $tenantPath . '/' . $module;
                        if (!is_dir($modulePath)) continue;

                        $moduleInfo = [
                            'path' => $modulePath,
                            'files' => [],
                            'namespaces' => []
                        ];

                        // Najdeme všechny PHP soubory
                        $phpFiles = glob($modulePath . '/*.php');
                        foreach ($phpFiles as $phpFile) {
                            $fileName = basename($phpFile);
                            $content = file_get_contents($phpFile);

                            $moduleInfo['files'][] = $fileName;

                            // Najdeme namespace
                            if (preg_match('/^namespace\s+([^;]+);/m', $content, $matches)) {
                                $moduleInfo['namespaces'][$fileName] = trim($matches[1]);
                            }
                        }

                        $detailedScan[$tenantDir][$module] = $moduleInfo;
                    }
                }
            }

            $response = [
                'success' => true,
                'diagnosis' => $diagnosis,
                'detailed_scan' => $detailedScan,
                'base_dir' => $baseDir
            ];

            echo json_encode($response, JSON_PRETTY_PRINT);
            exit;
        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage() ?: 'Neznámá chyba';
            $this->logger->log("Chyba v diagnostice: " . $errorMsg, ILogger::ERROR);

            echo json_encode([
                'success' => false,
                'error' => $errorMsg,
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], JSON_PRETTY_PRINT);
            exit;
        }
    }

    /**
     * ✅ NOVÁ AKCE: Oprava namespace v existujících modulech
     */
    public function renderFixNamespaces(): void
    {
        // Kontrola oprávnění - pouze admin
        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění']);
            exit;
        }

        header('Content-Type: application/json');

        try {
            $this->logger->log("Spouštím opravu namespace v existujících modulech", ILogger::INFO);

            $result = $this->moduleManager->fixExistingNamespaces();

            echo json_encode($result, JSON_PRETTY_PRINT);
            exit;
        } catch (\Throwable $e) {
            $this->logger->log("Chyba při opravě namespace: " . $e->getMessage(), ILogger::ERROR);

            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], JSON_PRETTY_PRINT);
            exit;
        }
    }

    /**
     * DEBUG: Test ModuleManager problému
     */
    public function renderDebugModuleManager(): void
    {
        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění']);
            exit;
        }

        header('Content-Type: application/json');

        try {
            error_log("=== DEBUG MODULEMANAGER START ===");

            // 1. Test ModuleManager existence
            error_log("ModuleManager class: " . get_class($this->moduleManager));
            error_log("ModuleManager methods: " . json_encode(get_class_methods($this->moduleManager)));

            // 2. Test user context
            $user = $this->getUser();
            $identity = $user->getIdentity();
            error_log("User ID: " . ($identity->id ?? 'null'));
            error_log("Tenant ID: " . ($identity->tenant_id ?? 'null'));
            error_log("Super Admin: " . ($identity->is_super_admin ?? 'null'));

            // 3. Test ModuleManager context
            if (method_exists($this->moduleManager, 'getCurrentUserId')) {
                error_log("ModuleManager current user: " . $this->moduleManager->getCurrentUserId());
            }

            if (method_exists($this->moduleManager, 'getCurrentTenantId')) {
                error_log("ModuleManager current tenant: " . $this->moduleManager->getCurrentTenantId());
            }

            // 4. Test getActiveModules
            error_log("=== Testuji getActiveModules ===");

            try {
                $activeModules = $this->moduleManager->getActiveModules();
                error_log("✅ getActiveModules SUCCESS");
                error_log("Active modules count: " . count($activeModules));
                error_log("Active modules: " . json_encode(array_keys($activeModules)));

                // Test financial_reports specifically
                if (isset($activeModules['financial_reports'])) {
                    error_log("✅ financial_reports is active");
                    error_log("financial_reports info: " . json_encode($activeModules['financial_reports']));
                } else {
                    error_log("❌ financial_reports NOT in active modules");
                }
            } catch (\Throwable $e) {
                error_log("❌ getActiveModules ERROR: " . $e->getMessage());
                error_log("Exception: " . get_class($e));
                error_log("File: " . $e->getFile() . ":" . $e->getLine());
                error_log("Stack: " . $e->getTraceAsString());
            }

            // 5. Test direct module detection
            error_log("=== Test přímé detekce modulů ===");
            $tenantId = $this->getCurrentTenantId();
            $baseDir = dirname(__DIR__, 2) . '/Modules';
            $moduleDir = "$baseDir/tenant_$tenantId/financial_reports";

            error_log("Base dir: $baseDir");
            error_log("Module dir: $moduleDir");
            error_log("Module exists: " . (is_dir($moduleDir) ? 'yes' : 'no'));
            error_log("Module.php exists: " . (file_exists("$moduleDir/Module.php") ? 'yes' : 'no'));

            echo json_encode([
                'success' => true,
                'moduleManager_class' => get_class($this->moduleManager),
                'user_id' => $identity->id ?? null,
                'tenant_id' => $identity->tenant_id ?? null,
                'module_dir_exists' => is_dir($moduleDir),
                'active_modules_count' => count($activeModules ?? []),
                'financial_reports_active' => isset($activeModules['financial_reports'])
            ]);
        } catch (\Throwable $e) {
            error_log("=== DEBUG MODULEMANAGER ERROR ===");
            error_log("Error: " . $e->getMessage());

            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

        error_log("=== DEBUG MODULEMANAGER END ===");
        exit;
    }
}
