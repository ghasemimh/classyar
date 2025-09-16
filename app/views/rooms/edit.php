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
        <h1 class="text-2xl font-extrabold text-gray-800 mb-6">ویرایش مکان</h1>

        <form action="<?= $CFG->wwwroot; ?>/room/edit/<?= $room['id']; ?>" method="post" class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">نام مکان</label>
                <input type="text" id="name" name="name" 
                       value="<?= htmlspecialchars($room['name'] ?? ''); ?>" required
                       class="w-full rounded-2xl border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2">
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" 
                        class="px-6 py-2 rounded-2xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white font-bold hover:opacity-90 transition">
                    بروزرسانی
                </button>
                <a href="<?= $CFG->wwwroot ?>/room" 
                   class="px-6 py-2 rounded-2xl bg-gradient-to-r from-gray-400 to-gray-600 text-white font-bold hover:opacity-90 transition">
                    انصراف
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
