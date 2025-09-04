<?php
defined('CLASSYAR_APP') || die('No direct access allowed!');

class Router {
    protected static $routes = [];

    public static function get($route, $controller) {
        self::$routes['GET'][$route] = $controller;
    }

    public static function post($route, $controller) {
        self::$routes['POST'][$route] = $controller;
    }

    public static function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        $path = trim($uri, '/');

        if (!isset(self::$routes[$method])) {
            return self::abort(405);
        }

        foreach (self::$routes[$method] as $route => $controller) {
            $routePattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_]+)', $route);
            $routePattern = "#^" . trim($routePattern, '/') . "$#";

            if (preg_match($routePattern, $path, $matches)) {
                array_shift($matches); // حذف کل مسیر
                list($controllerName, $action) = explode('@', $controller);

                $controllerFile = __DIR__ . '/../controllers/' . strtolower($controllerName) . '.php';
                if (!file_exists($controllerFile)) {
                    return self::abort(404);
                }

                require_once $controllerFile;

                if (!class_exists($controllerName)) {
                    return self::abort(500);
                }

                $controllerInstance = new $controllerName();

                if (!method_exists($controllerInstance, $action)) {
                    return self::abort(404);
                }

                $request = [
                    'get' => $_GET,
                    'post' => $_POST,
                    'route' => $matches,
                ];

                call_user_func_array([$controllerInstance, $action], [$request]);
                return;
            }
        }

        self::abort(404);
    }

    public static function abort($code = 404) {
        http_response_code($code);
        $errorView = __DIR__ . "/../views/errors/{$code}.php";
        if (file_exists($errorView)) {
            require $errorView;
        } else {
            echo "$code Error";
        }
        exit();
    }
}
