<?php

namespace App\Core;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

class RouterFactory
{
    /**
     * @return Nette\Application\IRouter
     */
    public static function createRouter(): Nette\Application\IRouter
    {
        $router = new RouteList;
        $router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
        return $router;
    }
}