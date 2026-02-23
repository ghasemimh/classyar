<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-4xl mx-auto px-4 py-10">
    
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

    <?php
        $profileImg = is_array($mdlUser) ? trim((string)($mdlUser['profileimageurl'] ?? '')) : '';
        if ($profileImg === '') {
            $profileImg = (string)($CFG->assets . '/images/site-icon.png');
        }
        $firstName = is_array($mdlUser) ? ($mdlUser['firstname'] ?? 'نامشخص') : 'نامشخص';
        $lastName = is_array($mdlUser) ? ($mdlUser['lastname'] ?? '') : '';
        $fullName = trim($firstName . ' ' . $lastName);

        $coursesMap = [];
        if (!empty($courses) && is_array($courses)) {
            foreach ($courses as $course) {
                $coursesMap[$course['id']] = ['crsid' => $course['crsid'], 'name' => $course['name'], 'category_id' => $course['category_id']];
            }
        }
    ?>

    <div class="rounded-3xl shadow-2xl p-8 glass-card">
        <div class="flex items-center gap-4 mb-6">
            <img src="<?= htmlspecialchars($profileImg, ENT_QUOTES, 'UTF-8') ?>" alt="Teacher profile" class="w-16 h-16 rounded-full object-cover">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-800">ویرایش معلم</h1>
                <p class="text-gray-500 text-sm"><?= htmlspecialchars($fullName) ?></p>
            </div>
        </div>

        <form action="<?= $CFG->wwwroot; ?>/teacher/edit/<?= $teacher['id']; ?>" method="post" class="grid gap-6" id="editTeacherPageForm">
            <div class="border rounded-2xl p-4 bg-gray-50">
                <p class="text-sm font-bold text-gray-700 mb-3 text-center">ویرایش زمان‌ها</p>
                <div class="grid grid-cols-2 gap-2">
                    <?php foreach ($times as $time): ?>
                        <label class="flex items-center gap-2 cursor-pointer p-1 hover:bg-gray-200 rounded">
                            <input
                                type="checkbox"
                                name="times[]"
                                value="<?= $time['id'] ?>"
                                class="w-4 h-4 text-blue-600 rounded"
                                <?= in_array($time['id'], $teacher['times']) ? 'checked' : '' ?>
                            >
                            <span class="text-sm"><?= $time['label'] ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="border rounded-2xl p-4 bg-white/70">
                <p class="text-sm font-bold text-gray-700 mb-3 text-center">مدیریت کلاس‌ها</p>

                <div class="overflow-x-auto border border-white/60 rounded-xl mb-3 bg-white/70">
                    <table class="w-full text-sm text-right">
                        <thead class="bg-gray-100 text-gray-700 border-b">
                            <tr>
                                <th class="px-3 py-2 font-medium">نام کلاس</th>
                                <th class="px-3 py-2 font-medium">کد دوره</th>
                                <th class="px-3 py-2 font-medium text-center">تنظیمات</th>
                            </tr>
                        </thead>
                        <tbody id="editTeacherCoursesTableBody" class="divide-y divide-gray-100">
                            <?php if (!empty($teacher['courses'])): ?>
                                <?php foreach ($teacher['courses'] as $courseId): ?>
                                    <?php if (!empty($coursesMap[$courseId])): ?>
                                        <?php $course = $coursesMap[$courseId]; ?>
                                        <tr class="hover:bg-gray-50 transition" id="course-row-<?= $courseId ?>">
                                            <td class="px-3 py-2 border-b text-gray-800"><?= htmlspecialchars($course['name']) ?></td>
                                            <td class="px-3 py-2 border-b text-gray-500 text-xs font-mono"><?= htmlspecialchars($course['crsid']) ?></td>
                                            <td class="px-3 py-2 border-b text-center">
                                                <button type="button" class="removeCourseBtn text-red-500 hover:text-red-700 bg-red-100 hover:bg-red-200 p-2 rounded-lg transition" 
                                                        data-course-id="<?= $courseId ?>" title="حذف کلاس از معلم">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                                        <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <p id="noCoursesMsg" class="text-center text-gray-500 py-4 text-xs <?= !empty($teacher['courses']) ? 'hidden' : '' ?>">هیچ کلاسی ثبت نشده است.</p>
                </div>

                <div class="flex gap-2 bg-white/70 p-2 rounded-xl items-center border border-white/60">
                    <input list="allCoursesList" id="newCourseInput" 
                           class="flex-1 border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white/80 focus:outline-none focus:ring-2 focus:ring-teal-200 focus:border-teal-400" 
                           placeholder="نام دوره را جستجو کنید...">
                    <datalist id="allCoursesList">
                        <?php foreach ($courses as $c): ?>
                            <?php if (!in_array($c['id'], $teacher['courses'])): ?>
                                <option value="<?= htmlspecialchars($c['name']) ?>" data-id="<?= $c['id'] ?>">
                                    <?= htmlspecialchars($c['category_id']) ?> - کد: <?= htmlspecialchars((string)$c['crsid']) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </datalist>
                    <button type="button" id="assignCourseBtn" 
                            class="bg-gradient-to-r from-teal-600 to-emerald-500 hover:opacity-90 text-white rounded-xl px-4 py-2 text-sm font-bold shadow transition">
                        افزودن
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" 
                        class="px-6 py-2 rounded-2xl bg-gradient-to-r from-amber-400 to-orange-500 text-white font-bold hover:opacity-90 transition">
                    بروزرسانی زمان‌ها
                </button>
                <a href="<?= $CFG->wwwroot ?>/teacher" 
                   class="px-6 py-2 rounded-2xl bg-gradient-to-r from-slate-400 to-slate-600 text-white font-bold hover:opacity-90 transition">
                    انصراف
                </a>
            </div>
        </form>
    </div>
</div>

<script>
const teacherId = <?= json_encode($teacher['id']) ?>;
const coursesMap = <?= json_encode($coursesMap) ?>;
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

function addCourseRow(courseId) {
    const course = coursesMap[courseId];
    if (!course) return;
    const safeName = $('<div>').text(course.name).html();
    const safeCrsid = $('<div>').text(course.crsid).html();
    const row = `
        <tr class="hover:bg-gray-50 transition" id="course-row-${courseId}">
            <td class="px-3 py-2 border-b text-gray-800">${safeName}</td>
            <td class="px-3 py-2 border-b text-gray-500 text-xs font-mono">${safeCrsid}</td>
            <td class="px-3 py-2 border-b text-center">
                <button type="button" class="removeCourseBtn text-red-500 hover:text-red-700 bg-red-100 hover:bg-red-200 p-2 rounded-lg transition" 
                        data-course-id="${courseId}" title="حذف کلاس از معلم">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                        <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                    </svg>
                </button>
            </td>
        </tr>
    `;
    $('#editTeacherCoursesTableBody').append(row);
}

$(function(){
    const initialCourses = $('#editTeacherCoursesTableBody tr')
        .map(function(){ return $(this).attr('id').replace('course-row-', ''); })
        .get();
    refreshCourseDatalist(initialCourses);

    $('#assignCourseBtn').click(function(){
        const courseName = $('#newCourseInput').val().trim();
        const courseId = courseNameToIdMap[courseName];

        if (!courseId) {
            alert('لطفاً یک دوره معتبر انتخاب کنید.');
            return;
        }
        if ($(`#course-row-${courseId}`).length > 0) {
            alert('این دوره قبلاً اضافه شده است.');
            return;
        }

        $.ajax({
            url: '<?= $CFG->wwwroot ?>/teacher/assign_course',
            method: 'POST',
            data: { teacher_id: teacherId, course_id: courseId },
            dataType: 'json',
            success: function(res){
                if (res.success) {
                    addCourseRow(courseId);
                    $('#noCoursesMsg').addClass('hidden');
                    $('#newCourseInput').val('');
                    const currentCourses = $('#editTeacherCoursesTableBody tr')
                        .map(function(){ return $(this).attr('id').replace('course-row-', ''); })
                        .get();
                    refreshCourseDatalist(currentCourses);
                } else {
                    alert(res.msg || 'خطا در افزودن دوره');
                }
            },
            error: function(){
                alert('خطای ارتباط با سرور');
            }
        });
    });

    $(document).on('click', '.removeCourseBtn', function(){
        const btn = $(this);
        window.classyarConfirm('آیا از حذف این کلاس برای معلم اطمینان دارید؟').then(function(ok){
            if (!ok) return;
            const courseId = btn.data('course-id');

            $.ajax({
                url: '<?= $CFG->wwwroot ?>/teacher/remove_course',
                method: 'POST',
                data: { teacher_id: teacherId, course_id: courseId },
                dataType: 'json',
                success: function(res){
                    if (res.success) {
                        btn.closest('tr').fadeOut(200, function(){
                            $(this).remove();
                            if ($('#editTeacherCoursesTableBody tr').length === 0) {
                                $('#noCoursesMsg').removeClass('hidden');
                            }
                            const currentCourses = $('#editTeacherCoursesTableBody tr')
                                .map(function(){ return $(this).attr('id').replace('course-row-', ''); })
                                .get();
                            refreshCourseDatalist(currentCourses);
                        });
                    } else {
                        alert(res.msg || 'خطا در حذف دوره');
                    }
                },
                error: function(){
                    alert('خطای ارتباط با سرور');
                }
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
