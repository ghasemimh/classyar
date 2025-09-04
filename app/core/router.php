<?php
defined('CLASSYAR_APP') || die('No direct access allowed!');

class Router {
    protected static $routes = [];
    protected static $basePath = '/moodle/app/classyar/'; // بخش base که می‌خوای حذف بشه

    public static function get($route, $controller) {
        self::$routes['GET'][$route] = $controller;
    }

    public static function post($route, $controller) {
        self::$routes['POST'][$route] = $controller;
    }

    public static function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        // حذف base path
        if (strpos($uri, self::$basePath) === 0) {
            $path = substr($uri, strlen(self::$basePath));
        } else {
            $path = $uri;
        }
        $path = trim($path, '/');

        if (!isset(self::$routes[$method])) {
            return self::abort(405);
        }

        foreach (self::$routes[$method] as $route => $controller) {
            // تبدیل route به regex
            $routePattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_]+)', trim($route, '/'));
            $routePattern = "#^" . $routePattern . "$#";

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

                // جمع‌آوری request
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
