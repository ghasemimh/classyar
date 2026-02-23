<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php
require_once __DIR__ . '/../../services/jalali/CalendarUtils.php';
use Morilog\Jalali\CalendarUtils;

function ts_to_jalali($ts, $withTime = false) {
    if (!$ts) return '';
    $ts = intval($ts);
    if ($ts <= 0) return '';
    $gy = intval(date('Y', $ts));
    $gm = intval(date('n', $ts));
    $gd = intval(date('j', $ts));
    $j = CalendarUtils::toJalali($gy, $gm, $gd);
    $date = sprintf('%04d/%02d/%02d', $j[0], $j[1], $j[2]);
    if ($withTime) {
        $time = date('H:i:s', $ts);
        return $date . ' ' . $time;
    }
    return $date;
}
?>
<?php if (!Auth::hasPermission(role: 'admin')): ?>
    <style>
        #addTermSection,
        .editBtn,
        .deleteBtn,
        #editModal,
        #deleteModal {
            display: none !important;
        }
    </style>
<?php endif; ?>

<div class="max-w-7xl mx-auto px-4 py-10">

    <?php if (!empty($msg)): ?>
        <div class="mb-6 p-4 rounded-2xl
            <?php if (isset($msgType) && $msgType === 'success'): ?>
                bg-green-100 text-green-700 border border-green-300
            <?php elseif (isset($msgType) && $msgType === 'error'): ?>
                bg-red-100 text-red-700 border border-red-300
            <?php else: ?>
                bg-blue-100 text-blue-700 border border-blue-300
            <?php endif; ?>
        ">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <h2 class="text-3xl font-extrabold mb-6">ترم‌ها</h2>

    <div class="mb-6 p-5 rounded-3xl glass-card">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
            <div>
                <label class="block text-base font-semibold text-gray-700 mb-1">جستجو</label>
                <input type="text" id="termSearch" placeholder="نام ترم..."
                       class="w-full rounded-xl border border-slate-200 px-3 py-3 text-base bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
            </div>
            <div>
                <label class="block text-base font-semibold text-gray-700 mb-1">فیلتر وضعیت</label>
                <select id="termEditableFilter" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-base bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
                    <option value="">همه</option>
                    <option value="1">قابل ویرایش</option>
                    <option value="0">قفل شده</option>
                </select>
            </div>
            <div class="text-sm text-gray-500" id="termFilterCount"></div>
        </div>
    </div>

    <div class="mb-6 p-4 rounded-3xl glass-card" id="addTermSection">
        <h3 class="font-bold mb-3">ایجاد ترم جدید</h3>
        <form id="addTermForm" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">نام ترم</label>
                <input type="text" name="name" required
                       class="w-full rounded-xl border border-slate-200 px-3 py-3 bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">شروع</label>
                <input type="hidden" name="start" id="start_ts">
                <input type="text" id="start_display" name="start_display" data-jdp
                       placeholder="مثال: 1403/07/01 12:00:00" required
                       class="w-full rounded-xl border border-slate-200 px-3 py-3 bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">پایان</label>
                <input type="hidden" name="end" id="end_ts">
                <input type="text" id="end_display" name="end_display" data-jdp
                       placeholder="مثال: 1403/11/30 23:59:59" required
                       class="w-full rounded-xl border border-slate-200 px-3 py-3 bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">اولین زمان باز</label>
                <input type="hidden" name="first_open_time" id="first_open_time_ts">
                <input type="text" id="first_open_time_display" name="first_open_time_display" data-jdp
                       placeholder="مثال: 1403/07/01 08:00:00"
                       class="w-full rounded-xl border border-slate-200 px-3 py-3 bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">زمان بسته‌شدن</label>
                <input type="hidden" name="close_time" id="close_time_ts">
                <input type="text" id="close_time_display" name="close_time_display" data-jdp
                       placeholder="مثال: 1403/11/30 20:00:00"
                       class="w-full rounded-xl border border-slate-200 px-3 py-3 bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="addEditable" name="editable" checked class="w-5 h-5 text-teal-600">
                <label for="addEditable" class="text-sm text-gray-700">قابل ویرایش</label>
            </div>
            <div class="md:col-span-3">
                <button type="submit" class="px-4 py-3 rounded-2xl bg-gradient-to-r from-teal-600 to-emerald-500 text-white font-bold shadow-md hover:opacity-90 transition">
                    افزودن ترم
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" id="termsGrid">
        <?php foreach ($terms as $term): ?>
            <?php $isActive = intval($term['start']) <= time() && intval($term['end']) >= time(); ?>
            <div class="rounded-3xl p-6 glass-card term-card"
                 data-id="<?= htmlspecialchars($term['id']) ?>"
                 data-name="<?= htmlspecialchars($term['name']) ?>"
                 data-editable="<?= htmlspecialchars($term['editable']) ?>">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold mb-1"><?= htmlspecialchars($term['name']) ?></h3>
                        <div class="text-sm text-slate-600">از <?= htmlspecialchars(ts_to_jalali($term['start'], true)) ?> تا <?= htmlspecialchars(ts_to_jalali($term['end'], true)) ?></div>
                    </div>
                    <?php if ($isActive): ?>
                        <span class="px-2 py-1 text-xs rounded-full bg-teal-100 text-teal-700">ترم فعال</span>
                    <?php endif; ?>
                    <?php if ($term['editable']): ?>
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">قابل ویرایش</span>
                    <?php else: ?>
                        <span class="px-2 py-1 text-xs rounded-full bg-slate-200 text-slate-700">قفل شده</span>
                    <?php endif; ?>
                </div>

                <div class="mt-3 text-xs text-slate-500 space-y-1">
                    <div>اولین زمان باز: <?= htmlspecialchars(ts_to_jalali($term['first_open_time'], true) ?: '-') ?></div>
                    <div>زمان بسته‌شدن: <?= htmlspecialchars(ts_to_jalali($term['close_time'], true) ?: '-') ?></div>
                </div>

                <div class="flex flex-wrap gap-2 mt-4">
                    <button class="editBtn px-4 py-2 rounded-xl bg-gradient-to-r from-amber-400 to-orange-500 text-white text-sm font-bold hover:opacity-90 transition"
                            data-id="<?= htmlspecialchars($term['id']) ?>"
                            data-name="<?= htmlspecialchars($term['name']) ?>"
                            data-start_ts="<?= htmlspecialchars($term['start']) ?>"
                            data-end_ts="<?= htmlspecialchars($term['end']) ?>"
                            data-first_open_time_ts="<?= htmlspecialchars($term['first_open_time']) ?>"
                            data-close_time_ts="<?= htmlspecialchars($term['close_time']) ?>"
                            data-start_j="<?= htmlspecialchars(ts_to_jalali($term['start'], true)) ?>"
                            data-end_j="<?= htmlspecialchars(ts_to_jalali($term['end'], true)) ?>"
                            data-first_open_time_j="<?= htmlspecialchars(ts_to_jalali($term['first_open_time'], true)) ?>"
                            data-close_time_j="<?= htmlspecialchars(ts_to_jalali($term['close_time'], true)) ?>"
                            data-editable="<?= htmlspecialchars($term['editable']) ?>">
                        ویرایش
                    </button>
                    <button class="deleteBtn px-4 py-2 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 text-white text-sm font-bold hover:opacity-90 transition"
                            data-id="<?= htmlspecialchars($term['id']) ?>"
                            data-name="<?= htmlspecialchars($term['name']) ?>">
                        حذف
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <p id="termsEmptyState" class="text-gray-500 <?= !empty($terms) ? 'hidden' : '' ?>">هیچ ترمی یافت نشد.</p>
</div>

<div id="floatingMsg"
     class="fixed top-4 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-2xl text-white font-bold shadow-lg hidden z-[9999]">
</div>

<!-- edit modal -->
<div id="editModal" class="fixed inset-0 hidden z-50 flex justify-center items-center">
    <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur"></div>
    <div class="flex items-center justify-center content-center h-full">
        <div class="rounded-3xl p-6 w-full max-w-md relative z-10 glass-card">
            <button id="closeEditModal"
                    class="absolute top-4 right-4 text-white bg-red-500 hover:bg-red-600 w-7 h-7 text-2xl rounded-full flex items-center justify-center font-bold">&times;</button>
            <h2 class="text-2xl font-bold mb-4 text-center">ویرایش ترم</h2>
            <form id="editTermForm" class="grid gap-3">
                <input type="hidden" id="editTermId" name="id">
                <input type="text" id="editTermName" name="name" placeholder="نام ترم" required class="w-full rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                <input type="hidden" id="editStartTs" name="start">
                <input type="text" id="editStartDisplay" name="start_display" data-jdp placeholder="شروع" required class="w-full rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                <input type="hidden" id="editEndTs" name="end">
                <input type="text" id="editEndDisplay" name="end_display" data-jdp placeholder="پایان" required class="w-full rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                <input type="hidden" id="editFirstOpenTimeTs" name="first_open_time">
                <input type="text" id="editFirstOpenTimeDisplay" name="first_open_time_display" data-jdp placeholder="اولین زمان باز" class="w-full rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                <input type="hidden" id="editCloseTimeTs" name="close_time">
                <input type="text" id="editCloseTimeDisplay" name="close_time_display" data-jdp placeholder="زمان بسته‌شدن" class="w-full rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" id="editEditable" name="editable" class="w-5 h-5 text-teal-600">
                    قابل ویرایش
                </label>
                <button type="submit" class="px-6 py-2 rounded-2xl bg-gradient-to-r from-amber-400 to-orange-500 text-white font-bold">ذخیره</button>
            </form>
        </div>
    </div>
</div>

<!-- delete modal -->
<div id="deleteModal" class="fixed inset-0 hidden z-50 flex justify-center items-center">
    <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur"></div>
    <div class="flex items-center justify-center content-center h-full">
        <div class="rounded-3xl p-8 w-full max-w-md relative z-10 text-center glass-card">
            <button id="closeDeleteModal"
                    class="absolute top-4 right-4 text-white bg-red-500 hover:bg-red-600 w-7 h-7 text-2xl rounded-full flex items-center justify-center font-bold">&times;</button>
            <h2 class="text-2xl font-bold mb-4 text-red-600">حذف ترم</h2>
            <p class="mb-4">آیا مطمئن هستید که می‌خواهید این ترم را حذف کنید؟</p>
            <p class="font-bold text-red-600 mb-4" id="deleteTermName"></p>
            <form id="deleteTermForm">
                <input type="hidden" name="id" id="deleteTermId">
                <div class="flex items-center justify-center gap-3">
                    <button type="submit" class="px-6 py-2 rounded-2xl bg-gradient-to-r from-red-500 to-pink-600 text-white font-bold">بله، حذف شود</button>
                    <button type="button" id="cancelDelete" class="px-6 py-2 rounded-2xl bg-gradient-to-r from-gray-400 to-gray-600 text-white font-bold">انصراف</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toGregorian(jy, jm, jd) {
    function div(a, b) { return ~~(a / b); }
    function mod(a, b) { return a - ~~(a / b) * b; }
    function j2d(jy, jm, jd) {
        jy += 1595;
        var days = -355668 + (365 * jy) + div(jy, 33) * 8 + div(mod(jy, 33) + 3, 4) + jd;
        if (jm < 7) days += (jm - 1) * 31;
        else days += (jm - 7) * 30 + 186;
        return days;
    }
    function d2g(jdn) {
        var j = 4 * jdn + 139361631;
        j = j + div(div(4 * jdn + 183187720, 146097) * 3, 4) * 4 - 3908;
        var i = div(mod(j, 1461), 4) * 5 + 308;
        var gd = div(mod(i, 153), 5) + 1;
        var gm = mod(div(i, 153), 12) + 1;
        var gy = div(j, 1461) - 100100 + div(8 - gm, 6);
        return [gy, gm, gd];
    }
    return d2g(j2d(jy, jm, jd));
}

function normalizeDigits(str) {
    if (!str) return '';
    const map = {'۰':'0','۱':'1','۲':'2','۳':'3','۴':'4','۵':'5','۶':'6','۷':'7','۸':'8','۹':'9',
                 '٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9'};
    return str.replace(/[۰-۹٠-٩]/g, (d) => map[d] || d);
}

function jalaliToTimestamp(str) {
    if (!str) return '';
    let raw = normalizeDigits(str.trim());
    raw = raw
        .replace(/[،٬٫]/g, ' ')
        .replace(/[^\d\/\-\:\s]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
    const dateMatch = raw.match(/(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})/);
    if (!dateMatch) return '';
    const jy = parseInt(dateMatch[1], 10);
    const jm = parseInt(dateMatch[2], 10);
    const jd = parseInt(dateMatch[3], 10);
    if (!jy || !jm || !jd) return '';
    const g = toGregorian(jy, jm, jd);

    let hh = 0, mm = 0, ss = 0;
    const timeMatch = raw.match(/(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?/);
    if (timeMatch) {
        hh = parseInt(timeMatch[1] || '0', 10);
        mm = parseInt(timeMatch[2] || '0', 10);
        ss = parseInt(timeMatch[3] || '0', 10);
    }
    const dt = new Date(g[0], g[1]-1, g[2], hh, mm, ss);
    const ts = Math.floor(dt.getTime() / 1000);
    if (!isFinite(ts) || ts <= 0) return '';
    return ts.toString();
}

function bindJalaliInput(displayId, hiddenId) {
    const $display = $('#' + displayId);
    const $hidden = $('#' + hiddenId);
    const setTs = () => {
        const ts = jalaliToTimestamp($display.val());
        $hidden.val(ts);
    };
    $display.on('change input jdp:change', setTs);
    setTs();
}

function showFloatingMsg(text, type='success') {
    let msgDiv = $('#floatingMsg');
    msgDiv.text(text)
          .removeClass('bg-green-600 bg-red-600 bg-blue-600')
          .addClass(type === 'success' ? 'bg-green-600' : (type === 'error' ? 'bg-red-600' : 'bg-blue-600'))
          .fadeIn(200);
    setTimeout(() => { msgDiv.fadeOut(500); }, 3000);
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, function(ch) {
        switch (ch) {
            case '&': return '&amp;';
            case '<': return '&lt;';
            case '>': return '&gt;';
            case '"': return '&quot;';
            case "'": return '&#39;';
            default: return ch;
        }
    });
}

$(function(){
    if (window.jalaliDatepicker) {
        jalaliDatepicker.startWatch({
            zIndex: 9999,
            autoHide: true,
            autoShow: true,
            showTodayBtn: true,
            showCloseBtn: true,
            date: true,
            time: true,
            hasSecond: true,
            minDate: { year: 1300, month: 1, day: 1 }
        });
    }

    bindJalaliInput('start_display', 'start_ts');
    bindJalaliInput('end_display', 'end_ts');
    bindJalaliInput('first_open_time_display', 'first_open_time_ts');
    bindJalaliInput('close_time_display', 'close_time_ts');

    bindJalaliInput('editStartDisplay', 'editStartTs');
    bindJalaliInput('editEndDisplay', 'editEndTs');
    bindJalaliInput('editFirstOpenTimeDisplay', 'editFirstOpenTimeTs');
    bindJalaliInput('editCloseTimeDisplay', 'editCloseTimeTs');

    $('#closeEditModal').click(() => $('#editModal').fadeOut(200));
    $('#closeDeleteModal, #cancelDelete').click(() => $('#deleteModal').fadeOut(200));
    $('#editModal, #deleteModal').click(function(e){ if(e.target === this) $(this).fadeOut(200); });

    function applyTermFilters() {
        const search = ($('#termSearch').val() || '').toLowerCase().trim();
        const editableFilter = ($('#termEditableFilter').val() || '').toString();
        let visibleCount = 0;

        $('.term-card').each(function(){
            const card = $(this);
            const name = (card.data('name') || '').toString().toLowerCase();
            const editable = (card.data('editable') || '').toString();
            const matchesSearch = !search || name.includes(search);
            const matchesEditable = !editableFilter || editable === editableFilter;
            const shouldShow = matchesSearch && matchesEditable;
            card.toggle(shouldShow);
            if (shouldShow) visibleCount += 1;
        });

        $('#termFilterCount').text(`نمایش ${visibleCount} ترم`);
        $('#termsEmptyState').toggleClass('hidden', visibleCount > 0);
    }

    $('#termSearch').on('input', applyTermFilters);
    $('#termEditableFilter').on('change', applyTermFilters);
    applyTermFilters();

    function isActiveTerm(startTs, endTs) {
        const now = Math.floor(Date.now() / 1000);
        return startTs && endTs && now >= startTs && now <= endTs;
    }

    function renderTermCard(data) {
        const safeName = escapeHtml(data.name);
        const safeStartJ = escapeHtml(data.start_j);
        const safeEndJ = escapeHtml(data.end_j);
        const safeFirstOpenJ = escapeHtml(data.first_open_time_j || '-');
        const safeCloseJ = escapeHtml(data.close_time_j || '-');
        const safeFirstOpenTs = escapeHtml(data.first_open_time_ts || '');
        const safeCloseTs = escapeHtml(data.close_time_ts || '');
        const safeStartTs = Number(data.start_ts) || 0;
        const safeEndTs = Number(data.end_ts) || 0;
        const safeId = Number(data.id) || 0;
        const safeEditable = Number(data.editable) === 1 ? 1 : 0;

        const activeBadge = isActiveTerm(safeStartTs, safeEndTs)
            ? '<span class="px-2 py-1 text-xs rounded-full bg-teal-100 text-teal-700">ترم فعال</span>'
            : (safeEditable === 1
                ? '<span class="px-2 py-1 text-xs rounded-full bg-emerald-100 text-emerald-700">قابل ویرایش</span>'
                : '<span class="px-2 py-1 text-xs rounded-full bg-slate-200 text-slate-700">قفل شده</span>');

        return `
            <div class="rounded-3xl p-6 glass-card term-card"
                 data-id="${safeId}"
                 data-name="${safeName}"
                 data-editable="${safeEditable}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold mb-1">${safeName}</h3>
                        <div class="text-sm text-slate-600">از ${safeStartJ} تا ${safeEndJ}</div>
                    </div>
                    ${activeBadge}
                </div>
                <div class="mt-3 text-xs text-slate-500 space-y-1">
                    <div>اولین زمان باز: ${safeFirstOpenJ}</div>
                    <div>زمان بسته‌شدن: ${safeCloseJ}</div>
                </div>
                <div class="flex flex-wrap gap-2 mt-4">
                    <button class="editBtn px-4 py-2 rounded-xl bg-gradient-to-r from-amber-400 to-orange-500 text-white text-sm font-bold hover:opacity-90 transition"
                            data-id="${safeId}"
                            data-name="${safeName}"
                            data-start_ts="${safeStartTs}"
                            data-end_ts="${safeEndTs}"
                            data-first_open_time_ts="${safeFirstOpenTs}"
                            data-close_time_ts="${safeCloseTs}"
                            data-start_j="${safeStartJ}"
                            data-end_j="${safeEndJ}"
                            data-first_open_time_j="${safeFirstOpenJ === '-' ? '' : safeFirstOpenJ}"
                            data-close_time_j="${safeCloseJ === '-' ? '' : safeCloseJ}"
                            data-editable="${safeEditable}">
                        ویرایش
                    </button>
                    <button class="deleteBtn px-4 py-2 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 text-white text-sm font-bold hover:opacity-90 transition"
                            data-id="${safeId}"
                            data-name="${safeName}">
                        حذف
                    </button>
                </div>
            </div>
        `;
    }

    $('#addTermForm').on('submit', function(e){
        e.preventDefault();
        const form = $(this);
        const startTs = jalaliToTimestamp($('#start_display').val());
        const endTs = jalaliToTimestamp($('#end_display').val());
        $('#start_ts').val(startTs);
        $('#end_ts').val(endTs);
        const data = form.serialize();
        $.ajax({
            url: '<?= $CFG->wwwroot ?>/term/new',
            method: 'POST',
            data,
            dataType: 'json',
            success: function(res){
                if(res.success){
                    showFloatingMsg(res.msg, 'success');
                    const cardData = {
                        id: res.id,
                        name: $('input[name="name"]', form).val(),
                        start_ts: startTs,
                        end_ts: endTs,
                        first_open_time_ts: $('#first_open_time_ts').val(),
                        close_time_ts: $('#close_time_ts').val(),
                        start_j: $('#start_display').val(),
                        end_j: $('#end_display').val(),
                        first_open_time_j: $('#first_open_time_display').val(),
                        close_time_j: $('#close_time_display').val(),
                        editable: $('#addEditable').is(':checked') ? 1 : 0
                    };
                    $('#termsGrid').prepend(renderTermCard(cardData));
                    form[0].reset();
                    $('#start_ts, #end_ts, #first_open_time_ts, #close_time_ts').val('');
                    applyTermFilters();
                } else {
                    showFloatingMsg(res.msg || 'Operation failed.', 'error');
                }
            },
            error: function(){ showFloatingMsg('خطای ارتباط با سرور', 'error'); }
        });
    });

    $(document).on('click', '.editBtn', function(){
        const btn = $(this);
        $('#editTermId').val(btn.data('id'));
        $('#editTermName').val(btn.data('name'));
        $('#editStartDisplay').val(btn.data('start_j'));
        $('#editEndDisplay').val(btn.data('end_j'));
        $('#editFirstOpenTimeDisplay').val(btn.data('first_open_time_j'));
        $('#editCloseTimeDisplay').val(btn.data('close_time_j'));
        $('#editStartTs').val(btn.data('start_ts'));
        $('#editEndTs').val(btn.data('end_ts'));
        $('#editFirstOpenTimeTs').val(btn.data('first_open_time_ts'));
        $('#editCloseTimeTs').val(btn.data('close_time_ts'));
        $('#editEditable').prop('checked', btn.data('editable') == 1);
        $('#editModal').fadeIn(200);
    });

    $('#editTermForm').on('submit', function(e){
        e.preventDefault();
        const id = $('#editTermId').val();
        const startTs = jalaliToTimestamp($('#editStartDisplay').val());
        const endTs = jalaliToTimestamp($('#editEndDisplay').val());
        $('#editStartTs').val(startTs);
        $('#editEndTs').val(endTs);
        const data = $(this).serialize();
        $.ajax({
            url: '<?= $CFG->wwwroot ?>/term/edit/' + id,
            method: 'POST',
            data,
            dataType: 'json',
            success: function(res){
                if(res.success){
                    showFloatingMsg(res.msg, 'success');
                    const card = $(`.term-card[data-id="${id}"]`);
                    const name = $('#editTermName').val();
                    const startJ = $('#editStartDisplay').val();
                    const endJ = $('#editEndDisplay').val();
                    const firstOpenJ = $('#editFirstOpenTimeDisplay').val();
                    const closeJ = $('#editCloseTimeDisplay').val();
                    const editable = $('#editEditable').is(':checked') ? 1 : 0;

                    card.attr('data-name', name).data('name', name);
                    card.attr('data-editable', editable).data('editable', editable);
                    card.find('h3').text(name);
                    card.find('.text-sm.text-slate-600').text(`از ${startJ} تا ${endJ}`);
                    card.find('.text-xs.text-slate-500 div:nth-child(1)').text(`اولین زمان باز: ${firstOpenJ || '-'}`);
                    card.find('.text-xs.text-slate-500 div:nth-child(2)').text(`زمان بسته‌شدن: ${closeJ || '-'}`);

                    const badge = card.find('span.text-xs').first();
                    if (isActiveTerm(startTs, endTs)) {
                        badge.attr('class', 'px-2 py-1 text-xs rounded-full bg-teal-100 text-teal-700').text('ترم فعال');
                    } else if (editable === 1) {
                        badge.attr('class', 'px-2 py-1 text-xs rounded-full bg-emerald-100 text-emerald-700').text('قابل ویرایش');
                    } else {
                        badge.attr('class', 'px-2 py-1 text-xs rounded-full bg-slate-200 text-slate-700').text('قفل شده');
                    }

                    const editBtn = card.find('.editBtn');
                    editBtn
                        .attr('data-name', name).data('name', name)
                        .attr('data-start_ts', startTs).data('start_ts', startTs)
                        .attr('data-end_ts', endTs).data('end_ts', endTs)
                        .attr('data-first_open_time_ts', $('#editFirstOpenTimeTs').val()).data('first_open_time_ts', $('#editFirstOpenTimeTs').val())
                        .attr('data-close_time_ts', $('#editCloseTimeTs').val()).data('close_time_ts', $('#editCloseTimeTs').val())
                        .attr('data-start_j', startJ).data('start_j', startJ)
                        .attr('data-end_j', endJ).data('end_j', endJ)
                        .attr('data-first_open_time_j', firstOpenJ).data('first_open_time_j', firstOpenJ)
                        .attr('data-close_time_j', closeJ).data('close_time_j', closeJ)
                        .attr('data-editable', editable).data('editable', editable);

                    $('#editModal').fadeOut(200);
                    applyTermFilters();
                } else {
                    showFloatingMsg(res.msg || 'Operation failed.', 'error');
                }
            },
            error: function(){ showFloatingMsg('خطای ارتباط با سرور', 'error'); }
        });
    });

    $(document).on('click', '.deleteBtn', function(){
        const id = $(this).data('id');
        if (!id) return;
        window.classyarConfirm('آیا از حذف این ترم مطمئن هستید؟').then(function(ok){
            if (!ok) return;
            $.ajax({
                url: '<?= $CFG->wwwroot ?>/term/delete/' + id,
                method: 'POST',
                dataType: 'json',
                success: function(res){
                    if(res.success){
                        showFloatingMsg(res.msg || 'ترم حذف شد.', 'success');
                        $(`.term-card[data-id="${id}"]`).remove();
                        applyTermFilters();
                    } else {
                        showFloatingMsg(res.msg || 'عملیات ناموفق بود.', 'error');
                    }
                },
                error: function(){ showFloatingMsg('خطا در ارتباط با سرور.', 'error'); }
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
