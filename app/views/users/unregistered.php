<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';

$csrf = htmlspecialchars((string)($_SESSION['_csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8');
$canManageUsers = (string)($_SESSION['USER']->role ?? 'guest') === 'admin';
$newUsers = is_array($newUsers ?? null) ? $newUsers : [];

$roleLabels = [
    'admin' => 'ادمین',
    'guide' => 'راهنما',
    'teacher' => 'معلم',
    'student' => 'دانش‌آموز',
];

$roleButtonClasses = [
    'admin' => 'bg-rose-600 hover:bg-rose-700',
    'guide' => 'bg-amber-600 hover:bg-amber-700',
    'teacher' => 'bg-emerald-600 hover:bg-emerald-700',
    'student' => 'bg-sky-600 hover:bg-sky-700',
];
?>

<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">
    <div class="rounded-3xl glass-card p-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">کاربران ثبت‌نام‌نشده</h1>
            <p class="text-sm text-slate-600 mt-1">کاربرانی که در مودل هستند اما هنوز در کلاسیار نقش ندارند.</p>
        </div>
        <a href="<?= htmlspecialchars($CFG->wwwroot . '/users', ENT_QUOTES, 'UTF-8') ?>"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-600 text-white text-sm font-semibold hover:bg-slate-700 transition">
            <span>بازگشت به مدیریت کاربران</span>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="rounded-2xl glass-card p-4">
            <div class="text-sm text-slate-500">تعداد کاربران ثبت‌نام‌نشده</div>
            <div class="text-2xl font-bold"><?= count($newUsers) ?></div>
        </div>
        <div class="rounded-2xl glass-card p-4">
            <div class="text-sm text-slate-500">وضعیت دسترسی</div>
            <div class="text-sm font-bold mt-1 <?= $canManageUsers ? 'text-emerald-700' : 'text-slate-700' ?>">
                <?= $canManageUsers ? 'ادمین: امکان ثبت کاربر با نقش' : 'راهنما: فقط مشاهده' ?>
            </div>
        </div>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="rounded-2xl glass-card p-3 bg-amber-100/80 text-amber-800 border border-amber-200">
            <?= htmlspecialchars((string)$msg, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (count($newUsers) === 0): ?>
        <div class="rounded-2xl glass-card p-4 bg-emerald-100/80 text-emerald-800 border border-emerald-200">
            همه کاربران Moodle در سیستم ثبت شده‌اند.
        </div>
    <?php else: ?>
        <section class="rounded-3xl glass-card p-5 space-y-4">
            <div class="overflow-x-auto rounded-2xl border border-white/70">
                <table class="min-w-[1100px] w-full text-sm">
                    <thead class="bg-white/80 text-slate-600">
                        <tr>
                            <th class="px-3 py-2 border">شناسه مودل</th>
                            <th class="px-3 py-2 border">مشخصات</th>
                            <th class="px-3 py-2 border">ثبت با نقش</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($newUsers as $u): ?>
                        <?php
                            $mdlId = (int)($u['id'] ?? 0);
                            $fullname = (string)($u['fullname'] ?? trim((string)($u['firstname'] ?? '') . ' ' . (string)($u['lastname'] ?? '')));
                            $email = (string)($u['email'] ?? '-');
                            $username = (string)($u['username'] ?? '-');
                            $avatarUrl = trim((string)($u['profileimageurl'] ?? ''));
                            if ($avatarUrl === '') {
                                $avatarUrl = (string)($CFG->assets . '/images/site-icon.png');
                            }
                        ?>
                        <tr class="hover:bg-white/60 transition">
                            <td class="px-3 py-2 border font-mono"><?= $mdlId ?></td>
                            <td class="px-3 py-2 border">
                                <div class="flex items-center gap-3">
                                    <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>"
                                         class="w-10 h-10 rounded-xl object-cover ring-1 ring-white/70"
                                         alt="تصویر کاربر">
                                    <div>
                                        <div class="font-semibold text-slate-800"><?= htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-xs text-slate-500"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-xs text-slate-500"><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-2 border">
                                <?php if ($canManageUsers): ?>
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach ($roleLabels as $role => $label): ?>
                                            <form method="post" action="<?= htmlspecialchars($CFG->wwwroot . '/users/add', ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                                                <input type="hidden" name="role" value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="mdl_id" value="<?= $mdlId ?>">
                                                <button type="submit"
                                                        class="px-3 py-2 rounded-xl text-white text-xs font-semibold transition <?= htmlspecialchars($roleButtonClasses[$role], ENT_QUOTES, 'UTF-8') ?>">
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
        </section>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
