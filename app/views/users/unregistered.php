<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';

$csrf = htmlspecialchars((string)($_SESSION['_csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8');
$canManageUsers = (string)($_SESSION['USER']->role ?? 'guest') === 'admin';
?>

<div class="max-w-7xl mx-auto px-4 py-8 space-y-5">
    <div class="rounded-3xl glass-card p-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">کاربران ثبت‌نشده</h1>
            <p class="text-sm text-slate-600 mt-1">کاربرانی که در مودل هستند اما هنوز در کلاسیار نقش ندارند.</p>
        </div>
        <a href="<?= htmlspecialchars($CFG->wwwroot . '/users', ENT_QUOTES, 'UTF-8') ?>"
           class="px-4 py-2 rounded-xl bg-slate-600 text-white text-sm font-semibold hover:bg-slate-700 transition">
            بازگشت به مدیریت کاربران
        </a>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="rounded-2xl p-3 bg-amber-100 text-amber-800 border border-amber-200">
            <?= htmlspecialchars((string)$msg, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (empty($newUsers)): ?>
        <div class="rounded-2xl p-4 bg-emerald-100 text-emerald-800 border border-emerald-200">
            همه کاربران Moodle در سیستم ثبت شده‌اند.
        </div>
    <?php else: ?>
        <div class="overflow-x-auto rounded-3xl glass-card">
            <table class="min-w-[1100px] w-full border border-white/60 text-sm">
                <thead class="bg-white/80 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 border">شناسه مودل</th>
                        <th class="px-4 py-3 border">نام کامل</th>
                        <th class="px-4 py-3 border">ایمیل</th>
                        <th class="px-4 py-3 border">نام کاربری</th>
                        <th class="px-4 py-3 border">ثبت با نقش</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($newUsers as $u): ?>
                    <?php
                        $mdlId = (int)($u['id'] ?? 0);
                        $fullname = (string)($u['fullname'] ?? trim((string)($u['firstname'] ?? '') . ' ' . (string)($u['lastname'] ?? '')));
                    ?>
                    <tr class="hover:bg-white/60 transition">
                        <td class="px-4 py-3 border font-mono"><?= $mdlId ?></td>
                        <td class="px-4 py-3 border font-semibold text-slate-800"><?= htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="px-4 py-3 border"><?= htmlspecialchars((string)($u['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="px-4 py-3 border"><?= htmlspecialchars((string)($u['username'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="px-4 py-3 border">
                            <?php if ($canManageUsers): ?>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach (['admin' => 'ادمین', 'guide' => 'راهنما', 'teacher' => 'معلم', 'student' => 'دانش‌آموز'] as $role => $label): ?>
                                        <form method="post" action="<?= htmlspecialchars($CFG->wwwroot . '/users/add', ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                                            <input type="hidden" name="role" value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="mdl_id" value="<?= $mdlId ?>">
                                            <button type="submit"
                                                    class="px-3 py-2 rounded-xl text-white text-xs font-semibold transition hover:opacity-90
                                                    <?= $role === 'admin' ? 'bg-rose-600' : ($role === 'guide' ? 'bg-amber-600' : ($role === 'teacher' ? 'bg-emerald-600' : 'bg-sky-600')) ?>">
                                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                            </button>
                                        </form>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-xs text-slate-500">حالت راهنما: فقط مشاهده</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
