<?php
define('CLASSYAR_APP', true);
ob_start();

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/models/db.php';
require_once __DIR__ . '/app/controllers/auth.php';

// Keep server warnings out of HTTP responses so AJAX JSON stays parseable.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

Auth::auth();

require_once __DIR__ . '/app/core/router.php';
require_once __DIR__ . '/app/core/routes.php';

Router::dispatch();
