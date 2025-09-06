<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found'); 

if (!isset($msg)) {
    $msg = NULL;
}
?>
<h1><?php echo $msg; ?></h1>
<h1><?php var_dump($category); ?></h1>