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

    <h2 class="text-2xl font-bold mb-6">دسته‌بندی‌ها</h2>

    <?php if ($userRole === 'admin'): ?>
        <div class="mb-6 p-4 bg-gray-100 rounded-2xl" id="addCategorySection">
            <h3 class="font-bold mb-2">اضافه کردن دسته‌بندی جدید</h3>
            <form id="addCategoryForm" class="flex gap-2">
                <input type="text" name="name" placeholder="نام دسته‌بندی" required
                       class="px-3 py-2 rounded-xl border border-gray-300 flex-1">
                <button type="submit" class="px-4 py-2 rounded-2xl bg-green-500 text-white font-bold">
                    اضافه کردن
                </button>
            </form>
            <div id="addCategoryMsg" class="mt-2"></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($categories)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="categoriesGrid">
            <?php foreach ($categories as $category): ?>
                <div class="bg-white rounded-2xl shadow p-6 flex flex-col justify-between category-card" data-id="<?= $category['id'] ?>">
                    <h3 class="text-lg font-semibold mb-4"><?= htmlspecialchars($category['name']) ?></h3>
                    <div class="flex flex-wrap gap-3 mt-auto">

                        <button class="viewBtn px-4 py-2 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-sm font-bold hover:opacity-90 transition"
                                data-id="<?= $category['id'] ?>" data-name="<?= htmlspecialchars($category['name']) ?>">
                            مشاهده
                        </button>

                        <?php if ($userRole === 'admin'): ?>
                            <button class="editBtn px-4 py-2 rounded-xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-sm font-bold hover:opacity-90 transition"
                                    data-id="<?= $category['id'] ?>" data-name="<?= htmlspecialchars($category['name']) ?>">
                                ویرایش
                            </button>
                            <button class="deleteBtn px-4 py-2 rounded-xl bg-gradient-to-r from-red-500 to-pink-600 text-white text-sm font-bold hover:opacity-90 transition"
                                    data-id="<?= $category['id'] ?>" data-name="<?= htmlspecialchars($category['name']) ?>">
                                حذف
                            </button>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-500">هیچ دسته‌بندی‌ای یافت نشد.</p>
    <?php endif; ?>
</div>

<div id="floatingMsg"
     class="fixed top-4 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-2xl text-white font-bold shadow-lg hidden z-50">
</div>

<div id="viewModal" class="fixed inset-0 hidden z-50 flex justify-center items-center">
    <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur"></div>
    <div class="bg-white rounded-3xl p-8 w-full max-w-md relative z-10">
        <button id="closeViewModal"
                class="absolute top-4 right-4 text-white bg-red-500 hover:bg-red-600 w-7 h-7 text-2xl rounded-full flex items-center justify-center font-bold">
            &times;
        </button>
        <h2 class="text-2xl font-bold mb-4 text-center" id="viewCategoryName"></h2>
        <p class="text-center">اطلاعات بیشتری در مورد این دسته‌بندی اینجا نمایش داده می‌شود.</p>
    </div>
</div>

<div id="editModal" class="fixed inset-0 hidden z-50 flex justify-center items-center">
    <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur"></div>
    <div class="bg-white rounded-3xl p-8 w-full max-w-md relative z-10">
        <button id="closeEditModal"
                class="absolute top-4 right-4 text-white bg-red-500 hover:bg-red-600 w-7 h-7 text-2xl rounded-full flex items-center justify-center font-bold">
            &times;
        </button>
        <h2 class="text-2xl font-bold mb-4 text-center">ویرایش دسته‌بندی</h2>
        <form id="editCategoryForm" class="space-y-4">
            <input type="hidden" name="id" id="editCategoryId">
            <div>
                <label for="editCategoryName" class="block text-sm font-medium text-gray-700 mb-1">نام دسته</label>
                <input type="text" name="name" id="editCategoryName" required
                       class="w-full rounded-2xl border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2">
            </div>
            <button type="submit"
                    class="px-6 py-2 rounded-2xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white font-bold hover:opacity-90 transition w-full">
                بروزرسانی
            </button>
        </form>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 hidden z-50 flex justify-center items-center">
    <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur"></div>
    <div class="bg-white rounded-3xl p-8 w-full max-w-md relative z-10 text-center">
        <button id="closeDeleteModal"
                class="absolute top-4 right-4 text-white bg-red-500 hover:bg-red-600 w-7 h-7 text-2xl rounded-full flex items-center justify-center font-bold">
            &times;
        </button>
        <h2 class="text-2xl font-bold mb-4 text-red-600">حذف دسته‌بندی</h2>
        <p class="mb-4">آیا مطمئن هستید که می‌خواهید این دسته‌بندی را حذف کنید؟</p>
        <p class="font-bold text-red-600 mb-4" id="deleteCategoryName"></p>
        <form id="deleteCategoryForm">
            <input type="hidden" name="id" id="deleteCategoryId">
            <div class="mb-4">
                <label for="confirmName" class="block text-sm font-medium text-gray-700 mb-1">برای تأیید نام دسته را وارد کنید</label>
                <input type="text" id="confirmName" name="name" required
                       class="w-full rounded-2xl border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500 px-4 py-2">
            </div>
            <div class="flex items-center justify-center gap-3">
                <button type="submit"
                        class="px-6 py-2 rounded-2xl bg-gradient-to-r from-red-500 to-pink-600 text-white font-bold hover:opacity-90 transition">
                    بله، حذف شود
                </button>
                <button type="button" id="cancelDelete"
                        class="px-6 py-2 rounded-2xl bg-gradient-to-r from-gray-400 to-gray-600 text-white font-bold hover:opacity-90 transition">
                    انصراف
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// تابع نمایش پیام شناور
function showFloatingMsg(text, type='success') {
    let msgDiv = $('#floatingMsg');
    msgDiv.text(text)
          .removeClass('bg-green-600 bg-red-600')
          .addClass(type === 'success' ? 'bg-green-600' : 'bg-red-600')
          .fadeIn(300);
    setTimeout(() => { msgDiv.fadeOut(500); }, 3000);
}

$(function(){
    // بستن مودال‌ها با دکمه
    $('#closeViewModal').click(() => $('#viewModal').fadeOut(200));
    $('#closeEditModal').click(() => $('#editModal').fadeOut(200));
    $('#closeDeleteModal, #cancelDelete').click(() => $('#deleteModal').fadeOut(200));

    // بستن مودال‌ها با کلیک روی پس‌زمینه (overlay)
    $('#viewModal, #editModal, #deleteModal').click(function(e){
        if(e.target === this) $(this).fadeOut(200);
    });

    // اضافه کردن دسته‌بندی
    $('#addCategoryForm').on('submit', function(e){
        e.preventDefault();
        let form = $(this);
        let name = form.find('input[name="name"]').val().trim();
        if(!name) return;

        $.ajax({
            url: '<?= $CFG->wwwroot ?>/category/new',
            method: 'POST',
            data: {name: name},
            dataType: 'json',
            success: function(res){
                if(res.success){
                    showFloatingMsg(res.msg, 'success');
                    form.find('input[name="name"]').val('');
                    let newCard = `
                    <div class="bg-white rounded-2xl shadow p-6 flex flex-col justify-between category-card" data-id="${res.id}">
                        <h3 class="text-lg font-semibold mb-4">${name}</h3>
                        <div class="flex flex-wrap gap-3 mt-auto">
                            <button class="viewBtn px-4 py-2 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-sm font-bold hover:opacity-90 transition"
                                data-id="${res.id}" data-name="${name}">مشاهده</button>
                            <button class="editBtn px-4 py-2 rounded-xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-sm font-bold hover:opacity-90 transition"
                                data-id="${res.id}" data-name="${name}">ویرایش</button>
                            <button class="deleteBtn px-4 py-2 rounded-xl bg-gradient-to-r from-red-500 to-pink-600 text-white text-sm font-bold hover:opacity-90 transition"
                                data-id="${res.id}" data-name="${name}">حذف</button>
                        </div>
                    </div>`;
                    $('.grid').prepend(newCard);
                } else showFloatingMsg(res.msg, 'error');
            },
            error: function(){ showFloatingMsg('خطایی رخ داده', 'error'); }
        });
    });

    // مشاهده مودال
    $(document).on('click', '.viewBtn', function(){
        let btn = $(this);
        $('#viewCategoryName').text(btn.data('name'));
        $('#viewModal').fadeIn(200);
    });

    // ویرایش مودال
    $(document).on('click', '.editBtn', function(){
        let btn = $(this);
        $('#editCategoryId').val(btn.data('id'));
        $('#editCategoryName').val(btn.data('name'));
        $('#editModal').fadeIn(200);
    });

    $('#editCategoryForm').on('submit', function(e){
        e.preventDefault();
        let id = $('#editCategoryId').val();
        let name = $('#editCategoryName').val().trim();
        if(!id || !name) return;

        $.ajax({
            url: '<?= $CFG->wwwroot ?>/category/edit/' + id,
            method: 'POST',
            data: {name: name},
            dataType: 'json',
            success: function(res){
                if(res.success){
                    showFloatingMsg(res.msg, 'success');
                    $(`.editBtn[data-id="${id}"]`).closest('.category-card').find('h3').text(name);
                    $(`.editBtn[data-id="${id}"]`).data('name', name);
                    $(`.viewBtn[data-id="${id}"]`).data('name', name);
                    $('#editModal').fadeOut(200);
                } else showFloatingMsg(res.msg, 'error');
            },
            error: function(){ showFloatingMsg('خطایی رخ داده', 'error'); }
        });
    });

    // حذف مودال
    $(document).on('click', '.deleteBtn', function(){
        let btn = $(this);
        $('#deleteCategoryId').val(btn.data('id'));
        $('#deleteCategoryName').text(btn.data('name'));
        $('#confirmName').val('');
        $('#deleteModal').fadeIn(200);
    });

    $('#deleteCategoryForm').on('submit', function(e){
        e.preventDefault();
        let id = $('#deleteCategoryId').val();
        let name = $('#confirmName').val().trim();
        if(!id || !name) return;

        $.ajax({
            url: '<?= $CFG->wwwroot ?>/category/delete/' + id,
            method: 'POST',
            data: {name: name},
            dataType: 'json',
            success: function(res){
                if(res.success){
                    showFloatingMsg(res.msg, 'success');
                    $(`.deleteBtn[data-id="${id}"]`).closest('.category-card').remove();
                    $('#deleteModal').fadeOut(200);
                } else showFloatingMsg(res.msg, 'error');
            },
            error: function(){ showFloatingMsg('خطایی رخ داده', 'error'); }
        });
    });
});
</script>






















<style>
  .flash-highlight {
    animation: flash 2s ease-in-out;
  }

  @keyframes flash {
    0%   { background-color: transparent; }
    20% { background-color: #b8fdc3ff; }
    80% { background-color: #86ffa0ff; }
    100%  { background-color: transparent; }
  }
</style>











<script>
  window.addEventListener("DOMContentLoaded", () => {
    const hash = window.location.hash; 
    if (hash) {
      const target = document.querySelector(hash);
      if (target) {
        // اسکرول نرم
        target.scrollIntoView({ behavior: "smooth", block: "center" });

        // بعد از کمی تاخیر برای دیده شدن
        setTimeout(() => {
          target.classList.add("flash-highlight");

          // بعد از انیمیشن حذفش کن
          setTimeout(() => {
            target.classList.remove("flash-highlight");
          }, 2000);
        }, 500);
      }
    }
  });
</script>





<?php require_once __DIR__ . '/../layouts/footer.php'; ?>