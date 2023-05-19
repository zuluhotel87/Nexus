<?php

declare(strict_types=1);

namespace App\Http\Routes;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization as ZRoute;

use function array_keys;
use function class_exists;
use function method_exists;
use function preg_match_all;
use function resource_path;

class Routes
{
    public const ENGLISH_ROUTES_FILE = 'lang/en/routes.php';

    public static function initializeGetPostRoutes()
    {
        $routePath = resource_path(self::ENGLISH_ROUTES_FILE);
        $routes = include $routePath;

        foreach (array_keys($routes) as $route) {
            // Matches any string between forward slashes excluding matches that contain curly braces
            preg_match_all('/(^|\/)([^{}\/]+)(?=\/|$)/', $route, $matches);
            $routeParts = $matches[2];

            $controllerName = 'App\\Http\\Controllers\\' . Str::studly($routeParts[0]);
            $controllerName .= isset($routeParts[1]) ? '\\' . Str::studly($routeParts[1]) : '';
            $controllerName .= 'Controller';

            if (class_exists($controllerName)) {
                $methodName = Str::camel($routeParts[1]) ?? null;

                method_exists($controllerName, '__invoke') ? self::definedRoute('get', $route, $controllerName) : null;

                if ($methodName && method_exists($controllerName, $methodName)) {
                    self::definedRoute('post', $route, [$controllerName, $methodName]);
                } elseif (method_exists($controllerName, 'action')) {
                    self::definedRoute('post', $route, [$controllerName, 'action']);
                }
            }
        }
    }

    private static function definedRoute($method, $route, $controllerName)
    {
        Route::$method(ZRoute::transRoute('routes.' . $route), $controllerName);
    }
}
