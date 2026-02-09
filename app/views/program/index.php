<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';

$timesMap = [];
if (!empty($times) && is_array($times)) {
    foreach ($times as $t) {
        $timesMap[$t['id']] = $t['label'];
    }
}

$coursesMap = [];
if (!empty($courses) && is_array($courses)) {
    foreach ($courses as $course) {
        $coursesMap[$course['id']] = ['name' => $course['name'], 'crsid' => $course['crsid']];
    }
}

$roomsMap = [];
if (!empty($rooms) && is_array($rooms)) {
    foreach ($rooms as $room) {
        $roomsMap[$room['id']] = $room['name'];
    }
}

$termsMap = [];
if (!empty($terms) && is_array($terms)) {
    foreach ($terms as $term) {
        $termsMap[$term['id']] = [
            'name' => $term['name'],
            'start' => $term['start'],
            'end' => $term['end']
        ];
    }
}

$usersMap = [];
if (!empty($users) && is_array($users)) {
    foreach ($users as $user) {
        $usersMap[$user['id']] = ['mdl_id' => $user['mdl_id'], 'role' => $user['role']];
    }
}

$MdlUsersMap = [];
if (!empty($Mdl_users) && is_array($Mdl_users)) {
    foreach ($Mdl_users as $Mdl_user) {
        $MdlUsersMap[$Mdl_user['id']] = ['firstname' => $Mdl_user['firstname'], 'lastname' => $Mdl_user['lastname']];
    }
}

$classesData = [];
if (!empty($classes) && is_array($classes)) {
    foreach ($classes as $c) {
        $classesData[] = [
            'id' => $c['id'],
            'term_id' => $c['term_id'],
            'teacher_id' => $c['teacher_id'],
            'room_id' => $c['room_id'],
            'times' => $c['time_list'] ?? []
        ];
    }
}

$prereqMap = [];
if (!empty($prereqs) && is_array($prereqs)) {
    foreach ($prereqs as $p) {
        $cid = $p['class_id'];
        if (!isset($prereqMap[$cid])) $prereqMap[$cid] = [];
        $prereqMap[$cid][] = [
            'course_id' => $p['course_id'],
            'alternative_text' => $p['alternative_text']
        ];
    }
}
?>

<div class="max-w-7xl mx-auto px-4 py-10">
    <?php if (!empty($msg)): ?>
        <div class="mb-6 p-4 rounded-2xl bg-blue-100 text-blue-700 border border-blue-300">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <h2 class="text-3xl font-extrabold mb-6">چیدمان</h2>

    <div class="mb-6 p-5 rounded-3xl glass-card">
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-base font-semibold text-gray-700 mb-1">ترم</label>
                <select id="termFilter" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-base bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
                    <?php foreach ($terms as $term): ?>
                        <option value="<?= htmlspecialchars($term['id']) ?>" <?= (isset($activeTermId) && $activeTermId == $term['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($term['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-base font-semibold text-gray-700 mb-1">جستجو</label>
                <input type="text" id="programSearch" placeholder="نام معلم یا نام کلاس..."
                       class="w-full rounded-xl border border-slate-200 px-3 py-3 text-base bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
            </div>
            <div>
                <label class="block text-base font-semibold text-gray-700 mb-1">فیلتر معلم</label>
                <select id="teacherFilter" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-base bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
                    <option value="">همه</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <?php
                            $teacherUser = $usersMap[$teacher['user_id']] ?? [];
                            $mdlUser = $MdlUsersMap[$teacherUser['mdl_id'] ?? 0] ?? [];
                            $fullName = trim(($mdlUser['firstname'] ?? '') . ' ' . ($mdlUser['lastname'] ?? ''));
                        ?>
                        <option value="<?= htmlspecialchars($teacher['id']) ?>"><?= htmlspecialchars($fullName ?: 'نامشخص') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-base font-semibold text-gray-700 mb-1">فیلتر مکان</label>
                <select id="roomFilter" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-base bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
                    <option value="">همه</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?= htmlspecialchars($room['id']) ?>"><?= htmlspecialchars($room['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-base font-semibold text-gray-700 mb-1">فیلتر دوره</label>
                <select id="courseFilter" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-base bg-white/80 focus:ring-2 focus:ring-teal-200 focus:border-teal-400">
                    <option value="">همه</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= htmlspecialchars($course['id']) ?>"><?= htmlspecialchars($course['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="text-sm text-gray-500" id="programFilterCount"></div>
        </div>
    </div>

    <div class="mb-6 p-4 rounded-3xl glass-card">
        <div class="flex flex-wrap gap-2">
            <?php foreach ($times as $t): ?>
                <button type="button"
                        class="time-btn px-4 py-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 text-white text-sm font-bold hover:opacity-90 transition"
                        data-time="<?= htmlspecialchars($t['id']) ?>">
                    <?= htmlspecialchars($t['label']) ?>
                </button>
            <?php endforeach; ?>
        </div>
        <p class="mt-3 text-xs text-slate-500">برای نمایش کلاس‌های یک زنگ، روی دکمه همان زنگ کلیک کنید.</p>
    </div>

    <?php if (Auth::hasPermission(role: 'admin')): ?>
    <div class="mb-6 p-5 rounded-3xl glass-card">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
            <div class="md:col-span-2">
                <label class="block text-base font-semibold text-gray-700 mb-1">انتخاب معلم برای افزودن کلاس</label>
                <select id="addTeacherSelect" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-base bg-white/80">
                    <option value="">ابتدا یک معلم انتخاب کنید...</option>
                </select>
            </div>
            <div class="text-xs text-slate-500" id="addTermHint"></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="overflow-x-auto rounded-3xl glass-card">
        <table class="min-w-[1200px] w-full border border-white/60 text-sm sm:text-base" id="programTable">
            <thead class="bg-white/80 backdrop-blur sticky top-0 text-sm uppercase tracking-wide text-slate-600">
                <tr>
                    <th class="px-5 py-4 border text-right">ترم</th>
                    <th class="px-5 py-4 border text-right">زمان‌ها</th>
                    <th class="px-5 py-4 border text-right">معلم</th>
                    <th class="px-5 py-4 border text-right">کلاس</th>
                    <th class="px-5 py-4 border text-right">مکان</th>
                    <th class="px-5 py-4 border text-right">هزینه</th>
                    <th class="px-5 py-4 border text-right">پیش‌نیازها</th>
                    <th class="px-5 py-4 border text-right">ظرفیت ۷</th>
                    <th class="px-5 py-4 border text-right">ظرفیت ۸</th>
                    <th class="px-5 py-4 border text-right">ظرفیت ۹</th>
                    <th class="px-5 py-4 border text-center">تنظیمات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classes as $class): ?>
                    <?php
                        $teacher = $teachers[array_search($class['teacher_id'], array_column($teachers, 'id'))] ?? null;
                        $teacherUser = $teacher ? ($usersMap[$teacher['user_id']] ?? []) : [];
                        $mdlUser = $teacherUser ? ($MdlUsersMap[$teacherUser['mdl_id']] ?? []) : [];
                        $teacherName = trim(($mdlUser['firstname'] ?? '') . ' ' . ($mdlUser['lastname'] ?? ''));
                        $timeList = $class['time_list'] ?? [];
                        $timeLabels = array_map(function($id) use ($timesMap) {
                            return $timesMap[$id] ?? $id;
                        }, $timeList);
                        $termInfo = $termsMap[$class['term_id']] ?? null;
                        $termActive = false;
                        if ($termInfo && !empty($termInfo['start']) && !empty($termInfo['end'])) {
                            $nowTs = time();
                            $termActive = ($nowTs >= (int)$termInfo['start'] && $nowTs <= (int)$termInfo['end']);
                        }
                        $prereqList = [];
                        $prereqsForClass = $prereqMap[$class['id']] ?? [];
                        foreach ($prereqsForClass as $p) {
                            if (!empty($p['course_id'])) {
                                $prereqList[] = $coursesMap[$p['course_id']]['name'] ?? 'دوره نامشخص';
                            } elseif (!empty($p['alternative_text'])) {
                                $prereqList[] = $p['alternative_text'];
                            }
                        }
                    ?>
                    <tr class="hover:bg-white/60 transition program-row"
                        data-id="<?= htmlspecialchars($class['id']) ?>"
                        data-term_id="<?= htmlspecialchars($class['term_id']) ?>"
                        data-term_active="<?= $termActive ? '1' : '0' ?>"
                        data-teacher_id="<?= htmlspecialchars($class['teacher_id']) ?>"
                        data-course_id="<?= htmlspecialchars($class['course_id']) ?>"
                        data-room_id="<?= htmlspecialchars($class['room_id']) ?>"
                        data-price="<?= htmlspecialchars($class['price'] ?? '') ?>"
                        data-seat7="<?= htmlspecialchars($class['seat7'] ?? '') ?>"
                        data-seat8="<?= htmlspecialchars($class['seat8'] ?? '') ?>"
                        data-seat9="<?= htmlspecialchars($class['seat9'] ?? '') ?>"
                        data-times="<?= htmlspecialchars(implode(',', $timeList)) ?>">
                        <td class="px-5 py-4 border"><?= htmlspecialchars($termsMap[$class['term_id']]['name'] ?? '-') ?></td>
                        <td class="px-5 py-4 border">
                            <div class="flex flex-wrap gap-1">
                                <?php foreach ($timeLabels as $label): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-slate-200 text-slate-700"><?= htmlspecialchars($label) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td class="px-5 py-4 border"><?= htmlspecialchars($teacherName ?: '-') ?></td>
                        <td class="px-5 py-4 border"><?= htmlspecialchars($coursesMap[$class['course_id']]['name'] ?? '-') ?></td>
                        <td class="px-5 py-4 border"><?= htmlspecialchars($roomsMap[$class['room_id']] ?? '-') ?></td>
                        <td class="px-5 py-4 border"><?= htmlspecialchars($class['price'] ?? '-') ?></td>
                        <td class="px-5 py-4 border text-xs text-slate-600">
                            <?= !empty($prereqList) ? htmlspecialchars(implode('، ', $prereqList)) : '-' ?>
                        </td>
                        <td class="px-5 py-4 border text-xs text-slate-600"><?= htmlspecialchars($class['seat7'] ?? '-') ?></td>
                        <td class="px-5 py-4 border text-xs text-slate-600"><?= htmlspecialchars($class['seat8'] ?? '-') ?></td>
                        <td class="px-5 py-4 border text-xs text-slate-600"><?= htmlspecialchars($class['seat9'] ?? '-') ?></td>
                        <td class="px-5 py-4 border text-center">
                            <?php if (Auth::hasPermission(role: 'admin')): ?>
                                <div class="flex flex-wrap gap-2 justify-center">
                                    <button class="editProgramBtn px-4 py-2 rounded-xl bg-gradient-to-r from-amber-400 to-orange-500 text-white text-sm font-bold hover:opacity-90 transition shadow <?= $termActive ? '' : 'opacity-50 cursor-not-allowed' ?>" <?= $termActive ? '' : 'disabled' ?>>
                                        ویرایش
                                    </button>
                                    <button class="deleteProgramBtn px-4 py-2 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 text-white text-sm font-bold hover:opacity-90 transition shadow <?= $termActive ? '' : 'opacity-50 cursor-not-allowed' ?>" <?= $termActive ? '' : 'disabled' ?>>
                                        حذف
                                    </button>
                                </div>
                            <?php else: ?>
                                <span class="text-xs text-slate-400">فقط مشاهده</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="floatingMsg"
     class="fixed top-4 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-2xl text-white font-bold shadow-lg hidden z-[9999]">
</div>

<div id="editProgramModal" class="fixed inset-0 hidden z-50 flex justify-center items-center">
    <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur"></div>
    <div class="flex items-center justify-center content-center h-full w-full">
        <div class="rounded-3xl p-6 w-full max-w-lg relative z-10 overflow-y-auto max-h-[95vh] glass-card">
            <button id="closeEditProgramModal"
                    class="absolute top-4 right-4 text-white bg-red-500 hover:bg-red-600 w-7 h-7 text-2xl rounded-full flex items-center justify-center font-bold">&times;</button>
            <h2 class="text-xl font-bold mb-4 text-center">ویرایش کلاس</h2>
            <form id="editProgramForm" class="grid gap-3">
                <input type="hidden" id="editProgramId" name="id">
                <input type="hidden" id="editPrereqs" name="prereqs">
                <select name="term_id" id="editTermId" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                    <?php foreach ($terms as $term): ?>
                        <?php
                            $nowTs = time();
                            $termActiveOpt = (!empty($term['start']) && !empty($term['end']) && $nowTs >= (int)$term['start'] && $nowTs <= (int)$term['end']);
                        ?>
                        <option value="<?= htmlspecialchars($term['id']) ?>" <?= $termActiveOpt ? '' : 'disabled' ?>><?= htmlspecialchars($term['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="teacher_id" id="editTeacherId" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                    <?php foreach ($teachers as $teacher): ?>
                        <?php
                            $teacherUser = $usersMap[$teacher['user_id']] ?? [];
                            $mdlUser = $MdlUsersMap[$teacherUser['mdl_id'] ?? 0] ?? [];
                            $fullName = trim(($mdlUser['firstname'] ?? '') . ' ' . ($mdlUser['lastname'] ?? ''));
                        ?>
                        <option value="<?= htmlspecialchars($teacher['id']) ?>"><?= htmlspecialchars($fullName ?: 'نامشخص') ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="course_id" id="editCourseId" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= htmlspecialchars($course['id']) ?>"><?= htmlspecialchars($course['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="room_id" id="editRoomId" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?= htmlspecialchars($room['id']) ?>"><?= htmlspecialchars($room['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="price" id="editPrice" placeholder="هزینه" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                <div class="grid grid-cols-3 gap-2">
                    <input type="number" name="seat7" id="editSeat7" placeholder="هفتم" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                    <input type="number" name="seat8" id="editSeat8" placeholder="هشتم" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                    <input type="number" name="seat9" id="editSeat9" placeholder="نهم" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-700 mb-2">زمان‌ها</p>
                    <div class="flex flex-wrap gap-3" id="editTimesWrap">
                        <?php foreach ($times as $t): ?>
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="times[]" value="<?= htmlspecialchars($t['id']) ?>" class="edit-time-checkbox w-5 h-5 text-teal-600">
                                <span class="text-sm"><?= htmlspecialchars($t['label']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-2" id="editTimesPreview"></div>
                </div>
                <details class="rounded-2xl border border-slate-200 bg-white/70 p-3">
                    <summary class="cursor-pointer text-sm font-semibold text-slate-700">پیش‌نیازها</summary>
                    <div class="mt-3 grid gap-3">
                        <div class="flex items-center gap-4 text-sm">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="editPrereqType" value="none" checked class="text-teal-600">
                                بدون پیش‌نیاز
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="editPrereqType" value="course" class="text-teal-600">
                                دوره
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="editPrereqType" value="text" class="text-teal-600">
                                متن
                            </label>
                        </div>
                        <select id="editPrereqCourse" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                            <option value="">انتخاب دوره...</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= htmlspecialchars($course['id']) ?>"><?= htmlspecialchars($course['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" id="editPrereqText" placeholder="متن پیش‌نیاز (مثلاً داشتن لپتاپ)" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80 hidden">
                        <button type="button" id="editPrereqBtn" class="px-3 py-2 rounded-xl bg-slate-700 text-white text-sm">افزودن پیش‌نیاز</button>
                        <div id="editPrereqList" class="flex flex-wrap gap-2"></div>
                    </div>
                </details>
                <button type="submit" class="w-full px-6 py-3 rounded-2xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white font-bold shadow-md hover:shadow-lg transition">
                    ذخیره تغییرات
                </button>
            </form>
        </div>
    </div>
</div>

<div id="addProgramModal" class="fixed inset-0 hidden z-50 flex justify-center items-center">
    <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur"></div>
    <div class="flex items-center justify-center content-center h-full w-full">
        <div class="rounded-3xl p-6 w-full max-w-lg relative z-10 overflow-y-auto max-h-[95vh] glass-card">
            <button id="closeAddProgramModal"
                    class="absolute top-4 right-4 text-white bg-red-500 hover:bg-red-600 w-7 h-7 text-2xl rounded-full flex items-center justify-center font-bold">&times;</button>
            <h2 class="text-xl font-bold mb-4 text-center">افزودن کلاس</h2>
            <form id="addProgramForm" class="grid gap-3">
                <input type="hidden" id="addTermId" name="term_id">
                <input type="hidden" id="addTeacherId" name="teacher_id">
                <input type="hidden" id="addPrereqs" name="prereqs">
                <div class="text-sm text-slate-600">
                    <span class="font-semibold">ترم:</span> <span id="addTermLabel"></span>
                </div>
                <div class="text-sm text-slate-600">
                    <span class="font-semibold">معلم:</span> <span id="addTeacherLabel"></span>
                </div>
                <select name="course_id" id="addCourseId" required class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80"></select>
                <select name="room_id" id="addRoomId" required class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80"></select>
                <input type="text" name="price" id="addPrice" placeholder="هزینه" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                <div class="grid grid-cols-3 gap-2">
                    <input type="number" name="seat7" placeholder="هفتم" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                    <input type="number" name="seat8" placeholder="هشتم" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                    <input type="number" name="seat9" placeholder="نهم" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-700 mb-2">زمان‌ها</p>
                    <div class="flex flex-wrap gap-3" id="addTimesWrap">
                        <?php foreach ($times as $t): ?>
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="times[]" value="<?= htmlspecialchars($t['id']) ?>" class="add-time-checkbox w-5 h-5 text-teal-600">
                                <span class="text-sm"><?= htmlspecialchars($t['label']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-2" id="addTimesPreview"></div>
                </div>
                <details class="rounded-2xl border border-slate-200 bg-white/70 p-3">
                    <summary class="cursor-pointer text-sm font-semibold text-slate-700">پیش‌نیازها</summary>
                    <div class="mt-3 grid gap-3">
                        <div class="flex items-center gap-4 text-sm">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="addPrereqType" value="none" checked class="text-teal-600">
                                بدون پیش‌نیاز
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="addPrereqType" value="course" class="text-teal-600">
                                دوره
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="addPrereqType" value="text" class="text-teal-600">
                                متن
                            </label>
                        </div>
                        <select id="addPrereqCourse" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80">
                            <option value="">انتخاب دوره...</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= htmlspecialchars($course['id']) ?>"><?= htmlspecialchars($course['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" id="addPrereqText" placeholder="متن پیش‌نیاز (مثلاً داشتن لپتاپ)" class="rounded-xl border border-slate-200 px-3 py-2 bg-white/80 hidden">
                        <button type="button" id="addPrereqBtn" class="px-3 py-2 rounded-xl bg-slate-700 text-white text-sm">افزودن پیش‌نیاز</button>
                        <div id="addPrereqList" class="flex flex-wrap gap-2"></div>
                    </div>
                </details>
                <button type="submit" id="addProgramSubmit" class="w-full px-6 py-3 rounded-2xl bg-gradient-to-r from-teal-600 to-emerald-500 text-white font-bold shadow-md hover:opacity-90 transition">
                    ثبت کلاس
                </button>
            </form>
        </div>
    </div>
</div>

<script>
const programState = {
    activeTime: null,
    activeTerm: $('#termFilter').val() || ''
};

const termsMap = <?= json_encode($termsMap) ?>;
const coursesMap = <?= json_encode($coursesMap) ?>;
const roomsMap = <?= json_encode($roomsMap) ?>;
const timesMap = <?= json_encode($timesMap) ?>;
let classesData = <?= json_encode($classesData) ?>;
const prereqMap = <?= json_encode($prereqMap) ?>;
const teachersMap = (function(){
    const map = {};
    <?php foreach ($teachers as $teacher): ?>
        map["<?= $teacher['id'] ?>"] = <?= json_encode($teacher['courses'] ?? []) ?>;
    <?php endforeach; ?>
    return map;
})();
const teacherNameMap = (function(){
    const map = {};
    <?php foreach ($teachers as $teacher): ?>
        <?php
            $teacherUser = $usersMap[$teacher['user_id']] ?? [];
            $mdlUser = $MdlUsersMap[$teacherUser['mdl_id'] ?? 0] ?? [];
            $fullName = trim(($mdlUser['firstname'] ?? '') . ' ' . ($mdlUser['lastname'] ?? ''));
        ?>
        map["<?= $teacher['id'] ?>"] = <?= json_encode($fullName ?: 'نامشخص') ?>;
    <?php endforeach; ?>
    return map;
})();

function isTermActive(termId) {
    const term = termsMap[termId];
    if (!term) return false;
    const now = Math.floor(Date.now() / 1000);
    const start = parseInt(term.start || 0, 10);
    const end = parseInt(term.end || 0, 10);
    return start && end && now >= start && now <= end;
}

function getBusyTeachers(termId, timeId) {
    const busy = new Set();
    classesData.forEach(c => {
        if (c.term_id.toString() === termId.toString() && (c.times || []).map(String).includes(timeId.toString())) {
            busy.add(c.teacher_id.toString());
        }
    });
    return busy;
}

function getBusyRooms(termId, timeId) {
    const busy = new Set();
    classesData.forEach(c => {
        if (c.term_id.toString() === termId.toString() && (c.times || []).map(String).includes(timeId.toString())) {
            busy.add(c.room_id.toString());
        }
    });
    return busy;
}

function updateAddTeacherOptions() {
    const termId = programState.activeTerm;
    const timeId = programState.activeTime;
    const select = $('#addTeacherSelect');
    select.empty();
    select.append('<option value="">ابتدا یک معلم انتخاب کنید...</option>');

    if (!termId || !timeId || !isTermActive(termId)) {
        select.prop('disabled', true);
        return;
    }

    select.prop('disabled', false);
    const busyTeachers = getBusyTeachers(termId, timeId);
    Object.keys(teacherNameMap).forEach(id => {
        if (!busyTeachers.has(id.toString())) {
            select.append(`<option value="${id}">${teacherNameMap[id]}</option>`);
        }
    });
}

function updateAvailableRooms(termId, timeId) {
    const select = $('#addRoomId');
    const busyRooms = getBusyRooms(termId, timeId);
    let options = '';
    Object.keys(roomsMap).forEach(id => {
        if (!busyRooms.has(id.toString())) {
            options += `<option value="${id}">${roomsMap[id]}</option>`;
        }
    });
    if (!options) {
        options = '<option value="">مکان خالی وجود ندارد</option>';
    }
    select.html(options);
}

function showFloatingMsg(text, type='success') {
    let msgDiv = $('#floatingMsg');
    msgDiv.text(text)
          .removeClass('bg-green-600 bg-red-600 bg-blue-600')
          .addClass(type === 'success' ? 'bg-green-600' : (type === 'error' ? 'bg-red-600' : 'bg-blue-600'))
          .fadeIn(200);
    setTimeout(() => { msgDiv.fadeOut(500); }, 3000);
}

function applyProgramFilters() {
    const search = ($('#programSearch').val() || '').toLowerCase().trim();
    const termFilter = (programState.activeTerm || '').toString();
    const timeFilter = (programState.activeTime || '').toString();
    const teacherFilter = ($('#teacherFilter').val() || '').toString();
    const roomFilter = ($('#roomFilter').val() || '').toString();
    const courseFilter = ($('#courseFilter').val() || '').toString();
    let visibleCount = 0;

    $('.program-row').each(function() {
        const row = $(this);
        const termId = row.data('term_id').toString();
        const times = (row.data('times') || '').toString().split(',').filter(Boolean);
        const teacherId = row.data('teacher_id').toString();
        const roomId = row.data('room_id').toString();
        const courseId = row.data('course_id').toString();
        const text = row.text().toLowerCase();

        const matchesSearch = !search || text.includes(search);
        const matchesTerm = !termFilter || termId === termFilter;
        const matchesTime = !timeFilter || times.includes(timeFilter);
        const matchesTeacher = !teacherFilter || teacherId === teacherFilter;
        const matchesRoom = !roomFilter || roomId === roomFilter;
        const matchesCourse = !courseFilter || courseId === courseFilter;

        const shouldShow = matchesSearch && matchesTerm && matchesTime && matchesTeacher && matchesRoom && matchesCourse;
        row.toggle(shouldShow);
        if (shouldShow) visibleCount += 1;
    });

    $('#programFilterCount').text(`نمایش ${visibleCount} کلاس`);
}

function setDefaultTimeCheckboxes(wrapperSelector) {
    const activeTime = programState.activeTime;
    if (!activeTime) return;
    const wrapper = $(wrapperSelector);
    if (wrapper.find('input[type="checkbox"]:checked').length === 0) {
        wrapper.find(`input[type="checkbox"][value="${activeTime}"]`).prop('checked', true);
    }
}

function updateTimesPreview(wrapperSelector, previewSelector) {
    const selected = [];
    $(wrapperSelector).find('input[type="checkbox"]:checked').each(function() {
        const id = $(this).val();
        selected.push(timesMap[id] || id);
    });
    const html = selected.length
        ? selected.map(t => `<span class="px-2 py-1 text-xs rounded-full bg-emerald-100 text-emerald-700">${t}</span>`).join('')
        : '<span class="text-xs text-slate-400">زمانی انتخاب نشده است.</span>';
    $(previewSelector).html(html);
}

let addPrereqItems = [];
let editPrereqItems = [];

function renderPrereqList(items, containerSelector) {
    const container = $(containerSelector);
    if (!items.length) {
        container.html('<span class="text-xs text-slate-400">پیش‌نیازی ثبت نشده است.</span>');
        return;
    }
    const html = items.map((item, idx) => {
        const label = item.type === 'course'
            ? (coursesMap[item.course_id]?.name || 'دوره نامشخص')
            : item.text;
        return `
            <span class="inline-flex items-center gap-2 bg-slate-100 text-slate-700 px-2 py-1 rounded-full text-xs">
                ${label}
                <button type="button" data-idx="${idx}" class="removePrereq text-rose-600">×</button>
            </span>
        `;
    }).join('');
    container.html(html);
}

function syncPrereqHidden(items, hiddenSelector) {
    $(hiddenSelector).val(JSON.stringify(items));
}

function togglePrereqInputs(prefix) {
    const type = $(`input[name="${prefix}PrereqType"]:checked`).val();
    if (type === 'course') {
        $(`#${prefix}PrereqCourse`).removeClass('hidden');
        $(`#${prefix}PrereqText`).addClass('hidden');
        $(`#${prefix}PrereqBtn`).prop('disabled', false).removeClass('opacity-50');
    } else if (type === 'text') {
        $(`#${prefix}PrereqCourse`).addClass('hidden');
        $(`#${prefix}PrereqText`).removeClass('hidden');
        $(`#${prefix}PrereqBtn`).prop('disabled', false).removeClass('opacity-50');
    } else {
        $(`#${prefix}PrereqCourse`).addClass('hidden');
        $(`#${prefix}PrereqText`).addClass('hidden');
        $(`#${prefix}PrereqBtn`).prop('disabled', true).addClass('opacity-50');
        if (prefix === 'add') {
            addPrereqItems = [];
            renderPrereqList(addPrereqItems, '#addPrereqList');
            syncPrereqHidden(addPrereqItems, '#addPrereqs');
        } else {
            editPrereqItems = [];
            renderPrereqList(editPrereqItems, '#editPrereqList');
            syncPrereqHidden(editPrereqItems, '#editPrereqs');
        }
    }
}
function renderTimeBadges(times) {
    const labels = times.map(id => timesMap[id] || id);
    return labels.map(l => `<span class="px-2 py-1 text-xs rounded-full bg-slate-200 text-slate-700">${l}</span>`).join('');
}

function renderProgramRow(data) {
    const times = (data.time_list || data.time || '').toString().split(',').filter(Boolean);
    const termName = termsMap[data.term_id]?.name || '-';
    const termActive = isTermActive(data.term_id);
    const teacherName = teacherNameMap[data.teacher_id] || '-';
    const courseName = coursesMap[data.course_id]?.name || '-';
    const roomName = roomsMap[data.room_id] || '-';
    const prereqItems = prereqMap[data.id] || [];
    const prereqLabels = prereqItems.map(p => {
        if (p.course_id) return coursesMap[p.course_id]?.name || 'دوره نامشخص';
        return p.alternative_text || p.text || '';
    }).filter(Boolean);
    return `
        <tr class="hover:bg-white/60 transition program-row"
            data-id="${data.id}"
            data-term_id="${data.term_id}"
            data-term_active="${termActive ? '1' : '0'}"
            data-teacher_id="${data.teacher_id}"
            data-course_id="${data.course_id}"
            data-room_id="${data.room_id}"
            data-price="${data.price || ''}"
            data-seat7="${data.seat7 || ''}"
            data-seat8="${data.seat8 || ''}"
            data-seat9="${data.seat9 || ''}"
            data-times="${times.join(',')}">
            <td class="px-5 py-4 border">${termName}</td>
            <td class="px-5 py-4 border"><div class="flex flex-wrap gap-1">${renderTimeBadges(times)}</div></td>
            <td class="px-5 py-4 border">${teacherName}</td>
            <td class="px-5 py-4 border">${courseName}</td>
            <td class="px-5 py-4 border">${roomName}</td>
            <td class="px-5 py-4 border">${data.price || '-'}</td>
            <td class="px-5 py-4 border text-xs text-slate-600">${prereqLabels.length ? prereqLabels.join('، ') : '-'}</td>
            <td class="px-5 py-4 border text-xs text-slate-600">${data.seat7 || '-'}</td>
            <td class="px-5 py-4 border text-xs text-slate-600">${data.seat8 || '-'}</td>
            <td class="px-5 py-4 border text-xs text-slate-600">${data.seat9 || '-'}</td>
            <td class="px-5 py-4 border text-center">
                <div class="flex flex-wrap gap-2 justify-center">
                    <button class="editProgramBtn px-4 py-2 rounded-xl bg-gradient-to-r from-amber-400 to-orange-500 text-white text-sm font-bold hover:opacity-90 transition shadow ${termActive ? '' : 'opacity-50 cursor-not-allowed'}" ${termActive ? '' : 'disabled'}>
                        ویرایش
                    </button>
                    <button class="deleteProgramBtn px-4 py-2 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 text-white text-sm font-bold hover:opacity-90 transition shadow ${termActive ? '' : 'opacity-50 cursor-not-allowed'}" ${termActive ? '' : 'disabled'}>
                        حذف
                    </button>
                </div>
            </td>
        </tr>
    `;
}

function refreshCourseOptions(teacherId, selectId) {
    const courses = teachersMap[teacherId] || [];
    const select = $('#' + selectId);
    let options = courses.map(id => {
        const name = coursesMap[id]?.name || 'نامشخص';
        return `<option value="${id}">${name}</option>`;
    }).join('');
    if (!options) {
        options = '<option value="">دوره‌ای یافت نشد</option>';
    }
    select.html(options);
    const hasValid = courses.length > 0;
    if (selectId === 'addCourseId') {
        $('#addProgramSubmit').prop('disabled', !hasValid).toggleClass('opacity-50', !hasValid);
    }
}

function updatePrereqCourseOptions(prefix, selectedCourseId) {
    const select = $(`#${prefix}PrereqCourse`);
    let options = '<option value="">انتخاب دوره...</option>';
    Object.keys(coursesMap).forEach(id => {
        if (selectedCourseId && id.toString() === selectedCourseId.toString()) return;
        options += `<option value="${id}">${coursesMap[id].name}</option>`;
    });
    select.html(options);
}

function updateAddTermState() {
    const termId = $('#addTermId').val();
    const active = isTermActive(termId);
    $('#addProgramSubmit').prop('disabled', !active).toggleClass('opacity-50', !active);
    $('#addTermHint').text(active ? '' : 'این ترم غیرفعال است و امکان افزودن کلاس وجود ندارد.');
}

function setActiveTimeButton(timeId) {
    $('.time-btn')
        .removeClass('from-indigo-700 to-blue-700 ring-2 ring-white/80')
        .addClass('from-sky-500 to-indigo-600');
    const btn = $(`.time-btn[data-time="${timeId}"]`);
    btn.removeClass('from-sky-500 to-indigo-600')
       .addClass('from-indigo-700 to-blue-700 ring-2 ring-white/80');
}

$(function() {
    const firstTimeBtn = $('.time-btn').first();
    if (firstTimeBtn.length) {
        programState.activeTime = firstTimeBtn.data('time').toString();
        setActiveTimeButton(programState.activeTime);
        setDefaultTimeCheckboxes('#addTimesWrap');
    }

    $('.time-btn').on('click', function() {
        programState.activeTime = $(this).data('time').toString();
        setActiveTimeButton(programState.activeTime);
        $('#addTimesWrap input[type="checkbox"]').prop('checked', false);
        $('#addTimesWrap input[type="checkbox"][value="' + programState.activeTime + '"]').prop('checked', true);
        updateTimesPreview('#addTimesWrap', '#addTimesPreview');
        setDefaultTimeCheckboxes('#editTimesWrap');
        updateAddTeacherOptions();
        applyProgramFilters();
    });

    $('#termFilter').on('change', function() {
        programState.activeTerm = $(this).val();
        $('#addTermId').val(programState.activeTerm);
        updateAddTermState();
        updateAddTeacherOptions();
        applyProgramFilters();
    });

    $('#programSearch').on('input', applyProgramFilters);
    $('#teacherFilter, #roomFilter, #courseFilter').on('change', applyProgramFilters);
    applyProgramFilters();

    $('#addTermId').val(programState.activeTerm);
    updateAddTermState();
    updateAddTeacherOptions();

    $('#addTeacherSelect').on('change', function() {
        const teacherId = $(this).val();
        if (!teacherId) return;
        if (!programState.activeTerm || !programState.activeTime) return;
        if (!isTermActive(programState.activeTerm)) {
            showFloatingMsg('این ترم غیرفعال است و امکان افزودن کلاس ندارد.', 'error');
            $(this).val('');
            return;
        }

        $('#addTermId').val(programState.activeTerm);
        $('#addTeacherId').val(teacherId);
        $('#addTermLabel').text(termsMap[programState.activeTerm]?.name || '-');
        $('#addTeacherLabel').text(teacherNameMap[teacherId] || '-');
        refreshCourseOptions(teacherId, 'addCourseId');
        updatePrereqCourseOptions('add', $('#addCourseId').val());
        updateAvailableRooms(programState.activeTerm, programState.activeTime);
        $('#addTimesWrap input[type="checkbox"]').prop('checked', false);
        $('#addTimesWrap input[type="checkbox"][value="' + programState.activeTime + '"]').prop('checked', true);
        updateTimesPreview('#addTimesWrap', '#addTimesPreview');
        addPrereqItems = [];
        renderPrereqList(addPrereqItems, '#addPrereqList');
        syncPrereqHidden(addPrereqItems, '#addPrereqs');
        $('input[name="addPrereqType"][value="none"]').prop('checked', true);
        togglePrereqInputs('add');

        $('#addProgramModal').fadeIn(150);
        $(this).val('');
    });

    $('#addTimesWrap').on('change', 'input[type="checkbox"]', function() {
        updateTimesPreview('#addTimesWrap', '#addTimesPreview');
    });
    updateTimesPreview('#addTimesWrap', '#addTimesPreview');

    togglePrereqInputs('add');
    $('input[name="addPrereqType"]').on('change', function() {
        togglePrereqInputs('add');
    });
    $('#addPrereqBtn').on('click', function() {
        const type = $('input[name="addPrereqType"]:checked').val();
        if (type === 'course') {
            const courseId = $('#addPrereqCourse').val();
            if (courseId && courseId.toString() === ($('#addCourseId').val() || '').toString()) return;
            if (!courseId) return;
            addPrereqItems.push({type: 'course', course_id: courseId});
        } else {
            const text = $('#addPrereqText').val().trim();
            if (!text) return;
            addPrereqItems.push({type: 'text', text});
        }
        renderPrereqList(addPrereqItems, '#addPrereqList');
        syncPrereqHidden(addPrereqItems, '#addPrereqs');
        $('#addPrereqText').val('');
        $('#addPrereqCourse').val('');
    });
    $('#addPrereqList').on('click', '.removePrereq', function() {
        const idx = parseInt($(this).data('idx'), 10);
        addPrereqItems.splice(idx, 1);
        renderPrereqList(addPrereqItems, '#addPrereqList');
        syncPrereqHidden(addPrereqItems, '#addPrereqs');
    });

    $('#addCourseId').on('change', function() {
        updatePrereqCourseOptions('add', $(this).val());
    });

    $('#addTermId').on('change', function() {
        updateAddTermState();
    });

    $('#addProgramForm').on('submit', function(e) {
        e.preventDefault();
        syncPrereqHidden(addPrereqItems, '#addPrereqs');
        $.ajax({
            url: '<?= $CFG->wwwroot ?>/program/new',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showFloatingMsg(res.msg, 'success');
                    const rowHtml = renderProgramRow(res.data);
                    $('#programTable tbody').prepend(rowHtml);
                    classesData.push({
                        id: res.data.id,
                        term_id: res.data.term_id,
                        teacher_id: res.data.teacher_id,
                        room_id: res.data.room_id,
                        times: res.data.time_list || (res.data.time ? res.data.time.toString().split(',') : [])
                    });
                    prereqMap[res.data.id] = addPrereqItems.slice();
                    updateAddTeacherOptions();
                    applyProgramFilters();
                    $('#addProgramModal').fadeOut(150);
                } else {
                    showFloatingMsg(res.msg, 'error');
                }
            },
            error: function() { showFloatingMsg('خطای ارتباط با سرور', 'error'); }
        });
    });

    $(document).on('click', '.editProgramBtn', function() {
        const row = $(this).closest('.program-row');
        if (!isTermActive(row.data('term_id'))) {
            showFloatingMsg('این ترم غیرفعال است و امکان ویرایش ندارد.', 'error');
            return;
        }
        $('#editProgramId').val(row.data('id'));
        $('#editTermId').val(row.data('term_id'));
        $('#editTeacherId').val(row.data('teacher_id'));
        refreshCourseOptions(row.data('teacher_id'), 'editCourseId');
        $('#editCourseId').val(row.data('course_id'));
        updatePrereqCourseOptions('edit', $('#editCourseId').val());
        $('#editRoomId').val(row.data('room_id'));
        $('#editPrice').val(row.data('price') || '');
        $('#editSeat7').val(row.data('seat7') || '');
        $('#editSeat8').val(row.data('seat8') || '');
        $('#editSeat9').val(row.data('seat9') || '');

        const times = (row.data('times') || '').toString().split(',').filter(Boolean);
        $('#editTimesWrap input[type="checkbox"]').prop('checked', false);
        times.forEach(t => {
            $('#editTimesWrap input[type="checkbox"][value="' + t + '"]').prop('checked', true);
        });
        setDefaultTimeCheckboxes('#editTimesWrap');
        updateTimesPreview('#editTimesWrap', '#editTimesPreview');
        editPrereqItems = (prereqMap[row.data('id')] || []).map(p => {
            if (p.course_id) return {type: 'course', course_id: p.course_id.toString()};
            return {type: 'text', text: p.alternative_text || p.text || ''};
        });
        renderPrereqList(editPrereqItems, '#editPrereqList');
        syncPrereqHidden(editPrereqItems, '#editPrereqs');
        const hasCourse = editPrereqItems.some(p => p.type === 'course');
        const hasText = editPrereqItems.some(p => p.type === 'text');
        if (hasCourse) {
            $('input[name="editPrereqType"][value="course"]').prop('checked', true);
        } else if (hasText) {
            $('input[name="editPrereqType"][value="text"]').prop('checked', true);
        } else {
            $('input[name="editPrereqType"][value="none"]').prop('checked', true);
        }
        togglePrereqInputs('edit');
        $('#editProgramModal').fadeIn(150);
    });

    $('#editTeacherId').on('change', function() {
        refreshCourseOptions($(this).val(), 'editCourseId');
    });
    $('#editTimesWrap').on('change', 'input[type="checkbox"]', function() {
        updateTimesPreview('#editTimesWrap', '#editTimesPreview');
    });

    togglePrereqInputs('edit');
    $('input[name="editPrereqType"]').on('change', function() {
        togglePrereqInputs('edit');
    });
    $('#editPrereqBtn').on('click', function() {
        const type = $('input[name="editPrereqType"]:checked').val();
        if (type === 'course') {
            const courseId = $('#editPrereqCourse').val();
            if (courseId && courseId.toString() === ($('#editCourseId').val() || '').toString()) return;
            if (!courseId) return;
            editPrereqItems.push({type: 'course', course_id: courseId});
        } else {
            const text = $('#editPrereqText').val().trim();
            if (!text) return;
            editPrereqItems.push({type: 'text', text});
        }
        renderPrereqList(editPrereqItems, '#editPrereqList');
        syncPrereqHidden(editPrereqItems, '#editPrereqs');
        $('#editPrereqText').val('');
        $('#editPrereqCourse').val('');
    });
    $('#editPrereqList').on('click', '.removePrereq', function() {
        const idx = parseInt($(this).data('idx'), 10);
        editPrereqItems.splice(idx, 1);
        renderPrereqList(editPrereqItems, '#editPrereqList');
        syncPrereqHidden(editPrereqItems, '#editPrereqs');
    });

    $('#editCourseId').on('change', function() {
        updatePrereqCourseOptions('edit', $(this).val());
    });

    $('#closeEditProgramModal').on('click', function() {
        $('#editProgramModal').fadeOut(150);
    });
    $('#editProgramModal').on('click', function(e) {
        if (e.target === this) $(this).fadeOut(150);
    });

    $('#closeAddProgramModal').on('click', function() {
        $('#addProgramModal').fadeOut(150);
    });
    $('#addProgramModal').on('click', function(e) {
        if (e.target === this) $(this).fadeOut(150);
    });

    $('#editProgramForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#editProgramId').val();
        syncPrereqHidden(editPrereqItems, '#editPrereqs');
        $.ajax({
            url: '<?= $CFG->wwwroot ?>/program/edit/' + id,
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showFloatingMsg(res.msg, 'success');
                    const row = $(`.program-row[data-id="${id}"]`);
                    row.replaceWith(renderProgramRow(res.data));
                    classesData = classesData.map(c => {
                        if (c.id == id) {
                            return {
                                id: res.data.id,
                                term_id: res.data.term_id,
                                teacher_id: res.data.teacher_id,
                                room_id: res.data.room_id,
                                times: res.data.time_list || (res.data.time ? res.data.time.toString().split(',') : [])
                            };
                        }
                        return c;
                    });
                    prereqMap[id] = editPrereqItems.slice();
                    updateAddTeacherOptions();
                    applyProgramFilters();
                    $('#editProgramModal').fadeOut(150);
                } else {
                    showFloatingMsg(res.msg, 'error');
                }
            },
            error: function() { showFloatingMsg('خطای ارتباط با سرور', 'error'); }
        });
    });

    $(document).on('click', '.deleteProgramBtn', function() {
        if (!confirm('آیا از حذف این کلاس مطمئن هستید؟')) return;
        const row = $(this).closest('.program-row');
        if (!isTermActive(row.data('term_id'))) {
            showFloatingMsg('این ترم غیرفعال است و امکان حذف ندارد.', 'error');
            return;
        }
        const id = row.data('id');
        $.ajax({
            url: '<?= $CFG->wwwroot ?>/program/delete/' + id,
            method: 'POST',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showFloatingMsg(res.msg, 'success');
                    row.remove();
                    classesData = classesData.filter(c => c.id != id);
                    delete prereqMap[id];
                    updateAddTeacherOptions();
                    applyProgramFilters();
                } else {
                    showFloatingMsg(res.msg, 'error');
                }
            },
            error: function() { showFloatingMsg('خطای ارتباط با سرور', 'error'); }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
