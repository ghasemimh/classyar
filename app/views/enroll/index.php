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

$studentDisplay = (string)($studentDisplayName ?? ('دانش‌آموز #' . (int)($student['id'] ?? 0)));
$initialPayload = [
    'student_id' => (int)($student['id'] ?? 0),
    'student_name' => $studentDisplay,
    'term' => [
        'id' => (int)($term['id'] ?? 0),
        'name' => (string)($term['name'] ?? ''),
        'active' => (bool)$isEditable,
    ],
    'time' => (string)$time,
    'times' => $times,
    'times_map' => $timesMap,
    'is_editable' => (bool)$isEditable,
    'admin_mode' => (bool)$adminMode,
    'classes' => $classes,
    'program' => $program,
    'messages' => $enrollMessages,
    'teacher_names' => $teacherNames,
    'required_category_names' => $requiredCategoryNames,
];
?>

<div class="max-w-7xl mx-auto px-4 py-8" id="enrollRoot" data-base-path="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" data-admin-mode="<?= $adminMode ? '1' : '0' ?>">
    <div class="mb-4 rounded-2xl glass-card p-4">
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <?php if ($adminMode): ?>
                <a href="<?= htmlspecialchars($backUrl ?? ($CFG->wwwroot . '/enroll/admin')) ?>" class="px-3 py-1 rounded-lg bg-slate-700 text-white hover:bg-slate-800">بازگشت</a>
                <span class="mx-2 text-slate-400">|</span>
                <span class="font-bold">دانش‌آموز:</span>
                <span id="enrollStudentName"><?= htmlspecialchars($studentDisplay, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="mx-2 text-slate-400">|</span>
            <?php endif; ?>
            <span class="font-bold">ترم:</span>
            <span><?= htmlspecialchars((string)($term['name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
            <span class="mx-2 text-slate-400">|</span>
            <span class="font-bold">وضعیت ثبت‌نام:</span>
            <span id="enrollEditableBadge" class="px-2 py-1 rounded-full <?= $isEditable ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                <?= $isEditable ? 'باز' : 'بسته' ?>
            </span>
        </div>
    </div>

    <div id="enrollActionMessage" class="mb-4 p-3 rounded-2xl border hidden"></div>
    <?php if (!empty($message)): ?>
        <script>window.__ENROLL_SERVER_MESSAGE = <?= json_encode((string)$message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
    <?php endif; ?>

    <div class="mb-6 p-4 rounded-3xl glass-card">
        <div class="flex flex-wrap gap-2" id="enrollTimesNav">
            <?php foreach ($times as $t): ?>
                <a class="js-time-link px-4 py-2 rounded-xl border <?= ((string)$time === (string)$t['id']) ? 'bg-teal-600 text-white border-teal-600' : 'bg-white/70 text-slate-700 border-slate-200 hover:border-teal-300' ?>"
                   data-time="<?= htmlspecialchars((string)$t['id'], ENT_QUOTES, 'UTF-8') ?>"
                   href="<?= htmlspecialchars($timePath($t['id']), ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars((string)($t['label'] ?? ('زنگ ' . $t['id'])), ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <section class="rounded-3xl glass-card overflow-hidden">
            <div class="px-4 py-3 bg-sky-50 border-b border-sky-100 font-bold">کلاس‌های قابل انتخاب - <span id="enrollCurrentTimeLabel"><?= htmlspecialchars((string)$currentTimeLabel, ENT_QUOTES, 'UTF-8') ?></span></div>
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
                    <tbody id="enrollClassesBody">
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
                                <td class="px-3 py-2 border"><?= htmlspecialchars((string)($cls['category_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-3 py-2 border font-semibold"><?= htmlspecialchars((string)($cls['course_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-3 py-2 border"><?= htmlspecialchars((string)$teacherName, ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-3 py-2 border"><?= htmlspecialchars((string)implode(' ، ', $timeLabels), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-3 py-2 border text-slate-500"><?= htmlspecialchars((string)($cls['prerequisite_text'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-3 py-2 border"><?= htmlspecialchars((string)($cls['price'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-3 py-2 border"><?= htmlspecialchars((string)($cls['seat_left'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-3 py-2 border">
                                    <?php if ($isEditable): ?>
                                        <button class="js-enroll-btn px-3 py-1 rounded-lg bg-teal-600 text-white hover:bg-teal-700" type="button" data-action="add" data-class-id="<?= (int)$cls['id'] ?>">افزودن</button>
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
                    <tbody id="enrollProgramBody">
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
                                <td class="px-3 py-2 border"><?= htmlspecialchars((string)implode(' ، ', $timeLabels), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-3 py-2 border"><?= htmlspecialchars((string)($row['category_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-3 py-2 border font-semibold"><?= htmlspecialchars((string)($row['course_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-3 py-2 border"><?= htmlspecialchars((string)$teacherName, ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-3 py-2 border"><?= htmlspecialchars((string)($row['room_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-3 py-2 border"><?= htmlspecialchars((string)($row['price'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-3 py-2 border">
                                    <?php if ($isEditable): ?>
                                        <button class="js-enroll-btn px-3 py-1 rounded-lg bg-rose-600 text-white hover:bg-rose-700" type="button" data-action="remove" data-class-id="<?= (int)$row['class_id'] ?>">حذف</button>
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

            <div class="p-4 border-t bg-white/60 space-y-2 text-sm" id="enrollSummary">
                <?php if (!empty($requiredCategoryNames)): ?>
                    <div class="font-semibold">دسته‌های اجباری: <?= htmlspecialchars((string)implode('، ', $requiredCategoryNames), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <?php if (!empty($enrollMessages['missing_categories'])): ?>
                    <div class="text-rose-700">دسته‌های اجباریِ تکمیل‌نشده: <?= htmlspecialchars((string)implode('، ', $enrollMessages['missing_categories']), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <?php if (!empty($enrollMessages['free_times'])): ?>
                    <div class="text-amber-700">زنگ‌های خالی: <?= htmlspecialchars((string)implode('، ', $enrollMessages['free_times']), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <?php if (!empty($enrollMessages['finished'])): ?>
                    <div class="text-emerald-700 font-bold">برنامه کامل است.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<script>
(function() {
    const root = document.getElementById('enrollRoot');
    if (!root) return;

    const basePath = root.getAttribute('data-base-path') || '';
    const csrfField = window.CSRF_FIELD || '_csrf';
    const csrfToken = window.CSRF_TOKEN || '';
    const requiredCategoryNames = <?= json_encode(array_values($requiredCategoryNames ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let payload = <?= json_encode($initialPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function toArray(value) {
        return Array.isArray(value) ? value : [];
    }

    function splitTimes(value) {
        if (Array.isArray(value)) {
            return value.map(v => String(v).trim()).filter(Boolean);
        }
        return String(value || '').split(',').map(v => v.trim()).filter(Boolean);
    }

    function timeLabel(id, map) {
        const key = String(id);
        return (map && map[key]) ? map[key] : ('زنگ ' + key);
    }

    function showMessage(text, ok = true) {
        const box = document.getElementById('enrollActionMessage');
        if (!box) return;
        const clean = String(text || '').trim();
        if (!clean) {
            box.classList.add('hidden');
            return;
        }
        box.textContent = clean;
        box.className = 'mb-4 p-3 rounded-2xl border ' + (ok ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-rose-100 text-rose-800 border-rose-200');
        box.classList.remove('hidden');
    }

    function renderTimes(data) {
        const currentTime = String(data.time || '');
        const nav = document.getElementById('enrollTimesNav');
        if (!nav) return;
        nav.querySelectorAll('.js-time-link').forEach((a) => {
            const t = String(a.getAttribute('data-time') || '');
            if (t === currentTime) {
                a.className = 'js-time-link px-4 py-2 rounded-xl border bg-teal-600 text-white border-teal-600';
            } else {
                a.className = 'js-time-link px-4 py-2 rounded-xl border bg-white/70 text-slate-700 border-slate-200 hover:border-teal-300';
            }
        });
    }

    function renderEditableBadge(data) {
        const badge = document.getElementById('enrollEditableBadge');
        if (!badge) return;
        const editable = !!data.is_editable;
        badge.textContent = editable ? 'باز' : 'بسته';
        badge.className = 'px-2 py-1 rounded-full ' + (editable ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700');
    }

    function renderCurrentTimeLabel(data) {
        const target = document.getElementById('enrollCurrentTimeLabel');
        if (!target) return;
        target.textContent = timeLabel(data.time, data.times_map || {});
    }

    function renderClasses(data) {
        const tbody = document.getElementById('enrollClassesBody');
        if (!tbody) return;

        const rows = toArray(data.classes);
        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="px-3 py-4 text-center text-slate-500">برای این زنگ کلاس فعالی وجود ندارد.</td></tr>';
            return;
        }

        const editable = !!data.is_editable;
        const teacherNames = data.teacher_names || {};
        const timesMap = data.times_map || {};

        tbody.innerHTML = rows.map((cls) => {
            const teacherName = teacherNames[Number(cls.teacher_mdl_id || 0)] || 'نامشخص';
            const labels = splitTimes(cls.time).map((id) => timeLabel(id, timesMap));
            const actionHtml = editable
                ? `<button class="js-enroll-btn px-3 py-1 rounded-lg bg-teal-600 text-white hover:bg-teal-700" type="button" data-action="add" data-class-id="${Number(cls.id || 0)}">افزودن</button>`
                : '<span class="text-xs text-slate-400">بسته</span>';

            return `<tr class="hover:bg-white/60">
                <td class="px-3 py-2 border">${escapeHtml(cls.category_name || '-')}</td>
                <td class="px-3 py-2 border font-semibold">${escapeHtml(cls.course_name || '-')}</td>
                <td class="px-3 py-2 border">${escapeHtml(teacherName)}</td>
                <td class="px-3 py-2 border">${escapeHtml(labels.join(' ، '))}</td>
                <td class="px-3 py-2 border text-slate-500">${escapeHtml(cls.prerequisite_text || '-')}</td>
                <td class="px-3 py-2 border">${escapeHtml(cls.price ?? 0)}</td>
                <td class="px-3 py-2 border">${escapeHtml(cls.seat_left ?? 0)}</td>
                <td class="px-3 py-2 border">${actionHtml}</td>
            </tr>`;
        }).join('');
    }

    function renderProgram(data) {
        const tbody = document.getElementById('enrollProgramBody');
        if (!tbody) return;

        const rows = toArray(data.program);
        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-3 py-4 text-center text-slate-500">هنوز کلاسی انتخاب نشده است.</td></tr>';
            return;
        }

        const editable = !!data.is_editable;
        const teacherNames = data.teacher_names || {};
        const timesMap = data.times_map || {};

        tbody.innerHTML = rows.map((row) => {
            const teacherName = teacherNames[Number(row.teacher_mdl_id || 0)] || 'نامشخص';
            const labels = splitTimes(row.time).map((id) => timeLabel(id, timesMap));
            const actionHtml = editable
                ? `<button class="js-enroll-btn px-3 py-1 rounded-lg bg-rose-600 text-white hover:bg-rose-700" type="button" data-action="remove" data-class-id="${Number(row.class_id || 0)}">حذف</button>`
                : '<span class="text-xs text-slate-400">بسته</span>';

            return `<tr class="hover:bg-white/60">
                <td class="px-3 py-2 border">${escapeHtml(labels.join(' ، '))}</td>
                <td class="px-3 py-2 border">${escapeHtml(row.category_name || '-')}</td>
                <td class="px-3 py-2 border font-semibold">${escapeHtml(row.course_name || '-')}</td>
                <td class="px-3 py-2 border">${escapeHtml(teacherName)}</td>
                <td class="px-3 py-2 border">${escapeHtml(row.room_name || '-')}</td>
                <td class="px-3 py-2 border">${escapeHtml(row.price ?? 0)}</td>
                <td class="px-3 py-2 border">${actionHtml}</td>
            </tr>`;
        }).join('');
    }

    function renderSummary(data) {
        const box = document.getElementById('enrollSummary');
        if (!box) return;

        const messages = data.messages || {};
        const parts = [];

        if (requiredCategoryNames.length > 0) {
            parts.push(`<div class="font-semibold">دسته‌های اجباری: ${escapeHtml(requiredCategoryNames.join('، '))}</div>`);
        }
        const missing = toArray(messages.missing_categories);
        if (missing.length > 0) {
            parts.push(`<div class="text-rose-700">دسته‌های اجباریِ تکمیل‌نشده: ${escapeHtml(missing.join('، '))}</div>`);
        }
        const freeTimes = toArray(messages.free_times);
        if (freeTimes.length > 0) {
            parts.push(`<div class="text-amber-700">زنگ‌های خالی: ${escapeHtml(freeTimes.join('، '))}</div>`);
        }
        if (messages.finished) {
            parts.push('<div class="text-emerald-700 font-bold">برنامه کامل است.</div>');
        }

        box.innerHTML = parts.length > 0 ? parts.join('') : '<div class="text-slate-500">موردی برای نمایش وجود ندارد.</div>';
    }

    function renderAll(data) {
        payload = data;
        renderTimes(data);
        renderEditableBadge(data);
        renderCurrentTimeLabel(data);
        renderClasses(data);
        renderProgram(data);
        renderSummary(data);
    }

    function endpointForTime(time) {
        return `${basePath}/${encodeURIComponent(String(time || payload.time || ''))}`;
    }

    async function fetchPayload(targetTime) {
        const url = `${endpointForTime(targetTime)}?format=json`;
        const res = await fetch(url, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        return res.json();
    }

    async function submitAction(action, classId) {
        const url = `${endpointForTime(payload.time)}?format=json`;
        const body = new URLSearchParams();
        body.set(csrfField, csrfToken);
        body.set(action === 'add' ? 'add_class_id' : 'remove_class_id', String(classId));

        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: body.toString(),
            credentials: 'same-origin'
        });
        return res.json();
    }

    async function withBusy(task) {
        try {
            if (window.ClassyarLoader && typeof window.ClassyarLoader.show === 'function') {
                window.ClassyarLoader.show();
            }
            return await task();
        } finally {
            if (window.ClassyarLoader && typeof window.ClassyarLoader.hide === 'function') {
                window.ClassyarLoader.hide();
            }
        }
    }

    document.getElementById('enrollTimesNav')?.addEventListener('click', async function(ev) {
        const a = ev.target.closest('.js-time-link');
        if (!a) return;
        ev.preventDefault();
        const t = a.getAttribute('data-time') || '';
        if (!t || String(payload.time) === String(t)) return;

        try {
            const out = await withBusy(() => fetchPayload(t));
            if (out && out.data) {
                showMessage(out.msg || '', !!out.success);
                renderAll(out.data);
            }
        } catch (err) {
            showMessage('خطا در به‌روزرسانی اطلاعات ثبت‌نام.', false);
        }
    });

    root.addEventListener('click', async function(ev) {
        const btn = ev.target.closest('.js-enroll-btn');
        if (!btn) return;
        ev.preventDefault();

        const action = (btn.getAttribute('data-action') || '').toLowerCase();
        const classId = Number(btn.getAttribute('data-class-id') || 0);
        if (!['add', 'remove'].includes(action) || classId <= 0) return;

        try {
            const out = await withBusy(() => submitAction(action, classId));
            if (out && out.data) {
                showMessage(out.msg || '', !!out.success);
                renderAll(out.data);
            } else {
                showMessage((out && out.msg) ? out.msg : 'پاسخ نامعتبر از سرور دریافت شد.', false);
            }
        } catch (err) {
            showMessage('خطا در ثبت عملیات. لطفا دوباره تلاش کنید.', false);
        }
    });

    renderAll(payload);
    if (window.__ENROLL_SERVER_MESSAGE) {
        showMessage(window.__ENROLL_SERVER_MESSAGE, true);
    }
})();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
