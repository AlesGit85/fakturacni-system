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
        
        // Přidáme specifickou routu pro ModuleAdmin
        $router->addRoute('moduleadmin/<action>[/<id>]', 'ModuleAdmin:default');
        
        // Obecná routa pro ostatní presentery
        $router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
        
        return $router;
    }
}