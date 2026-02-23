<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';

$csrf = htmlspecialchars((string)($_SESSION['_csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8');
$currentUserId = (int)($_SESSION['USER']->id ?? 0);
$currentUserRole = (string)($_SESSION['USER']->role ?? 'guest');
$canManageUsers = ($currentUserRole === 'admin');
$newUsersCount = is_array($newUsers ?? null) ? count($newUsers) : 0;

$roleLabels = [
    'admin' => 'ادمین',
    'guide' => 'معلم راهنما',
    'teacher' => 'معلم',
    'student' => 'دانش‌آموز',
];

$defaultPerPage = 20;
$perPageOptions = [10, 20, 50, 100];
?>

<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">
    <div class="rounded-3xl glass-card p-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">مدیریت کاربران</h1>
        </div>
        <a href="<?= htmlspecialchars($CFG->wwwroot . '/users/unregistered', ENT_QUOTES, 'UTF-8') ?>"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 text-white text-sm font-semibold hover:opacity-90 transition">
            <span>کاربران ثبت‌نام‌نشده</span>
            <span class="inline-flex items-center justify-center min-w-6 h-6 px-2 rounded-full bg-white/20 text-xs"><?= (int)$newUsersCount ?></span>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl glass-card p-4">
            <div class="text-sm text-slate-500">کل کاربران</div>
            <div class="text-2xl font-bold"><?= (int)$total ?></div>
        </div>
        <div class="rounded-2xl glass-card p-4">
            <div class="text-sm text-slate-500">کاربران فعال</div>
            <div class="text-2xl font-bold text-emerald-700"><?= (int)($suspendStats['active'] ?? 0) ?></div>
        </div>
        <div class="rounded-2xl glass-card p-4">
            <div class="text-sm text-slate-500">کاربران غیرفعال</div>
            <div class="text-2xl font-bold text-rose-700"><?= (int)($suspendStats['suspended'] ?? 0) ?></div>
        </div>
        <div class="rounded-2xl glass-card p-4">
            <div class="text-sm text-slate-500">ادمین / راهنما / معلم / دانش‌آموز</div>
            <div class="text-sm font-bold leading-7">
                <?= (int)($roleStats['admin'] ?? 0) ?> /
                <?= (int)($roleStats['guide'] ?? 0) ?> /
                <?= (int)($roleStats['teacher'] ?? 0) ?> /
                <?= (int)($roleStats['student'] ?? 0) ?>
            </div>
        </div>
    </div>

    <section class="rounded-3xl glass-card p-5 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <label for="usersSearchInput" class="block text-sm font-semibold text-slate-700 mb-1">جست‌وجو (نام، ایمیل، یوزرنیم، شناسه)</label>
                <input id="usersSearchInput" type="text"
                       class="w-full rounded-xl border border-slate-300 bg-white/85 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-200 focus:border-sky-400"
                       placeholder="مثلا: علی یا 1023">
            </div>
            <div>
                <label for="usersRoleFilter" class="block text-sm font-semibold text-slate-700 mb-1">نقش</label>
                <select id="usersRoleFilter" class="w-full rounded-xl border border-slate-300 bg-white/85 px-3 py-2 text-sm">
                    <option value="">همه</option>
                    <?php foreach ($roleLabels as $rk => $rv): ?>
                        <option value="<?= htmlspecialchars($rk, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($rv, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="usersStatusFilter" class="block text-sm font-semibold text-slate-700 mb-1">وضعیت</label>
                <select id="usersStatusFilter" class="w-full rounded-xl border border-slate-300 bg-white/85 px-3 py-2 text-sm">
                    <option value="">همه</option>
                    <option value="active">فعال</option>
                    <option value="suspended">غیرفعال</option>
                </select>
            </div>
            <div>
                <label for="usersPerPage" class="block text-sm font-semibold text-slate-700 mb-1">تعداد در صفحه</label>
                <select id="usersPerPage" class="w-full rounded-xl border border-slate-300 bg-white/85 px-3 py-2 text-sm">
                    <?php foreach ($perPageOptions as $opt): ?>
                        <option value="<?= (int)$opt ?>" <?= ($opt === $defaultPerPage ? 'selected' : '') ?>><?= (int)$opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" id="usersToggleAllBtn" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">نمایش همه</button>
            <button type="button" id="usersResetBtn" class="px-4 py-2 rounded-xl bg-slate-600 text-white text-sm font-semibold hover:bg-slate-700 transition">حذف فیلتر</button>
            <span id="usersFilterCount" class="text-xs text-slate-500">-</span>
        </div>
    </section>

    <section class="rounded-3xl glass-card p-5 space-y-4">
        <form id="bulkUsersForm" method="post" action="<?= htmlspecialchars($CFG->wwwroot . '/users/bulk', ENT_QUOTES, 'UTF-8') ?>" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="_csrf" value="<?= $csrf ?>">
            <div id="bulkSelectedUsers"></div>
            <select id="bulkAction" name="bulk_action" class="rounded-xl border border-slate-300 bg-white/85 px-3 py-2 text-sm">
                <option value="">عملیات گروهی</option>
                <option value="activate">فعال‌سازی</option>
                <option value="suspend">غیرفعال‌سازی</option>
                <option value="role">تغییر نقش</option>
            </select>
            <select id="bulkRole" name="bulk_role" class="rounded-xl border border-slate-300 bg-white/85 px-3 py-2 text-sm hidden">
                <?php foreach ($roleLabels as $rk => $rv): ?>
                    <option value="<?= htmlspecialchars($rk, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($rv, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($canManageUsers): ?>
                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">اجرای عملیات</button>
            <?php endif; ?>
            <span class="text-xs text-slate-500"><?= $canManageUsers ? 'نکته: عملیات گروهی روی کاربران انتخاب‌شده اعمال می‌شود.' : 'حالت راهنما: فقط مشاهده' ?></span>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-white/70">
            <table class="min-w-[1200px] w-full text-sm">
                <thead class="bg-white/80 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 border text-center"><input id="checkAllUsers" type="checkbox" class="w-4 h-4" <?= $canManageUsers ? '' : 'disabled' ?>></th>
                        <th class="px-3 py-2 border">شناسه داخلی</th>
                        <th class="px-3 py-2 border">شناسه مودل</th>
                        <th class="px-3 py-2 border">مشخصات</th>
                        <th class="px-3 py-2 border">نقش</th>
                        <th class="px-3 py-2 border">پروفایل‌ها</th>
                        <th class="px-3 py-2 border">وضعیت</th>
                        <th class="px-3 py-2 border">عملیات</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                <?php foreach (($users ?? []) as $u): ?>
                    <?php
                        $uid = (int)($u['id'] ?? 0);
                        $isSelf = ($uid === $currentUserId);
                        $role = (string)($u['role'] ?? 'student');
                        $isSuspended = ((int)($u['suspend'] ?? 0) === 1);
                        $status = $isSuspended ? 'suspended' : 'active';
                        $fullName = (string)($u['mdl_fullname'] ?? ('کاربر #' . $uid));
                        $email = (string)($u['mdl_email'] ?? '-');
                        $username = (string)($u['mdl_username'] ?? '-');
                        $searchBlob = strtolower(trim(implode(' ', [
                            $uid,
                            (int)($u['mdl_id'] ?? 0),
                            $fullName,
                            $email,
                            $username,
                            $roleLabels[$role] ?? $role,
                            $role,
                            $status,
                        ])));
                    ?>
                    <tr class="hover:bg-white/60 user-row"
                        data-id="<?= $uid ?>"
                        data-role="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>"
                        data-status="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>"
                        data-search="<?= htmlspecialchars($searchBlob, ENT_QUOTES, 'UTF-8') ?>">
                        <td class="px-3 py-2 border text-center">
                            <?php if ($canManageUsers && !$isSelf): ?>
                                <input type="checkbox" value="<?= $uid ?>" class="user-check w-4 h-4">
                            <?php else: ?>
                                <span class="text-xs text-slate-500">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-2 border font-mono"><?= $uid ?></td>
                        <td class="px-3 py-2 border font-mono"><?= (int)($u['mdl_id'] ?? 0) ?></td>
                        <td class="px-3 py-2 border">
                            <div class="flex items-center gap-3">
                                <img src="<?= htmlspecialchars((trim((string)($u['mdl_profileimageurl'] ?? '')) !== '' ? trim((string)($u['mdl_profileimageurl'] ?? '')) : ($CFG->assets . '/images/site-icon.png')), ENT_QUOTES, 'UTF-8') ?>"
                                     class="w-10 h-10 rounded-xl object-cover ring-1 ring-white/70"
                                     alt="تصویر کاربر">
                                <div>
                                    <div class="font-semibold text-slate-800"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="text-xs text-slate-500"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="text-xs text-slate-500"><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2 border align-top">
                            <?php if ($canManageUsers): ?>
                                <form method="post" action="<?= htmlspecialchars($CFG->wwwroot . '/users/role', ENT_QUOTES, 'UTF-8') ?>" class="flex items-center gap-2">
                                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                                    <input type="hidden" name="user_id" value="<?= $uid ?>">
                                    <select name="role" class="rounded-lg border border-slate-300 px-2 py-1 text-xs" <?= $isSelf ? 'disabled' : '' ?>>
                                        <?php foreach ($roleLabels as $rk => $rv): ?>
                                            <option value="<?= htmlspecialchars($rk, ENT_QUOTES, 'UTF-8') ?>" <?= ($role === $rk ? 'selected' : '') ?>><?= htmlspecialchars($rv, ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="px-2 py-1 rounded-lg bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600 transition" <?= $isSelf ? 'disabled' : '' ?>>
                                        ذخیره
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-700"><?= htmlspecialchars($roleLabels[$role] ?? $role, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-2 border">
                            <div class="flex flex-wrap gap-1">
                                <?php if (!empty($u['teacher_profile_id'])): ?>
                                    <span class="px-2 py-1 rounded-full text-xs <?= !empty($u['teacher_profile_active']) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' ?>">معلم</span>
                                <?php endif; ?>
                                <?php if (!empty($u['student_profile_id'])): ?>
                                    <span class="px-2 py-1 rounded-full text-xs <?= !empty($u['student_profile_active']) ? 'bg-sky-100 text-sky-800' : 'bg-slate-200 text-slate-700' ?>">دانش‌آموز</span>
                                <?php endif; ?>
                                <?php if (empty($u['teacher_profile_id']) && empty($u['student_profile_id'])): ?>
                                    <span class="text-xs text-slate-500">بدون پروفایل فرعی</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-3 py-2 border">
                            <?php if ($isSuspended): ?>
                                <span class="px-2 py-1 rounded-full bg-rose-100 text-rose-700 text-xs">غیرفعال</span>
                            <?php else: ?>
                                <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs">فعال</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-2 border">
                            <?php if ($canManageUsers): ?>
                                <form method="post" action="<?= htmlspecialchars($CFG->wwwroot . '/users/suspend', ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                                    <input type="hidden" name="user_id" value="<?= $uid ?>">
                                    <input type="hidden" name="suspend" value="<?= $isSuspended ? 0 : 1 ?>">
                                    <button type="submit" class="px-3 py-1 rounded-lg text-xs font-semibold text-white <?= $isSuspended ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700' ?> transition" <?= $isSelf ? 'disabled' : '' ?>>
                                        <?= $isSuspended ? 'فعال‌سازی' : 'غیرفعال‌سازی' ?>
                                    </button>
                                </form>
                                <?php if ($isSelf): ?>
                                    <div class="text-[11px] text-slate-500 mt-1">کاربر جاری</div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-xs text-slate-500">فقط مشاهده</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr id="usersEmptyState" class="hidden">
                    <td colspan="8" class="px-4 py-6 text-center text-slate-500">کاربری با فیلتر فعلی پیدا نشد.</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div id="usersPagination" class="flex flex-wrap items-center gap-2">
            <button id="usersPrevPage" type="button" class="px-3 py-2 rounded-xl bg-white/80 border border-slate-200 text-sm disabled:opacity-50 disabled:cursor-not-allowed">قبلی</button>
            <span id="usersPageInfo" class="text-sm text-slate-600">-</span>
            <button id="usersNextPage" type="button" class="px-3 py-2 rounded-xl bg-white/80 border border-slate-200 text-sm disabled:opacity-50 disabled:cursor-not-allowed">بعدی</button>
        </div>
    </section>
</div>

<script>
$(function() {
    const canManageUsers = <?= $canManageUsers ? 'true' : 'false' ?>;

    const state = {
        search: '',
        role: '',
        status: '',
        perPage: <?= (int)$defaultPerPage ?>,
        page: 1,
        showAll: false,
    };

    const $search = $('#usersSearchInput');
    const $role = $('#usersRoleFilter');
    const $status = $('#usersStatusFilter');
    const $perPage = $('#usersPerPage');
    const $toggleAllBtn = $('#usersToggleAllBtn');
    const $resetBtn = $('#usersResetBtn');
    const $rows = $('#usersTableBody').find('tr.user-row');
    const $emptyState = $('#usersEmptyState');
    const $pagination = $('#usersPagination');
    const $prev = $('#usersPrevPage');
    const $next = $('#usersNextPage');
    const $pageInfo = $('#usersPageInfo');
    const $count = $('#usersFilterCount');

    const $bulkAction = $('#bulkAction');
    const $bulkRole = $('#bulkRole');
    const $bulkForm = $('#bulkUsersForm');
    const $bulkSelectedUsers = $('#bulkSelectedUsers');

    function normalize(value) {
        return String(value || '').toLowerCase().trim();
    }

    function syncBulkRole() {
        if ($bulkAction.val() === 'role') {
            $bulkRole.removeClass('hidden');
        } else {
            $bulkRole.addClass('hidden');
        }
    }

    function filteredRows() {
        const searchNeedle = normalize(state.search);

        return $rows.filter(function() {
            const $row = $(this);
            const rowRole = String($row.data('role') || '');
            const rowStatus = String($row.data('status') || '');
            const rowSearch = normalize($row.data('search'));

            if (state.role && rowRole !== state.role) return false;
            if (state.status && rowStatus !== state.status) return false;
            if (searchNeedle && !rowSearch.includes(searchNeedle)) return false;
            return true;
        });
    }

    function updateCheckAllState() {
        const visibleChecks = $('.user-check:visible');
        const checkedVisible = $('.user-check:visible:checked').length;
        const allChecked = visibleChecks.length > 0 && checkedVisible === visibleChecks.length;
        $('#checkAllUsers').prop('checked', allChecked);
    }

    function renderUsers() {
        $rows.hide();

        const $filtered = filteredRows();
        const totalFiltered = $filtered.length;

        if (state.showAll) {
            state.page = 1;
            $filtered.show();
            $pagination.hide();
            $toggleAllBtn.text('بازگشت به صفحه‌بندی');
        } else {
            const totalPages = Math.max(1, Math.ceil(totalFiltered / state.perPage));
            if (state.page > totalPages) state.page = totalPages;

            const start = (state.page - 1) * state.perPage;
            const end = start + state.perPage;
            $filtered.slice(start, end).show();

            $pagination.show();
            $pageInfo.text(`صفحه ${state.page} از ${totalPages}`);
            $prev.prop('disabled', state.page <= 1);
            $next.prop('disabled', state.page >= totalPages);
            $toggleAllBtn.text('نمایش همه');
        }

        $emptyState.toggle(totalFiltered === 0);
        $count.text(totalFiltered > 0 ? `${totalFiltered} کاربر` : 'موردی یافت نشد');

        updateCheckAllState();
    }

    function resetFilters() {
        state.search = '';
        state.role = '';
        state.status = '';
        state.perPage = <?= (int)$defaultPerPage ?>;
        state.page = 1;
        state.showAll = false;

        $search.val('');
        $role.val('');
        $status.val('');
        $perPage.val(String(state.perPage));
        renderUsers();
    }

    $search.on('input', function() {
        state.search = $(this).val();
        state.page = 1;
        renderUsers();
    });

    $role.on('change', function() {
        state.role = $(this).val();
        state.page = 1;
        renderUsers();
    });

    $status.on('change', function() {
        state.status = $(this).val();
        state.page = 1;
        renderUsers();
    });

    $perPage.on('change', function() {
        const value = parseInt($(this).val(), 10);
        state.perPage = Number.isFinite(value) && value > 0 ? value : <?= (int)$defaultPerPage ?>;
        state.page = 1;
        renderUsers();
    });

    $toggleAllBtn.on('click', function() {
        state.showAll = !state.showAll;
        state.page = 1;
        renderUsers();
    });

    $resetBtn.on('click', function() {
        resetFilters();
    });

    $prev.on('click', function() {
        if (state.page > 1) {
            state.page -= 1;
            renderUsers();
        }
    });

    $next.on('click', function() {
        state.page += 1;
        renderUsers();
    });

    $('#checkAllUsers').on('change', function() {
        if (!canManageUsers) return;
        $('.user-check:visible').prop('checked', $(this).is(':checked'));
    });

    $(document).on('change', '.user-check', function() {
        updateCheckAllState();
    });

    syncBulkRole();
    $bulkAction.on('change', syncBulkRole);

    $bulkForm.on('submit', function(e) {
        if (!canManageUsers) {
            e.preventDefault();
            return;
        }

        const selectedIds = $('.user-row:visible .user-check:checked').map(function() { return $(this).val(); }).get();

        if (!$bulkAction.val()) {
            e.preventDefault();
            alert('ابتدا نوع عملیات گروهی را انتخاب کنید.');
            return;
        }

        if (selectedIds.length === 0) {
            e.preventDefault();
            alert('حداقل یک کاربر را انتخاب کنید.');
            return;
        }

        if ($bulkAction.val() === 'role' && !$bulkRole.val()) {
            e.preventDefault();
            alert('نقش مقصد را انتخاب کنید.');
            return;
        }

        $bulkSelectedUsers.empty();
        selectedIds.forEach(function(id) {
            $('<input>', { type: 'hidden', name: 'user_ids[]', value: id }).appendTo($bulkSelectedUsers);
        });
    });

    renderUsers();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
