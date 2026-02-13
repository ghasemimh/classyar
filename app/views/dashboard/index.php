<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">
    <div class="rounded-3xl glass-card p-6">
        <h1 class="text-2xl font-bold mb-2">داشبورد ادمین</h1>
        <?php if (!empty($activeTerm['name'])): ?>
            <p class="text-slate-600">ترم فعال: <?= htmlspecialchars((string)$activeTerm['name']) ?></p>
        <?php else: ?>
            <p class="text-slate-600">ترم فعالی شناسایی نشد.</p>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="rounded-2xl glass-card p-4">
            <div class="text-sm text-slate-500">دانش آموز</div>
            <div class="text-2xl font-bold"><?= (int)$stats['students'] ?></div>
        </div>
        <div class="rounded-2xl glass-card p-4">
            <div class="text-sm text-slate-500">کلاس فعال</div>
            <div class="text-2xl font-bold"><?= (int)$stats['classes'] ?></div>
        </div>
        <div class="rounded-2xl glass-card p-4">
            <div class="text-sm text-slate-500">ثبت نام فعال</div>
            <div class="text-2xl font-bold"><?= (int)$stats['enrolls'] ?></div>
        </div>
        <div class="rounded-2xl glass-card p-4">
            <div class="text-sm text-slate-500">معلم</div>
            <div class="text-2xl font-bold"><?= (int)$stats['teachers'] ?></div>
        </div>
        <div class="rounded-2xl glass-card p-4">
            <div class="text-sm text-slate-500">دوره</div>
            <div class="text-2xl font-bold"><?= (int)$stats['courses'] ?></div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
