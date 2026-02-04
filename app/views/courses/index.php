<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

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

    <h2 class="text-2xl font-bold mb-6">دوره‌ها</h2>

    <div class="mb-6 p-4 rounded-3xl glass-card">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">جستجو</label>
                <input type="text" id="courseSearch" placeholder="نام یا کد دوره..."
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">فیلتر دسته</label>
                <select id="courseCategoryFilter" class="w-full rounded-xl border border-slate-200 px-3 py-2 bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
                    <option value="">همه دسته‌ها</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="text-xs text-gray-500" id="courseFilterCount"></div>
        </div>
    </div>

    <?php if ($userRole === 'admin'): ?>
        <div class="mb-6 p-4 rounded-3xl glass-card" id="addCourseSection">
            <h3 class="font-bold mb-2">اضافه کردن دوره جدید</h3>

            <?php $hasCategories = !empty($categories) && is_array($categories); ?>

            <?php if (!$hasCategories): ?>
                <div class="mb-3 text-sm text-red-600">
                    برای ایجاد دوره نیاز به حداقل یک دسته‌بندی دارید. ابتدا یک دسته‌بندی ایجاد کنید.
                </div>
            <?php endif; ?>

            <form id="addCourseForm" class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-end">
                <div class="sm:col-span-1">
                    <input type="text" id="crsid" name="crsid" placeholder="کد دوره (crsid)" required
                           class="w-full rounded-xl border border-slate-200 px-3 py-2 bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400" <?= $hasCategories ? '' : 'disabled' ?>>
                </div>

                <div class="sm:col-span-1">
                    <input type="text" id="name" name="name" placeholder="نام دوره" required
                           class="w-full rounded-xl border border-slate-200 px-3 py-2 bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400" <?= $hasCategories ? '' : 'disabled' ?>>
                </div>

                <div class="sm:col-span-1">
                    <?php if ($hasCategories): ?>
                        <select id="category_id" name="category_id" required
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
                            <option value="">انتخاب دسته...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <div class="text-sm text-gray-600">دسته‌ای موجود نیست</div>
                    <?php endif; ?>
                </div>

                <div class="sm:col-span-3">
                    <button type="submit" class="px-4 py-2 rounded-2xl bg-gradient-to-r from-teal-600 to-emerald-500 text-white font-bold shadow-md hover:opacity-90 transition"
                            <?= $hasCategories ? '' : 'disabled' ?>>
                        اضافه کردن
                    </button>
                    <div id="addCourseMsg" class="inline-block mr-4 text-sm"></div>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php
    // نقشه id → name دسته‌بندی‌ها
    $catMap = [];
    if (!empty($categories) && is_array($categories)) {
        foreach ($categories as $c) {
            $catMap[$c['id']] = $c['name'];
        }
    }
    ?>

    <?php if (!empty($courses)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="coursesGrid">
            <?php foreach ($courses as $course): ?>
                <?php
                    $catName = '';
                    if (!empty($course['category_id']) && isset($catMap[$course['category_id']])) {
                        $catName = $catMap[$course['category_id']];
                    }
                ?>
                <div class="rounded-3xl p-6 flex flex-col justify-between course-card glass-card hover:-translate-y-0.5 transition"
                     data-id="<?= htmlspecialchars($course['id']) ?>"
                     data-name="<?= htmlspecialchars($course['name']) ?>"
                     data-crsid="<?= htmlspecialchars($course['crsid'] ?? '') ?>"
                     data-category="<?= htmlspecialchars($catName) ?>"
                     data-category_id="<?= htmlspecialchars($course['category_id']) ?>">
                    <h3 class="text-lg font-semibold mb-2"><?= htmlspecialchars($course['name']) ?></h3>
                    <?php if (!empty($course['crsid'])): ?>
                        <p class="text-sm text-gray-500 mb-4">کد: <?= htmlspecialchars($course['crsid']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($catName)): ?>
                        <p class="text-sm text-gray-400 mb-4">دسته: <?= htmlspecialchars($catName) ?></p>
                    <?php endif; ?>

                    <div class="flex flex-wrap gap-3 mt-auto">
                        <button class="viewBtn px-4 py-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 text-white text-sm font-bold hover:opacity-90 transition"
                                data-id="<?= htmlspecialchars($course['id']) ?>"
                                data-name="<?= htmlspecialchars($course['name']) ?>"
                                data-crsid="<?= htmlspecialchars($course['crsid'] ?? '') ?>"
                                data-category="<?= htmlspecialchars($catName) ?>">
                            مشاهده
                        </button>

                        <?php if ($userRole === 'admin'): ?>
                            <button class="editBtn px-4 py-2 rounded-xl bg-gradient-to-r from-amber-400 to-orange-500 text-white text-sm font-bold hover:opacity-90 transition"
                                    data-id="<?= htmlspecialchars($course['id']) ?>"
                                    data-name="<?= htmlspecialchars($course['name']) ?>"
                                    data-crsid="<?= htmlspecialchars($course['crsid'] ?? '') ?>"
                                    data-category_id="<?= htmlspecialchars($course['category_id']) ?>">
                                ویرایش
                            </button>
                            <button class="deleteBtn px-4 py-2 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 text-white text-sm font-bold hover:opacity-90 transition"
                                    data-id="<?= htmlspecialchars($course['id']) ?>"
                                    data-name="<?= htmlspecialchars($course['name']) ?>">
                                حذف
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-500">هیچ دوره‌ای یافت نشد.</p>
    <?php endif; ?>
</div>

<!-- شناور پیام -->
<div id="floatingMsg"
     class="fixed top-4 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-2xl text-white font-bold shadow-lg hidden z-[9999]">
</div>

<!-- view modal -->
<div id="viewModal" class="fixed inset-0 hidden z-50 flex justify-center items-center">
    <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur"></div>
    <div class="flex items-center justify-center content-center h-full">
        <div class="rounded-3xl p-6 w-full max-w-md relative z-10 glass-card">
            <button id="closeViewModal"
                    class="absolute top-4 right-4 text-white bg-red-500 hover:bg-red-600 w-7 h-7 text-2xl rounded-full flex items-center justify-center font-bold">&times;</button>
            <h2 class="text-2xl font-bold mb-2 text-center" id="viewCourseName"></h2>
            <p class="text-center text-sm text-gray-600 mb-2" id="viewCourseCrsid"></p>
            <p class="text-center text-sm text-gray-500" id="viewCourseCategory"></p>
        </div>
    </div>
</div>

<!-- edit modal -->
<div id="editModal" class="fixed inset-0 hidden z-50 flex justify-center items-center">
    <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur"></div>
    <div class="flex items-center justify-center content-center h-full">
        <div class="rounded-3xl p-6 w-full max-w-md relative z-10 glass-card">
            <button id="closeEditModal"
                    class="absolute top-4 right-4 text-white bg-red-500 hover:bg-red-600 w-7 h-7 text-2xl rounded-full flex items-center justify-center font-bold">&times;</button>
            <h2 class="text-2xl font-bold mb-4 text-center">ویرایش دوره</h2>
            <form id="editCourseForm" class="grid gap-3">
                <input type="hidden" id="editCourseId" name="id">
                <div>
                    <input type="text" id="editName" name="name" placeholder="نام دوره" required class="w-full rounded-xl border px-3 py-2">
                </div>
                <div>
                    <input type="text" id="editCrsid" name="crsid" placeholder="کد دوره" required class="w-full rounded-xl border px-3 py-2">
                </div>
                <div>
                    <select id="editCategoryId" name="category_id" required class="w-full rounded-xl border px-3 py-2">
                        <option value="">انتخاب دسته...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="text-center">
                    <button type="submit" class="px-6 py-2 rounded-2xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white font-bold">ذخیره تغییرات</button>
                </div>
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
            <h2 class="text-2xl font-bold mb-4 text-red-600">حذف دوره</h2>
            <p class="mb-4">آیا مطمئن هستید که می‌خواهید این دوره را حذف کنید؟</p>
            <p class="font-bold text-red-600 mb-4" id="deleteCourseName"></p>
            <form id="deleteCourseForm">
                <input type="hidden" name="id" id="deleteCourseId">
                <div class="mb-4">
                    <label for="confirmName" class="block text-sm font-medium text-gray-700 mb-1">برای تأیید نام دوره را وارد کنید👇</label>
                    <input type="text" id="confirmName" name="name" required class="w-full rounded-2xl border px-4 py-2">
                </div>
                <div class="flex items-center justify-center gap-3">
                    <button type="submit" class="px-6 py-2 rounded-2xl bg-gradient-to-r from-red-500 to-pink-600 text-white font-bold">بله، حذف شود</button>
                    <button type="button" id="cancelDelete" class="px-6 py-2 rounded-2xl bg-gradient-to-r from-gray-400 to-gray-600 text-white font-bold">انصراف</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// شناور پیام
function showFloatingMsg(text, type='success') {
    let msgDiv = $('#floatingMsg');
    msgDiv.text(text)
          .removeClass('bg-green-600 bg-red-600')
          .addClass(type === 'success' ? 'bg-green-600' : 'bg-red-600')
          .fadeIn(200);
    setTimeout(() => { msgDiv.fadeOut(500); }, 3000);
}

$(function(){
    // مودال‌ها
    $('#closeViewModal').click(() => $('#viewModal').fadeOut(150));
    $('#closeEditModal').click(() => $('#editModal').fadeOut(150));
    $('#closeDeleteModal, #cancelDelete').click(() => $('#deleteModal').fadeOut(150));
    $('#viewModal, #editModal, #deleteModal').click(function(e){ if(e.target === this) $(this).fadeOut(150); });

    // اضافه کردن دوره
    $('#addCourseForm').on('submit', function(e){
        e.preventDefault();
        let crsid = $('#crsid').val().trim();
        let name = $('#name').val().trim();
        let category_id = $('#category_id').val();
        let categoryText = $('#category_id option:selected').text();
        if(!crsid || !name || !category_id) return;

        $.ajax({
            url: '<?= $CFG->wwwroot ?>/course/new',
            method: 'POST',
            data: {crsid, name, category_id},
            dataType: 'json',
            success: function(res){
                if(res.success){
                    showFloatingMsg(res.msg, 'success');
                    const safeName = $('<div>').text(name).html();
                    const safeCrsid = $('<div>').text(crsid).html();
                    const newCard = `
                    <div class="bg-white rounded-2xl shadow p-6 flex flex-col justify-between course-card"
                         data-id="${res.id}" data-name="${safeName}" data-crsid="${safeCrsid}" data-category="${categoryText}" data-category_id="${category_id}">
                        <h3 class="text-lg font-semibold mb-2">${safeName}</h3>
                        <p class="text-sm text-gray-500 mb-4">کد: ${safeCrsid}</p>
                        <p class="text-sm text-gray-400 mb-4">دسته: ${categoryText}</p>
                        <div class="flex flex-wrap gap-3 mt-auto">
                            <button class="viewBtn px-4 py-2 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-sm font-bold">مشاهده</button>
                            <button class="editBtn px-4 py-2 rounded-xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-sm font-bold"
                                    data-id="${res.id}" data-name="${safeName}" data-crsid="${safeCrsid}" data-category_id="${category_id}">ویرایش</button>
                            <button class="deleteBtn px-4 py-2 rounded-xl bg-gradient-to-r from-red-500 to-pink-600 text-white text-sm font-bold"
                                    data-id="${res.id}" data-name="${safeName}">حذف</button>
                        </div>
                    </div>`;
                    $('#coursesGrid').prepend(newCard);
                    $('#crsid').val(''); $('#name').val(''); $('#category_id').val('');
                    applyCourseFilters();
                } else {
                    showFloatingMsg(res.msg, 'error');
                }
            },
            error: function(){ showFloatingMsg('خطایی رخ داده', 'error'); }
        });
    });

    // مشاهده
    $(document).on('click', '.viewBtn', function(){
        const btn = $(this).closest('.course-card');
        $('#viewCourseName').text(btn.data('name'));
        $('#viewCourseCrsid').text(btn.data('crsid') ? 'کد: ' + btn.data('crsid') : '');
        $('#viewCourseCategory').text(btn.data('category') ? 'دسته: ' + btn.data('category') : '');
        $('#viewModal').fadeIn(150);
    });

    // ویرایش
    $(document).on('click', '.editBtn', function(){
        const btn = $(this).closest('.course-card');
        $('#editCourseId').val(btn.data('id'));
        $('#editName').val(btn.data('name'));
        $('#editCrsid').val(btn.data('crsid'));
        $('#editCategoryId').val(btn.data('category_id'));
        $('#editModal').fadeIn(150);
    });

    $('#editCourseForm').on('submit', function(e){
        e.preventDefault();
        const id = $('#editCourseId').val();
        const name = $('#editName').val().trim();
        const crsid = $('#editCrsid').val().trim();
        const category_id = $('#editCategoryId').val();
        const categoryText = $('#editCategoryId option:selected').text();
        if(!id || !name || !crsid || !category_id) return;

        $.ajax({
            url: '<?= $CFG->wwwroot ?>/course/edit/' + id,
            method: 'POST',
            data: {name, crsid, category_id},
            dataType: 'json',
            success: function(res){
                if(res.success){
                    showFloatingMsg(res.msg, 'success');
                    const card = $(`.course-card[data-id="${id}"]`);
                    card.data('name', name).data('crsid', crsid).data('category', categoryText).data('category_id', category_id);
                    card.find('h3').text(name);
                    card.find('.text-gray-500').text('کد: ' + crsid);
                    card.find('.text-gray-400').text('دسته: ' + categoryText);
                    $('#editModal').fadeOut(150);
                    applyCourseFilters();
                } else {
                    showFloatingMsg(res.msg, 'error');
                }
            },
            error: function(){ showFloatingMsg('خطایی رخ داده', 'error'); }
        });
    });

    // حذف
    $(document).on('click', '.deleteBtn', function(){
        const btn = $(this);
        $('#deleteCourseId').val(btn.data('id'));
        $('#deleteCourseName').text(btn.data('name'));
        $('#confirmName').val('');
        $('#deleteModal').fadeIn(150);
    });

    $('#deleteCourseForm').on('submit', function(e){
        e.preventDefault();
        const id = $('#deleteCourseId').val();
        const name = $('#confirmName').val().trim();
        if(!id || !name) return;

        $.ajax({
            url: '<?= $CFG->wwwroot ?>/course/delete/' + id,
            method: 'POST',
            data: {id, name},
            dataType: 'json',
            success: function(res){
                if(res.success){
                    showFloatingMsg(res.msg, 'success');
                    $(`.course-card[data-id="${id}"]`).remove();
                    $('#deleteModal').fadeOut(150);
                    applyCourseFilters();
                } else {
                    showFloatingMsg(res.msg, 'error');
                }
            },
            error: function(){ showFloatingMsg('خطایی رخ داده', 'error'); }
        });
    });

    // جستجو و فیلتر
    function applyCourseFilters() {
        const search = ($('#courseSearch').val() || '').toLowerCase().trim();
        const categoryFilter = ($('#courseCategoryFilter').val() || '').toString();
        let visibleCount = 0;
        $('.course-card').each(function(){
            const card = $(this);
            const name = (card.data('name') || '').toString().toLowerCase();
            const crsid = (card.data('crsid') || '').toString().toLowerCase();
            const categoryId = (card.data('category_id') || '').toString();

            const matchesSearch = !search || name.includes(search) || crsid.includes(search);
            const matchesCategory = !categoryFilter || categoryId === categoryFilter;
            const shouldShow = matchesSearch && matchesCategory;
            card.toggle(shouldShow);
            if (shouldShow) visibleCount += 1;
        });
        $('#courseFilterCount').text(`نمایش ${visibleCount} دوره`);
    }

    $('#courseSearch').on('input', applyCourseFilters);
    $('#courseCategoryFilter').on('change', applyCourseFilters);
    applyCourseFilters();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
