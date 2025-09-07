<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-3xl mx-auto px-4 py-10">
    
    <?php if (!empty($msg)): ?>
        <div class="mb-6 p-4 rounded-2xl 
            <?php if (isset($msgType) && $msgType === 'success'): ?>
                bg-green-100 text-green-700 border border-green-300
            <?php elseif (isset($msgType) && $msgType === 'error'): ?>
                bg-red-100 text-red-700 border border-red-300
            <?php else: ?>
                bg-blue-100 text-blue-700 border border-blue-300
            <?php endif; ?>
        ">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl shadow-2xl p-8">
        <h1 class="text-3xl font-extrabold text-gray-800 mb-4">
            <?= htmlspecialchars($category['name']) ?>
        </h1>

        <div class="flex flex-wrap gap-3">
            <a href="<?= $CFG->wwwroot ?>/category" 
               class="px-5 py-2 rounded-2xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold text-sm hover:opacity-90 transition">
                بازگشت به دسته‌ها
            </a>

            <?php if ($userRole === 'admin'): ?>
                <a href="<?= $CFG->wwwroot ?>/category/edit/<?= $category['id'] ?>" 
                   class="px-5 py-2 rounded-2xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white font-bold text-sm hover:opacity-90 transition">
                    ویرایش
                </a>
                <a href="<?= $CFG->wwwroot ?>/category/delete/<?= $category['id'] ?>" 
                   class="px-5 py-2 rounded-2xl bg-gradient-to-r from-red-500 to-pink-600 text-white font-bold text-sm hover:opacity-90 transition"
                   onclick="return confirm('آیا مطمئن هستید که می‌خواهید این دسته را حذف کنید؟');">
                    حذف
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
