<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<style>
.print-wrap {
    max-width: 1100px;
    margin: 0 auto;
    padding: 1rem;
}
.print-toolbar {
    display: flex;
    gap: 0.5rem;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}
.print-page {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1rem;
}
.print-title {
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}
.print-meta {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.4rem;
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
}
.print-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}
.print-table th,
.print-table td {
    border: 1px solid #d1d5db;
    padding: 0.35rem;
    text-align: center;
}
.print-table th {
    background: #f3f4f6;
}
@media (max-width: 768px) {
    .print-meta {
        grid-template-columns: 1fr;
    }
}
@media print {
    header, footer, .print-toolbar {
        display: none !important;
    }
    .app-main {
        margin: 0 !important;
        padding: 0 !important;
    }
    .print-wrap {
        max-width: none;
        padding: 0;
    }
    .print-page {
        border: none;
        border-radius: 0;
        margin: 0;
        padding: 10mm;
        page-break-after: always;
    }
}
</style>

<div class="print-wrap">
    <div class="print-toolbar">
        <div class="text-sm text-slate-700">
            معلم: <b><?= htmlspecialchars($teacherName ?? '---') ?></b>
            | ترم: <b><?= htmlspecialchars($activeTerm['name'] ?? '---') ?></b>
        </div>
        <div class="flex gap-2">
            <a href="<?= $CFG->wwwroot ?>/teacher" class="px-3 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm">بازگشت</a>
            <button onclick="window.print()" class="px-3 py-2 rounded-xl bg-teal-600 text-white text-sm">چاپ</button>
        </div>
    </div>

    <?php if (empty($classes)): ?>
        <div class="print-page">
            <p>کلاسی برای این معلم در ترم جاری ثبت نشده است.</p>
        </div>
    <?php else: ?>
        <?php foreach ($classes as $class): ?>
            <section class="print-page">
                <div class="print-title">
                    <?= htmlspecialchars($class['course_name'] ?? '-') ?>
                    <a href="<?= $CFG->wwwroot ?>/teacher/print/class/<?= (int)$class['id'] ?>" class="text-xs text-teal-700 underline mr-2 single-print-link">
                        لیست تک‌درس
                    </a>
                </div>
                <div class="print-meta">
                    <div>زمان: <?= htmlspecialchars($class['time'] ?? '-') ?></div>
                    <div>مکان: <?= htmlspecialchars($class['room_name'] ?? '-') ?></div>
                    <div>تعداد ثبت‌نام: <?= (int)($class['enrolled_count'] ?? 0) ?></div>
                    <div>کد دوره: <?= htmlspecialchars($class['course_crsid'] ?? '-') ?></div>
                </div>

                <table class="print-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام</th>
                            <th>نام خانوادگی</th>
                            <th>پایه/شناسه</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($class['roster'])): ?>
                            <tr>
                                <td colspan="10">دانش‌آموزی ثبت‌نام نشده است.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($class['roster'] as $idx => $student): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><?= htmlspecialchars($student['firstname'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($student['lastname'] ?? '') ?></td>
                                    <td><?= htmlspecialchars((string)($student['grade'] ?? '-')) ?></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
