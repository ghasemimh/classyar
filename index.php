<?php
define('CLASSYAR_APP', true);
require_once("config/config.php");
require_once("app/controllers/auth.php");

Auth::auth();

// فعال کردن گزارش خطاهای PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

var_dump($_SESSION);