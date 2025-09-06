<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
http_response_code(403);

global $MSG;

if (!isset($msg)) {
    $msg = $MSG->notallowed;
}
echo "<h1>403</h1>";
echo "<p>$msg</p>";