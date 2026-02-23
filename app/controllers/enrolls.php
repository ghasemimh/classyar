<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../models/enroll.php';
require_once __DIR__ . '/../models/student.php';
require_once __DIR__ . '/../models/term.php';
require_once __DIR__ . '/../models/category.php';
require_once __DIR__ . '/../services/moodleAPI.php';

class Enrolls {
    private static function wantsJson($request) {
        if (($request['get']['format'] ?? '') === 'json') {
            return true;
        }
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json');
    }

    private static function isTermActive($term) {
        if (!$term) {
            return false;
        }
        $now = time();
        $start = (int)($term['start'] ?? 0);
        $end = (int)($term['end'] ?? 0);
        return ($start > 0 && $end > 0 && $now >= $start && $now <= $end);
    }

    private static function resolveAdminTerm(&$termIsActive = false) {
        $term = Term::getTerm(mode: 'active');
        if ($term) {
            $termIsActive = true;
            return $term;
        }

        $all = Term::getTerm(mode: 'all');
        if (!empty($all)) {
            $termIsActive = false;
            return $all[0];
        }

        $termIsActive = false;
        return null;
    }

    private static function respond($data, $redirectUrl = '') {
        if (self::wantsJson(['get' => $_GET])) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        }
        if ($redirectUrl !== '') {
        if (!empty($data['msg'])) {
            $type = (!empty($data['success']) && $data['success']) ? 'success' : 'error';
            Flash::set($data['msg'], $type);
        }
        header("Location: $redirectUrl");
            exit();
        }
        return $data;
    }

    private static function getTeacherNamesMap() {
        $map = [];
        $mdlUsers = Moodle::getUser(mode: 'all');
        foreach ($mdlUsers as $u) {
            $full = trim(($u['firstname'] ?? '') . ' ' . ($u['lastname'] ?? ''));
            $map[(int)($u['id'] ?? 0)] = $full;
        }
        return $map;
    }

    private static function resolveStudentMdlId($student) {
        if (!is_array($student)) {
            return 0;
        }

        $mdlId = (int)($student['mdl_id'] ?? 0);
        if ($mdlId > 0) {
            return $mdlId;
        }

        $userId = (int)($student['user_id'] ?? 0);
        if ($userId > 0) {
            $user = User::getUser($userId);
            $mdlId = (int)($user['mdl_id'] ?? 0);
            if ($mdlId > 0) {
                return $mdlId;
            }
        }

        return 0;
    }

    private static function getStudentDisplayName($student, $mdlNamesMap = null) {
        if (!is_array($mdlNamesMap)) {
            $mdlNamesMap = self::getTeacherNamesMap();
        }

        $mdlId = self::resolveStudentMdlId($student);
        if ($mdlId > 0) {
            $name = trim((string)($mdlNamesMap[$mdlId] ?? ''));
            if ($name !== '') {
                return $name;
            }
        }

        return 'دانش‌آموز #' . (int)($student['id'] ?? 0);
    }

    private static function ensureStudentAndTerm() {
        global $MSG;
        $student = Enroll::getStudentByUser($_SESSION['USER']);
        if (!$student) {
            return ['error' => $MSG->notallowed ?? 'دانش‌آموز یافت نشد.'];
        }
        $term = Term::getTerm(mode: 'active');
        if (!$term) {
            return ['error' => 'ترم فعال یافت نشد.'];
        }
        return ['student' => $student, 'term' => $term];
    }

    private static function categoriesMap() {
        $categories = Category::getCategory(mode: 'all');
        $categoriesMap = [];
        foreach ($categories as $cat) {
            $categoriesMap[(int)$cat['id']] = $cat['name'];
        }
        return $categoriesMap;
    }

    private static function requiredCategoryNames($categoriesMap) {
        $requiredCategories = Enroll::getRequiredCategoryIds();
        $requiredCategoryNames = array_map(function ($id) use ($categoriesMap) {
            return $categoriesMap[$id] ?? ('#' . $id);
        }, $requiredCategories);
        return [$requiredCategories, $requiredCategoryNames];
    }

    private static function buildPayload($student, $term, $time, $isEditable, $times, $categoriesMap, $adminMode = false, $userSession = null) {
        $termId = (int)$term['id'];
        $classes = Enroll::getTermClassesForStudent($student, $termId, $time, $userSession);
        $program = Enroll::getProgram((int)$student['id'], $termId);
        $enrollMessages = Enroll::getMessages($student, $termId, $times, $categoriesMap);
        $teacherNames = self::getTeacherNamesMap();
        [$requiredCategories, $requiredCategoryNames] = self::requiredCategoryNames($categoriesMap);
        $timesMap = [];
        foreach ($times as $t) {
            $timesMap[(string)$t['id']] = $t['label'] ?? ('زنگ ' . $t['id']);
        }

        return [
            'student_id' => (int)$student['id'],
            'student_name' => self::getStudentDisplayName($student, $teacherNames),
            'term' => [
                'id' => $termId,
                'name' => $term['name'] ?? '',
                'active' => self::isTermActive($term),
            ],
            'time' => (string)$time,
            'times' => $times,
            'times_map' => $timesMap,
            'is_editable' => (bool)$isEditable,
            'admin_mode' => (bool)$adminMode,
            'classes' => $classes,
            'program' => $program,
            'messages' => $enrollMessages,
            'teacher_names' => $teacherNames,
            'required_categories' => $requiredCategories,
            'required_category_names' => $requiredCategoryNames,
        ];
    }

    public static function index($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'student')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $ctx = self::ensureStudentAndTerm();
        if (!empty($ctx['error'])) {
            $msg = $ctx['error'];
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $student = $ctx['student'];
        $term = $ctx['term'];
        $termId = (int)$term['id'];

        $times = Enroll::getTimes();
        $routeTime = $request['route']['time'] ?? ($request['route'][0] ?? null);
        $time = $routeTime ?? ($request['get']['time'] ?? (($times[0]['id'] ?? '1')));

        $categoriesMap = self::categoriesMap();

        [$openTime, $closeTime] = Student::getComputedOpenClose($student, $term);
        $now = time();
        $isEditable = ($openTime > 0 && $closeTime > 0 && $now >= $openTime && $now <= $closeTime);

        $message = null;
        $actionResult = null;
        if (isset($request['post']['add_class_id']) && $isEditable) {
            $result = Enroll::addClass($student, $request['post']['add_class_id'], $termId, $_SESSION['USER']);
            $message = $result['msg'];
            $actionResult = $result;
        }
        if (isset($request['post']['remove_class_id']) && $isEditable) {
            $result = Enroll::removeClass($student, $request['post']['remove_class_id'], $termId, $_SESSION['USER']);
            $message = $result['msg'];
            $actionResult = $result;
        }
        if ((isset($request['post']['add_class_id']) || isset($request['post']['remove_class_id'])) && !$isEditable) {
            $message = 'در حال حاضر بازه ثبت‌نام شما فعال نیست.';
            $actionResult = ['success' => false, 'msg' => $message];
        }
        $payload = self::buildPayload($student, $term, $time, $isEditable, $times, $categoriesMap, false, $_SESSION['USER']);
        if (self::wantsJson($request)) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $actionResult['success'] ?? true,
                'msg' => $message ?? ($actionResult['msg'] ?? ''),
                'data' => $payload
            ]);
            exit();
        }

        $classes = $payload['classes'];
        $program = $payload['program'];
        $enrollMessages = $payload['messages'];
        $teacherNames = $payload['teacher_names'];
        $requiredCategories = $payload['required_categories'];
        $requiredCategoryNames = $payload['required_category_names'];
        $subtitle = 'ثبت‌نام';

        return include_once __DIR__ . '/../views/enroll/index.php';
    }

    public static function admin($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'guide')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $termIsActive = false;
        $term = self::resolveAdminTerm($termIsActive);
        if (!$term) {
            $msg = 'ترم فعال یافت نشد.';
            return include_once __DIR__ . '/../views/errors/403.php';
        }
        $termId = (int)$term['id'];
        $times = Enroll::getTimes();

        $categoriesMap = self::categoriesMap();
        [$requiredCategories, $requiredCategoryNames] = self::requiredCategoryNames($categoriesMap);

        $students = Student::getAll();
        $teacherNames = self::getTeacherNamesMap();
        $rows = [];
        foreach ($students as $s) {
            $mdlId = (int)($s['mdl_id'] ?? 0);
            $user = null;
            if ($mdlId > 0) {
                $user = ['id' => $mdlId, 'fullname' => $teacherNames[$mdlId] ?? ('Moodle#' . $mdlId)];
            }
            [$openTime, $closeTime] = Student::getComputedOpenClose($s, $term);
            $messages = Enroll::getMessages($s, $termId, $times, $categoriesMap);
            $rows[] = [
                'student' => $s,
                'user' => $user,
                'open_time' => $openTime,
                'close_time' => $closeTime,
                'messages' => $messages
            ];
        }

        $subtitle = 'مدیریت ثبت‌نام';
        if (!$termIsActive) {
            $msg = 'ترم فعال یافت نشد؛ مدیریت ثبت‌نام بر اساس آخرین ترم انجام می‌شود.';
        }
        return include_once __DIR__ . '/../views/enroll/admin.php';
    }

    public static function adminStudent($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'guide')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $studentId = (int)($request['route']['id'] ?? 0);
        if ($studentId <= 0) {
            $msg = $MSG->idnotgiven ?? 'شناسه دانش‌آموز ارسال نشده است.';
            return include_once __DIR__ . '/../views/errors/400.php';
        }

        $student = Student::getStudent(id: $studentId);
        if (!$student) {
            $msg = 'دانش‌آموز یافت نشد.';
            return include_once __DIR__ . '/../views/errors/400.php';
        }

        $termIsActive = false;
        $term = self::resolveAdminTerm($termIsActive);
        if (!$term) {
            $msg = 'ترم فعال یافت نشد.';
            return include_once __DIR__ . '/../views/errors/403.php';
        }
        $termId = (int)$term['id'];
        $times = Enroll::getTimes();
        $routeTime = $request['route']['time'] ?? ($request['route'][1] ?? null);
        $time = $routeTime ?? ($request['get']['time'] ?? (($times[0]['id'] ?? '1')));

        $categoriesMap = self::categoriesMap();

        [$openTime, $closeTime] = Student::getComputedOpenClose($student, $term);
        // Admin edit permission depends only on active term window.
        $isEditable = $termIsActive && self::isTermActive($term);

        $message = null;
        $actionResult = null;
        if (isset($request['post']['add_class_id']) && $isEditable) {
            $result = Enroll::addClass($student, $request['post']['add_class_id'], $termId, null);
            $message = $result['msg'];
            $actionResult = $result;
        }
        if (isset($request['post']['remove_class_id']) && $isEditable) {
            $result = Enroll::removeClass($student, $request['post']['remove_class_id'], $termId, null);
            $message = $result['msg'];
            $actionResult = $result;
        }
        if ((isset($request['post']['add_class_id']) || isset($request['post']['remove_class_id'])) && !$isEditable) {
            $message = 'امکان ثبت‌نام برای این دانش‌آموز در این بازه زمانی وجود ندارد.';
            $actionResult = ['success' => false, 'msg' => $message];
        }

        $payload = self::buildPayload($student, $term, $time, $isEditable, $times, $categoriesMap, true, null);
        if (self::wantsJson($request)) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $actionResult['success'] ?? true,
                'msg' => $message ?? ($actionResult['msg'] ?? ''),
                'data' => $payload
            ]);
            exit();
        }

        $classes = $payload['classes'];
        $program = $payload['program'];
        $enrollMessages = $payload['messages'];
        $teacherNames = $payload['teacher_names'];
        $requiredCategories = $payload['required_categories'];
        $requiredCategoryNames = $payload['required_category_names'];
        $studentDisplayName = $payload['student_name'] ?? self::getStudentDisplayName($student, $teacherNames);
        $subtitle = 'ثبت‌نام دانش‌آموز';
        $adminMode = true;
        $backUrl = $CFG->wwwroot . '/enroll/admin';

        return include_once __DIR__ . '/../views/enroll/index.php';
    }

    public static function exportAdminCsv($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'guide')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $termIsActive = false;
        $term = self::resolveAdminTerm($termIsActive);
        if (!$term) {
            $msg = 'No term found for export.';
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $termId = (int)$term['id'];
        $times = Enroll::getTimes();
        $categoriesMap = self::categoriesMap();
        $timesMap = [];
        foreach ($times as $t) {
            $timesMap[(string)($t['id'] ?? '')] = (string)($t['label'] ?? ('Time ' . ($t['id'] ?? '')));
        }

        $students = Student::getAll();
        $teacherNames = self::getTeacherNamesMap();
        $rows = [];
        foreach ($students as $s) {
            $mdlId = (int)($s['mdl_id'] ?? 0);
            $user = null;
            if ($mdlId > 0) {
                $user = ['id' => $mdlId, 'fullname' => $teacherNames[$mdlId] ?? ('Moodle#' . $mdlId)];
            }
            [$openTime, $closeTime] = Student::getComputedOpenClose($s, $term);
            $messages = Enroll::getMessages($s, $termId, $times, $categoriesMap);
            $rows[] = [
                'student' => $s,
                'user' => $user,
                'open_time' => $openTime,
                'close_time' => $closeTime,
                'messages' => $messages
            ];
        }

        $filename = 'enroll-admin-term-' . $termId . '-' . date('Ymd-His') . '.csv';
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
            'student_id',
            'student_name',
            'open_time',
            'close_time',
            'missing_required_categories',
            'free_times',
            'status'
        ]);

        foreach ($rows as $r) {
            $s = $r['student'];
            $u = $r['user'];
            $m = $r['messages'];
            $name = $u['fullname'] ?? ('Student #' . (int)$s['id']);
            $missing = !empty($m['missing_categories']) ? implode(' | ', array_map('strval', $m['missing_categories'])) : '';
            $freeTimeLabels = [];
            foreach (($m['free_times'] ?? []) as $timeId) {
                $freeTimeLabels[] = $timesMap[(string)$timeId] ?? (string)$timeId;
            }
            fputcsv($out, [
                (int)$s['id'],
                $name,
                (string)$r['open_time'],
                (string)$r['close_time'],
                $missing,
                implode(' | ', $freeTimeLabels),
                !empty($m['finished']) ? 'complete' : 'incomplete',
            ]);
        }

        fclose($out);
        exit();
    }
}






