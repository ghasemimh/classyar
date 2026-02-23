<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../models/term.php';
require_once __DIR__ . '/../models/room.php';
require_once __DIR__ . '/../models/course.php';
require_once __DIR__ . '/../models/teacher.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/setting.php';
require_once __DIR__ . '/../models/class.php';
require_once __DIR__ . '/../models/prerequisite.php';
require_once __DIR__ . '/../services/moodleAPI.php';
require_once __DIR__ . '/../services/jalali/CalendarUtils.php';
use Morilog\Jalali\CalendarUtils;

class Program {
    private static function hasColumn(string $table, string $column): bool {
        $col = DB::getRow("SHOW COLUMNS FROM {$table} LIKE :column", [':column' => $column]);
        return !empty($col);
    }

    private static function toEnglishDigits(string $input): string {
        $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $ar = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        return str_replace(array_merge($fa, $ar), ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'], $input);
    }

    private static function jalaliStartYear(array $term): string {
        $termName = self::toEnglishDigits((string)($term['name'] ?? ''));
        if (preg_match('/(13|14)\d{2}/', $termName, $m)) {
            return $m[0];
        }

        $startTs = (int)($term['start'] ?? 0);
        if ($startTs > 0) {
            $gy = (int)date('Y', $startTs);
            $gm = (int)date('n', $startTs);
            $gd = (int)date('j', $startTs);
            $j = CalendarUtils::toJalali($gy, $gm, $gd);
            return str_pad((string)($j[0] ?? '9999'), 4, '9', STR_PAD_LEFT);
        }
        return '9999';
    }

    private static function termDigit(string $termName): string {
        if (mb_strpos($termName, 'تابستان') !== false) return '0';
        if (mb_strpos($termName, 'پاییز') !== false) return '1';
        if (mb_strpos($termName, 'بهار') !== false) return '2';
        return '9';
    }

    private static function courseTypeMeta(string $categoryName): array {
        $name = trim($categoryName);
        $name = str_replace(['ي', 'ك', '‌', ' '], ['ی', 'ک', '', ''], $name);
        $name = mb_strtolower($name);

        if (mb_strpos($name, 'انضباط') !== false) return ['digit' => '0', 'prefix' => 'n'];
        if (mb_strpos($name, 'دولتی') !== false) return ['digit' => '1', 'prefix' => 'd'];
        if (mb_strpos($name, 'پویش') !== false || mb_strpos($name, 'پویشی') !== false) return ['digit' => '2', 'prefix' => 'p'];
        if (mb_strpos($name, 'پیشرفت') !== false) return ['digit' => '3', 'prefix' => 'r'];
        return ['digit' => '9', 'prefix' => 'x'];
    }

    private static function groupLetter(int $index): string {
        $index = max(1, $index);
        $letters = '';
        while ($index > 0) {
            $index--;
            $letters = chr(65 + ($index % 26)) . $letters;
            $index = (int)floor($index / 26);
        }
        return $letters;
    }

    private static function firstTimeId(string $timeCsv): int {
        $items = array_filter(array_map('trim', explode(',', $timeCsv)), fn($x) => $x !== '');
        $nums = array_map('intval', $items);
        if (empty($nums)) return 0;
        sort($nums, SORT_NUMERIC);
        return (int)$nums[0];
    }

    private static function buildMoodleCoursePayload(array $classRow, array $term): array {
        $termName = (string)($term['name'] ?? '');
        $type = self::courseTypeMeta((string)($classRow['category_name'] ?? ''));
        if (($type['digit'] ?? '9') === '9') {
            // In current workflow, synced classes are poyeshi by default.
            $type = ['digit' => '2', 'prefix' => 'p'];
        }
        $groupIndex = (int)($classRow['course_rank'] ?? 1);
        $groupLetter = self::groupLetter($groupIndex);
        $groupDigit = (string)min(9, max(1, $groupIndex));

        $baseName = trim((string)($classRow['course_name'] ?? 'کلاس'));
        $fullName = $baseName;
        if ((int)($classRow['course_group_count'] ?? 1) > 1) {
            $fullName .= ' - ' . $groupLetter;
        }

        $firstTime = self::firstTimeId((string)($classRow['time'] ?? '0'));
        $slotRank = (int)($classRow['slot_rank'] ?? 1);
        $shortNum = (string)$firstTime . str_pad((string)$slotRank, 2, '0', STR_PAD_LEFT);
        $shortName = 'p-' . $shortNum;

        $courseCrsidRaw = preg_replace('/\D+/', '', (string)($classRow['course_crsid'] ?? ''));
        $courseCode4 = str_pad(substr($courseCrsidRaw, -4), 4, '0', STR_PAD_LEFT);

        $idNumber = self::jalaliStartYear($term)
            . self::termDigit($termName)
            . $type['digit']
            . $courseCode4
            . $groupDigit
            . '0';

        $startDate = (int)($term['start'] ?? 0);
        $endDate = (int)($term['end'] ?? 0);
        if ($endDate > 0 && $startDate > 0 && $endDate < $startDate) {
            $endDate = $startDate;
        }

        return [
            'fullname' => $fullName,
            'shortname' => $shortName,
            'idnumber' => $idNumber,
            'startdate' => $startDate,
            'enddate' => $endDate
        ];
    }

    private static function assertShortnameAvailable(string $shortname, int $currentMoodleCourseId = 0): void {
        try {
            $existing = Moodle::getCourseByField('shortname', $shortname);
        } catch (Throwable $e) {
            $existing = null;
        }
        if (!empty($existing) && (int)($existing['id'] ?? 0) !== $currentMoodleCourseId) {
            throw new Exception("Shortname already exists: {$shortname}");
        }
    }

    public static function syncMoodle($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . '/program');
        }

        if (!self::hasColumn($CFG->classestable, 'mdl_id')) {
            return self::respond(['success' => false, 'msg' => 'فیلد classes.mdl_id وجود ندارد.'], $CFG->wwwroot . '/program');
        }
        if (!self::hasColumn($CFG->termstable, 'mdl_id')) {
            return self::respond(['success' => false, 'msg' => 'فیلد terms.mdl_id وجود ندارد.'], $CFG->wwwroot . '/program');
        }

        $term = Term::getTerm(mode: 'active');
        if (!$term) {
            return self::respond(['success' => false, 'msg' => 'ترم فعال برای سینک پیدا نشد.'], $CFG->wwwroot . '/program');
        }
        $termId = (int)$term['id'];
        $mdlCategoryId = (int)($term['mdl_id'] ?? 0);
        if ($mdlCategoryId <= 0) {
            return self::respond(['success' => false, 'msg' => 'برای ترم فعال mdl_id تنظیم نشده است.'], $CFG->wwwroot . '/program');
        }

        $classes = DB::getAll("
            SELECT c.*, cr.name AS course_name, cr.crsid AS course_crsid, cat.name AS category_name
            FROM {$CFG->classestable} c
            JOIN {$CFG->coursestable} cr ON cr.id = c.course_id
            LEFT JOIN {$CFG->categoriestable} cat ON cat.id = cr.category_id
            WHERE c.deleted = 0 AND c.term_id = :term_id
            ORDER BY c.course_id ASC, c.time ASC, c.id ASC
        ", [':term_id' => $termId]);

        if (empty($classes)) {
            return self::respond(['success' => false, 'msg' => 'کلاسی در ترم فعال پیدا نشد.'], $CFG->wwwroot . '/program');
        }

        try {
            $coursesByCategoryData = Moodle::getCoursesByField('category', (string)$mdlCategoryId);
            $moodleCoursesInCategory = $coursesByCategoryData['courses'] ?? [];
            $moodleById = [];
            $moodleByIdNumber = [];
            foreach ($moodleCoursesInCategory as $mc) {
                if (!empty($mc['id'])) $moodleById[(int)$mc['id']] = $mc;
                if (!empty($mc['idnumber'])) $moodleByIdNumber[(string)$mc['idnumber']] = $mc;
            }

            usort($classes, function ($a, $b) {
                $courseCmp = ((int)$a['course_id']) <=> ((int)$b['course_id']);
                if ($courseCmp !== 0) return $courseCmp;
                $timeCmp = Program::firstTimeId((string)($a['time'] ?? '')) <=> Program::firstTimeId((string)($b['time'] ?? ''));
                if ($timeCmp !== 0) return $timeCmp;
                return ((int)$a['id']) <=> ((int)$b['id']);
            });

            $groupCounter = [];
            $groupCountByCourse = [];
            foreach ($classes as $row) {
                $cid = (int)$row['course_id'];
                $groupCountByCourse[$cid] = ($groupCountByCourse[$cid] ?? 0) + 1;
            }
            foreach ($classes as &$row) {
                $cid = (int)$row['course_id'];
                $groupCounter[$cid] = ($groupCounter[$cid] ?? 0) + 1;
                $row['course_rank'] = $groupCounter[$cid];
                $row['course_group_count'] = $groupCountByCourse[$cid] ?? 1;
            }
            unset($row);

            $slotCounter = [];
            foreach ($classes as &$row) {
                $firstTime = self::firstTimeId((string)($row['time'] ?? ''));
                $slotCounter[$firstTime] = ($slotCounter[$firstTime] ?? 0) + 1;
                $row['slot_rank'] = $slotCounter[$firstTime];
            }
            unset($row);

            $createdCourses = 0;
            $updatedCourses = 0;
            $matchedByIdNumber = 0;
            $unchangedCourses = 0;

            foreach ($classes as $classRow) {
                $payload = self::buildMoodleCoursePayload($classRow, $term);
                $payload['categoryid'] = $mdlCategoryId;
                $payload['visible'] = 1;

                $existing = null;
                $classMdlId = (int)($classRow['mdl_id'] ?? 0);
                if ($classMdlId > 0 && isset($moodleById[$classMdlId])) {
                    $existing = $moodleById[$classMdlId];
                } elseif (!empty($payload['idnumber']) && isset($moodleByIdNumber[$payload['idnumber']])) {
                    $existing = $moodleByIdNumber[$payload['idnumber']];
                    $matchedByIdNumber++;
                }

                if ($existing) {
                    self::assertShortnameAvailable((string)$payload['shortname'], (int)$existing['id']);

                    $needsUpdate =
                        ((string)($existing['fullname'] ?? '') !== (string)$payload['fullname']) ||
                        ((string)($existing['shortname'] ?? '') !== (string)$payload['shortname']) ||
                        ((string)($existing['idnumber'] ?? '') !== (string)$payload['idnumber']) ||
                        ((int)($existing['startdate'] ?? 0) !== (int)($payload['startdate'] ?? 0)) ||
                        ((int)($existing['enddate'] ?? 0) !== (int)($payload['enddate'] ?? 0)) ||
                        ((int)($existing['categoryid'] ?? 0) !== (int)$payload['categoryid']);

                    if ($needsUpdate) {
                        Moodle::updateCourse([
                            'id' => (int)$existing['id'],
                            'fullname' => $payload['fullname'],
                            'shortname' => $payload['shortname'],
                            'idnumber' => $payload['idnumber'],
                            'startdate' => (int)$payload['startdate'],
                            'enddate' => (int)$payload['enddate'],
                            'categoryid' => (int)$payload['categoryid'],
                            'visible' => 1
                        ]);
                        $updatedCourses++;
                    } else {
                        $unchangedCourses++;
                    }

                    if ($classMdlId !== (int)$existing['id']) {
                        DB::query("UPDATE {$CFG->classestable} SET mdl_id = :mdl_id WHERE id = :id", [
                            ':mdl_id' => (int)$existing['id'],
                            ':id' => (int)$classRow['id']
                        ]);
                    }
                    continue;
                }

                self::assertShortnameAvailable((string)$payload['shortname'], 0);
                $newId = Moodle::createCourse($payload);
                DB::query("UPDATE {$CFG->classestable} SET mdl_id = :mdl_id WHERE id = :id", [
                    ':mdl_id' => (int)$newId,
                    ':id' => (int)$classRow['id']
                ]);
                $createdCourses++;
            }

            $msg = "سینک دروس مودل انجام شد | ایجاد: {$createdCourses} | به‌روزرسانی: {$updatedCourses} | بدون تغییر: {$unchangedCourses} | مچ با idnumber: {$matchedByIdNumber}";
            return self::respond(['success' => true, 'msg' => $msg], $CFG->wwwroot . '/program');
        } catch (Throwable $e) {
            return self::respond(['success' => false, 'msg' => 'خطا در سینک دروس مودل: ' . $e->getMessage()], $CFG->wwwroot . '/program');
        }
    }

    public static function syncMoodleTeachers($request) {
        global $CFG, $MSG, $MDL;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . '/program');
        }

        if (!self::hasColumn($CFG->classestable, 'mdl_id')) {
            return self::respond(['success' => false, 'msg' => 'فیلد classes.mdl_id وجود ندارد.'], $CFG->wwwroot . '/program');
        }

        $term = Term::getTerm(mode: 'active');
        if (!$term) {
            return self::respond(['success' => false, 'msg' => 'ترم فعال پیدا نشد.'], $CFG->wwwroot . '/program');
        }
        $termId = (int)$term['id'];

        $rows = DB::getAll("
            SELECT c.id AS class_id, c.mdl_id AS mdl_course_id, u.mdl_id AS mdl_teacher_id
            FROM {$CFG->classestable} c
            JOIN {$CFG->teacherstable} t ON t.id = c.teacher_id AND t.deleted = 0
            JOIN {$CFG->userstable} u ON u.id = t.user_id AND u.suspend = 0
            WHERE c.deleted = 0 AND c.term_id = :term_id
        ", [':term_id' => $termId]);

        if (empty($rows)) {
            return self::respond(['success' => false, 'msg' => 'کلاسی برای سینک معلمان پیدا نشد.'], $CFG->wwwroot . '/program');
        }

        try {
            $teacherRoleId = (int)($MDL->teacherRoleId ?? 3);
            $enrolments = [];
            $uniq = [];
            $skipped = 0;

            foreach ($rows as $r) {
                $courseId = (int)($r['mdl_course_id'] ?? 0);
                $teacherId = (int)($r['mdl_teacher_id'] ?? 0);
                if ($courseId <= 0 || $teacherId <= 0) {
                    $skipped++;
                    continue;
                }
                $key = $teacherId . ':' . $courseId;
                if (isset($uniq[$key])) {
                    continue;
                }
                $uniq[$key] = true;
                $enrolments[] = [
                    'roleid' => $teacherRoleId,
                    'userid' => $teacherId,
                    'courseid' => $courseId
                ];
            }

            [$sent, $failed, $sampleError] = self::enrolmentsWithFallback($enrolments);
            $totalSkipped = $skipped + $failed;
            $msg = "سینک معلمان انجام شد | موفق: {$sent} | رد شده: {$totalSkipped}";
            if ($sampleError !== '') {
                $msg .= " | نمونه خطا: {$sampleError}";
            }
            return self::respond(['success' => true, 'msg' => $msg], $CFG->wwwroot . '/program');
        } catch (Throwable $e) {
            return self::respond(['success' => false, 'msg' => 'خطا در سینک معلمان مودل: ' . $e->getMessage()], $CFG->wwwroot . '/program');
        }
    }

    public static function syncMoodleStudents($request) {
        global $CFG, $MSG, $MDL;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . '/program');
        }

        if (!self::hasColumn($CFG->classestable, 'mdl_id')) {
            return self::respond(['success' => false, 'msg' => 'فیلد classes.mdl_id وجود ندارد.'], $CFG->wwwroot . '/program');
        }

        $term = Term::getTerm(mode: 'active');
        if (!$term) {
            return self::respond(['success' => false, 'msg' => 'ترم فعال پیدا نشد.'], $CFG->wwwroot . '/program');
        }
        $termId = (int)$term['id'];

        $rows = DB::getAll("
            SELECT DISTINCT c.mdl_id AS mdl_course_id, u.mdl_id AS mdl_student_id
            FROM {$CFG->enrollstable} e
            JOIN {$CFG->classestable} c ON c.id = e.class_id AND c.deleted = 0
            JOIN {$CFG->studentstable} s ON s.id = e.student_id AND s.deleted = 0
            JOIN {$CFG->userstable} u ON u.id = s.user_id AND u.suspend = 0
            WHERE e.deleted = 0
              AND c.term_id = :term_id
        ", [':term_id' => $termId]);

        if (empty($rows)) {
            return self::respond(['success' => false, 'msg' => 'ثبت‌نامی برای سینک دانش‌آموزان پیدا نشد.'], $CFG->wwwroot . '/program');
        }

        try {
            $studentRoleId = (int)($MDL->studentRoleId ?? 5);
            $enrolments = [];
            $uniq = [];
            $skipped = 0;

            foreach ($rows as $r) {
                $courseId = (int)($r['mdl_course_id'] ?? 0);
                $studentId = (int)($r['mdl_student_id'] ?? 0);
                if ($courseId <= 0 || $studentId <= 0) {
                    $skipped++;
                    continue;
                }
                $key = $studentId . ':' . $courseId;
                if (isset($uniq[$key])) {
                    continue;
                }
                $uniq[$key] = true;
                $enrolments[] = [
                    'roleid' => $studentRoleId,
                    'userid' => $studentId,
                    'courseid' => $courseId
                ];
            }

            [$sent, $failed, $sampleError] = self::enrolmentsWithFallback($enrolments);
            $totalSkipped = $skipped + $failed;
            $msg = "سینک دانش‌آموزان انجام شد | موفق: {$sent} | رد شده: {$totalSkipped}";
            if ($sampleError !== '') {
                $msg .= " | نمونه خطا: {$sampleError}";
            }
            return self::respond(['success' => true, 'msg' => $msg], $CFG->wwwroot . '/program');
        } catch (Throwable $e) {
            return self::respond(['success' => false, 'msg' => 'خطا در سینک دانش‌آموزان مودل: ' . $e->getMessage()], $CFG->wwwroot . '/program');
        }
    }

    private static function enrolmentsWithFallback(array $enrolments): array {
        $success = 0;
        $failed = 0;
        $sampleError = '';

        if (empty($enrolments)) {
            return [0, 0, ''];
        }

        foreach (array_chunk($enrolments, 100) as $chunk) {
            try {
                Moodle::enrolUsers($chunk);
                $success += count($chunk);
                continue;
            } catch (Throwable $batchError) {
                // fallback per row
            }

            foreach ($chunk as $e) {
                try {
                    Moodle::enrolUsers([$e]);
                    $success++;
                } catch (Throwable $singleError) {
                    $errMsg = (string)$singleError->getMessage();
                    $isMessageFailure = stripos($errMsg, 'Message was not sent') !== false;
                    if ($isMessageFailure) {
                        try {
                            Moodle::assignRoles([[
                                'roleid' => (int)($e['roleid'] ?? 0),
                                'userid' => (int)($e['userid'] ?? 0),
                                'contextlevel' => 'course',
                                'instanceid' => (int)($e['courseid'] ?? 0),
                            ]]);
                            $success++;
                            continue;
                        } catch (Throwable $assignError) {
                            $errMsg = (string)$assignError->getMessage();
                        }
                    }

                    $failed++;
                    if ($sampleError === '') {
                        $sampleError = $errMsg;
                    }
                }
            }
        }

        return [$success, $failed, $sampleError];
    }

    private static function isTermActive($term) {
        if (!$term) return false;
        $now = time();
        $start = (int)($term['start'] ?? 0);
        $end = (int)($term['end'] ?? 0);
        return ($start && $end && $now >= $start && $now <= $end);
    }
    private static function respond($data, $redirectUrl) {
        if (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        }
        if (!empty($data['msg'])) {
            $type = (!empty($data['success']) && $data['success']) ? 'success' : 'error';
            Flash::set($data['msg'], $type);
        }
        $redirectUrl = preg_replace('/([?&])msg=[^&]*(&?)/', '$1', (string)$redirectUrl);
        $redirectUrl = str_replace(['?&', '&&'], ['?', '&'], $redirectUrl);
        $redirectUrl = rtrim($redirectUrl, '?&');
        header("Location: $redirectUrl");
        exit();
    }

    public static function index($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'guide')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $terms = Term::getTerm(mode: 'all');
        $activeTerm = Term::getTerm(mode: 'active');
        $activeTermId = $activeTerm ? (int)$activeTerm['id'] : null;
        $courses = Course::getCourse(mode: 'all');
        $rooms = Room::getRoom(mode: 'all');
        $teachers = Teacher::getTeacher(mode: 'all');
        $users = User::getUser(mode: 'all');
        $Mdl_users = Moodle::getUser(mode: 'all');
        $timesInfo = json_decode(Setting::getSetting('Times Information'), true);
        $times = $timesInfo['times'] ?? [];

        $classes = SchoolClass::getAll();
        $prereqs = Prerequisite::getAll();
        $msg = $request['get']['msg'] ?? NULL;
        $subtitle = 'چیدمان';

        return include_once __DIR__ . '/../views/program/index.php';
    }

    public static function store($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . "/program?msg=" . urlencode($MSG->notallowed));
        }

        $post = $request['post'] ?? [];
        $termId = (int)($post['term_id'] ?? 0);
        $courseId = (int)($post['course_id'] ?? 0);
        $teacherId = (int)($post['teacher_id'] ?? 0);
        $roomId = (int)($post['room_id'] ?? 0);
        $price = trim($post['price'] ?? '');
        $seat7 = $post['seat7'] ?? null;
        $seat8 = $post['seat8'] ?? null;
        $seat9 = $post['seat9'] ?? null;
        $times = $post['times'] ?? [];
        $prereqsRaw = $post['prereqs'] ?? '[]';
        $prereqs = json_decode($prereqsRaw, true);
        if (!is_array($prereqs)) $prereqs = [];
        $prereqs = array_map(function ($p) {
            $type = $p['type'] ?? null;
            if ($type === 'none') return [];
            if ($type === 'text') {
                return ['alternative_text' => trim($p['text'] ?? '')];
            }
            if ($type === 'course') {
                return ['course_id' => (int)($p['course_id'] ?? 0)];
            }
            return $p;
        }, $prereqs);

        if (!$termId || !$courseId || !$teacherId || !$roomId || empty($times)) {
            return self::respond(['success' => false, 'msg' => $MSG->baddata], $CFG->wwwroot . "/program?msg=" . urlencode($MSG->baddata));
        }

        $term = Term::getTerm(id: $termId);
        if (!self::isTermActive($term)) {
            return self::respond(['success' => false, 'msg' => 'ترم انتخاب‌شده غیرفعال است.'], $CFG->wwwroot . "/program?msg=" . urlencode('ترم انتخاب‌شده غیرفعال است.'));
        }

        $conflicts = SchoolClass::findConflicts($termId, $teacherId, $roomId, $times);
        if (!empty($conflicts)) {
            $teacherTimes = [];
            $roomTimes = [];
            foreach ($conflicts as $c) {
                if ($c['type'] === 'teacher') $teacherTimes = array_merge($teacherTimes, $c['times']);
                if ($c['type'] === 'room') $roomTimes = array_merge($roomTimes, $c['times']);
            }
            $teacherTimes = array_values(array_unique($teacherTimes));
            $roomTimes = array_values(array_unique($roomTimes));
            $parts = [];
            if (!empty($teacherTimes)) {
                $parts[] = 'تداخل با معلم در زنگ‌های: ' . implode(', ', $teacherTimes);
            }
            if (!empty($roomTimes)) {
                $parts[] = 'تداخل با مکان در زنگ‌های: ' . implode(', ', $roomTimes);
            }
            $msg = implode(' | ', $parts);
            return self::respond(['success' => false, 'msg' => $msg], $CFG->wwwroot . "/program?msg=" . urlencode($msg));
        }

        $id = SchoolClass::create([
            'term_id' => $termId,
            'course_id' => $courseId,
            'teacher_id' => $teacherId,
            'room_id' => $roomId,
            'time' => $times,
            'price' => $price,
            'seat7' => $seat7,
            'seat8' => $seat8,
            'seat9' => $seat9
        ]);

        if ($id) {
            $prereqs = array_values(array_filter($prereqs, function ($p) use ($courseId) {
                return empty($p['course_id']) || (int)$p['course_id'] !== (int)$courseId;
            }));
            Prerequisite::createMany($id, $prereqs);
            $row = SchoolClass::getById($id);
            return self::respond(['success' => true, 'msg' => 'کلاس با موفقیت ایجاد شد.', 'data' => $row], $CFG->wwwroot . "/program?msg=" . urlencode('کلاس با موفقیت ایجاد شد.'));
        }

        return self::respond(['success' => false, 'msg' => $MSG->unknownerror], $CFG->wwwroot . "/program?msg=" . urlencode($MSG->unknownerror));
    }

    public static function update($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . "/program?msg=" . urlencode($MSG->notallowed));
        }

        $id = (int)($request['route'][0] ?? 0);
        if (!$id) {
            return self::respond(['success' => false, 'msg' => $MSG->idnotgiven], $CFG->wwwroot . "/program?msg=" . urlencode($MSG->idnotgiven));
        }

        $post = $request['post'] ?? [];
        $termId = (int)($post['term_id'] ?? 0);
        $courseId = (int)($post['course_id'] ?? 0);
        $teacherId = (int)($post['teacher_id'] ?? 0);
        $roomId = (int)($post['room_id'] ?? 0);
        $price = trim($post['price'] ?? '');
        $seat7 = $post['seat7'] ?? null;
        $seat8 = $post['seat8'] ?? null;
        $seat9 = $post['seat9'] ?? null;
        $times = $post['times'] ?? [];
        $prereqsRaw = $post['prereqs'] ?? '[]';
        $prereqs = json_decode($prereqsRaw, true);
        if (!is_array($prereqs)) $prereqs = [];
        $prereqs = array_map(function ($p) {
            $type = $p['type'] ?? null;
            if ($type === 'none') return [];
            if ($type === 'text') {
                return ['alternative_text' => trim($p['text'] ?? '')];
            }
            if ($type === 'course') {
                return ['course_id' => (int)($p['course_id'] ?? 0)];
            }
            return $p;
        }, $prereqs);

        if (!$termId || !$courseId || !$teacherId || !$roomId || empty($times)) {
            return self::respond(['success' => false, 'msg' => $MSG->baddata], $CFG->wwwroot . "/program?msg=" . urlencode($MSG->baddata));
        }

        $classRow = SchoolClass::getById($id);
        if (!$classRow) {
            return self::respond(['success' => false, 'msg' => $MSG->baddata], $CFG->wwwroot . "/program?msg=" . urlencode($MSG->baddata));
        }

        $term = Term::getTerm(id: $termId);
        if (!self::isTermActive($term)) {
            return self::respond(['success' => false, 'msg' => 'ترم انتخاب‌شده غیرفعال است.'], $CFG->wwwroot . "/program?msg=" . urlencode('ترم انتخاب‌شده غیرفعال است.'));
        }

        $conflicts = SchoolClass::findConflicts($termId, $teacherId, $roomId, $times, $id);
        if (!empty($conflicts)) {
            $teacherTimes = [];
            $roomTimes = [];
            foreach ($conflicts as $c) {
                if ($c['type'] === 'teacher') $teacherTimes = array_merge($teacherTimes, $c['times']);
                if ($c['type'] === 'room') $roomTimes = array_merge($roomTimes, $c['times']);
            }
            $teacherTimes = array_values(array_unique($teacherTimes));
            $roomTimes = array_values(array_unique($roomTimes));
            $parts = [];
            if (!empty($teacherTimes)) {
                $parts[] = 'تداخل با معلم در زنگ‌های: ' . implode(', ', $teacherTimes);
            }
            if (!empty($roomTimes)) {
                $parts[] = 'تداخل با مکان در زنگ‌های: ' . implode(', ', $roomTimes);
            }
            $msg = implode(' | ', $parts);
            return self::respond(['success' => false, 'msg' => $msg], $CFG->wwwroot . "/program?msg=" . urlencode($msg));
        }

        $ok = SchoolClass::update($id, [
            'term_id' => $termId,
            'course_id' => $courseId,
            'teacher_id' => $teacherId,
            'room_id' => $roomId,
            'time' => $times,
            'price' => $price,
            'seat7' => $seat7,
            'seat8' => $seat8,
            'seat9' => $seat9
        ]);

        if ($ok) {
            $prereqs = array_values(array_filter($prereqs, function ($p) use ($courseId) {
                return empty($p['course_id']) || (int)$p['course_id'] !== (int)$courseId;
            }));
            Prerequisite::deleteByClass($id);
            Prerequisite::createMany($id, $prereqs);
            $row = SchoolClass::getById($id);
            return self::respond(['success' => true, 'msg' => 'کلاس با موفقیت بروزرسانی شد.', 'data' => $row], $CFG->wwwroot . "/program?msg=" . urlencode('کلاس با موفقیت بروزرسانی شد.'));
        }

        return self::respond(['success' => false, 'msg' => $MSG->unknownerror], $CFG->wwwroot . "/program?msg=" . urlencode($MSG->unknownerror));
    }

    public static function exportCsv($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $get = $request['get'] ?? [];
        $termId = (int)($get['term_id'] ?? 0);
        $teacherId = (int)($get['teacher_id'] ?? 0);
        $roomId = (int)($get['room_id'] ?? 0);
        $courseId = (int)($get['course_id'] ?? 0);
        $search = trim((string)($get['search'] ?? ''));

        $where = ["c.deleted = 0"];
        $params = [];
        if ($termId > 0) {
            $where[] = "c.term_id = :term_id";
            $params[':term_id'] = $termId;
        }
        if ($teacherId > 0) {
            $where[] = "c.teacher_id = :teacher_id";
            $params[':teacher_id'] = $teacherId;
        }
        if ($roomId > 0) {
            $where[] = "c.room_id = :room_id";
            $params[':room_id'] = $roomId;
        }
        if ($courseId > 0) {
            $where[] = "c.course_id = :course_id";
            $params[':course_id'] = $courseId;
        }
        if ($search !== '') {
            $where[] = "(cr.name LIKE :search OR COALESCE(r.name, '') LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $sql = "
            SELECT
                c.id,
                t.name AS term_name,
                c.time,
                cr.name AS course_name,
                COALESCE(r.name, '') AS room_name,
                u.mdl_id AS teacher_mdl_id,
                c.price,
                c.seat7,
                c.seat8,
                c.seat9
            FROM {$CFG->classestable} c
            LEFT JOIN {$CFG->termstable} t ON t.id = c.term_id
            LEFT JOIN {$CFG->coursestable} cr ON cr.id = c.course_id
            LEFT JOIN {$CFG->roomstable} r ON r.id = c.room_id
            LEFT JOIN {$CFG->teacherstable} te ON te.id = c.teacher_id
            LEFT JOIN {$CFG->userstable} u ON u.id = te.user_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY c.id DESC
        ";
        $rows = DB::getAll($sql, $params);
        $teacherNames = [];
        foreach (Moodle::getUser(mode: 'all') as $mdlUser) {
            $teacherNames[(int)($mdlUser['id'] ?? 0)] = trim(
                (string)($mdlUser['firstname'] ?? '') . ' ' . (string)($mdlUser['lastname'] ?? '')
            );
        }

        $timesInfo = json_decode(Setting::getSetting('Times Information'), true);
        $timesMap = [];
        foreach (($timesInfo['times'] ?? []) as $slot) {
            $timesMap[(string)($slot['id'] ?? '')] = (string)($slot['label'] ?? ('Time ' . ($slot['id'] ?? '')));
        }

        $filename = 'program-' . date('Ymd-His') . '.csv';
        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, [
            'id',
            'term',
            'times',
            'teacher',
            'course',
            'room',
            'price',
            'seat7',
            'seat8',
            'seat9',
        ]);

        foreach ($rows as $row) {
            $timeLabels = [];
            foreach (array_filter(array_map('trim', explode(',', (string)($row['time'] ?? '')))) as $timeId) {
                $timeLabels[] = $timesMap[$timeId] ?? $timeId;
            }
            fputcsv($out, [
                (int)($row['id'] ?? 0),
                (string)($row['term_name'] ?? ''),
                implode(' | ', $timeLabels),
                (string)($teacherNames[(int)($row['teacher_mdl_id'] ?? 0)] ?? ''),
                (string)($row['course_name'] ?? ''),
                (string)($row['room_name'] ?? ''),
                (string)($row['price'] ?? ''),
                (string)($row['seat7'] ?? ''),
                (string)($row['seat8'] ?? ''),
                (string)($row['seat9'] ?? ''),
            ]);
        }

        fclose($out);
        exit();
    }

    public static function delete($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], '');
        }

        $id = (int)($request['route'][0] ?? 0);
        if (!$id) {
            return self::respond(['success' => false, 'msg' => $MSG->idnotgiven], '');
        }

        $classRow = SchoolClass::getById($id);
        if (!$classRow) {
            return self::respond(['success' => false, 'msg' => $MSG->baddata], '');
        }
        $term = Term::getTerm(id: (int)$classRow['term_id']);
        if (!self::isTermActive($term)) {
            return self::respond(['success' => false, 'msg' => 'ترم انتخاب‌شده غیرفعال است.'], '');
        }

        $ok = SchoolClass::softDelete($id);
        if ($ok) {
            return self::respond(['success' => true, 'msg' => 'کلاس حذف شد.', 'id' => $id], '');
        }

        return self::respond(['success' => false, 'msg' => $MSG->unknownerror], '');
    }
}



