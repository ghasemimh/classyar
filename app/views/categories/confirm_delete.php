<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<h1><?php echo $msg; ?></h1>

<form action="<?php echo $CFG->wwwroot; ?>/category/delete/<?php echo $category['id']; ?>" method="post">
    <p>Are you sure you want to delete the category "<?php echo $category['name'] ?? ''; ?>"?</p>
    <input type="text" name="name">
    <button type="submit">Yes, Delete</button>
    <a href="<?php echo $CFG->wwwroot; ?>/category">Cancel</a>
</form>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>