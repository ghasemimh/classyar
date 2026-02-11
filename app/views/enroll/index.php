<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';

$timesMap = [];
foreach ($times as $t) {
    $timesMap[(string)$t['id']] = $t['label'] ?? ('زنگ ' . $t['id']);
}

$currentTimeLabel = $timesMap[(string)$time] ?? ('زنگ ' . htmlspecialchars((string)$time));
$adminMode = !empty($adminMode);
$basePath = $adminMode ? ($CFG->wwwroot . '/enroll/admin/student/' . (int)$student['id']) : ($CFG->wwwroot . '/enroll');
$timePath = function ($t) use ($basePath) {
    return $basePath . '/' . urlencode((string)$t);
};
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-4 rounded-2xl glass-card p-4">
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <?php if ($adminMode): ?>
                <a href="<?= htmlspecialchars($backUrl ?? ($CFG->wwwroot . '/enroll/admin')) ?>" class="px-3 py-1 rounded-lg bg-slate-700 text-white hover:bg-slate-800">بازگشت</a>
                <span class="mx-2 text-slate-400">|</span>
                <span class="font-bold">دانش‌آموز:</span>
                <span>#<?= htmlspecialchars((string)$student['id']) ?></span>
                <span class="mx-2 text-slate-400">|</span>
            <?php endif; ?>
            <span class="font-bold">ترم:</span>
            <span><?= htmlspecialchars($term['name'] ?? '-') ?></span>
            <span class="mx-2 text-slate-400">|</span>
            <span class="font-bold">وضعیت ثبت‌نام:</span>
            <?php if ($isEditable): ?>
                <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">باز</span>
            <?php else: ?>
                <span class="px-2 py-1 rounded-full bg-rose-100 text-rose-700">بسته</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="mb-4 p-3 rounded-2xl bg-blue-100 text-blue-800 border border-blue-200">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="mb-6 p-4 rounded-3xl glass-card">
        <div class="flex flex-wrap gap-2">
            <?php foreach ($times as $t): ?>
                <a class="px-4 py-2 rounded-xl border <?= ((string)$time === (string)$t['id']) ? 'bg-teal-600 text-white border-teal-600' : 'bg-white/70 text-slate-700 border-slate-200 hover:border-teal-300' ?>"
                   href="<?= $timePath($t['id']) ?>">
                    <?= htmlspecialchars((string)$t['id']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <section class="rounded-3xl glass-card overflow-hidden">
            <div class="px-4 py-3 bg-sky-50 border-b border-sky-100 font-bold">کلاس‌های قابل انتخاب - <?= htmlspecialchars($currentTimeLabel) ?></div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead class="bg-white/80 text-slate-600">
                        <tr>
                            <th class="px-3 py-2 border">دسته</th>
                            <th class="px-3 py-2 border">دوره</th>
                            <th class="px-3 py-2 border">معلم</th>
                            <th class="px-3 py-2 border">زمان</th>
                            <th class="px-3 py-2 border">پیش‌نیاز</th>
                            <th class="px-3 py-2 border">هزینه</th>
                            <th class="px-3 py-2 border">ظرفیت</th>
                            <th class="px-3 py-2 border">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $cls): ?>
                            <?php
                                $teacherName = $teacherNames[(int)($cls['teacher_mdl_id'] ?? 0)] ?? 'نامشخص';
                                $timeLabels = [];
                                foreach (explode(',', (string)$cls['time']) as $tt) {
                                    $tt = trim($tt);
                                    if ($tt === '') continue;
                                    $timeLabels[] = $timesMap[$tt] ?? ('زنگ ' . $tt);
                                }
                            ?>
                            <tr class="hover:bg-white/60">
                                <td class="px-3 py-2 border"><?= htmlspecialchars($cls['category_name'] ?? '-') ?></td>
                                <td class="px-3 py-2 border font-semibold"><?= htmlspecialchars($cls['course_name'] ?? '-') ?></td>
                                <td class="px-3 py-2 border"><?= htmlspecialchars($teacherName) ?></td>
                                <td class="px-3 py-2 border"><?= htmlspecialchars(implode(' ، ', $timeLabels)) ?></td>
                                <td class="px-3 py-2 border text-slate-500"><?= htmlspecialchars($cls['prerequisite_text'] ?? '-') ?></td>
                                <td class="px-3 py-2 border"><?= htmlspecialchars((string)($cls['price'] ?? 0)) ?></td>
                                <td class="px-3 py-2 border"><?= htmlspecialchars((string)($cls['seat_left'] ?? 0)) ?></td>
                                <td class="px-3 py-2 border">
                                    <?php if ($isEditable): ?>
                                        <form method="post" action="<?= $timePath($time) ?>">
                                            <input type="hidden" name="add_class_id" value="<?= (int)$cls['id'] ?>">
                                            <button class="px-3 py-1 rounded-lg bg-teal-600 text-white hover:bg-teal-700" type="submit">افزودن</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">بسته</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($classes)): ?>
                            <tr><td colspan="8" class="px-3 py-4 text-center text-slate-500">برای این زنگ کلاس فعالی وجود ندارد.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-3xl glass-card overflow-hidden">
            <div class="px-4 py-3 bg-emerald-50 border-b border-emerald-100 font-bold">برنامه من</div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-sm">
                    <thead class="bg-white/80 text-slate-600">
                        <tr>
                            <th class="px-3 py-2 border">زمان</th>
                            <th class="px-3 py-2 border">دسته</th>
                            <th class="px-3 py-2 border">دوره</th>
                            <th class="px-3 py-2 border">معلم</th>
                            <th class="px-3 py-2 border">مکان</th>
                            <th class="px-3 py-2 border">هزینه</th>
                            <th class="px-3 py-2 border">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($program as $row): ?>
                            <?php
                                $teacherName = $teacherNames[(int)($row['teacher_mdl_id'] ?? 0)] ?? 'نامشخص';
                                $timeLabels = [];
                                foreach (explode(',', (string)$row['time']) as $tt) {
                                    $tt = trim($tt);
                                    if ($tt === '') continue;
                                    $timeLabels[] = $timesMap[$tt] ?? ('زنگ ' . $tt);
                                }
                            ?>
                            <tr class="hover:bg-white/60">
                                <td class="px-3 py-2 border"><?= htmlspecialchars(implode(' ، ', $timeLabels)) ?></td>
                                <td class="px-3 py-2 border"><?= htmlspecialchars($row['category_name'] ?? '-') ?></td>
                                <td class="px-3 py-2 border font-semibold"><?= htmlspecialchars($row['course_name'] ?? '-') ?></td>
                                <td class="px-3 py-2 border"><?= htmlspecialchars($teacherName) ?></td>
                                <td class="px-3 py-2 border"><?= htmlspecialchars($row['room_name'] ?? '-') ?></td>
                                <td class="px-3 py-2 border"><?= htmlspecialchars((string)($row['price'] ?? 0)) ?></td>
                                <td class="px-3 py-2 border">
                                    <?php if ($isEditable): ?>
                                        <form method="post" action="<?= $timePath($time) ?>">
                                            <input type="hidden" name="remove_class_id" value="<?= (int)$row['class_id'] ?>">
                                            <button class="px-3 py-1 rounded-lg bg-rose-600 text-white hover:bg-rose-700" type="submit">حذف</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">بسته</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($program)): ?>
                            <tr><td colspan="7" class="px-3 py-4 text-center text-slate-500">هنوز کلاسی انتخاب نشده است.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t bg-white/60 space-y-2 text-sm">
                <?php if (!empty($requiredCategoryNames)): ?>
                    <div class="font-semibold">دسته‌های اجباری: <?= htmlspecialchars(implode('، ', $requiredCategoryNames)) ?></div>
                <?php endif; ?>

                <?php if (!empty($enrollMessages['missing_categories'])): ?>
                    <div class="text-rose-700">دسته‌های اجباریِ تکمیل‌نشده: <?= htmlspecialchars(implode('، ', $enrollMessages['missing_categories'])) ?></div>
                <?php endif; ?>

                <?php if (!empty($enrollMessages['free_times'])): ?>
                    <div class="text-amber-700">زنگ‌های خالی: <?= htmlspecialchars(implode('، ', $enrollMessages['free_times'])) ?></div>
                <?php endif; ?>

                <?php if (!empty($enrollMessages['finished'])): ?>
                    <div class="text-emerald-700 font-bold">برنامه کامل است.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
