<?php

declare(strict_types=1);

namespace App;

use Nette;
use Nette\Bootstrap\Configurator;
use App\Security\SecurityHeaders;

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
        // NOVÉ: Nastavení časového pásma pro celou aplikaci
        date_default_timezone_set('Europe/Prague');

        // OPRAVENO: Správná definice WWW_DIR pro produkční server
        define('WWW_DIR', dirname(__DIR__));

        // OPRAVENO: Debug mode pouze pro localhost
        // Na produkci je vyřízeno přes config/local.neon

        // Tracy jen pro development (na produkci bude vypnutá přes local.neon)
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
    }
}
