<?php namespace lib;

use controllers;

class Router
{
    static private $routes = array();

    public static function get($route, $controller) {
        self::addRoute('get', $route, $controller);
    }

    public static function post($route, $controller) {
        self::addRoute('post', $route, $controller);
    }

    private static function addRoute($method, $path, $controller) {
        $route = [];
        if (strpos($path, '/:') !== false) {
            $parse = explode(':', $path);
            $route['path'] = substr($parse[0], 0, -1);
            $route['param'] = true;
        } else {
            $route['path'] = $path;
        }
        $route['method'] = strtoupper($method);

        if (strpos($controller, '@') !== false) {
            $parse = explode('@', $controller);
            $route['controller'] = $parse[0];
            $route['action'] = $parse[1];
        } else {
            $route['controller'] = $controller;
            $route['action'] = 'index';
        }

        self::$routes[] = $route;
    }

    public static function dispatch() {
        $path = str_replace(PATH, '', $_SERVER['REQUEST_URI']);
        $method = $_SERVER["REQUEST_METHOD"];
        foreach (self::$routes as $route) {
            if ($route['path'] == $path && $route['method'] == $method) {

                $name = 'controllers\\'.$route['controller'];
                $controller = new $name();

                if ($method == 'POST') {
                    $postdata = file_get_contents("php://input");
                    $request = json_decode($postdata);

                    return $controller->$route['action']($request);
                } else {
                    return $controller->$route['action']();
                }
            } elseif (strpos($path, $route['path']) !== false && $route['method'] == $method && array_key_exists('param', $route)) {
                $name = 'controllers\\'.$route['controller'];
                $controller = new $name();
                $param = str_replace($route['path'].'/', '', $path);

                return $controller->$route['action']($param);
            }
        }

        $controller = new controllers\BaseController();
        return $controller->notFound();
    }
}