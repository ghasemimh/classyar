<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';
$teacherAvatar = trim((string)($teacherProfile['profileimageurl'] ?? ''));
if ($teacherAvatar === '') {
    $teacherAvatar = (string)($CFG->assets . '/images/site-icon.png');
}
?>

<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">
    <section class="rounded-3xl glass-card p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <img
                    src="<?= htmlspecialchars($teacherAvatar, ENT_QUOTES, 'UTF-8') ?>"
                    alt="Teacher profile"
                    class="w-16 h-16 rounded-2xl object-cover ring-2 ring-white/70 shadow"
                >
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-800">پنل معلم</h1>
                    <p class="text-sm text-slate-600"><?= htmlspecialchars((string)($teacherProfile['fullname'] ?? 'معلم'), ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-xs text-slate-500">ترم فعال: <?= htmlspecialchars((string)($activeTerm['name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
            <a href="<?= $CFG->wwwroot ?>/prints" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 text-sm font-semibold">چاپ لیست‌ها</a>
        </div>
    </section>

    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl glass-card p-4"><div class="text-xs text-slate-500 mb-1">کلاس‌های ترم</div><div class="text-2xl font-bold"><?= (int)($stats['classes_count'] ?? 0) ?></div></div>
        <div class="rounded-2xl glass-card p-4"><div class="text-xs text-slate-500 mb-1">ثبت‌نام‌ها</div><div class="text-2xl font-bold"><?= (int)($stats['enrollments_count'] ?? 0) ?></div></div>
        <div class="rounded-2xl glass-card p-4"><div class="text-xs text-slate-500 mb-1">دانش‌آموز یکتا</div><div class="text-2xl font-bold"><?= (int)($stats['students_count'] ?? 0) ?></div></div>
        <div class="rounded-2xl glass-card p-4"><div class="text-xs text-slate-500 mb-1">زنگ‌های فعال</div><div class="text-2xl font-bold"><?= (int)($stats['occupied_slots_count'] ?? 0) ?></div></div>
    </section>

    <section class="rounded-3xl glass-card p-5">
        <div class="flex items-center justify-between gap-3 mb-3">
            <h2 class="text-lg font-bold text-slate-800">تقویم هفتگی زنگ‌ها</h2>
            <div class="text-xs text-slate-500">نمای فشرده برنامه معلم بر اساس زنگ‌ها</div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            <?php foreach ($calendar as $slot): ?>
                <div class="rounded-2xl border border-slate-200/70 bg-white/70 p-3">
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-bold text-slate-700"><?= htmlspecialchars((string)$slot['label'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600"><?= count($slot['classes']) ?> کلاس</div>
                    </div>
                    <?php if (empty($slot['classes'])): ?>
                        <div class="text-xs text-slate-400">این زنگ کلاس ندارد.</div>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach ($slot['classes'] as $cls): ?>
                                <a href="<?= $CFG->wwwroot ?>/classroom/<?= (int)$cls['id'] ?>" class="block rounded-xl px-3 py-2 bg-gradient-to-r from-sky-50 to-teal-50 border border-sky-100 hover:from-sky-100 hover:to-teal-100 transition">
                                    <div class="text-sm font-semibold text-slate-800"><?= htmlspecialchars((string)($cls['course_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="text-xs text-slate-600">مکان: <?= htmlspecialchars((string)($cls['room_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> | ثبت‌نام: <?= (int)($cls['enrolled_count'] ?? 0) ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl glass-card overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200/70"><h2 class="text-lg font-bold text-slate-800">لیست کلاس‌های من</h2></div>
        <div class="overflow-x-auto">
            <table class="min-w-[980px] w-full text-sm">
                <thead class="bg-white/70 text-slate-600"><tr><th class="px-4 py-3 text-right">درس</th><th class="px-4 py-3 text-right">زنگ‌ها</th><th class="px-4 py-3 text-right">مکان</th><th class="px-4 py-3 text-right">ثبت‌نام</th><th class="px-4 py-3 text-center">عملیات</th></tr></thead>
                <tbody>
                    <?php if (empty($classes)): ?>
                        <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">کلاسی برای این ترم ثبت نشده است.</td></tr>
                    <?php else: ?>
                        <?php foreach ($classes as $cls): ?>
                            <tr class="border-t border-slate-200/60 hover:bg-white/60 transition">
                                <td class="px-4 py-3 font-semibold text-slate-800"><?= htmlspecialchars((string)($cls['course_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-4 py-3"><div class="flex flex-wrap gap-1"><?php foreach (($cls['time_labels'] ?? []) as $label): ?><span class="px-2 py-1 text-xs rounded-full bg-slate-100 text-slate-600"><?= htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?></div></td>
                                <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars((string)($cls['room_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-4 py-3 text-slate-700"><?= (int)($cls['enrolled_count'] ?? 0) ?></td>
                                <td class="px-4 py-3 text-center"><div class="inline-flex gap-2"><a href="<?= $CFG->wwwroot ?>/classroom/<?= (int)$cls['id'] ?>" class="px-3 py-1.5 rounded-lg bg-sky-600 text-white hover:bg-sky-700 text-xs font-semibold">جزئیات</a><a href="<?= $CFG->wwwroot ?>/classroom/<?= (int)$cls['id'] ?>/csv" data-no-loader="1" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 text-xs font-semibold">CSV</a></div></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
