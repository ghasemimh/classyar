<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

if (!isset($msg)) {
    $msg = NULL;
}
?>
<h1><?php echo $msg; ?></h1>
<form action="<?php echo $CFG->wwwroot; ?>/category/new" method="post">
    <label for="name">Category Name:</label>
    <input type="text" id="name" name="name" required>
    <button type="submit">Create Category</button>