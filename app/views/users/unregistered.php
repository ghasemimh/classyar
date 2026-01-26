<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-6xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-4">کاربران ثبت‌نام‌نشده</h1>

    <?php if (!empty($msg)): ?>
        <div class="mb-4 p-3 bg-yellow-100 text-yellow-800 rounded">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($newUsers)): ?>
        <div class="p-4 bg-green-100 text-green-800 rounded">
            همه کاربران Moodle در سیستم ثبت شده‌اند ✅
        </div>
    <?php else: ?>
        <div class="overflow-x-auto bg-white rounded shadow">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border">Moodle ID</th>
                        <th class="px-4 py-2 border">نام کامل</th>
                        <th class="px-4 py-2 border">ایمیل</th>
                        <th class="px-4 py-2 border">یوزرنیم</th>
                        <th class="px-4 py-2 border">ثبت‌نام به‌صورت: </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($newUsers as $u): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="px-4 py-2 border"><?= htmlspecialchars($u['id']) ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($u['fullname'] ?? ($u['firstname'].' '.$u['lastname'])) ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($u['username'] ?? '-') ?></td>
                            <td class="px-4 py-2 border">
                                <a href="#" class="px-4 py-2 rounded-xl bg-gradient-to-r from-red-500 to-pink-600 text-white text-sm font-bold hover:opacity-90 transition">معلم راهنما</a>
                                <a href="#" class="px-4 py-2 rounded-xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-sm font-bold hover:opacity-90 transition">معلم</a>
                                <a href="#" class="px-4 py-2 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-sm font-bold hover:opacity-90 transition">دانش‌آموز</a>
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
