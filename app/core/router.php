<?php
defined('CLASSYAR_APP') || die('No direct access allowed!');

class Router {
    protected static $routes = [];

    /**
     * ثبت یک مسیر GET با یک پرمیشن اختیاری.
     *
     * @param string $route
     * @param string $controller
     * @param string|null $permission
     */
    public static function get($route, $controller, $permission = null)
    {
        self::$routes['GET'][$route] = ['controller' => $controller, 'permission' => $permission];
    }

    /**
     * ثبت یک مسیر POST با یک پرمیشن اختیاری.
     *
     * @param string $route
     * @param string $controller
     * @param string|null $permission
     */
    public static function post($route, $controller, $permission = null)
    {
        self::$routes['POST'][$route] = ['controller' => $controller, 'permission' => $permission];
    }

    /**
     * اجرای فرآیند روتینگ و بررسی پرمیشن‌ها.
     *
     * @param string $uri
     * @param string $method
     */
    // public static function dispatch($uri, $method) {
    //     $uri = strtok($uri, '?');
    //     $method = strtoupper($method);

    //     // چک کردن اینکه آیا متد درخواست در مسیرهای ما وجود دارد یا خیر.
    //     if (!isset(self::$routes[$method])) {
    //         http_response_code(405); // Method Not Allowed
    //         die("Method Not Allowed.");
    //     }

    //     foreach (self::$routes[$method] as $route => $config) {
    //         $routePattern = preg_replace('/\{([a-zA-Z0-9]+)\}/', '([a-zA-Z0-9]+)', $route);
    //         $routePattern = "#^" . $routePattern . "$#";

    //         if (preg_match($routePattern, $uri, $matches)) {
                
    //             // --- Middleware: چک کردن پرمیشن ---
    //             // اگر یک پرمیشن تعریف شده باشد، آن را چک می‌کند.
    //             if ($config['permission'] !== null) {
    //                 // AuthController را به صورت پویا include می‌کنیم.
    //                 require_once __DIR__ . '/../controllers/AuthController.php';
                    
    //                 if (!Auth::checkPermission($config['permission'])) {
    //                     self::abort(403); // Forbidden
    //                     return;
    //                 }
    //             }

    //             array_shift($matches); // حذف اولین عنصر (کل URL)
                
    //             list($controllerName, $action) = explode('@', $config['controller']);
                
    //             $controllerFile = __DIR__ . "/../controllers/" . $controllerName . ".php";
    //             if (file_exists($controllerFile)) {
    //                 require_once $controllerFile;
    //                 $controllerInstance = new $controllerName();
                    
    //                 if (method_exists($controllerInstance, $action)) {
    //                     call_user_func_array([$controllerInstance, $action], $matches);
    //                     return;
    //                 }
    //             }
    //         }
    //     }
        
    //     // اگر هیچ مسیری پیدا نشد
    //     self::abort(404);
    // }












    public static function dispatch($uri) {
        // بخش base (classyar) رو حذف کن
        $path = str_replace('/moodle/app/classyar/', '', parse_url($uri, PHP_URL_PATH));
        $parts = explode('/', trim($path, '/'));

        // پیشفرض
        $controllerName = !empty($parts[0]) ? ucfirst($parts[0]) : 'HomeController';
        $action = $parts[1] ?? 'index';
        $params = array_slice($parts, 2);

        // فایل کنترلر
        $controllerFile = __DIR__ . '/../controllers/' . strtolower($controllerName) . '.php';
        if (!file_exists($controllerFile)) {
            return self::abort(404);
        }

        require_once $controllerFile;
        if (!class_exists($controllerName)) {
            return self::abort(500); // کنترلر وجود نداره
        }

        $controller = new $controllerName();
        if (!method_exists($controller, $action)) {
            return self::abort(404);
        }

        // اجرا
        call_user_func_array([$controller, $action], $params);
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