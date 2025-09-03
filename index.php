<?php
define('CLASSYAR_APP', true);
require_once("config/config.php");
require_once("app/controllers/auth.php");

Auth::auth();

// فعال کردن گزارش خطاهای PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);




// var_dump(parse_url($_SERVER['REQUEST_URI']));
echo "<br>";
// var_dump(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));



require_once __DIR__ . '/app/core/router.php';

Router::dispatch($_SERVER['REQUEST_URI']);

