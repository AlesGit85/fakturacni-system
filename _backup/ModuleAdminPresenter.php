<?php

declare(strict_types=1);

namespace App\Presentation\ModuleAdmin;

use Nette;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use App\Model\ModuleManager;
use App\Model\InvoicesManager;
use App\Model\CompanyManager;
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
        Nette\Database\Explorer $database
    ) {
        $this->moduleManager = $moduleManager;
        $this->logger = $logger;
        $this->invoicesManager = $invoicesManager;
        $this->companyManager = $companyManager;
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
     * AJAX action pro načítání dat z modulů - NOVÁ OBECNÁ IMPLEMENTACE
     */
    public function handleModuleData(): void
    {
        try {
            $this->logger->log("=== ZAČÁTEK OBECNÉHO AJAX VOLÁNÍ ===", ILogger::INFO);

            // ✅ OPRAVENO: Sanitizace parametrů hned při čtení
            $moduleId = SecurityValidator::sanitizeString($this->getHttpRequest()->getQuery('moduleId') ?? '');
            $action = SecurityValidator::sanitizeString($this->getHttpRequest()->getQuery('action') ?? 'getAllData');

            $this->logger->log("AJAX parametry - moduleId: '$moduleId', action: '$action'", ILogger::INFO);

            if (!$moduleId) {
                $this->logger->log("CHYBA: Nebyl zadán moduleId", ILogger::ERROR);
                $this->sendJson([
                    'success' => false,
                    'error' => 'Nebyl zadán moduleId'
                ]);
                return;
            }

            // ✅ PŘIDÁNO: Základní validace moduleId
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $moduleId)) {
                $this->logger->log("CHYBA: Neplatný formát moduleId: '$moduleId'", ILogger::ERROR);
                $this->sendJson([
                    'success' => false,
                    'error' => 'Neplatný formát moduleId'
                ]);
                return;
            }

            // ✅ PŘIDÁNO: Základní validace action
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $action)) {
                $this->logger->log("CHYBA: Neplatný formát action: '$action'", ILogger::ERROR);
                $this->sendJson([
                    'success' => false,
                    'error' => 'Neplatný formát action'
                ]);
                return;
            }

            // Kontrola, zda je modul aktivní
            $this->logger->log("Kontroluji aktivní moduly...", ILogger::INFO);
            $activeModules = $this->moduleManager->getActiveModules();
            $this->logger->log("Aktivní moduly: " . json_encode(array_keys($activeModules)), ILogger::INFO);

            if (!isset($activeModules[$moduleId])) {
                $this->logger->log("CHYBA: Modul '$moduleId' není aktivní nebo neexistuje", ILogger::ERROR);
                $this->sendJson([
                    'success' => false,
                    'error' => "Modul '$moduleId' není aktivní nebo neexistuje"
                ]);
                return;
            }

            $this->logger->log("Modul '$moduleId' je aktivní, pokračuji...", ILogger::INFO);

            // Vytvoření instance modulu
            $moduleInstance = $this->createModuleInstance($moduleId);
            if (!$moduleInstance) {
                $this->logger->log("CHYBA: Nepodařilo se vytvořit instanci modulu '$moduleId'", ILogger::ERROR);
                $this->sendJson([
                    'success' => false,
                    'error' => "Nepodařilo se načíst modul '$moduleId'"
                ]);
                return;
            }

            $this->logger->log("Instance modulu '$moduleId' úspěšně vytvořena", ILogger::INFO);

            // Kontrola, zda modul podporuje AJAX
            if (!method_exists($moduleInstance, 'handleAjaxRequest')) {
                $this->logger->log("CHYBA: Modul '$moduleId' nepodporuje AJAX požadavky", ILogger::ERROR);
                $this->sendJson([
                    'success' => false,
                    'error' => "Modul '$moduleId' nepodporuje AJAX požadavky"
                ]);
                return;
            }

            $this->logger->log("Modul '$moduleId' podporuje AJAX, volám handleAjaxRequest...", ILogger::INFO);

            // Příprava závislostí pro modul
            $dependencies = $this->prepareDependencies();

            // ✅ OPRAVENO: Bezpečná příprava parametrů
            $parameters = $this->prepareParameters();

            // Přidání user_id pokud je uživatel přihlášen
            if ($this->getUser()->isLoggedIn()) {
                $identity = $this->getUser()->getIdentity();
                if ($identity && $identity->id) {
                    $parameters['user_id'] = $identity->id;
                }
            }

            $this->logger->log("Volám handleAjaxRequest s akcí '$action'", ILogger::INFO);

            // Volání metody modulu
            $result = $moduleInstance->handleAjaxRequest($action, $parameters, $dependencies);

            $this->logger->log("AJAX akce '$action' úspěšně dokončena", ILogger::INFO);
            $this->logger->log("=== KONEC OBECNÉHO AJAX VOLÁNÍ ===", ILogger::INFO);

            $this->sendJson([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Throwable $e) {
            $this->logger->log("=== CHYBA V OBECNÉM AJAX VOLÁNÍ ===", ILogger::ERROR);
            $this->logger->log("Exception type: " . get_class($e), ILogger::ERROR);
            $this->logger->log("Message: " . $e->getMessage(), ILogger::ERROR);
            $this->logger->log("File: " . $e->getFile() . " (line " . $e->getLine() . ")", ILogger::ERROR);
            $this->logger->log("Stack trace: " . $e->getTraceAsString(), ILogger::ERROR);

            $this->sendJson([
                'success' => false,
                'error' => 'Chyba při načítání dat: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * NOVÁ METODA: Vytvoří instanci modulu - OPRAVENÁ PRO TENANT-SPECIFIC CESTY
     */
    private function createModuleInstance(string $moduleId): ?\App\Modules\IModule
    {
        try {
            $this->logger->log("Vytvářím instanci modulu '$moduleId'", ILogger::INFO);

            // Získáme info o modulu z ModuleManager
            $activeModules = $this->moduleManager->getActiveModules();
            $moduleInfo = $activeModules[$moduleId] ?? null;

            if (!$moduleInfo) {
                $this->logger->log("CHYBA: Modul '$moduleId' nenalezen v aktivních modulech", ILogger::ERROR);
                return null;
            }

            // Používáme physical_path z moduleInfo
            $modulePath = $moduleInfo['physical_path'] ?? null;
            if (!$modulePath) {
                $this->logger->log("CHYBA: Modul '$moduleId' nemá physical_path", ILogger::ERROR);
                return null;
            }

            $moduleFile = $modulePath . '/Module.php';
            $this->logger->log("Hledám soubor modulu: $moduleFile", ILogger::INFO);

            if (!file_exists($moduleFile)) {
                $this->logger->log("CHYBA: Soubor modulu neexistuje: $moduleFile", ILogger::ERROR);
                return null;
            }

            $this->logger->log("Soubor modulu nalezen, načítám...", ILogger::INFO);

            // Načtení souboru modulu
            require_once $moduleFile;

            // Vytvoření názvu třídy (používáme skutečné ID modulu, ne klíč)
            $realModuleId = $moduleInfo['id'] ?? $moduleId;
            // ✅ OPRAVENO: Používáme tenant-specific namespace
            $tenantId = $moduleInfo['tenant_id'] ?? 1;
            $moduleNameForClass = ucfirst($realModuleId); // např. "Financial_reports"
            $moduleClassName = "Modules\\Tenant{$tenantId}\\{$moduleNameForClass}\\Module";
            
            $this->logger->log("Vytvářím instanci tenant-specific třídy: $moduleClassName pro modul: $realModuleId (tenant: $tenantId)", ILogger::INFO);

            $this->logger->log("Kontroluji existenci třídy: $moduleClassName", ILogger::INFO);

            if (!class_exists($moduleClassName)) {
                $this->logger->log("CHYBA: Třída modulu neexistuje: $moduleClassName", ILogger::ERROR);
                return null;
            }

            $this->logger->log("Třída nalezena, vytvářím instanci...", ILogger::INFO);

            // Vytvoření instance modulu
            $moduleInstance = new $moduleClassName();

            if (!$moduleInstance instanceof \App\Modules\IModule) {
                $this->logger->log("CHYBA: Třída modulu neimplementuje IModule: $moduleClassName", ILogger::ERROR);
                return null;
            }

            $this->logger->log("Instance modulu '$moduleId' úspěšně vytvořena", ILogger::INFO);
            return $moduleInstance;
        } catch (\Throwable $e) {
            $this->logger->log("CHYBA při vytváření instance modulu '$moduleId': " . $e->getMessage(), ILogger::ERROR);
            return null;
        }
    }

    /**
     * NOVÁ METODA: Připraví závislosti pro moduly
     */
    private function prepareDependencies(): array
    {
        $this->logger->log("Připravuji závislosti pro modul", ILogger::INFO);

        return [
            $this->invoicesManager,
            $this->companyManager,
            $this->database,
            $this->logger
        ];
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
            // OPRAVA: Načteme pouze administrátory (admin role nebo super admin)
            $users = $this->database->table('users')
                ->where('role = ? OR is_super_admin = ?', 'admin', 1)
                ->order('username ASC')
                ->fetchAll();

            foreach ($users as $user) {
                // Pro každého uživatele načteme jeho moduly
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
                    'user' => $user,
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

            $this->logger->log("Super admin: Načten přehled modulů pro " . count($usersWithModules) . " administrátorů", ILogger::INFO);
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

        // OPRAVA: Používáme tenant-specific cestu pro assets
        $tenantId = $moduleInfo['tenant_id'] ?? null;
        if ($tenantId) {
            // CSS cesta
            $cssPath = "/Modules/tenant_{$tenantId}/{$id}/assets/css/style.css";
            $cssFullPath = WWW_DIR . '/web' . $cssPath;
            if (file_exists($cssFullPath)) {
                $this->template->moduleCss = $cssPath;
            }

            // JS cesta
            $jsPath = "/Modules/tenant_{$tenantId}/{$id}/assets/js/script.js";
            $jsFullPath = WWW_DIR . '/web' . $jsPath;
            if (file_exists($jsFullPath)) {
                $this->template->moduleJs = $jsPath;
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
     * OPRAVENÁ METODA: Aktualizuje assets modulu - pro tenant-specific cesty
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

        $wwwModuleDir = WWW_DIR . "/web/Modules/tenant_{$tenantId}/{$moduleId}";

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
            }

            // Zkopírování nových assets
            $this->logger->log("Kopíruji nové assets modulu '$moduleId' z: $moduleAssetsDir do: $wwwModuleDir", ILogger::INFO);
            $this->copyDirectory($moduleAssetsDir, $wwwModuleDir . '/assets');

            $this->logger->log("Assets modulu '$moduleId' byly úspěšně aktualizovány", ILogger::INFO);
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

        $form->addUpload('moduleZip', 'ZIP soubor s modulem:')
            ->setRequired('Vyberte ZIP soubor s modulem')
            ->addRule(Form::MIME_TYPE, 'Soubor musí být ve formátu ZIP', 'application/zip')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost souboru je ' . $this->formatBytes($maxUploadSize), $maxUploadSize);

        $form->addSubmit('upload', 'Nahrát modul');

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
     * ✅ FINÁLNÍ: Zpracování nahraného modulu s kompletní bezpečnostní validací
     */
    public function uploadFormSucceeded(Form $form): void
    {
        $values = $form->getValues();
        $file = $values->moduleZip;

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

                // ✅ PŘEDEXTRAKČNÍ KONTROLA MODULE.JSON
                $this->performPreExtractionValidation($tempZipPath);

                // ✅ BEZPEČNÁ EXTRAKCE A VALIDACE
                $extractedModuleInfo = $this->performSecureExtraction($tempZipPath, $tempDir);

                // ✅ KONTROLA DUPLICITNÍ INSTALACE
                $this->checkDuplicateInstallation($extractedModuleInfo['id'], $identity->id);

                // ✅ PŮVODNÍ INSTALACE MODULU (nyní s předvalidovanými daty)
                $result = $this->moduleManager->installModuleForUser(
                    $file,
                    $identity->id,
                    $this->getCurrentTenantId(),
                    $identity->id
                );

                if ($result['success']) {
                    // ✅ LOGOVÁNÍ ÚSPĚŠNÉ INSTALACE
                    $this->logger->log(sprintf(
                        'Modul %s úspěšně nainstalován s bezpečnostními kontrolami - uživatel: %s, tenant: %s',
                        $extractedModuleInfo['id'],
                        $identity->id,
                        $this->getCurrentTenantId()
                    ), ILogger::INFO);
                    
                    $this->flashMessage($result['message'], 'success');
                } else {
                    throw new \Exception($result['message']);
                }

            } finally {
                // ✅ VŽDY VYČISTIT DOČASNÝ ADRESÁŘ
                $this->cleanupTempDirectory($tempDir);
            }

        } catch (\Exception $e) {
            // ✅ DETAILNÍ LOGOVÁNÍ CHYB
            $this->logger->log(sprintf(
                'Chyba při bezpečné instalaci modulu: %s, soubor=%s, uživatel=%s',
                $e->getMessage(),
                $file->getName() ?? 'neznámý',
                $this->getUser()->getId() ?? 'nepřihlášen'
            ), ILogger::ERROR);
            
            $this->flashMessage('Chyba při instalaci modulu: ' . $e->getMessage(), 'danger');
        }

        $this->redirect('this');
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
}
