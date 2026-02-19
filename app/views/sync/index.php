<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-3xl font-extrabold">همگام‌سازی مودل</h2>
        <div class="flex items-center gap-2">
            <label class="text-sm font-semibold text-slate-600">ترم:</label>
            <select id="syncTerm" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                <?php foreach (($terms ?? []) as $t): ?>
                    <option value="<?= (int)$t['id'] ?>" <?= ((int)($term['id'] ?? 0) === (int)$t['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['name'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="mb-6 p-4 rounded-3xl glass-card">
        <div class="flex flex-wrap gap-2">
            <button data-tab="courses" class="sync-tab px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-bold">دروس</button>
            <button data-tab="teachers" class="sync-tab px-4 py-2 rounded-xl bg-white text-slate-700 border border-slate-200 text-sm font-bold">معلمان</button>
            <button data-tab="students" class="sync-tab px-4 py-2 rounded-xl bg-white text-slate-700 border border-slate-200 text-sm font-bold">دانش‌آموزان</button>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <button id="bulkSyncBtn" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-bold">سینک انتخاب‌شده‌ها</button>
            <button id="retryFailedBtn" class="px-4 py-2 rounded-xl bg-amber-600 text-white text-sm font-bold">تلاش مجددِ خطادارها</button>
            <button id="bulkDeleteBtn" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-bold">حذف انتخاب‌شده‌ها</button>
            <button id="refreshBtn" class="px-4 py-2 rounded-xl bg-slate-700 text-white text-sm font-bold">بروزرسانی</button>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-2">
            <input id="syncSearch" type="text" placeholder="جستجو در عنوان / کد / شناسه"
                   class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80 text-sm">
            <select id="statusFilter" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80 text-sm">
                <option value="all">همه وضعیت‌ها</option>
                <option value="synced">فقط سینک‌شده</option>
                <option value="unsynced">فقط سینک‌نشده</option>
            </select>
            <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 bg-white/80 text-sm">
                <input id="errorOnlyFilter" type="checkbox">
                فقط خطادارها
            </label>
            <div id="syncCount" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/60 text-sm text-slate-600"></div>
        </div>
    </div>

    <div id="syncAlert" class="hidden mb-4 p-3 rounded-2xl text-sm font-semibold"></div>

    <div class="overflow-x-auto rounded-3xl glass-card">
        <table class="min-w-[1200px] w-full border border-white/60 text-sm" id="syncTable">
            <thead class="bg-white/80 backdrop-blur sticky top-0 text-xs uppercase text-slate-600">
                <tr>
                    <th class="px-3 py-3 border text-center"><input id="checkAll" type="checkbox"></th>
                    <th class="px-3 py-3 border text-right">شناسه</th>
                    <th class="px-3 py-3 border text-right">عنوان</th>
                    <th class="px-3 py-3 border text-right">نام مختصر</th>
                    <th class="px-3 py-3 border text-right">کد درس</th>
                    <th class="px-3 py-3 border text-right">Moodle ID</th>
                    <th class="px-3 py-3 border text-right">وضعیت</th>
                    <th class="px-3 py-3 border text-right">دلیل/خطا</th>
                    <th class="px-3 py-3 border text-center">عملیات</th>
                </tr>
            </thead>
            <tbody id="syncTableBody"></tbody>
        </table>
    </div>
</div>

<script>
const syncState = {
    tab: 'courses',
    rows: { courses: [], teachers: [], students: [] },
    termId: Number(document.getElementById('syncTerm')?.value || 0),
    filters: {
        q: '',
        status: 'all',
        errorOnly: false,
    }
};

function showAlert(msg, ok = true) {
    const el = document.getElementById('syncAlert');
    el.classList.remove('hidden', 'bg-emerald-100', 'text-emerald-800', 'border-emerald-200', 'bg-rose-100', 'text-rose-800', 'border-rose-200');
    el.classList.add(ok ? 'bg-emerald-100' : 'bg-rose-100');
    el.classList.add(ok ? 'text-emerald-800' : 'text-rose-800');
    el.classList.add(ok ? 'border-emerald-200' : 'border-rose-200');
    el.classList.add('border');
    el.textContent = msg;
}

function statusBadge(status) {
    if (status === 'synced') {
        return '<span class="inline-flex items-center px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">✓ سینک‌شده</span>';
    }
    return '<span class="inline-flex items-center px-2 py-1 rounded-full bg-rose-100 text-rose-700 text-xs font-bold">✕ سینک‌نشده</span>';
}

function currentRows() {
    return syncState.rows[syncState.tab] || [];
}

function filteredRows() {
    const q = (syncState.filters.q || '').trim().toLowerCase();
    return currentRows().filter((row) => {
        const statusOk = syncState.filters.status === 'all' || row.status === syncState.filters.status;
        if (!statusOk) return false;

        const reason = String(row.reason || '').trim();
        if (syncState.filters.errorOnly && !reason) return false;

        if (!q) return true;
        const hay = [
            row.id,
            row.local_course_name,
            row.course_name,
            row.shortname,
            row.idnumber,
            row.mdl_course_id,
            row.mdl_teacher_id,
            row.mdl_student_id,
            row.reason
        ].join(' ').toLowerCase();
        return hay.includes(q);
    });
}

function renderTable() {
    const body = document.getElementById('syncTableBody');
    const rows = filteredRows();
    body.innerHTML = rows.map((row) => {
        const title = row.local_course_name || row.course_name || ('#' + row.id);
        const shortname = row.shortname || '-';
        const idnumber = row.idnumber || '-';
        const mdl = row.mdl_course_id || row.mdl_teacher_id || row.mdl_student_id || '-';
        const reason = row.reason || '-';
        return `
            <tr class="hover:bg-white/60">
                <td class="px-3 py-3 border text-center"><input class="rowCheck" type="checkbox" value="${row.id}"></td>
                <td class="px-3 py-3 border">${row.id}</td>
                <td class="px-3 py-3 border">${escapeHtml(title)}</td>
                <td class="px-3 py-3 border">${escapeHtml(shortname)}</td>
                <td class="px-3 py-3 border">${escapeHtml(idnumber)}</td>
                <td class="px-3 py-3 border">${escapeHtml(String(mdl))}</td>
                <td class="px-3 py-3 border">${statusBadge(row.status)}</td>
                <td class="px-3 py-3 border text-xs text-slate-600">${escapeHtml(reason)}</td>
                <td class="px-3 py-3 border text-center">
                    <div class="flex flex-wrap gap-2 justify-center">
                        <button class="singleSync px-3 py-1 rounded-lg bg-emerald-600 text-white text-xs font-bold" data-id="${row.id}">همگام‌سازی</button>
                        <button class="singleDelete px-3 py-1 rounded-lg bg-rose-600 text-white text-xs font-bold" data-id="${row.id}">حذف</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    document.getElementById('syncCount').textContent = `نمایش ${rows.length} از ${currentRows().length}`;
}

function escapeHtml(str) {
    return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function withSpinner(html) {
    return `<span class="inline-flex items-center gap-2"><span class="inline-block w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>${html}</span>`;
}

function setBtnLoading(btn, loading, loadingLabel = 'در حال انجام...') {
    if (!btn) return;
    if (loading) {
        if (!btn.dataset.originalHtml) btn.dataset.originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.classList.add('opacity-70', 'cursor-not-allowed');
        btn.innerHTML = withSpinner(loadingLabel);
    } else {
        btn.disabled = false;
        btn.classList.remove('opacity-70', 'cursor-not-allowed');
        if (btn.dataset.originalHtml) {
            btn.innerHTML = btn.dataset.originalHtml;
        }
    }
}

async function postForm(url, data) {
    const body = new URLSearchParams();
    Object.keys(data).forEach((k) => {
        const v = data[k];
        if (Array.isArray(v)) {
            v.forEach((item) => body.append(`${k}[]`, item));
        } else {
            body.append(k, v);
        }
    });

    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
        body: body.toString()
    });
    return res.json();
}

async function loadData() {
    const url = `<?= $CFG->wwwroot ?>/sync/data?term_id=${encodeURIComponent(syncState.termId)}`;
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    if (!data.success) {
        showAlert(data.msg || 'بارگذاری اطلاعات ناموفق بود', false);
        return;
    }
    syncState.rows = data.rows || { courses: [], teachers: [], students: [] };
    renderTable();
}

function selectedIds() {
    return Array.from(document.querySelectorAll('.rowCheck:checked')).map((el) => Number(el.value)).filter(Boolean);
}

async function runSingle(id) {
    const btn = document.querySelector(`.singleSync[data-id="${id}"]`);
    setBtnLoading(btn, true, 'در حال سینک...');
    try {
        const res = await postForm('<?= $CFG->wwwroot ?>/sync/run', {
            kind: syncState.tab.slice(0, -1),
            id,
            term_id: syncState.termId
        });
        showAlert(res.msg || (res.success ? 'سینک انجام شد' : 'سینک ناموفق بود'), !!res.success);
        await loadData();
    } finally {
        setBtnLoading(btn, false);
    }
}

async function runBulk(ids = null, triggerBtn = null) {
    const runIds = Array.isArray(ids) ? ids : selectedIds();
    if (!runIds.length) {
        showAlert('حداقل یک ردیف انتخاب کنید', false);
        return;
    }
    setBtnLoading(triggerBtn, true, 'در حال سینک...');
    try {
        const res = await postForm('<?= $CFG->wwwroot ?>/sync/run_bulk', {
            kind: syncState.tab.slice(0, -1),
            ids: runIds,
            term_id: syncState.termId
        });
        showAlert(res.msg || (res.success ? 'سینک گروهی انجام شد' : 'سینک گروهی ناموفق بود'), !!res.success);
        await loadData();
    } finally {
        setBtnLoading(triggerBtn, false);
    }
}

async function retryFailedOnly() {
    const failedRows = filteredRows().filter((row) => row.status === 'unsynced' && String(row.reason || '').trim() !== '');
    const ids = failedRows.map((r) => Number(r.id)).filter(Boolean);
    if (!ids.length) {
        showAlert('مورد خطادار برای Retry پیدا نشد', false);
        return;
    }
    const retryBtn = document.getElementById('retryFailedBtn');
    await runBulk(ids, retryBtn);
}

async function runDelete(id, triggerBtn = null) {
    setBtnLoading(triggerBtn, true, 'در حال حذف...');
    try {
        const res = await postForm('<?= $CFG->wwwroot ?>/sync/delete', {
            kind: syncState.tab.slice(0, -1),
            id,
            remote: 1
        });
        showAlert(res.msg || (res.success ? 'حذف انجام شد' : 'حذف ناموفق بود'), !!res.success);
        await loadData();
    } finally {
        setBtnLoading(triggerBtn, false);
    }
}

document.addEventListener('click', async (e) => {
    if (e.target.closest('.sync-tab')) {
        const btn = e.target.closest('.sync-tab');
        syncState.tab = btn.dataset.tab;
        document.querySelectorAll('.sync-tab').forEach((el) => {
            el.classList.remove('bg-indigo-600', 'text-white');
            el.classList.add('bg-white', 'text-slate-700', 'border', 'border-slate-200');
        });
        btn.classList.add('bg-indigo-600', 'text-white');
        btn.classList.remove('bg-white', 'text-slate-700', 'border', 'border-slate-200');
        document.getElementById('checkAll').checked = false;
        renderTable();
    }
    if (e.target.closest('.singleSync')) {
        const id = Number(e.target.closest('.singleSync').dataset.id);
        await runSingle(id);
    }
    if (e.target.closest('.singleDelete')) {
        const btn = e.target.closest('.singleDelete');
        const id = Number(btn.dataset.id);
        if (confirm('حذف از مودل انجام شود؟')) {
            await runDelete(id, btn);
        }
    }
});

document.getElementById('bulkSyncBtn').addEventListener('click', (e) => runBulk(null, e.currentTarget));
document.getElementById('retryFailedBtn').addEventListener('click', retryFailedOnly);
document.getElementById('bulkDeleteBtn').addEventListener('click', async () => {
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const ids = selectedIds();
    if (!ids.length) {
        showAlert('حداقل یک ردیف انتخاب کنید', false);
        return;
    }
    if (!confirm('حذف انتخاب‌شده‌ها انجام شود؟')) return;
    setBtnLoading(bulkDeleteBtn, true, 'در حال حذف...');
    try {
        for (const id of ids) {
            const res = await postForm('<?= $CFG->wwwroot ?>/sync/delete', {
                kind: syncState.tab.slice(0, -1),
                id,
                remote: 1
            });
            if (!res.success) {
                showAlert(res.msg || 'حذف برخی موارد ناموفق بود', false);
            }
        }
        await loadData();
        showAlert('حذف گروهی انجام شد', true);
    } finally {
        setBtnLoading(bulkDeleteBtn, false);
    }
});

document.getElementById('refreshBtn').addEventListener('click', loadData);
document.getElementById('syncSearch').addEventListener('input', (e) => {
    syncState.filters.q = e.target.value || '';
    renderTable();
});
document.getElementById('statusFilter').addEventListener('change', (e) => {
    syncState.filters.status = e.target.value || 'all';
    renderTable();
});
document.getElementById('errorOnlyFilter').addEventListener('change', (e) => {
    syncState.filters.errorOnly = !!e.target.checked;
    renderTable();
});

document.getElementById('syncTerm').addEventListener('change', async (e) => {
    syncState.termId = Number(e.target.value || 0);
    await loadData();
});

document.getElementById('checkAll').addEventListener('change', (e) => {
    const checked = e.target.checked;
    document.querySelectorAll('.rowCheck').forEach((c) => c.checked = checked);
});

loadData();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
