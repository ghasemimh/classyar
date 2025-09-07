<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<h1><?php echo $msg; ?></h1>
<form action="<?php echo $CFG->wwwroot; ?>/category/new" method="post">
    <label for="name">Category Name:</label>
    <input type="text" id="name" name="name" required>
    <button type="submit">Create Category</button>

</form>
    <a href="<?php echo $CFG->wwwroot; ?>/category">Cancel</a>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>