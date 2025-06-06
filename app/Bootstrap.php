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

        // Vytvoření složky pro přístup k assets modulů
        $modulesDir = __DIR__ . '/Modules';
        $wwwModulesDir = dirname(__DIR__) . '/www/Modules';

        if (!is_dir($wwwModulesDir)) {
            if (!is_dir($modulesDir)) {
                mkdir($modulesDir, 0755, true);
            }

            // Na Windows můžeme potřebovat kopírovat místo symlinku
            if (PHP_OS_FAMILY === 'Windows') {
                // Vytvoříme pouze prázdný adresář, který bude později naplněn
                mkdir($wwwModulesDir, 0755, true);
            } else {
                // Na Linuxu/macOS můžeme použít symlink
                symlink($modulesDir, $wwwModulesDir);
            }
        }

        // OPRAVENO: Debug mode jen na localhost/developmentu
        // $this->configurator->setDebugMode(true); // <-- SMAZÁNO!
        
        return $container;
    }


    public function initializeEnvironment(): void
    {
        define('WWW_DIR', dirname(__DIR__));
        
        // OPRAVENO: Debug mode jen pro specifické IP nebo localhost
        // Pro produkci zakomentováno:
        // $this->configurator->setDebugMode('secret@23.75.345.200');
        
        // Tracy jen pro development (na produkci bude vypnutá local.neon)
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