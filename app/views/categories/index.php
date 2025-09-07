<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found'); 
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>




<h1><?php echo $msg; ?></h1>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Categories</h2>
            <ul>
                <?php foreach ($categories as $category): ?>
                    <li>
                        <?php echo htmlspecialchars($category['name']); ?>
                        <?php if ($userRole === 'admin'): ?>
                            <a href="<?= $CFG->wwwroot ?>/category/edit/<?= $category['id'] ?>">ویرایش</a>
                            <a href="<?= $CFG->wwwroot ?>/category/delete/<?= $category['id'] ?>">حذف</a>
                        <?php endif; ?>
                        <a href="<?= $CFG->wwwroot ?>/category/show/<?= $category['id'] ?>">مشاهده</a>
                    </li>

                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
    


<?php require_once __DIR__ . '/../layouts/footer.php'; ?>