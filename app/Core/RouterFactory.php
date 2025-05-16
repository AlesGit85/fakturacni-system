<?php
declare(strict_types=1);
namespace App\Core;

use Nette;
use Nette\Application\Routers\RouteList;

final class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList;
        
        // Definování základních routovacích pravidel
        $router->addRoute('dodavatel', 'Dodavatel:default');
        $router->addRoute('odberatel', 'Odberatel:default');
        $router->addRoute('faktura', 'Faktura:default');
        $router->addRoute('', 'Faktura:default'); // výchozí route
        
        // Fallback route pro další presenter a akce
        $router->addRoute('<presenter>/<action>[/<id>]', 'Faktura:default');
        
        return $router;
    }
}