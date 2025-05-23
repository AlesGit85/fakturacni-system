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
        $router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
        return $router;
    }
}