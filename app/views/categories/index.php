<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 py-10">

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
    <h2 class="text-2xl font-bold mb-6">دسته‌بندی‌ها</h2>
    <div class="mb-6">
        <?php if ($userRole === 'admin'): ?>
            <a href="<?= $CFG->wwwroot ?>/category/new" 
               class="px-5 py-2 rounded-2xl bg-gradient-to-r from-green-400 to-teal-500 text-white font-bold text-sm hover:opacity-90 transition">
                ایجاد دسته‌بندی جدید
            </a>
        <?php endif; ?>
    </div>

    <?php if (!empty($categories)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($categories as $category): ?>
                <div class="bg-white rounded-2xl shadow p-6 flex flex-col justify-between">
                    <h3 class="text-lg font-semibold mb-4">
                        <?= htmlspecialchars($category['name']) ?>
                    </h3>
                    <div class="flex flex-wrap gap-3 mt-auto">
                        <a href="<?= $CFG->wwwroot ?>/category/show/<?= $category['id'] ?>" 
                           class="px-4 py-2 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-sm font-bold hover:opacity-90 transition">
                            مشاهده
                        </a>

                        <?php if ($userRole === 'admin'): ?>
                            <a href="<?= $CFG->wwwroot ?>/category/edit/<?= $category['id'] ?>" 
                               class="px-4 py-2 rounded-xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-sm font-bold hover:opacity-90 transition">
                                ویرایش
                            </a>
                            <a href="<?= $CFG->wwwroot ?>/category/delete/<?= $category['id'] ?>" 
                               class="px-4 py-2 rounded-xl bg-gradient-to-r from-red-500 to-pink-600 text-white text-sm font-bold hover:opacity-90 transition"
                               onclick="return confirm('آیا مطمئن هستید که می‌خواهید حذف کنید؟');">
                                حذف
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-500">هیچ دسته‌بندی‌ای یافت نشد.</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
