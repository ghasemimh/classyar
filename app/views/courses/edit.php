<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-2xl mx-auto px-4 py-10">
    
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
        <h1 class="text-2xl font-extrabold text-gray-800 mb-6">ویرایش دوره</h1>

        <form action="<?= $CFG->wwwroot; ?>/course/edit/<?= $course['id']; ?>" method="post" class="space-y-6">
            <div>
                <label for="crsid" class="block text-sm font-medium text-gray-700 mb-2">کد دوره (Course ID)</label>
                <input type="text" id="crsid" name="crsid" 
                       value="<?= htmlspecialchars($course['crsid'] ?? ''); ?>" required
                       class="w-full rounded-2xl border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2">
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">نام دوره</label>
                <input type="text" id="name" name="name" 
                       value="<?= htmlspecialchars($course['name'] ?? ''); ?>" required
                       class="w-full rounded-2xl border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2">
            </div>

            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">دسته‌بندی</label>
                <select id="category_id" name="category_id" required
                        class="w-full rounded-2xl border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2">
                    <option value="">انتخاب کنید...</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" 
                            <?= (isset($course['category_id']) && $course['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" 
                        class="px-6 py-2 rounded-2xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white font-bold hover:opacity-90 transition">
                    بروزرسانی
                </button>
                <a href="<?= $CFG->wwwroot ?>/course" 
                   class="px-6 py-2 rounded-2xl bg-gradient-to-r from-gray-400 to-gray-600 text-white font-bold hover:opacity-90 transition">
                    انصراف
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
