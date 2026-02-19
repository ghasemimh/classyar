<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<style>
.single-print-wrap {
    max-width: 1000px;
    margin: 0 auto;
    padding: 1rem;
}
.single-print-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1rem;
}
.single-print-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    margin-top: 0.8rem;
}
.single-print-table th,
.single-print-table td {
    border: 1px solid #d1d5db;
    padding: 0.4rem;
    text-align: center;
}
.single-print-table th {
    background: #f3f4f6;
}
@media print {
    header, footer, .single-print-toolbar {
        display: none !important;
    }
    .single-print-card {
        border: none;
        border-radius: 0;
        padding: 0;
    }
}
</style>

<div class="single-print-wrap">
    <div class="single-print-toolbar mb-3 flex flex-wrap items-center justify-between gap-2">
        <div class="text-sm text-slate-700">
            درس: <b><?= htmlspecialchars($classRow['course_name'] ?? '-') ?></b>
            | معلم: <b><?= htmlspecialchars($teacherName ?? '-') ?></b>
        </div>
        <div class="flex gap-2">
            <a href="<?= $CFG->wwwroot ?>/program" class="px-3 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm">بازگشت</a>
            <button onclick="window.print()" class="px-3 py-2 rounded-xl bg-teal-600 text-white text-sm">چاپ</button>
        </div>
    </div>

    <section class="single-print-card">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
            <div>ترم: <?= htmlspecialchars($classRow['term_name'] ?? '-') ?></div>
            <div>زمان: <?= htmlspecialchars($classRow['time'] ?? '-') ?></div>
            <div>مکان: <?= htmlspecialchars($classRow['room_name'] ?? '-') ?></div>
            <div>کد دوره: <?= htmlspecialchars($classRow['course_crsid'] ?? '-') ?></div>
        </div>

        <table class="single-print-table">
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
                </tr>
            </thead>
            <tbody>
                <?php if (empty($roster)): ?>
                    <tr><td colspan="8">دانش‌آموزی ثبت‌نام نشده است.</td></tr>
                <?php else: ?>
                    <?php foreach ($roster as $idx => $student): ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td><?= htmlspecialchars($student['firstname'] ?? '') ?></td>
                            <td><?= htmlspecialchars($student['lastname'] ?? '') ?></td>
                            <td><?= htmlspecialchars((string)($student['grade'] ?? '-')) ?></td>
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
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
