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
            <div class="flex items-center justify-between gap-2">
                <div class="text-xs text-gray-500" id="courseFilterCount"></div>
                <button type="button" id="courseToggleAll" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white/80 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                    نمایش همه
                </button>
            </div>
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
                <button type="submit" class="sm:col-span-3 px-4 py-2 rounded-2xl bg-gradient-to-r from-teal-600 to-emerald-500 text-white font-bold shadow-md hover:opacity-90 transition">افزودن</button>
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
            <div class="rounded-3xl p-6 flex flex-col justify-between course-card glass-card hover:-translate-y-0.5 transition"
                 data-id="<?= (int)$course['id'] ?>"
                 data-name="<?= htmlspecialchars($course['name']) ?>"
                 data-crsid="<?= htmlspecialchars((string)$course['crsid']) ?>"
                 data-category-id="<?= (int)$course['category_id'] ?>"
                 data-category-name="<?= htmlspecialchars($catName) ?>">
                <h3 class="text-lg font-semibold mb-2"><?= htmlspecialchars($course['name']) ?></h3>
                <p class="text-sm text-gray-500 mb-1">کد: <?= htmlspecialchars((string)$course['crsid']) ?></p>
                <p class="text-sm text-gray-500 mb-4">دسته: <?= htmlspecialchars($catName) ?></p>
                <div class="flex flex-wrap gap-3 mt-auto">
                    <button type="button" class="viewBtn px-4 py-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 text-white text-sm font-bold hover:opacity-90 transition">مشاهده</button>
                    <?php if ($userRole === 'admin'): ?>
                        <button type="button" class="editBtn px-4 py-2 rounded-xl bg-gradient-to-r from-amber-400 to-orange-500 text-white text-sm font-bold hover:opacity-90 transition">ویرایش</button>
                        <button type="button" class="deleteBtn px-4 py-2 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 text-white text-sm font-bold hover:opacity-90 transition">حذف</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <p id="coursesEmptyState" class="text-gray-500 mt-4 <?= !empty($courses) ? 'hidden' : '' ?>">هیچ دوره‌ای یافت نشد.</p>

    <div id="coursePager" class="mt-6 flex items-center justify-center gap-2"></div>
</div>

<div id="floatingMsg"
     class="fixed top-4 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-2xl text-white font-bold shadow-lg hidden z-[9999]">
</div>

<div id="viewModal" class="fixed inset-0 hidden z-50 flex justify-center items-center">
    <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur"></div>
    <div class="flex items-center justify-center content-center h-full">
        <div class="rounded-3xl p-8 w-full max-w-md relative z-10 glass-card">
            <button id="closeViewModal"
                    class="absolute top-4 right-4 text-white bg-red-500 hover:bg-red-600 w-7 h-7 text-2xl rounded-full flex items-center justify-center font-bold">
                &times;
            </button>
            <h2 class="text-2xl font-bold mb-4 text-center">مشاهده دوره</h2>
            <div class="space-y-2 text-sm text-slate-700">
                <div><span class="font-semibold">نام:</span> <span id="viewCourseName">-</span></div>
                <div><span class="font-semibold">کد:</span> <span id="viewCourseCrsid">-</span></div>
                <div><span class="font-semibold">دسته:</span> <span id="viewCourseCategory">-</span></div>
            </div>
        </div>
    </div>
</div>

<div id="editModal" class="fixed inset-0 hidden z-50 flex justify-center items-center">
    <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur"></div>
    <div class="flex items-center justify-center content-center h-full">
        <div class="rounded-3xl p-8 w-full max-w-md relative z-10 glass-card">
            <button id="closeEditModal"
                    class="absolute top-4 right-4 text-white bg-red-500 hover:bg-red-600 w-7 h-7 text-2xl rounded-full flex items-center justify-center font-bold">
                &times;
            </button>
            <h2 class="text-2xl font-bold mb-4 text-center">ویرایش دوره</h2>
            <form id="editCourseForm" class="space-y-4">
                <input type="hidden" id="editId">
                <input type="text" id="editCrsid" placeholder="کد دوره" class="w-full rounded-2xl border border-slate-200 px-4 py-2 bg-white/80 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <input type="text" id="editName" placeholder="نام دوره" class="w-full rounded-2xl border border-slate-200 px-4 py-2 bg-white/80 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <select id="editCategoryId" class="w-full rounded-2xl border border-slate-200 px-4 py-2 bg-white/80 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="px-6 py-2 rounded-2xl bg-gradient-to-r from-amber-400 to-orange-500 text-white font-bold hover:opacity-90 transition w-full">بروزرسانی</button>
            </form>
        </div>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 hidden z-50 flex justify-center items-center">
    <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur"></div>
    <div class="flex items-center justify-center content-center h-full">
        <div class="rounded-3xl p-8 w-full max-w-md relative z-10 text-center glass-card">
            <button id="closeDeleteModal"
                    class="absolute top-4 right-4 text-white bg-red-500 hover:bg-red-600 w-7 h-7 text-2xl rounded-full flex items-center justify-center font-bold">
                &times;
            </button>
            <h2 class="text-2xl font-bold mb-4 text-red-600">حذف دوره</h2>
            <p class="mb-4">آیا مطمئن هستید که می‌خواهید این دوره را حذف کنید؟</p>
            <p class="font-bold text-red-600 mb-4" id="deleteCourseName"></p>
            <form id="deleteCourseForm">
                <input type="hidden" name="id" id="deleteCourseId">
                <div class="flex items-center justify-center gap-3">
                    <button type="submit" class="px-6 py-2 rounded-2xl bg-gradient-to-r from-red-500 to-pink-600 text-white font-bold hover:opacity-90 transition">
                        بله، حذف شود
                    </button>
                    <button type="button" id="cancelDelete" class="px-6 py-2 rounded-2xl bg-gradient-to-r from-gray-400 to-gray-600 text-white font-bold hover:opacity-90 transition">
                        انصراف
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function () {
    const canManageCourse = <?= ($userRole === 'admin') ? 'true' : 'false' ?>;
    const perPage = 12;
    let currentPage = 1;
    let showAll = false;
    let editingId = null;
    let editingCard = null;

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

    function buildCourseCardHtml(id, name, crsid, categoryId, categoryName) {
        const safeId = Number(id) || 0;
        const safeName = escapeHtml(name);
        const safeCrsid = escapeHtml(crsid);
        const safeCategoryId = Number(categoryId) || 0;
        const safeCategoryName = escapeHtml(categoryName);
        const manageButtons = canManageCourse ? `
            <button type="button" class="editBtn px-4 py-2 rounded-xl bg-gradient-to-r from-amber-400 to-orange-500 text-white text-sm font-bold hover:opacity-90 transition">ویرایش</button>
            <button type="button" class="deleteBtn px-4 py-2 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 text-white text-sm font-bold hover:opacity-90 transition">حذف</button>
        ` : '';

        return `
            <div class="rounded-3xl p-6 flex flex-col justify-between course-card glass-card hover:-translate-y-0.5 transition"
                 data-id="${safeId}"
                 data-name="${safeName}"
                 data-crsid="${safeCrsid}"
                 data-category-id="${safeCategoryId}"
                 data-category-name="${safeCategoryName}">
                <h3 class="text-lg font-semibold mb-2">${safeName}</h3>
                <p class="text-sm text-gray-500 mb-1">کد: ${safeCrsid}</p>
                <p class="text-sm text-gray-500 mb-4">دسته: ${safeCategoryName}</p>
                <div class="flex flex-wrap gap-3 mt-auto">
                    <button type="button" class="viewBtn px-4 py-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 text-white text-sm font-bold hover:opacity-90 transition">مشاهده</button>
                    ${manageButtons}
                </div>
            </div>`;
    }

    function showFloatingMsg(text, type = 'success') {
        const msgDiv = $('#floatingMsg');
        msgDiv.text(String(text || ''))
            .removeClass('bg-green-600 bg-red-600')
            .addClass(type === 'success' ? 'bg-green-600' : 'bg-red-600')
            .fadeIn(200);
        setTimeout(() => { msgDiv.fadeOut(500); }, 3000);
    }

    function renderPager(total) {
        if (showAll) {
            $('#coursePager').empty();
            return;
        }
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

    function syncCourseToggleState() {
        $('#courseToggleAll').text(showAll ? 'بازگشت به صفحه‌بندی' : 'نمایش همه');
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

        if (showAll) {
            matched.forEach((card) => card.show());
            $('#coursePager').empty();
            $('#courseFilterCount').text(`نمایش همه ${matched.length} دوره`);
            $('#coursesEmptyState').toggleClass('hidden', matched.length > 0);
            syncCourseToggleState();
            return;
        }

        renderPager(matched.length);
        const start = (currentPage - 1) * perPage;
        const end = start + perPage;
        matched.forEach((card, idx) => card.toggle(idx >= start && idx < end));
        $('#courseFilterCount').text(`نمایش ${matched.length} دوره`);
        $('#coursesEmptyState').toggleClass('hidden', matched.length > 0);
        syncCourseToggleState();
    }

    $(document).on('click', '#coursePrev', function () {
        currentPage -= 1;
        applyFilters();
    });
    $(document).on('click', '#courseNext', function () {
        currentPage += 1;
        applyFilters();
    });
    $('#courseToggleAll').on('click', function () {
        showAll = !showAll;
        currentPage = 1;
        applyFilters();
    });
    $('#courseSearch').on('input', function () { applyFilters(true); });
    $('#courseCategoryFilter').on('change', function () { applyFilters(true); });

    $('#closeViewModal').on('click', function () { $('#viewModal').fadeOut(200); });
    $('#closeEditModal').on('click', function () { $('#editModal').fadeOut(200); });
    $('#closeDeleteModal, #cancelDelete').on('click', function () { $('#deleteModal').fadeOut(200); });
    $('#viewModal, #editModal, #deleteModal').on('click', function (e) {
        if (e.target === this) $(this).fadeOut(200);
    });

    $(document).on('click', '.viewBtn', function () {
        const card = $(this).closest('.course-card');
        $('#viewCourseName').text(card.data('name') || '-');
        $('#viewCourseCrsid').text(card.data('crsid') || '-');
        $('#viewCourseCategory').text(card.data('category-name') || '-');
        $('#viewModal').fadeIn(200);
    });

    $('#addCourseForm').on('submit', function (e) {
        e.preventDefault();
        const crsid = $('#addCrsid').val().trim();
        const name = $('#addName').val().trim();
        const categoryId = $('#addCategoryId').val();
        const categoryName = $('#addCategoryId option:selected').text().trim();
        $.ajax({
            url: '<?= $CFG->wwwroot ?>/course/new',
            method: 'POST',
            dataType: 'json',
            data: {
                crsid: crsid,
                name: name,
                category_id: categoryId
            },
            success: function (res) {
                if (!res.success) return showFloatingMsg(res.msg || 'خطا', 'error');
                $('#coursesGrid').prepend(buildCourseCardHtml(res.id, name, crsid, categoryId, categoryName));
                $('#addCourseForm')[0].reset();
                applyFilters(true);
                showFloatingMsg(res.msg || 'دوره ایجاد شد', 'success');
            },
            error: function () { showFloatingMsg('خطای ارتباط با سرور', 'error'); }
        });
    });

    $(document).on('click', '.editBtn', function () {
        const card = $(this).closest('.course-card');
        editingCard = card;
        editingId = card.data('id');
        $('#editId').val(editingId);
        $('#editCrsid').val(card.data('crsid'));
        $('#editName').val(card.data('name'));
        $('#editCategoryId').val(card.data('category-id'));
        $('#editModal').fadeIn(200);
    });

    $('#editCourseForm').on('submit', function (e) {
        e.preventDefault();
        const crsid = $('#editCrsid').val().trim();
        const name = $('#editName').val().trim();
        const categoryId = $('#editCategoryId').val();
        const categoryName = $('#editCategoryId option:selected').text().trim();
        $.ajax({
            url: '<?= $CFG->wwwroot ?>/course/edit/' + editingId,
            method: 'POST',
            dataType: 'json',
            data: {
                crsid: crsid,
                name: name,
                category_id: categoryId
            },
            success: function (res) {
                if (!res.success) return showFloatingMsg(res.msg || 'خطا', 'error');
                if (editingCard && editingCard.length) {
                    editingCard
                        .attr('data-name', name).data('name', name)
                        .attr('data-crsid', crsid).data('crsid', crsid)
                        .attr('data-category-id', categoryId).data('category-id', categoryId)
                        .attr('data-category-name', categoryName).data('category-name', categoryName);
                    editingCard.find('h3').text(name);
                    editingCard.find('p').eq(0).text('کد: ' + crsid);
                    editingCard.find('p').eq(1).text('دسته: ' + categoryName);
                }
                $('#editModal').fadeOut(200);
                applyFilters();
                showFloatingMsg(res.msg || 'دوره بروزرسانی شد', 'success');
            },
            error: function () { showFloatingMsg('خطای ارتباط با سرور', 'error'); }
        });
    });
    $(document).on('click', '.deleteBtn', function () {
        const card = $(this).closest('.course-card');
        const id = card.data('id');
        if (!id || !card.length) return;
        window.classyarConfirm('Are you sure you want to delete this item?').then(function (ok) {
            if (!ok) return;
            $.ajax({
                url: '<?= $CFG->wwwroot ?>/course/delete/' + id,
                method: 'POST',
                dataType: 'json',
                data: { id: id },
                success: function (res) {
                    if (!res.success) return showFloatingMsg(res.msg || 'Operation failed.', 'error');
                    card.remove();
                    applyFilters();
                    showFloatingMsg(res.msg || 'Deleted successfully.', 'success');
                },
                error: function () { showFloatingMsg('Server communication error.', 'error'); }
            });
        });
    });

    applyFilters();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
