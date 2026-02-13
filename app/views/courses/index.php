<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-10">
    <h2 class="text-2xl font-bold mb-6">دوره ها</h2>

    <div class="mb-6 p-4 rounded-3xl glass-card">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">جستجو</label>
                <input type="text" id="courseSearch" placeholder="نام یا کد دوره..." class="w-full rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">فیلتر دسته</label>
                <select id="courseCategoryFilter" class="w-full rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                    <option value="">همه دسته ها</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="text-xs text-gray-500" id="courseFilterCount"></div>
        </div>
    </div>

    <?php if ($userRole === 'admin'): ?>
        <div class="mb-6 p-4 rounded-3xl glass-card">
            <h3 class="font-bold mb-2">افزودن دوره جدید</h3>
            <form id="addCourseForm" class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-end">
                <input type="text" id="addCrsid" name="crsid" placeholder="کد دوره" required class="w-full rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                <input type="text" id="addName" name="name" placeholder="نام دوره" required class="w-full rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                <select id="addCategoryId" name="category_id" required class="w-full rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                    <option value="">انتخاب دسته...</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="sm:col-span-3 px-4 py-2 rounded-2xl bg-gradient-to-r from-teal-600 to-emerald-500 text-white font-bold">افزودن</button>
            </form>
        </div>
    <?php endif; ?>

    <?php
    $catMap = [];
    foreach ($categories as $c) {
        $catMap[(int)$c['id']] = $c['name'];
    }
    ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="coursesGrid">
        <?php foreach ($courses as $course): ?>
            <?php $catName = $catMap[(int)$course['category_id']] ?? ''; ?>
            <div class="rounded-3xl p-6 flex flex-col justify-between course-card glass-card"
                 data-id="<?= (int)$course['id'] ?>"
                 data-name="<?= htmlspecialchars($course['name']) ?>"
                 data-crsid="<?= htmlspecialchars((string)$course['crsid']) ?>"
                 data-category-id="<?= (int)$course['category_id'] ?>"
                 data-category-name="<?= htmlspecialchars($catName) ?>">
                <h3 class="text-lg font-semibold mb-2"><?= htmlspecialchars($course['name']) ?></h3>
                <p class="text-sm text-gray-500 mb-1">کد: <?= htmlspecialchars((string)$course['crsid']) ?></p>
                <p class="text-sm text-gray-500 mb-4">دسته: <?= htmlspecialchars($catName) ?></p>
                <div class="flex gap-2 mt-auto">
                    <?php if ($userRole === 'admin'): ?>
                        <button type="button" class="editBtn px-3 py-2 rounded-xl bg-amber-500 text-white text-sm font-bold">ویرایش</button>
                        <button type="button" class="deleteBtn px-3 py-2 rounded-xl bg-rose-600 text-white text-sm font-bold">حذف</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="coursePager" class="mt-6 flex items-center justify-center gap-2"></div>
</div>

<div id="editModal" class="fixed inset-0 hidden z-50 flex justify-center items-center">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative z-10 w-full max-w-md rounded-3xl glass-card p-6">
        <h3 class="font-bold text-lg mb-4">ویرایش دوره</h3>
        <form id="editCourseForm" class="space-y-3">
            <input type="hidden" id="editId">
            <input type="text" id="editCrsid" placeholder="کد دوره" class="w-full rounded-xl border border-slate-200 px-3 py-2">
            <input type="text" id="editName" placeholder="نام دوره" class="w-full rounded-xl border border-slate-200 px-3 py-2">
            <select id="editCategoryId" class="w-full rounded-xl border border-slate-200 px-3 py-2">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white">ذخیره</button>
                <button type="button" id="closeEditModal" class="px-4 py-2 rounded-xl bg-slate-300">بستن</button>
            </div>
        </form>
    </div>
</div>

<script>
$(function () {
    const perPage = 12;
    let currentPage = 1;
    let editingId = null;

    function showMsg(text, ok = true) {
        const color = ok ? 'bg-emerald-600' : 'bg-rose-600';
        const node = $(`<div class="fixed top-4 left-1/2 -translate-x-1/2 ${color} text-white px-4 py-2 rounded-xl z-[9999]">${text}</div>`);
        $('body').append(node);
        setTimeout(() => node.fadeOut(250, () => node.remove()), 2200);
    }

    function renderPager(total) {
        const pages = Math.max(1, Math.ceil(total / perPage));
        if (currentPage > pages) currentPage = pages;
        const pager = $('#coursePager');
        pager.empty();
        if (pages <= 1) return;
        const prevDisabled = currentPage <= 1 ? 'opacity-50 pointer-events-none' : '';
        const nextDisabled = currentPage >= pages ? 'opacity-50 pointer-events-none' : '';
        pager.append(`<button type="button" id="coursePrev" class="px-3 py-2 rounded-xl border border-slate-200 bg-white/80 ${prevDisabled}">قبلی</button>`);
        pager.append(`<span class="px-3 py-2 text-sm text-slate-600">صفحه ${currentPage} از ${pages}</span>`);
        pager.append(`<button type="button" id="courseNext" class="px-3 py-2 rounded-xl border border-slate-200 bg-white/80 ${nextDisabled}">بعدی</button>`);
    }

    function applyFilters(resetPage = false) {
        if (resetPage) currentPage = 1;
        const q = ($('#courseSearch').val() || '').toLowerCase().trim();
        const cat = ($('#courseCategoryFilter').val() || '').toString();
        const matched = [];

        $('.course-card').each(function () {
            const card = $(this);
            const name = (card.data('name') || '').toString().toLowerCase();
            const crsid = (card.data('crsid') || '').toString().toLowerCase();
            const categoryId = (card.data('category-id') || '').toString();
            const ok = (!q || name.includes(q) || crsid.includes(q)) && (!cat || categoryId === cat);
            card.toggle(false);
            if (ok) matched.push(card);
        });

        renderPager(matched.length);
        const start = (currentPage - 1) * perPage;
        const end = start + perPage;
        matched.forEach((card, idx) => card.toggle(idx >= start && idx < end));
        $('#courseFilterCount').text(`نمایش ${matched.length} دوره`);
    }

    $(document).on('click', '#coursePrev', function () {
        currentPage -= 1;
        applyFilters();
    });
    $(document).on('click', '#courseNext', function () {
        currentPage += 1;
        applyFilters();
    });
    $('#courseSearch').on('input', function () { applyFilters(true); });
    $('#courseCategoryFilter').on('change', function () { applyFilters(true); });

    $('#addCourseForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '<?= $CFG->wwwroot ?>/course/new',
            method: 'POST',
            dataType: 'json',
            data: {
                crsid: $('#addCrsid').val().trim(),
                name: $('#addName').val().trim(),
                category_id: $('#addCategoryId').val()
            },
            success: function (res) {
                if (!res.success) return showMsg(res.msg || 'خطا', false);
                location.reload();
            },
            error: function () { showMsg('خطای ارتباط با سرور', false); }
        });
    });

    $(document).on('click', '.editBtn', function () {
        const card = $(this).closest('.course-card');
        editingId = card.data('id');
        $('#editId').val(editingId);
        $('#editCrsid').val(card.data('crsid'));
        $('#editName').val(card.data('name'));
        $('#editCategoryId').val(card.data('category-id'));
        $('#editModal').removeClass('hidden');
    });
    $('#closeEditModal').on('click', function () { $('#editModal').addClass('hidden'); });
    $('#editModal').on('click', function (e) { if (e.target === this) $('#editModal').addClass('hidden'); });

    $('#editCourseForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '<?= $CFG->wwwroot ?>/course/edit/' + editingId,
            method: 'POST',
            dataType: 'json',
            data: {
                crsid: $('#editCrsid').val().trim(),
                name: $('#editName').val().trim(),
                category_id: $('#editCategoryId').val()
            },
            success: function (res) {
                if (!res.success) return showMsg(res.msg || 'خطا', false);
                location.reload();
            },
            error: function () { showMsg('خطای ارتباط با سرور', false); }
        });
    });

    $(document).on('click', '.deleteBtn', function () {
        const card = $(this).closest('.course-card');
        const id = card.data('id');
        if (!confirm('حذف انجام شود؟')) return;
        $.ajax({
            url: '<?= $CFG->wwwroot ?>/course/delete/' + id,
            method: 'POST',
            dataType: 'json',
            data: { id: id },
            success: function (res) {
                if (!res.success) return showMsg(res.msg || 'خطا', false);
                card.remove();
                applyFilters();
            },
            error: function () { showMsg('خطای ارتباط با سرور', false); }
        });
    });

    applyFilters();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
