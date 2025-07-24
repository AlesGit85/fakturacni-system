<?php

namespace App\Core;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

class RouterFactory
{
    /**
     * @return Nette\Application\Routers\RouteList
     */
    public static function createRouter(): Nette\Application\Routers\RouteList
    {
        $router = new RouteList;
        
        // ✅ OPRAVENO: Specifická routa pro Security presenter s podporou signálů
        $router->addRoute('security/<action>[/<id>]', [
            'presenter' => 'Security',
            'action' => 'default'
        ]);
        
        // Přidáme specifickou routu pro ModuleAdmin
        $router->addRoute('moduleadmin/<action>[/<id>]', [
            'presenter' => 'ModuleAdmin',
            'action' => 'default'
        ]);
        
        // Obecná routa pro ostatní presentery
        $router->addRoute('<presenter>/<action>[/<id>]', [
            'presenter' => 'Home',
            'action' => 'default'
        ]);
        
        return $router;
    }
}