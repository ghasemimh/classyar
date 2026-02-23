<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-3xl mx-auto px-4 py-10">
    <?php if (!empty($msg)): ?>
        <div class="mb-6 p-4 rounded-2xl bg-blue-100 text-blue-700 border border-blue-300"><?= htmlspecialchars((string)$msg, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl shadow-2xl p-8">
        <h1 class="text-3xl font-extrabold text-gray-800 mb-4"><?= htmlspecialchars((string)($course['name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-gray-600 mb-6"><span class="font-bold">کد دوره:</span> <?= htmlspecialchars((string)($course['crsid'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
        <div class="flex flex-wrap gap-3">
            <a href="<?= $CFG->wwwroot ?>/course" class="px-5 py-2 rounded-2xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold text-sm hover:opacity-90 transition">بازگشت به دوره‌ها</a>
            <?php if ($userRole === 'admin'): ?>
                <a href="<?= $CFG->wwwroot ?>/course/edit/<?= (int)($course['id'] ?? 0) ?>" class="px-5 py-2 rounded-2xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white font-bold text-sm hover:opacity-90 transition">ویرایش</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
