<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-6xl mx-auto px-4 py-8 space-y-6">
    <section class="rounded-3xl glass-card p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800"><?= htmlspecialchars((string)($classRow['course_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></h1>
                <div class="text-sm text-slate-600 mt-1">معلم: <?= htmlspecialchars((string)$teacherName, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= $CFG->wwwroot ?>/panel" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 text-sm font-semibold">بازگشت</a>
                <a href="<?= $CFG->wwwroot ?>/classroom/<?= (int)$classRow['id'] ?>/csv" data-no-loader="1" class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-semibold">خروجی CSV</a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4 text-sm">
            <div class="rounded-xl bg-white/70 border border-slate-200/70 p-3"><div class="text-slate-500 text-xs">ترم</div><div class="font-semibold text-slate-800"><?= htmlspecialchars((string)($classRow['term_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div></div>
            <div class="rounded-xl bg-white/70 border border-slate-200/70 p-3"><div class="text-slate-500 text-xs">مکان</div><div class="font-semibold text-slate-800"><?= htmlspecialchars((string)($classRow['room_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div></div>
            <div class="rounded-xl bg-white/70 border border-slate-200/70 p-3"><div class="text-slate-500 text-xs">کد دوره</div><div class="font-semibold text-slate-800"><?= htmlspecialchars((string)($classRow['course_crsid'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div></div>
            <div class="rounded-xl bg-white/70 border border-slate-200/70 p-3"><div class="text-slate-500 text-xs">تعداد دانش‌آموز</div><div class="font-semibold text-slate-800"><?= count($roster) ?></div></div>
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
            <?php foreach ($classTimeLabels as $label): ?>
                <span class="px-2 py-1 rounded-full text-xs bg-sky-100 text-sky-700 border border-sky-200"><?= htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl glass-card overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200/70 flex items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-slate-800">لیست دانش‌آموزان</h2>
            <div class="text-xs text-slate-500"><?= count($roster) ?> نفر</div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-[900px] w-full text-sm">
                <thead class="bg-white/70 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-right">#</th>
                        <th class="px-4 py-3 text-right">نام و نام‌خانوادگی</th>
                        <th class="px-4 py-3 text-right">پایه/شناسه</th>
                        <th class="px-4 py-3 text-right">ایمیل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($roster)): ?>
                        <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">دانش‌آموزی ثبت‌نام نشده است.</td></tr>
                    <?php else: ?>
                        <?php foreach ($roster as $idx => $student): ?>
                            <tr class="border-t border-slate-200/60 hover:bg-white/60 transition">
                                <td class="px-4 py-3 text-slate-500"><?= $idx + 1 ?></td>
                                <td class="px-4 py-3 font-semibold text-slate-800"><?= htmlspecialchars((string)($student['fullname'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars((string)($student['grade'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars((string)($student['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
