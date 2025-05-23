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
        
        return $container;
    }


    public function initializeEnvironment(): void
    {
        define('WWW_DIR', dirname(__DIR__) . '/www');
        //$this->configurator->setDebugMode('secret@23.75.345.200'); // enable for your remote IP
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