<?php

declare(strict_types=1);

namespace App;

use Nette;
use Nette\Bootstrap\Configurator;
use App\Security\SecurityHeaders;
use Dotenv\Dotenv;

class Bootstrap
{
    private Configurator $configurator;
    private string $rootDir;


    public function __construct()
    {
        $this->rootDir = dirname(__DIR__);
        $this->configurator = new Configurator;
        $this->configurator->setTempDirectory($this->rootDir . '/temp');
    }


    public function bootWebApplication(): Nette\DI\Container
{
    $this->initializeEnvironment();
    $this->setupContainer();
    $container = $this->configurator->createContainer();

    // Aplikace bezpečnostních hlaviček
    $httpResponse = $container->getByType(Nette\Http\Response::class);
    SecurityHeaders::apply($httpResponse);

    // OPRAVENO: Bezpečnější vytvoření složky pro moduly bez symlinků
    $this->createModulesDirectorySafely();

    return $container;
}

    /**
     * Bezpečné vytvoření adresáře pro moduly bez symlinků
     */
    private function createModulesDirectorySafely(): void
    {
        try {
            $modulesDir = __DIR__ . '/Modules';
            $webModulesDir = dirname(__DIR__) . '/web/Modules';

            // Vytvoření základních adresářů pokud neexistují
            if (!is_dir($modulesDir)) {
                mkdir($modulesDir, 0755, true);
            }

            if (!is_dir($webModulesDir)) {
                mkdir($webModulesDir, 0755, true);
            }

            // Na produkčním serveru nepoužíváme symlinky - pouze vytvoříme adresář
            // Moduly si později zkopírují své assets podle potřeby

        } catch (\Exception $e) {
            // Logování chyby, ale nepřerušujeme načítání aplikace
            error_log("Bootstrap: Chyba při vytváření adresáře pro moduly: " . $e->getMessage());
        }
    }


public function initializeEnvironment(): void
{
    // NEJDŘÍVE načtení .env souboru - MUSÍ být před vším ostatním
    $envPath = $this->rootDir . '/.env';
    if (file_exists($envPath)) {
        $dotenv = Dotenv::createImmutable($this->rootDir);
        $dotenv->load();
        
        // KLÍČOVÉ: Předání environment variables do Nette konfiguratoru
        $this->configurator->addStaticParameters([
            'env' => $_ENV
        ]);
        
        if (!isset($_ENV['DB_PASSWORD'])) {
            throw new \Exception('.env soubor se načetl, ale DB_PASSWORD není nastaveno');
        }
    } else {
        throw new \Exception('.env soubor nenalezen na cestě: ' . $envPath);
    }

    // Nastavení časového pásma pro celou aplikaci
    date_default_timezone_set('Europe/Prague');

    // Správná definice WWW_DIR pro produkční server
    define('WWW_DIR', dirname(__DIR__));

    // Tracy jen pro development
    $this->configurator->enableTracy($this->rootDir . '/log');

    $this->configurator->createRobotLoader()
        ->addDirectory(__DIR__)
        ->register();
}


private function setupContainer(): void
{
    $configDir = $this->rootDir . '/config';
    $this->configurator->addConfig($configDir . '/common.neon');
    $this->configurator->addConfig($configDir . '/services.neon');

    // Chytrý výběr konfigurace podle prostředí
    if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['HTTP_HOST'] === 'localhost:8080' || isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1') {
        // Localhost - development
        $envConfig = $configDir . '/localhost.neon';
        if (file_exists($envConfig)) {
            $this->configurator->addConfig($envConfig);
        }
    } else {
        // Produkce
        $localConfig = $configDir . '/local.neon';
        if (file_exists($localConfig)) {
            $this->configurator->addConfig($localConfig);
        }
    }
}
}
