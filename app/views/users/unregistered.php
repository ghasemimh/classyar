<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-extrabold mb-4">کاربران ثبت‌نام‌نشده</h1>

    <?php if (!empty($msg)): ?>
        <div class="mb-4 p-3 rounded-2xl bg-yellow-100 text-yellow-800 border border-yellow-200">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($newUsers)): ?>
        <div class="p-4 rounded-2xl bg-green-100 text-green-800 border border-green-200">
            همه کاربران Moodle در سیستم ثبت شده‌اند ✅
        </div>
    <?php else: ?>
        <div class="overflow-x-auto rounded-3xl glass-card">
            <table class="min-w-[1100px] w-full border border-white/60 text-sm sm:text-base">
                <thead class="bg-white/80 backdrop-blur sticky top-0 text-sm uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-5 py-4 border">Moodle ID</th>
                        <th class="px-5 py-4 border">نام کامل</th>
                        <th class="px-5 py-4 border">ایمیل</th>
                        <th class="px-5 py-4 border">یوزرنیم</th>
                        <th class="px-5 py-4 border">ثبت‌نام به‌صورت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($newUsers as $u): ?>
                        <tr class="hover:bg-white/60 transition">
                            <td class="px-5 py-4 border whitespace-nowrap font-mono"><?= htmlspecialchars($u['id']) ?></td>
                            <td class="px-5 py-4 border font-semibold text-slate-800"><?= htmlspecialchars($u['fullname'] ?? ($u['firstname'].' '.$u['lastname'])) ?></td>
                            <td class="px-5 py-4 border"><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                            <td class="px-5 py-4 border"><?= htmlspecialchars($u['username'] ?? '-') ?></td>
                            <td class="px-5 py-4 border">
                                <div class="flex flex-wrap gap-2">
                                    <a href="<?= $CFG->wwwroot ?>/user/add/admin/<?= $u['id'] ?>" class="px-4 py-2 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 text-white text-sm font-bold hover:opacity-90 transition whitespace-nowrap">ادمین</a>
                                    <a href="<?= $CFG->wwwroot ?>/user/add/guide/<?= $u['id'] ?>" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white text-sm font-bold hover:opacity-90 transition whitespace-nowrap">معلم راهنما</a>
                                    <a href="<?= $CFG->wwwroot ?>/user/add/teacher/<?= $u['id'] ?>" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-bold hover:opacity-90 transition whitespace-nowrap">معلم</a>
                                    <a href="<?= $CFG->wwwroot ?>/user/add/student/<?= $u['id'] ?>" class="px-4 py-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 text-white text-sm font-bold hover:opacity-90 transition whitespace-nowrap">دانش‌آموز</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
