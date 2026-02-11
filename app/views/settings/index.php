<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-5xl mx-auto px-4 py-8 space-y-6">
    <div class="rounded-3xl glass-card p-6">
        <h1 class="text-2xl font-bold mb-2">تنظیمات برنامه</h1>
        <p class="text-slate-600">منبع آپدیت: <code>https://github.com/ghasemimh/classyar</code></p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="rounded-2xl border p-3 <?= $messageType === 'success' ? 'bg-emerald-100 border-emerald-200 text-emerald-800' : 'bg-rose-100 border-rose-200 text-rose-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-3xl glass-card p-6">
            <h2 class="text-lg font-bold mb-3">نسخه فعلی</h2>
            <div class="space-y-2 text-sm">
                <div><span class="font-semibold">نسخه:</span> <?= htmlspecialchars((string)($localVersion['version'] ?? '-')) ?></div>
                <div><span class="font-semibold">بیلد:</span> <?= htmlspecialchars((string)($localVersion['build'] ?? '-')) ?></div>
                <div><span class="font-semibold">کانال:</span> <?= htmlspecialchars((string)($localVersion['channel'] ?? '-')) ?></div>
            </div>
        </div>

        <div class="rounded-3xl glass-card p-6">
            <h2 class="text-lg font-bold mb-3">نسخه مخزن</h2>
            <?php if (!empty($remoteVersion)): ?>
                <div class="space-y-2 text-sm">
                    <div><span class="font-semibold">نسخه:</span> <?= htmlspecialchars((string)($remoteVersion['version'] ?? '-')) ?></div>
                    <div><span class="font-semibold">بیلد:</span> <?= htmlspecialchars((string)($remoteVersion['build'] ?? '-')) ?></div>
                    <div><span class="font-semibold">کانال:</span> <?= htmlspecialchars((string)($remoteVersion['channel'] ?? '-')) ?></div>
                </div>
            <?php else: ?>
                <div class="text-sm text-slate-500">دریافت نسخه از مخزن ممکن نشد.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="rounded-3xl glass-card p-6">
        <div class="mb-4">
            <span class="font-semibold">وضعیت آپدیت:</span>
            <?php if ($hasUpdate): ?>
                <span class="inline-flex px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-800">نسخه جدید موجود است</span>
            <?php else: ?>
                <span class="inline-flex px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800">به‌روز است</span>
            <?php endif; ?>
        </div>

        <div class="flex flex-wrap gap-3">
            <form method="post" action="<?= $CFG->wwwroot ?>/settings">
                <input type="hidden" name="action" value="check">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-700 text-white hover:bg-slate-800">بررسی آپدیت</button>
            </form>

            <form method="post" action="<?= $CFG->wwwroot ?>/settings" onsubmit="return confirm('آپدیت نصب شود؟');">
                <input type="hidden" name="action" value="update">
                <button type="submit" class="px-4 py-2 rounded-xl bg-teal-600 text-white hover:bg-teal-700" <?= $hasUpdate ? '' : 'disabled' ?>>نصب آپدیت</button>
            </form>
        </div>

        <div class="mt-4 text-xs text-slate-500">
            مسیرهای محافظت‌شده هنگام آپدیت: <code>app/config.php</code> و <code>data/</code>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
