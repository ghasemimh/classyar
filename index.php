<?php
define('CLASSYAR_APP', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/models/db.php';
require_once __DIR__ . '/app/controllers/auth.php';

Auth::auth();

// فعال کردن گزارش خطاهای PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



require_once __DIR__ . '/app/core/router.php';
require_once __DIR__ . '/app/core/routes.php';

Router::dispatch();