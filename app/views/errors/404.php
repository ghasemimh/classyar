<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
http_response_code(404);

global $CFG, $MSG;
$msg = $msg ?? $MSG->pagenotfound;

$subtitle = 404 . ' - ' . $msg;
?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="flex items-center justify-center bg-gray-50 px-4 py-4">
    <div class="bg-white rounded-3xl shadow-2xl p-10 max-w-md text-center">
        <h1 class="text-9xl font-extrabold text-gradient bg-clip-text text-transparent bg-gradient-to-r from-yellow-400 via-orange-500 to-red-500 mb-6">
            404
        </h1>
        <p class="text-gray-700 text-lg mb-6"><?= $msg ?></p>
        <a href="<?= $CFG->wwwroot ?>" class="inline-block px-8 py-3 rounded-2xl bg-gradient-to-r from-yellow-400 via-orange-500 to-red-500 text-white font-bold hover:opacity-90 transition">
            بازگشت به صفحه اصلی
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
