<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
http_response_code(400);

global $CFG, $MSG;
$msg = $msg ?? $MSG->badrequest;
$subtitle = 400 . ' - ' . $msg;
?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="flex items-center justify-center px-4 py-10 min-h-[80vh]">
    <div class="rounded-3xl shadow-2xl p-10 max-w-md text-center glass-card">
        <h1 class="text-9xl font-extrabold text-gradient bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 via-lime-400 to-orange-400 mb-6">
            400
        </h1>
        <p class="text-slate-700 text-lg mb-6"><?= $msg ?></p>
        <a href="<?= $CFG->wwwroot ?>" class="inline-block px-8 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 via-lime-400 to-amber-400 text-white font-bold hover:opacity-90 transition">
            بازگشت به صفحه اصلی
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
