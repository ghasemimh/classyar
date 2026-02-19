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

    <h2 class="text-3xl font-extrabold mb-6">معلمان</h2>

    <!-- Search & Filter -->
    <div class="mb-6 p-5 rounded-3xl glass-card">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
            <div>
                <label class="block text-base font-semibold text-gray-700 mb-1">جستجو</label>
                <input type="text" id="teacherSearch" placeholder="نام یا نام‌خانوادگی..."
                       class="w-full rounded-xl border border-slate-200 px-3 py-3 text-base bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
            </div>
            <div>
                <label class="block text-base font-semibold text-gray-700 mb-1">فیلتر زمان</label>
                <select id="timeFilter" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-base bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
                    <option value="">همه زمان‌ها</option>
                    <?php foreach ($times as $time): ?>
                        <option value="<?= htmlspecialchars($time['id']) ?>"><?= htmlspecialchars($time['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-base font-semibold text-gray-700 mb-1">فیلتر دوره</label>
                <select id="courseFilter" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-base bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
                    <option value="">همه دوره‌ها</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= htmlspecialchars($course['id']) ?>"><?= htmlspecialchars($course['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mt-3 text-sm text-gray-500" id="teacherFilterCount"></div>
    </div>

    <?php
    // نقشه id → name زمان ها
    $timesMap = [];
    if (!empty($times) && is_array($times)) {
        foreach ($times as $t) {
            $timesMap[$t['id']] = $t['label'];
        }
    }

    $coursesMap = [];
    if (!empty($courses) && is_array($courses)) {
        foreach ($courses as $course) {
            $coursesMap[$course['id']] = ['crsid' => $course['crsid'], 'name' => $course['name'], 'category_id' => $course['category_id']];
        }
    }

    $usersMap = [];
    if (!empty($users) && is_array($users)) {
        foreach ($users as $user) {
            $usersMap[$user['id']] = ['mdl_id' => $user['mdl_id'], 'role' => $user['role'], 'suspend' => $user['suspend']];
        }
    }

    $MdlUsersMap = [];
    if (!empty($Mdl_users) && is_array($Mdl_users)) {
        foreach ($Mdl_users as $Mdl_user) {
            $MdlUsersMap[$Mdl_user['id']] = ['username' => $Mdl_user['username'], 'firstname' => $Mdl_user['firstname'], 'lastname' => $Mdl_user['lastname'], 'email' => $Mdl_user['email'] ?? NULL, 'profileimageurl' => $Mdl_user['profileimageurl']];
        }
    }
    ?>

    <?php if (!empty($teachers)): ?>
        <div class="overflow-x-auto rounded-3xl glass-card" id="teachersGrid">
            <table class="min-w-[1100px] w-full border border-white/60 text-sm sm:text-base">
                <thead class="bg-white/80 backdrop-blur sticky top-0 text-sm uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-5 py-4 border text-right">تصویر</th>
                        <th class="px-5 py-4 border text-right">نام</th>
                        <th class="px-5 py-4 border text-right">نام‌خانوادگی</th>
                        <th class="px-5 py-4 border text-right">زمان‌ها</th>
                        <th class="px-5 py-4 border text-right">دوره‌ها</th>
                        <th class="px-5 py-4 border text-center">تنظیمات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $teacher):?>
                        <?php 
                            $teacherUser = $usersMap[$teacher['user_id']] ?? [];
                            $mdlUser = $MdlUsersMap[$teacherUser['mdl_id'] ?? 0] ?? [];
                            $profileImg = $mdlUser['profileimageurl'] ?? ''; 
                            $firstName = $mdlUser['firstname'] ?? 'ناشناس';
                            $lastName = $mdlUser['lastname'] ?? '';
                        ?>
                    <tr class="hover:bg-white/60 transition teacher-card odd:bg-white/40"
                        data-id="<?= htmlspecialchars($teacher['id']) ?>"
                        data-profileimageurl="<?= $profileImg ?>"
                        data-fullname="<?= htmlspecialchars($firstName) . ' ' . htmlspecialchars($lastName) ?>"
                        data-times="<?= implode(',', $teacher['times']) ?>"
                        data-courses="<?= implode(',', $teacher['courses']) ?>">

                        <td class="px-5 py-4 border">
                            <img src="<?= $profileImg ?>" class="w-14 h-14 rounded-2xl object-cover ring-2 ring-white/70 shadow-sm md:block hidden">
                        </td>
                        <td class="px-5 py-4 border text-base font-semibold text-slate-800"><?= $firstName ?></td>
                        <td class="px-5 py-4 border text-base text-slate-700"><?= $lastName ?></td>
                        <td class="px-5 py-4 border teacher-times-cell">
                            <div class="grid grid-cols-2 gap-3">
                                <?php foreach ($times as $time): ?>
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                                        <input
                                            type="checkbox"
                                            disabled
                                            value="<?= $time['id'] ?>"
                                            <?= in_array($time['id'], $teacher['times']) ? 'checked' : '' ?>
                                            title="<?= $time['label'] ?>"
                                            class="w-5 h-5 rounded border-slate-300 text-teal-600 bg-white/80"
                                        >
                                        <span class="truncate max-w-[140px]"><?= $time['label'] ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </td>

                        <td class="px-5 py-4 border teacher-courses-cell">
                            <?php if (is_array($teacher['courses']) and !empty($teacher['courses'])) : ?>
                            <select class="border border-slate-200 rounded-xl px-3 py-2 max-w-[220px] bg-white/80 text-sm">
                                <?php foreach ($teacher['courses'] as $course_id): ?>
                                    <option>
                                        <?php $course = $coursesMap[$course_id] ?? ['name' => 'نامشخص']; ?>
                                        <?= $course['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php else: ?>
                                    <span class="text-sm text-slate-500">بدون دوره</span>
                            <?php endif; ?>
                            
                        </td>
                        <td class="px-4 py-4 border text-center">
                            <div class="flex flex-wrap gap-2 justify-center">
                            <button class="viewBtn px-4 py-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 text-white text-sm font-bold hover:opacity-90 transition shadow">
                                مشاهده
                            </button>

                            <?php if ($userRole === 'admin'): ?>
                                <a href="<?= $CFG->wwwroot ?>/teacher/print/<?= htmlspecialchars($teacher['id']) ?>"
                                   class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-bold hover:opacity-90 transition shadow">
                                    چاپ لیست
                                </a>
                                <button class="editBtn px-4 py-2 rounded-xl bg-gradient-to-r from-amber-400 to-orange-500 text-white text-sm font-bold hover:opacity-90 transition shadow">
                                    ویرایش
                                </button>
                                <!-- <button class="deleteBtn px-3 py-1 m-1 rounded-xl bg-gradient-to-r from-red-500 to-pink-600 text-white text-sm font-bold hover:opacity-90 transition"
                                        data-id="<?= htmlspecialchars($teacher['id']) ?>"
                                        data-name="<?= htmlspecialchars($firstName . ' ' . $lastName) ?>">
                                    حذف
                                </button> -->
                            <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-gray-500">هیچ معلمی یافت نشد.</p>
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
        <div class="rounded-3xl p-6 w-full max-w-md relative z-10 overflow-y-auto max-h-[90vh] glass-card">
            <button id="closeViewModal"
                    class="absolute top-4 right-4 text-white bg-red-500 hover:bg-red-600 w-7 h-7 text-2xl rounded-full flex items-center justify-center font-bold">&times;</button>
            <img class="w-24 h-24 rounded-full object-cover md:block mx-auto mb-4" src="" id="viewTeacherProfileImage">
            <h2 class="text-2xl font-bold mb-2 text-center" id="viewTeacherFullName"></h2>
            
            <p class="text-center font-bold mb-2 text-gray-700">زمان‌های حضور:</p>
            <div class="grid grid-cols-2 gap-2 mb-4 text-sm" id="viewTeacherTimes">
                 <!-- Filled by JS -->
            </div>

            <div class="mb-2 text-center">
                <p class="font-bold text-gray-700 mb-2">لیست دوره‌ها:</p>
                <div id="viewTeacherCourses" class="text-sm text-gray-700 bg-gray-50 p-3 rounded-xl border"></div>
            </div>
        </div>
    </div>
</div>

<!-- edit modal (UPDATED TO MATCH REQUEST) -->
<div id="editModal" class="fixed inset-0 hidden z-50 flex justify-center items-center">
    <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur"></div>
    <div class="flex items-center justify-center content-center h-full w-full">
        <div class="rounded-3xl p-6 w-full max-w-lg relative z-10 overflow-y-auto max-h-[95vh] glass-card">
            <button id="closeEditModal"
                    class="absolute top-4 right-4 text-white bg-red-500 hover:bg-red-600 w-7 h-7 text-2xl rounded-full flex items-center justify-center font-bold">&times;</button>
            
            <img class="w-20 h-20 rounded-full object-cover md:block mx-auto mb-2" src="" id="editTeacherProfileImage">
            <h2 class="text-xl font-bold mb-4 text-center" id="editTeacherFullName"></h2>
            
            <form id="editTeacherForm" class="grid gap-3">
                <input type="hidden" id="editTeacherId" name="id">
                
                <!-- Time Selection -->
                <div class="border rounded-2xl p-3 bg-gray-50">
                    <p class="text-sm font-bold text-gray-700 mb-2 text-center">ویرایش زمان‌ها</p>
                    <div class="grid grid-cols-2 gap-2" id="editTeacherTimes">
                        <?php foreach ($times as $time): ?>
                            <label class="flex items-center gap-2 cursor-pointer p-1 hover:bg-gray-200 rounded">
                                <input
                                    type="checkbox"
                                    name="times[]"
                                    id="editTeacherTime-<?= $time['id'] ?>"
                                    value="<?= $time['id'] ?>"
                                    class="w-4 h-4 text-blue-600 rounded"
                                >
                                <span class="text-sm"><?= $time['label'] ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Course Management (Table Style) -->
                <div class="border rounded-2xl p-3">
                    <p class="text-sm font-bold text-gray-700 mb-2 text-center">مدیریت کلاس‌ها</p>
                    
                    <div class="overflow-x-auto border rounded-xl mb-3">
                        <table class="w-full text-sm text-right">
                            <thead class="bg-gray-100 text-gray-700 border-b">
                                <tr>
                                    <th class="px-3 py-2 font-medium">نام کلاس</th>
                                    <th class="px-3 py-2 font-medium">کد دوره</th>
                                    <th class="px-3 py-2 font-medium text-center">تنظیمات</th>
                                </tr>
                            </thead>
                            <tbody id="editTeacherCoursesTableBody" class="divide-y divide-gray-100">
                                <!-- Rows will be populated by JS -->
                            </tbody>
                        </table>
                        <p id="noCoursesMsg" class="text-center text-gray-500 py-4 text-xs hidden">معلم هنوز هیچ کلاسی ندارد</p>
                    </div>

                    <!-- Add New Course Section -->
                    <div class="flex gap-2 bg-gray-100 p-2 rounded-xl items-center">
                        <input list="allCoursesList" id="newCourseInput" 
                               class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500" 
                               placeholder="نام دوره را جستجو کنید...">
                        <datalist id="allCoursesList">
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= htmlspecialchars($c['name']) ?>" data-id="<?= $c['id'] ?>">
                                    <?= htmlspecialchars($c['category_id']) // یا نام دسته بندی اگر موجود است ?> - کد: <?= $c['crsid'] ?>
                                </option>
                            <?php endforeach; ?>
                        </datalist>
                        <button type="button" id="assignCourseBtn" 
                                class="bg-gradient-to-r from-green-500 to-teal-600 hover:opacity-90 text-white rounded-xl px-4 py-2 text-sm font-bold shadow transition">
                            افزودن
                        </button>
                    </div>
                </div>

                <div class="text-center mt-2">
                    <button type="submit" class="w-full px-6 py-3 rounded-2xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white font-bold shadow-md hover:shadow-lg transition">
                        ذخیره تغییرات
                    </button>
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
          .removeClass('bg-green-600 bg-red-600 bg-blue-600')
          .addClass(type === 'success' ? 'bg-green-600' : (type === 'error' ? 'bg-red-600' : 'bg-blue-600'))
          .fadeIn(200);
    setTimeout(() => { msgDiv.fadeOut(500); }, 3000);
}

// Map helpers
const coursesMap = <?= json_encode($coursesMap) ?>;
// ساخت یک مپ برعکس برای پیدا کردن ID از روی نام در دیتالیست
const courseNameToIdMap = {};
Object.keys(coursesMap).forEach(key => {
    courseNameToIdMap[coursesMap[key].name] = key;
});

function refreshCourseDatalist(excludedIds) {
    const excluded = new Set((excludedIds || []).map(id => id.toString()));
    const options = Object.keys(coursesMap)
        .filter(id => !excluded.has(id.toString()))
        .map(id => {
            const c = coursesMap[id];
            const safeName = $('<div>').text(c.name).html();
            const safeCat = $('<div>').text(c.category_id ?? '').html();
            const safeCrsid = $('<div>').text(c.crsid ?? '').html();
            return `<option value="${safeName}" data-id="${id}">${safeCat} - کد: ${safeCrsid}</option>`;
        })
        .join('');
    $('#allCoursesList').html(options);
}

function updateTeacherTimesCell(tr, timesArr) {
    const normalized = (timesArr || []).map(t => t.toString()).filter(t => t !== '');
    const joined = normalized.join(',');
    tr.data('times', joined);
    tr.attr('data-times', joined);
    tr.find('.teacher-times-cell input[type="checkbox"]').each(function(){
        const timeId = $(this).val().toString();
        $(this).prop('checked', normalized.includes(timeId));
    });
}

function renderTeacherCoursesCell(courseIds) {
    if (courseIds.length === 0) {
        return '<span class="text-xs text-gray-500">معلم دوره‌ای ندارد</span>';
    }
    const options = courseIds.map(id => {
        const course = coursesMap[id] ? coursesMap[id] : { name: 'نام مشخص نشده' };
        const safeName = $('<div>').text(course.name).html();
        return `<option>${safeName}</option>`;
    }).join('');
    return `<select class="border border-slate-200 rounded-xl px-2 py-1 max-w-[180px] bg-white/80 text-xs">${options}</select>`;
}

function updateTeacherCoursesCell(teacherId, courseIds) {
    const tr = $(`.teacher-card[data-id="${teacherId}"]`);
    const normalized = (courseIds || []).map(id => id.toString()).filter(id => id);
    const joined = normalized.join(',');
    tr.data('courses', joined);
    tr.attr('data-courses', joined);
    tr.find('.teacher-courses-cell').html(renderTeacherCoursesCell(normalized));
}

$(function(){
    // مودال‌ها
    $('#closeViewModal').click(() => $('#viewModal').fadeOut(150));
    $('#closeEditModal').click(() => $('#editModal').fadeOut(150));
    $('#viewModal, #editModal').click(function(e){ if(e.target === this) $(this).fadeOut(150); });

    // مشاهده
    $(document).on('click', '.viewBtn', function(){
        const tr = $(this).closest('.teacher-card');
        const timesStr = tr.data('times');
        const timesArr = timesStr ? timesStr.toString().split(',') : [];
        const timeLabels = [];

        // نام و تصویر
        $('#viewTeacherFullName').text(tr.data('fullname'));
        $('#viewTeacherProfileImage').attr('src', tr.data('profileimageurl') || '');

        // پیدا کردن نام زمان‌ها
        $('#editTeacherTimes input').each(function(){
            if(timesArr.includes($(this).val())) {
                timeLabels.push($(this).parent().text().trim());
            }
        });
        
        $('#viewTeacherTimes').html(
            timeLabels.length 
            ? timeLabels.map(t => `<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-lg text-center">${t}</span>`).join('')
            : '<span class="col-span-2 text-center text-gray-500">زمانی ثبت نشده</span>'
        );

        // نمایش دوره‌ها
        const courseIds = tr.data('courses') ? tr.data('courses').toString().split(',') : [];
        const courseNames = courseIds.map(id => coursesMap[id] ? coursesMap[id].name : '').filter(n => n);
        $('#viewTeacherCourses').html(
            courseNames.length 
                ? '<ul class="list-disc list-inside space-y-1">' + courseNames.map(n => '<li>' + n + '</li>').join('') + '</ul>'
                : 'معلم دوره‌ای ندارد'
        );

        $('#viewModal').fadeIn(150);
    });

    // --- جستجو و فیلتر ---
    function applyTeacherFilters() {
        const search = ($('#teacherSearch').val() || '').toLowerCase().trim();
        const timeFilter = ($('#timeFilter').val() || '').toString();
        const courseFilter = ($('#courseFilter').val() || '').toString();
        let visibleCount = 0;

        $('.teacher-card').each(function(){
            const tr = $(this);
            const fullname = (tr.data('fullname') || '').toString().toLowerCase();
            const times = (tr.data('times') || '').toString().split(',').filter(Boolean);
            const courses = (tr.data('courses') || '').toString().split(',').filter(Boolean);

            const matchesSearch = !search || fullname.includes(search);
            const matchesTime = !timeFilter || times.includes(timeFilter);
            const matchesCourse = !courseFilter || courses.includes(courseFilter);

            const shouldShow = matchesSearch && matchesTime && matchesCourse;
            tr.toggle(shouldShow);
            if (shouldShow) visibleCount += 1;
        });

        $('#teacherFilterCount').text(`نمایش ${visibleCount} معلم`);
    }

    $('#teacherSearch').on('input', applyTeacherFilters);
    $('#timeFilter, #courseFilter').on('change', applyTeacherFilters);
    applyTeacherFilters();

    // --- منطق ویرایش معلم ---
    $(document).on('click', '.editBtn', function(){
        const tr = $(this).closest('.teacher-card');
        const teacherId = tr.data('id');
        
        $('#editTeacherId').val(teacherId);
        $('#editTeacherFullName').text(tr.data('fullname'));
        $('#editTeacherProfileImage').attr('src', tr.data('profileimageurl') || '');

        const timesStr = tr.data('times');
        const timesArr = timesStr ? timesStr.toString().split(',') : [];
        
        // ریست checkbox ها و تیک زدن موارد موجود
        $('#editTeacherTimes input[type="checkbox"]').prop('checked', false);
        timesArr.forEach(function(timeId){
            $('#editTeacherTime-' + timeId).prop('checked', true);
        });

        // رندر جدول کلاس‌ها
        renderCourseTable(tr.data('courses'));
        const currentCourses = tr.data('courses') ? tr.data('courses').toString().split(',').filter(id => id) : [];
        refreshCourseDatalist(currentCourses);
        
        $('#editModal').fadeIn(150);
    });

    // تابع ساخت جدول کلاس‌ها در مودال
    function renderCourseTable(coursesData) {
        const tbody = $('#editTeacherCoursesTableBody');
        tbody.empty();
        
        const courseIds = coursesData ? coursesData.toString().split(',') : [];
        const validCourses = courseIds.filter(id => coursesMap[id]);

        if (validCourses.length === 0) {
            $('#noCoursesMsg').removeClass('hidden');
        } else {
            $('#noCoursesMsg').addClass('hidden');
            validCourses.forEach(id => {
                const course = coursesMap[id];
                const row = `
                    <tr class="hover:bg-gray-50 transition" id="course-row-${id}">
                        <td class="px-3 py-2 border-b text-gray-800">${course.name}</td>
                        <td class="px-3 py-2 border-b text-gray-500 text-xs font-mono">${course.crsid}</td>
                        <td class="px-3 py-2 border-b text-center">
                            <button type="button" class="removeCourseBtn text-red-500 hover:text-red-700 bg-red-100 hover:bg-red-200 p-2 rounded-lg transition" 
                                    data-course-id="${id}" title="حذف کلاس از معلم">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        }
    }

    // --- افزودن کلاس به معلم (AJAX) ---
    $('#assignCourseBtn').click(function(){
        const teacherId = $('#editTeacherId').val();
        const courseName = $('#newCourseInput').val().trim();
        
        // پیدا کردن ID دوره از روی نام
        const courseId = courseNameToIdMap[courseName];

        if(!teacherId || !courseId) {
            showFloatingMsg('لطفا یک دوره معتبر انتخاب کنید', 'error');
            return;
        }

        // بررسی تکراری نبودن در جدول فعلی
        if($(`#course-row-${courseId}`).length > 0) {
            showFloatingMsg('این دوره قبلا اضافه شده است', 'error');
            return;
        }

        $.ajax({
            url: '<?= $CFG->wwwroot ?>/teacher/assign_course', // مسیر فرضی backend
            method: 'POST',
            data: { teacher_id: teacherId, course_id: courseId },
            dataType: 'json',
            success: function(res){
                if(res.success){
                    showFloatingMsg('دوره با موفقیت اضافه شد');
                    // آپدیت UI جدول مودال
                    const currentTr = $(`.teacher-card[data-id="${teacherId}"]`);
                    let currentCourses = currentTr.data('courses') ? currentTr.data('courses').toString().split(',') : [];
                    currentCourses.push(courseId);
                    currentTr.data('courses', currentCourses.join(','));
                    
                    renderCourseTable(currentCourses.join(',')); // رندر مجدد جدول
                    updateTeacherCoursesCell(teacherId, currentCourses);
                    refreshCourseDatalist(currentCourses);
                    $('#newCourseInput').val(''); // پاک کردن ورودی
                } else {
                    showFloatingMsg(res.msg || 'خطا در افزودن دوره', 'error');
                }
            },
            error: function(){
                showFloatingMsg('خطای ارتباط با سرور', 'error');
            }
        });
    });

    // --- حذف کلاس از معلم (AJAX) ---
    $(document).on('click', '.removeCourseBtn', function(){
        if(!confirm('آیا از حذف این کلاس برای معلم اطمینان دارید؟')) return;

        const btn = $(this);
        const courseId = btn.data('course-id');
        const teacherId = $('#editTeacherId').val();

        $.ajax({
            url: '<?= $CFG->wwwroot ?>/teacher/remove_course', // مسیر فرضی backend
            method: 'POST',
            data: { teacher_id: teacherId, course_id: courseId },
            dataType: 'json',
            success: function(res){
                if(res.success){
                    showFloatingMsg('دوره حذف شد');
                    // آپدیت دیتا
                    const currentTr = $(`.teacher-card[data-id="${teacherId}"]`);
                    let currentCourses = currentTr.data('courses').toString().split(',');
                    currentCourses = currentCourses.filter(id => id && id != courseId);
                    currentTr.data('courses', currentCourses.join(','));
                    
                    // حذف سطر از جدول
                    btn.closest('tr').fadeOut(300, function(){ $(this).remove(); });
                    updateTeacherCoursesCell(teacherId, currentCourses);
                    refreshCourseDatalist(currentCourses);
                    if (currentCourses.length === 0) {
                        $('#noCoursesMsg').removeClass('hidden');
                    }
                } else {
                    showFloatingMsg(res.msg || 'خطا در حذف', 'error');
                }
            },
            error: function(){
                showFloatingMsg('خطای ارتباط با سرور', 'error');
            }
        });
    });

    // سابمیت فرم اصلی ویرایش (زمان‌ها)
    $('#editTeacherForm').on('submit', function(e){
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.ajax({
            url: '<?= $CFG->wwwroot ?>/teacher/edit_times', // مسیر فرضی
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res){
                if(res.success){
                    showFloatingMsg('زمان‌ها بروزرسانی شدند', 'success');
                    const teacherId = $('#editTeacherId').val();
                    const updatedTimes = res.times ? res.times : [];
                    const tr = $(`.teacher-card[data-id="${teacherId}"]`);
                    updateTeacherTimesCell(tr, updatedTimes);
                    applyTeacherFilters();
                    $('#editModal').fadeOut(150);
                } else {
                    showFloatingMsg(res.msg, 'error');
                }
            },
            error: function(){ showFloatingMsg('خطای ارتباط با سرور', 'error'); }
        });
    });

});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
