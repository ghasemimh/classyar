<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <?php if (!empty($msg)): ?>
        <div class="mb-4 p-3 rounded-2xl bg-amber-100 text-amber-800 border border-amber-200">
            <?= htmlspecialchars((string)$msg, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="mb-6 rounded-3xl glass-card p-4 flex items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold">مدیریت ثبت‌نام</h1>
            <div class="text-sm text-slate-600">ترم: <?= htmlspecialchars((string)($term['name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <a
            href="<?= htmlspecialchars($CFG->wwwroot . '/enroll/admin/export', ENT_QUOTES, 'UTF-8') ?>"
            data-no-loader="1"
            class="inline-flex items-center px-4 py-2 rounded-xl bg-sky-600 text-white text-sm font-semibold hover:bg-sky-700 transition"
        >
            خروجی CSV
        </a>
    </div>

    <div class="overflow-x-auto rounded-3xl glass-card">
        <table class="w-full min-w-[1100px] text-sm">
            <thead class="bg-white/80 text-slate-600">
                <tr>
                    <th class="px-3 py-2 border">شناسه دانش‌آموز</th>
                    <th class="px-3 py-2 border">نام</th>
                    <th class="px-3 py-2 border">زمان شروع</th>
                    <th class="px-3 py-2 border">زمان پایان</th>
                    <th class="px-3 py-2 border">دسته‌های الزامیِ باقی‌مانده</th>
                    <th class="px-3 py-2 border">زمان‌های آزاد</th>
                    <th class="px-3 py-2 border">وضعیت</th>
                    <th class="px-3 py-2 border">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <?php
                        $s = $r['student'];
                        $u = $r['user'];
                        $m = $r['messages'];
                        $name = $u['fullname'] ?? ('دانش‌آموز #' . (int)$s['id']);
                    ?>
                    <tr class="hover:bg-white/60">
                        <td class="px-3 py-2 border"><?= htmlspecialchars((string)$s['id'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="px-3 py-2 border font-semibold"><?= htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="px-3 py-2 border"><?= htmlspecialchars((string)$r['open_time'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="px-3 py-2 border"><?= htmlspecialchars((string)$r['close_time'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="px-3 py-2 border">
                            <?php if (!empty($m['missing_categories'])): ?>
                                <span class="text-rose-700"><?= htmlspecialchars(implode(' , ', $m['missing_categories']), ENT_QUOTES, 'UTF-8') ?></span>
                            <?php else: ?>
                                <span class="text-emerald-700">ندارد</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-2 border">
                            <?php if (!empty($m['free_times'])): ?>
                                <?= htmlspecialchars(implode(' , ', $m['free_times']), ENT_QUOTES, 'UTF-8') ?>
                            <?php else: ?>
                                <span class="text-emerald-700">ندارد</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-2 border">
                            <?php if (!empty($m['finished'])): ?>
                                <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">کامل</span>
                            <?php else: ?>
                                <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-700">ناقص</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-2 border">
                            <a class="px-3 py-1 rounded-lg bg-sky-600 text-white hover:bg-sky-700" href="<?= $CFG->wwwroot ?>/enroll/admin/student/<?= (int)$s['id'] ?>">
                                مشاهده / ویرایش
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="8" class="px-3 py-4 text-center text-slate-500">دانش‌آموزی یافت نشد.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
