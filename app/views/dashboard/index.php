<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">
    <div class="rounded-3xl glass-card p-6">
        <h1 class="text-2xl font-bold mb-2">داشبورد مدیریت</h1>
        <?php if (is_array($activeTerm ?? null) && !empty($activeTerm['name'])): ?>
            <p class="text-slate-600">ترم فعال: <?= htmlspecialchars((string)$activeTerm['name'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php else: ?>
            <p class="text-amber-700">در حال حاضر ترم فعالی تنظیم نشده است. لطفاً از بخش ترم‌ها بازه زمانی پروژه را تنظیم کنید.</p>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="rounded-2xl glass-card p-4">
            <div class="text-sm text-slate-500">دانش‌آموزان</div>
            <div class="text-2xl font-bold"><?= (int)$stats['students'] ?></div>
        </div>
        <div class="rounded-2xl glass-card p-4">
            <div class="text-sm text-slate-500">کلاس‌ها</div>
            <div class="text-2xl font-bold"><?= (int)$stats['classes'] ?></div>
        </div>
        <div class="rounded-2xl glass-card p-4">
            <div class="text-sm text-slate-500">ثبت‌نام‌ها</div>
            <div class="text-2xl font-bold"><?= (int)$stats['enrolls'] ?></div>
        </div>
        <div class="rounded-2xl glass-card p-4">
            <div class="text-sm text-slate-500">معلمان</div>
            <div class="text-2xl font-bold"><?= (int)$stats['teachers'] ?></div>
        </div>
        <div class="rounded-2xl glass-card p-4">
            <div class="text-sm text-slate-500">دوره‌ها</div>
            <div class="text-2xl font-bold"><?= (int)$stats['courses'] ?></div>
        </div>
    </div>

    <div class="rounded-3xl glass-card p-6">
        <h2 class="text-lg font-bold mb-4">آمار سریع ترم فعال</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-white/60 p-4">
                <div class="text-sm text-slate-500">کلاس‌های ترم</div>
                <div class="text-xl font-semibold"><?= (int)($stats['term']['classes'] ?? 0) ?></div>
            </div>
            <div class="rounded-2xl bg-white/60 p-4">
                <div class="text-sm text-slate-500">ثبت‌نام‌های ترم</div>
                <div class="text-xl font-semibold"><?= (int)($stats['term']['enrolls'] ?? 0) ?></div>
            </div>
            <div class="rounded-2xl bg-white/60 p-4">
                <div class="text-sm text-slate-500">دانش‌آموزان فعال</div>
                <div class="text-xl font-semibold"><?= (int)($stats['term']['active_students'] ?? 0) ?></div>
            </div>
            <div class="rounded-2xl bg-white/60 p-4">
                <div class="text-sm text-slate-500">میانگین ثبت‌نام هر دانش‌آموز</div>
                <div class="text-xl font-semibold"><?= htmlspecialchars((string)($stats['term']['avg_enroll_per_student'] ?? 0), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
