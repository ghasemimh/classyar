<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

if (!isset($msg)) {
    $msg = NULL;
}
?>
<h1><?php echo $msg; ?></h1>

<form action="<?php echo $CFG->wwwroot; ?>/category/edit/<?php echo $category['id']; ?>" method="post">
    <label for="name">Category Name:</label>
    <input type="text" id="name" name="name" value="<?php echo $category['name'] ?? ''; ?>" required>
    <button type="submit">Update Category</button>
</form>