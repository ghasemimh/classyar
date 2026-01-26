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

    <h2 class="text-2xl font-bold mb-6">معلمان</h2>

    <?php if ($userRole === 'admin'): ?>
        <div class="mb-6 p-4 bg-gray-100 rounded-2xl" id="addTeacherSection">
            <h3 class="font-bold mb-2">اضافه کردن معلم جدید</h3>

            <form id="addTeacherForm" class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-end">

                <div class="sm:col-span-1">
                    <?php if (is_array($unregistered_Mdl_users)): ?>
                        <select id="new_teacher_id" name="new_teacher_id" required
                                class="w-full rounded-xl border border-gray-300 px-3 py-2">
                            <option value="">انتخاب معلم...</option>
                            <?php foreach ($unregistered_Mdl_users as $new_Mdl_user): ?>
                                <option value="<?= htmlspecialchars($new_Mdl_user['id']) ?>"><?= htmlspecialchars($new_Mdl_user['firstname']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <div class="text-sm text-gray-600">کاربری برای ثبت‌نام موجود نیست</div>
                    <?php endif; ?>
                </div>

                <div class="sm:col-span-3">
                    <button type="submit" class="px-4 py-2 rounded-2xl bg-gradient-to-r from-green-400 to-teal-500 text-white font-bold"
                            <?php if (!is_array($unregistered_Mdl_users)) { echo'disabled';} ?>>
                        اضافه کردن
                    </button>
                    <div id="addTeacherMsg" class="inline-block mr-4 text-sm"></div>
                </div>
            </form>
        </div>
    <?php endif; ?>

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
            $MdlUsersMap[$Mdl_user['id']] = ['username' => $Mdl_user['username'], 'firstname' => $Mdl_user['firstname'], 'lastname' => $Mdl_user['lastname'], 'email' => $Mdl_user['email'], 'profileimageurl' => $Mdl_user['profileimageurl']];
        }
    }
    ?>

    <?php if (!empty($teachers)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 overflow-x-auto bg-white rounded shadow" id="teachersGrid">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border">تصویر</th>
                        <th class="px-4 py-2 border">نام</th>
                        <th class="px-4 py-2 border">نام‌خانوادگی</th>
                        <th class="px-4 py-2 border">زمان‌ها</th>
                        <th class="px-4 py-2 border">دوره‌ها</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $teacher):?>
                        
                    <tr class="hover:bg-gray-100">
                        <td class="px-4 py-2 border"><img src="<?= $MdlUsersMap[$usersMap[$teacher['user_id']]['mdl_id']]['profileimageurl'] ?>" class="w-15 h-15 rounded-full object-cover md:block hidden"></td>
                        <td class="px-4 py-2 border"><?= $MdlUsersMap[$usersMap[$teacher['user_id']]['mdl_id']]['firstname'] ?></td>
                        <td class="px-4 py-2 border"><?= $MdlUsersMap[$usersMap[$teacher['user_id']]['mdl_id']]['lastname'] ?></td>
                        <td class="px-4 py-2 border">
                            <div class="grid grid-cols-1 gap-6">
                                <?php foreach ($times as $time): ?>
                                    <input
                                        type="checkbox"
                                        name="times[<?= $teacher['id'] ?>][]"
                                        value="<?= $time['id'] ?>"
                                        title="<?= $time['label'] ?>" 
                                        <?= in_array($time['id'], $teacher['times']) ? 'checked' : '' ?>
                                    >
                                <?php endforeach; ?>
                            </div>
                        </td>

                        <td class="px-4 py-2 border">
                            <?php if (is_array($teacher['courses']) and !empty($teacher['courses'])) : ?>
                            <select>
                                <?php foreach ($teacher['courses'] as $course_id): ?>
                                    <option>
                                        <?php $course = $coursesMap[$course_id] ?>
                                        <?= $course['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php else: ?>
                                    <option>معلم دوره‌ای ندارد</option>
                            <?php endif; ?>
                            
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
     class="fixed top-4 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-2xl text-white font-bold shadow-lg hidden z-50">
</div>

<!-- view modal -->
<div id="viewModal" class="fixed inset-0 hidden z-50 flex justify-center items-center">
    <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur"></div>
    <div class="flex items-center justify-center content-center h-full">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md relative z-10">
            <button id="closeViewModal"
                    class="absolute top-4 right-4 text-white bg-red-500 hover:bg-red-600 w-7 h-7 text-2xl rounded-full flex items-center justify-center font-bold">&times;</button>
            <h2 class="text-2xl font-bold mb-2 text-center" id="viewTeacherName"></h2>
            <p class="text-center text-sm text-gray-600 mb-2" id="viewTeacherCrsid"></p>
            <p class="text-center text-sm text-gray-500" id="viewTeacherCategory"></p>
        </div>
    </div>
</div>

<!-- edit modal -->
<div id="editModal" class="fixed inset-0 hidden z-50 flex justify-center items-center">
    <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur"></div>
    <div class="flex items-center justify-center content-center h-full">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md relative z-10">
            <button id="closeEditModal"
                    class="absolute top-4 right-4 text-white bg-red-500 hover:bg-red-600 w-7 h-7 text-2xl rounded-full flex items-center justify-center font-bold">&times;</button>
            <h2 class="text-2xl font-bold mb-4 text-center">ویرایش معلم</h2>
            <form id="editTeacherForm" class="grid gap-3">
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
        <div class="bg-white rounded-3xl p-8 w-full max-w-md relative z-10 text-center">
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
                } else {
                    showFloatingMsg(res.msg, 'error');
                }
            },
            error: function(){ showFloatingMsg('خطایی رخ داده', 'error'); }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>