<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../services/csrf.php';

class Router {
    protected static $routes = [];
    protected static $basePath;

    public static function get($route, $controller) {
        self::$routes['GET'][$route] = $controller;
    }

    public static function post($route, $controller) {
        self::$routes['POST'][$route] = $controller;
    }

    public static function dispatch() {
        global $CFG;

        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        self::$basePath = $CFG->routerbasepath;

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

        if ($method === 'POST' && !Csrf::validateRequest($_POST, $_SERVER)) {
            return self::abortCsrf();
        }

        foreach (self::$routes[$method] as $route => $controller) {

            // route → regex با named params
            $routePattern = preg_replace(
                '/\{([a-zA-Z0-9_]+)\}/u',
                '(?P<$1>[^/]+)',
                trim($route, '/')
            );

            $routePattern = "#^{$routePattern}$#";

            if (preg_match($routePattern, $path, $matches)) {

                // حذف match کامل
                unset($matches[0]);

                // پارامترهای عددی (دقیقاً مثل قبل)
                $numericParams = array_values(
                    array_filter($matches, 'is_int', ARRAY_FILTER_USE_KEY)
                );

                // پارامترهای نام‌دار
                $namedParams = array_filter(
                    $matches,
                    'is_string',
                    ARRAY_FILTER_USE_KEY
                );

                // ترکیب هر دو (backward compatible)
                $routeParams = array_merge($numericParams, $namedParams);

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
                    'get'   => $_GET,
                    'post'  => $_POST,
                    'route' => $routeParams,
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

    private static function abortCsrf() {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $isJson = str_contains($accept, 'application/json')
            || str_contains(strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''), 'xmlhttprequest');

        if ($isJson) {
            if (ob_get_length()) {
                ob_clean();
            }
            http_response_code(419);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'msg' => 'درخواست نامعتبر است. صفحه را رفرش کنید و دوباره تلاش کنید.'
            ]);
            exit();
        }

        if (ob_get_length()) {
            ob_clean();
        }
        http_response_code(419);
        $msg = 'درخواست نامعتبر است. صفحه را رفرش کنید و دوباره تلاش کنید.';
        $errorView = __DIR__ . '/../views/errors/400.php';
        if (file_exists($errorView)) {
            require $errorView;
        } else {
            echo '419 Invalid request';
        }
        exit();
    }
}
