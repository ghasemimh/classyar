<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
http_response_code(404);

global $MSG;

if (!isset($msg)) {
    $msg = $MSG->pagenotfound;
}
echo "<h1>404</h1>";
echo "<p>$msg</p>";