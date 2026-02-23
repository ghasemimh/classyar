<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../models/term.php';
require_once __DIR__ . '/../services/moodleAPI.php';
require_once __DIR__ . '/../services/flash.php';
require_once __DIR__ . '/../services/jalali/CalendarUtils.php';

use Morilog\Jalali\CalendarUtils;

class Sync {
    private static function respond(array $data, string $redirectUrl = ''): void {
        if (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            if (ob_get_length()) {
                ob_clean();
            }
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        }

        if (!empty($data['msg'])) {
            Flash::set($data['msg'], !empty($data['success']) ? 'success' : 'error');
        }
        header('Location: ' . ($redirectUrl ?: '/'));
        exit();
    }

    private static function mustBeAdmin(): void {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . '/program');
        }
    }

    private static function mustBeGuide(): void {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'guide')) {
            self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . '/program');
        }
    }

    private static function hasColumn(string $table, string $column): bool {
        $row = DB::getRow("SHOW COLUMNS FROM {$table} LIKE :column", [':column' => $column]);
        return !empty($row);
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
            $type = ['digit' => '2', 'prefix' => 'p'];
        }
        $groupIndex = (int)($classRow['course_rank'] ?? 1);
        $groupLetter = self::groupLetter($groupIndex);
        $groupDigit = (string)min(9, max(1, $groupIndex));

        $baseName = trim((string)($classRow['course_name'] ?? 'Class'));
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

    private static function getSelectedTerm(?int $termId = null): ?array {
        if ($termId && $termId > 0) {
            return Term::getTerm(id: $termId);
        }
        $active = Term::getTerm(mode: 'active');
        if ($active) return $active;
        $all = Term::getTerm(mode: 'all');
        if (empty($all)) return null;
        return $all[0];
    }

    private static function getDecoratedClasses(int $termId): array {
        global $CFG;
        $rows = DB::getAll("
            SELECT c.*, cr.name AS course_name, cr.crsid AS course_crsid, cat.name AS category_name
            FROM {$CFG->classestable} c
            JOIN {$CFG->coursestable} cr ON cr.id = c.course_id
            LEFT JOIN {$CFG->categoriestable} cat ON cat.id = cr.category_id
            WHERE c.deleted = 0 AND c.term_id = :term_id
            ORDER BY c.course_id ASC, c.time ASC, c.id ASC
        ", [':term_id' => $termId]);

        usort($rows, function ($a, $b) {
            $courseCmp = ((int)$a['course_id']) <=> ((int)$b['course_id']);
            if ($courseCmp !== 0) return $courseCmp;
            $timeCmp = Sync::firstTimeId((string)($a['time'] ?? '')) <=> Sync::firstTimeId((string)($b['time'] ?? ''));
            if ($timeCmp !== 0) return $timeCmp;
            return ((int)$a['id']) <=> ((int)$b['id']);
        });

        $groupCounter = [];
        $groupCountByCourse = [];
        foreach ($rows as $row) {
            $cid = (int)$row['course_id'];
            $groupCountByCourse[$cid] = ($groupCountByCourse[$cid] ?? 0) + 1;
        }
        foreach ($rows as &$row) {
            $cid = (int)$row['course_id'];
            $groupCounter[$cid] = ($groupCounter[$cid] ?? 0) + 1;
            $row['course_rank'] = $groupCounter[$cid];
            $row['course_group_count'] = $groupCountByCourse[$cid] ?? 1;
        }
        unset($row);

        $slotCounter = [];
        foreach ($rows as &$row) {
            $firstTime = self::firstTimeId((string)($row['time'] ?? ''));
            $slotCounter[$firstTime] = ($slotCounter[$firstTime] ?? 0) + 1;
            $row['slot_rank'] = $slotCounter[$firstTime];
        }
        unset($row);

        return $rows;
    }

    private static function enrolmentsWithFallback(array $enrolments): array {
        $success = 0;
        $failed = 0;
        $sampleError = '';
        if (empty($enrolments)) return [0, 0, ''];

        foreach (array_chunk($enrolments, 100) as $chunk) {
            try {
                Moodle::enrolUsers($chunk);
                $success += count($chunk);
                continue;
            } catch (Throwable $batchError) {
                // row fallback
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

    private static function buildTeacherRows(int $termId): array {
        global $CFG;
        return DB::getAll("
            SELECT
                c.id AS class_id,
                c.mdl_id AS mdl_course_id,
                cr.name AS course_name,
                t.id AS teacher_id,
                u.id AS user_id,
                u.mdl_id AS mdl_teacher_id
            FROM {$CFG->classestable} c
            JOIN {$CFG->coursestable} cr ON cr.id = c.course_id AND cr.deleted = 0
            JOIN {$CFG->teacherstable} t ON t.id = c.teacher_id AND t.deleted = 0
            JOIN {$CFG->userstable} u ON u.id = t.user_id AND u.suspend = 0
            WHERE c.deleted = 0 AND c.term_id = :term_id
            ORDER BY c.id ASC
        ", [':term_id' => $termId]);
    }

    private static function buildStudentRows(int $termId): array {
        global $CFG;
        return DB::getAll("
            SELECT
                e.id AS enroll_id,
                e.class_id AS class_id,
                c.mdl_id AS mdl_course_id,
                cr.name AS course_name,
                s.id AS student_id,
                u.id AS user_id,
                u.mdl_id AS mdl_student_id
            FROM {$CFG->enrollstable} e
            JOIN {$CFG->classestable} c ON c.id = e.class_id AND c.deleted = 0
            JOIN {$CFG->coursestable} cr ON cr.id = c.course_id AND cr.deleted = 0
            JOIN {$CFG->studentstable} s ON s.id = e.student_id AND s.deleted = 0
            JOIN {$CFG->userstable} u ON u.id = s.user_id AND u.suspend = 0
            WHERE e.deleted = 0 AND c.term_id = :term_id
            ORDER BY e.class_id ASC, s.id ASC
        ", [':term_id' => $termId]);
    }

    private static function buildRemoteEnrolMap(array $courseIds): array {
        $map = [];
        foreach ($courseIds as $courseId) {
            $courseId = (int)$courseId;
            if ($courseId <= 0) continue;
            try {
                $users = Moodle::getEnrolledUsers($courseId);
                foreach ($users as $u) {
                    $uid = (int)($u['id'] ?? 0);
                    if ($uid > 0) {
                        $map[$courseId][$uid] = true;
                    }
                }
            } catch (Throwable $e) {
                // keep empty map for this course
            }
        }
        return $map;
    }

    private static function syncCourseByClassId(int $classId, array $term): array {
        global $CFG;
        $classes = self::getDecoratedClasses((int)$term['id']);
        $classRow = null;
        foreach ($classes as $row) {
            if ((int)$row['id'] === $classId) {
                $classRow = $row;
                break;
            }
        }
        if (!$classRow) {
            return ['success' => false, 'msg' => 'کلاس پیدا نشد'];
        }

        $mdlCategoryId = (int)($term['mdl_id'] ?? 0);
        if ($mdlCategoryId <= 0) {
            return ['success' => false, 'msg' => 'شناسه دسته‌بندی مودل برای ترم تنظیم نشده است'];
        }

        $payload = self::buildMoodleCoursePayload($classRow, $term);
        $payload['categoryid'] = $mdlCategoryId;
        $payload['visible'] = 1;

        $byCategory = Moodle::getCoursesByField('category', (string)$mdlCategoryId);
        $courses = $byCategory['courses'] ?? [];
        $moodleById = [];
        $moodleByIdNumber = [];
        foreach ($courses as $mc) {
            if (!empty($mc['id'])) $moodleById[(int)$mc['id']] = $mc;
            if (!empty($mc['idnumber'])) $moodleByIdNumber[(string)$mc['idnumber']] = $mc;
        }

        $existing = null;
        $classMdlId = (int)($classRow['mdl_id'] ?? 0);
        if ($classMdlId > 0 && isset($moodleById[$classMdlId])) {
            $existing = $moodleById[$classMdlId];
        } elseif (!empty($payload['idnumber']) && isset($moodleByIdNumber[$payload['idnumber']])) {
            $existing = $moodleByIdNumber[$payload['idnumber']];
        }

        if ($existing) {
            self::assertShortnameAvailable((string)$payload['shortname'], (int)$existing['id']);
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
            DB::query("UPDATE {$CFG->classestable} SET mdl_id = :mdl_id WHERE id = :id", [
                ':mdl_id' => (int)$existing['id'],
                ':id' => $classId
            ]);
            return ['success' => true, 'msg' => 'درس در مودل بروزرسانی شد', 'mdl_id' => (int)$existing['id']];
        }

        self::assertShortnameAvailable((string)$payload['shortname'], 0);
        $newId = Moodle::createCourse($payload);
        DB::query("UPDATE {$CFG->classestable} SET mdl_id = :mdl_id WHERE id = :id", [
            ':mdl_id' => (int)$newId,
            ':id' => $classId
        ]);
        return ['success' => true, 'msg' => 'درس در مودل ایجاد شد', 'mdl_id' => (int)$newId];
    }

    private static function syncTeacherByClassId(int $classId): array {
        global $CFG, $MDL;
        $row = DB::getRow("
            SELECT c.id AS class_id, c.mdl_id AS mdl_course_id, u.mdl_id AS mdl_teacher_id
            FROM {$CFG->classestable} c
            JOIN {$CFG->teacherstable} t ON t.id = c.teacher_id AND t.deleted = 0
            JOIN {$CFG->userstable} u ON u.id = t.user_id AND u.suspend = 0
            WHERE c.id = :class_id AND c.deleted = 0
            LIMIT 1
        ", [':class_id' => $classId]);

        if (!$row) return ['success' => false, 'msg' => 'رابطه معلم/کلاس پیدا نشد'];
        $courseId = (int)($row['mdl_course_id'] ?? 0);
        $teacherId = (int)($row['mdl_teacher_id'] ?? 0);
        if ($courseId <= 0 || $teacherId <= 0) return ['success' => false, 'msg' => 'شناسه مودل درس یا معلم ناقص است'];

        $teacherRoleId = (int)($MDL->teacherRoleId ?? 3);
        [$sent, $failed, $sampleError] = self::enrolmentsWithFallback([[
            'roleid' => $teacherRoleId,
            'userid' => $teacherId,
            'courseid' => $courseId
        ]]);

        if ($sent > 0) return ['success' => true, 'msg' => 'معلم با مودل سینک شد'];
        return ['success' => false, 'msg' => $sampleError ?: 'سینک معلم ناموفق بود'];
    }

    private static function syncStudentByEnrollId(int $enrollId): array {
        global $CFG, $MDL;
        $row = DB::getRow("
            SELECT e.id AS enroll_id, c.mdl_id AS mdl_course_id, u.mdl_id AS mdl_student_id
            FROM {$CFG->enrollstable} e
            JOIN {$CFG->classestable} c ON c.id = e.class_id AND c.deleted = 0
            JOIN {$CFG->studentstable} s ON s.id = e.student_id AND s.deleted = 0
            JOIN {$CFG->userstable} u ON u.id = s.user_id AND u.suspend = 0
            WHERE e.id = :enroll_id AND e.deleted = 0
            LIMIT 1
        ", [':enroll_id' => $enrollId]);

        if (!$row) return ['success' => false, 'msg' => 'ثبت‌نام پیدا نشد'];
        $courseId = (int)($row['mdl_course_id'] ?? 0);
        $studentId = (int)($row['mdl_student_id'] ?? 0);
        if ($courseId <= 0 || $studentId <= 0) return ['success' => false, 'msg' => 'شناسه مودل درس یا دانش‌آموز ناقص است'];

        $studentRoleId = (int)($MDL->studentRoleId ?? 5);
        [$sent, $failed, $sampleError] = self::enrolmentsWithFallback([[
            'roleid' => $studentRoleId,
            'userid' => $studentId,
            'courseid' => $courseId
        ]]);

        if ($sent > 0) return ['success' => true, 'msg' => 'دانش‌آموز با مودل سینک شد'];
        return ['success' => false, 'msg' => $sampleError ?: 'سینک دانش‌آموز ناموفق بود'];
    }

    private static function deleteCourseLink(int $classId, bool $remoteDelete = true): array {
        global $CFG;
        $row = DB::getRow("SELECT id, mdl_id FROM {$CFG->classestable} WHERE id = :id AND deleted = 0 LIMIT 1", [':id' => $classId]);
        if (!$row) return ['success' => false, 'msg' => 'کلاس پیدا نشد'];

        $mdlId = (int)($row['mdl_id'] ?? 0);
        if ($remoteDelete && $mdlId > 0) {
            Moodle::deleteCourses([$mdlId]);
        }
        DB::query("UPDATE {$CFG->classestable} SET mdl_id = NULL WHERE id = :id", [':id' => $classId]);
        return ['success' => true, 'msg' => 'اتصال درس با مودل حذف شد'];
    }

    private static function deleteTeacherEnroll(int $classId): array {
        global $CFG;
        $row = DB::getRow("
            SELECT c.mdl_id AS mdl_course_id, u.mdl_id AS mdl_teacher_id
            FROM {$CFG->classestable} c
            JOIN {$CFG->teacherstable} t ON t.id = c.teacher_id AND t.deleted = 0
            JOIN {$CFG->userstable} u ON u.id = t.user_id AND u.suspend = 0
            WHERE c.id = :class_id AND c.deleted = 0
            LIMIT 1
        ", [':class_id' => $classId]);

        if (!$row) return ['success' => false, 'msg' => 'رابطه معلم/کلاس پیدا نشد'];
        $courseId = (int)($row['mdl_course_id'] ?? 0);
        $teacherId = (int)($row['mdl_teacher_id'] ?? 0);
        if ($courseId <= 0 || $teacherId <= 0) return ['success' => false, 'msg' => 'شناسه مودل درس یا معلم ناقص است'];

        Moodle::unenrolUsers([[
            'userid' => $teacherId,
            'courseid' => $courseId
        ]]);
        return ['success' => true, 'msg' => 'معلم از درس مودل حذف شد'];
    }

    private static function deleteStudentEnroll(int $enrollId): array {
        global $CFG;
        $row = DB::getRow("
            SELECT c.mdl_id AS mdl_course_id, u.mdl_id AS mdl_student_id
            FROM {$CFG->enrollstable} e
            JOIN {$CFG->classestable} c ON c.id = e.class_id AND c.deleted = 0
            JOIN {$CFG->studentstable} s ON s.id = e.student_id AND s.deleted = 0
            JOIN {$CFG->userstable} u ON u.id = s.user_id AND u.suspend = 0
            WHERE e.id = :enroll_id AND e.deleted = 0
            LIMIT 1
        ", [':enroll_id' => $enrollId]);

        if (!$row) return ['success' => false, 'msg' => 'ثبت‌نام پیدا نشد'];
        $courseId = (int)($row['mdl_course_id'] ?? 0);
        $studentId = (int)($row['mdl_student_id'] ?? 0);
        if ($courseId <= 0 || $studentId <= 0) return ['success' => false, 'msg' => 'شناسه مودل درس یا دانش‌آموز ناقص است'];

        Moodle::unenrolUsers([[
            'userid' => $studentId,
            'courseid' => $courseId
        ]]);
        return ['success' => true, 'msg' => 'دانش‌آموز از درس مودل حذف شد'];
    }

    public static function index($request) {
        global $CFG;
        self::mustBeGuide();
        $term = self::getSelectedTerm();
        $terms = Term::getTerm(mode: 'all');
        $subtitle = 'Sync';
        return include_once __DIR__ . '/../views/sync/index.php';
    }

    public static function data($request) {
        global $CFG;
        self::mustBeGuide();

        if (!self::hasColumn($CFG->classestable, 'mdl_id') || !self::hasColumn($CFG->termstable, 'mdl_id')) {
            self::respond(['success' => false, 'msg' => 'فیلد mdl_id در جداول لازم موجود نیست'], $CFG->wwwroot . '/sync');
        }

        $termId = (int)($request['get']['term_id'] ?? 0);
        $term = self::getSelectedTerm($termId);
        if (!$term) {
            self::respond(['success' => false, 'msg' => 'ترم پیدا نشد'], $CFG->wwwroot . '/sync');
        }
        $termId = (int)$term['id'];
        $categoryId = (int)($term['mdl_id'] ?? 0);

        $classes = self::getDecoratedClasses($termId);
        $moodleById = [];
        if ($categoryId > 0) {
            try {
                $catData = Moodle::getCoursesByField('category', (string)$categoryId);
                foreach (($catData['courses'] ?? []) as $mc) {
                    if (!empty($mc['id'])) {
                        $moodleById[(int)$mc['id']] = $mc;
                    }
                }
            } catch (Throwable $e) {
                // keep empty
            }
        }

        $courseRows = [];
        foreach ($classes as $row) {
            $payload = self::buildMoodleCoursePayload($row, $term);
            $mdlCourseId = (int)($row['mdl_id'] ?? 0);
            $isSynced = $mdlCourseId > 0 && isset($moodleById[$mdlCourseId]);
            $reason = '';
            if (!$isSynced) {
                if ($categoryId <= 0) $reason = 'شناسه دسته‌بندی مودل برای ترم تنظیم نشده است';
                elseif ($mdlCourseId <= 0) $reason = 'هنوز به مودل متصل نشده';
                else $reason = 'شناسه مودل در دسته‌بندی ترم پیدا نشد';
            }
            $courseRows[] = [
                'id' => (int)$row['id'],
                'course_name' => (string)($payload['fullname'] ?? ''),
                'local_course_name' => (string)($row['course_name'] ?? ''),
                'shortname' => (string)($payload['shortname'] ?? ''),
                'idnumber' => (string)($payload['idnumber'] ?? ''),
                'mdl_course_id' => $mdlCourseId,
                'status' => $isSynced ? 'synced' : 'unsynced',
                'reason' => $reason,
                'time' => (string)($row['time'] ?? '')
            ];
        }

        $teacherRowsRaw = self::buildTeacherRows($termId);
        $studentRowsRaw = self::buildStudentRows($termId);
        $allCourseIds = array_values(array_unique(array_map(fn($r) => (int)($r['mdl_course_id'] ?? 0), array_merge($teacherRowsRaw, $studentRowsRaw))));
        $enrolMap = self::buildRemoteEnrolMap($allCourseIds);

        $teacherRows = [];
        foreach ($teacherRowsRaw as $row) {
            $courseId = (int)($row['mdl_course_id'] ?? 0);
            $userId = (int)($row['mdl_teacher_id'] ?? 0);
            $isSynced = ($courseId > 0 && $userId > 0 && !empty($enrolMap[$courseId][$userId]));
            $reason = '';
            if (!$isSynced) {
                $reason = ($courseId <= 0 || $userId <= 0) ? 'شناسه مودل ناقص است' : 'در درس مودل ثبت نشده';
            }
            $teacherRows[] = [
                'id' => (int)$row['class_id'],
                'class_id' => (int)$row['class_id'],
                'teacher_id' => (int)$row['teacher_id'],
                'mdl_teacher_id' => $userId,
                'mdl_course_id' => $courseId,
                'course_name' => (string)($row['course_name'] ?? ''),
                'status' => $isSynced ? 'synced' : 'unsynced',
                'reason' => $reason
            ];
        }

        $studentRows = [];
        foreach ($studentRowsRaw as $row) {
            $courseId = (int)($row['mdl_course_id'] ?? 0);
            $userId = (int)($row['mdl_student_id'] ?? 0);
            $isSynced = ($courseId > 0 && $userId > 0 && !empty($enrolMap[$courseId][$userId]));
            $reason = '';
            if (!$isSynced) {
                $reason = ($courseId <= 0 || $userId <= 0) ? 'شناسه مودل ناقص است' : 'در درس مودل ثبت نشده';
            }
            $studentRows[] = [
                'id' => (int)$row['enroll_id'],
                'enroll_id' => (int)$row['enroll_id'],
                'class_id' => (int)$row['class_id'],
                'student_id' => (int)$row['student_id'],
                'mdl_student_id' => $userId,
                'mdl_course_id' => $courseId,
                'course_name' => (string)($row['course_name'] ?? ''),
                'status' => $isSynced ? 'synced' : 'unsynced',
                'reason' => $reason
            ];
        }

        self::respond([
            'success' => true,
            'term' => [
                'id' => $termId,
                'name' => (string)($term['name'] ?? ''),
                'mdl_id' => $categoryId
            ],
            'rows' => [
                'courses' => $courseRows,
                'teachers' => $teacherRows,
                'students' => $studentRows
            ]
        ], $CFG->wwwroot . '/sync');
    }

    public static function run($request) {
        global $CFG;
        self::mustBeAdmin();
        $kind = trim((string)($request['post']['kind'] ?? ''));
        $id = (int)($request['post']['id'] ?? 0);
        $termId = (int)($request['post']['term_id'] ?? 0);
        $term = self::getSelectedTerm($termId);

        if (!$kind || $id <= 0) {
            self::respond(['success' => false, 'msg' => 'پارامترهای ورودی نامعتبر است'], $CFG->wwwroot . '/sync');
        }

        try {
            if ($kind === 'course') {
                if (!$term) throw new Exception('ترم پیدا نشد');
                $res = self::syncCourseByClassId($id, $term);
            } elseif ($kind === 'teacher') {
                $res = self::syncTeacherByClassId($id);
            } elseif ($kind === 'student') {
                $res = self::syncStudentByEnrollId($id);
            } else {
                throw new Exception('نوع عملیات نامعتبر است');
            }
            self::respond($res, $CFG->wwwroot . '/sync');
        } catch (Throwable $e) {
            self::respond(['success' => false, 'msg' => $e->getMessage()], $CFG->wwwroot . '/sync');
        }
    }

    public static function runBulk($request) {
        global $CFG;
        self::mustBeAdmin();
        $kind = trim((string)($request['post']['kind'] ?? ''));
        $termId = (int)($request['post']['term_id'] ?? 0);
        $ids = $request['post']['ids'] ?? [];
        if (!is_array($ids)) $ids = [$ids];
        $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));

        if (!$kind || empty($ids)) {
            self::respond(['success' => false, 'msg' => 'پارامترهای ورودی نامعتبر است'], $CFG->wwwroot . '/sync');
        }

        $ok = 0;
        $fail = 0;
        $sample = '';
        $term = self::getSelectedTerm($termId);

        foreach ($ids as $id) {
            try {
                if ($kind === 'course') {
                    if (!$term) throw new Exception('ترم پیدا نشد');
                    $res = self::syncCourseByClassId($id, $term);
                } elseif ($kind === 'teacher') {
                    $res = self::syncTeacherByClassId($id);
                } elseif ($kind === 'student') {
                    $res = self::syncStudentByEnrollId($id);
                } else {
                    throw new Exception('نوع عملیات نامعتبر است');
                }
                if (!empty($res['success'])) $ok++;
                else {
                    $fail++;
                    if ($sample === '') $sample = (string)($res['msg'] ?? 'ناموفق');
                }
            } catch (Throwable $e) {
                $fail++;
                if ($sample === '') $sample = $e->getMessage();
            }
        }

        $kindTitle = $kind === 'course' ? 'دروس' : ($kind === 'teacher' ? 'معلمان' : 'دانش‌آموزان');
        $msg = "سینک {$kindTitle} انجام شد | موفق: {$ok} | ناموفق: {$fail}";
        if ($sample !== '') $msg .= " | نمونه خطا: {$sample}";
        self::respond(['success' => true, 'msg' => $msg], $CFG->wwwroot . '/sync');
    }

    public static function delete($request) {
        global $CFG;
        self::mustBeAdmin();
        $kind = trim((string)($request['post']['kind'] ?? ''));
        $id = (int)($request['post']['id'] ?? 0);
        $remote = (int)($request['post']['remote'] ?? 1) === 1;
        if (!$kind || $id <= 0) {
            self::respond(['success' => false, 'msg' => 'پارامترهای ورودی نامعتبر است'], $CFG->wwwroot . '/sync');
        }

        try {
            if ($kind === 'course') {
                $res = self::deleteCourseLink($id, $remote);
            } elseif ($kind === 'teacher') {
                $res = self::deleteTeacherEnroll($id);
            } elseif ($kind === 'student') {
                $res = self::deleteStudentEnroll($id);
            } else {
                throw new Exception('نوع عملیات نامعتبر است');
            }
            self::respond($res, $CFG->wwwroot . '/sync');
        } catch (Throwable $e) {
            self::respond(['success' => false, 'msg' => $e->getMessage()], $CFG->wwwroot . '/sync');
        }
    }
}
