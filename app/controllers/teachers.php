<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../services/moodleAPI.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../models/setting.php';
require_once __DIR__ . '/../models/teacher.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/course.php';
require_once __DIR__ . '/../models/category.php';
require_once __DIR__ . '/../models/term.php';
require_once __DIR__ . '/../controllers/users.php';

class Teachers {
    public static function index($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'guide')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $times = json_decode(Setting::getSetting('Times Information'), true)['times'];
        $times_count = count($times);
        $unregistered_Mdl_users = Users::getUnregisteredMdlUsers() ?? NULL;
        $teachers = Teacher::getTeacher(mode: 'all');
        $users = User::getUser(mode: 'all');
        $courses = Course::getCourse(mode: 'all');
        $Mdl_users = Moodle::getUser(mode: 'all');
        $msg = $request['get']['msg'] ?? NULL;
        return include_once __DIR__ . '/../views/teachers/index.php';
    }

    public static function printList($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'teacher')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $currentRole = $_SESSION['USER']->role ?? 'guest';
        $routeTeacherId = (int)($request['route']['id'] ?? ($request['route'][0] ?? 0));
        $teacher = null;

        if ($currentRole === 'admin') {
            if ($routeTeacherId > 0) {
                $teacher = Teacher::getTeacher(id: $routeTeacherId);
            }
            if (!$teacher) {
                $msg = 'لطفا یک معلم معتبر انتخاب کنید.';
                return include_once __DIR__ . '/../views/errors/400.php';
            }
        } else {
            $userId = (int)($_SESSION['USER']->id ?? 0);
            $teacher = Teacher::getTeacherByUserId($userId);
            if (!$teacher) {
                $msg = 'اطلاعات معلم برای کاربر فعلی یافت نشد.';
                return include_once __DIR__ . '/../views/errors/400.php';
            }
        }

        $activeTerm = Term::getTerm(mode: 'active');
        if (!$activeTerm) {
            $allTerms = Term::getTerm(mode: 'all');
            $activeTerm = !empty($allTerms) ? $allTerms[0] : null;
        }
        if (!$activeTerm) {
            $msg = 'ترم برای چاپ لیست کلاس‌ها یافت نشد.';
            return include_once __DIR__ . '/../views/errors/400.php';
        }

        $termId = (int)$activeTerm['id'];
        $classes = Teacher::getTeacherClasses((int)$teacher['id'], $termId);

        $mdlUsers = Moodle::getUser(mode: 'all');
        $mdlMap = [];
        foreach ($mdlUsers as $u) {
            $mdlId = (int)($u['id'] ?? 0);
            if ($mdlId <= 0) {
                continue;
            }
            $mdlMap[$mdlId] = [
                'firstname' => $u['firstname'] ?? '',
                'lastname' => $u['lastname'] ?? '',
                'fullname' => trim(($u['firstname'] ?? '') . ' ' . ($u['lastname'] ?? '')),
                'idnumber' => $u['idnumber'] ?? '',
            ];
        }

        $teacherMdlId = 0;
        $teacherUser = User::getUser($teacher['user_id'] ?? 0);
        if (!empty($teacherUser['mdl_id'])) {
            $teacherMdlId = (int)$teacherUser['mdl_id'];
        }
        $teacherName = $mdlMap[$teacherMdlId]['fullname'] ?? 'معلم';

        foreach ($classes as &$cls) {
            $roster = Teacher::getClassRoster((int)$cls['id']);
            foreach ($roster as &$st) {
                $mdlId = (int)($st['mdl_id'] ?? 0);
                $mdl = $mdlMap[$mdlId] ?? ['firstname' => '', 'lastname' => '', 'idnumber' => ''];
                $st['firstname'] = $mdl['firstname'];
                $st['lastname'] = $mdl['lastname'];
                $st['idnumber'] = $mdl['idnumber'];
                if (!empty($st['cohort'])) {
                    $st['grade'] = $st['cohort'];
                } elseif (!empty($st['idnumber'])) {
                    $st['grade'] = $st['idnumber'];
                } else {
                    $st['grade'] = '-';
                }
            }
            unset($st);
            $cls['roster'] = $roster;
        }
        unset($cls);

        $subtitle = 'چاپ لیست کلاس‌های معلم';
        return include_once __DIR__ . '/../views/teachers/print.php';
    }

    public static function printClassList($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'teacher')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $classId = (int)($request['route']['id'] ?? ($request['route'][0] ?? 0));
        if ($classId <= 0) {
            $msg = 'شناسه کلاس معتبر نیست.';
            return include_once __DIR__ . '/../views/errors/400.php';
        }

        $classRow = Teacher::getClassDetails($classId);
        if (!$classRow) {
            $msg = 'کلاس موردنظر یافت نشد.';
            return include_once __DIR__ . '/../views/errors/400.php';
        }

        $currentRole = $_SESSION['USER']->role ?? 'guest';
        if ($currentRole !== 'admin') {
            $teacher = Teacher::getTeacherByUserId((int)($_SESSION['USER']->id ?? 0));
            if (!$teacher || (int)$teacher['id'] !== (int)$classRow['teacher_id']) {
                $msg = 'شما به این کلاس دسترسی ندارید.';
                return include_once __DIR__ . '/../views/errors/403.php';
            }
        }

        $mdlUsers = Moodle::getUser(mode: 'all');
        $mdlMap = [];
        foreach ($mdlUsers as $u) {
            $mdlId = (int)($u['id'] ?? 0);
            if ($mdlId <= 0) continue;
            $mdlMap[$mdlId] = [
                'firstname' => $u['firstname'] ?? '',
                'lastname' => $u['lastname'] ?? '',
                'fullname' => trim(($u['firstname'] ?? '') . ' ' . ($u['lastname'] ?? '')),
                'idnumber' => $u['idnumber'] ?? '',
            ];
        }

        $teacherName = 'معلم';
        $teacherRows = Teacher::getTeacher((int)$classRow['teacher_id']);
        if (!empty($teacherRows['user_id'])) {
            $teacherUser = User::getUser((int)$teacherRows['user_id']);
            $teacherMdlId = (int)($teacherUser['mdl_id'] ?? 0);
            $teacherName = $mdlMap[$teacherMdlId]['fullname'] ?? $teacherName;
        }

        $roster = Teacher::getClassRoster($classId);
        foreach ($roster as &$st) {
            $mdlId = (int)($st['mdl_id'] ?? 0);
            $mdl = $mdlMap[$mdlId] ?? ['firstname' => '', 'lastname' => '', 'idnumber' => ''];
            $st['firstname'] = $mdl['firstname'];
            $st['lastname'] = $mdl['lastname'];
            $st['idnumber'] = $mdl['idnumber'];
            if (!empty($st['cohort'])) {
                $st['grade'] = $st['cohort'];
            } elseif (!empty($st['idnumber'])) {
                $st['grade'] = $st['idnumber'];
            } else {
                $st['grade'] = '-';
            }
        }
        unset($st);

        $subtitle = 'لیست ثبت‌نام درس';
        return include_once __DIR__ . '/../views/teachers/print_class.php';
    }

    public static function create($request) {
        global $CFG;
        header('Location: ' . $CFG->wwwroot . '/teacher');
        exit();
    }

    public static function store($request) {
        global $CFG, $MSG;
        $post = $request['post'] ?? NULL;

        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . "/course?msg=" . urlencode($MSG->notallowed));
        }

        if ($post) {
            $crsid = intval(trim($post['crsid'] ?? NULL));
            $name = trim($post['name'] ?? NULL);
            $categoryId = intval($post['category_id'] ?? 0);

            if ($crsid && $name && $categoryId) {
                $crsidExists = Course::getCourse(crsid: $crsid);
                if ($crsidExists) {
                    return self::respond(['success' => false, 'msg' => $MSG->coursecrsidexisterror], $CFG->wwwroot . "/course/new?msg=" . urlencode($MSG->coursecrsidexisterror));
                }
                $nameExists = Course::getCourse(name: $name);
                if ($nameExists) {
                    return self::respond(['success' => false, 'msg' => $MSG->coursenameexisterror], $CFG->wwwroot . "/course/new?msg=" . urlencode($MSG->coursenameexisterror));
                }
                $result = Course::create($crsid, $name, $categoryId);
                if ($result) {
                    return self::respond(['success' => true, 'msg' => $MSG->coursecreated, 'id' => $result], $CFG->wwwroot . "/course?msg=" . urlencode($MSG->coursecreated));
                }
                return self::respond(['success' => false, 'msg' => $MSG->coursecreateerror], $CFG->wwwroot . "/course/new?msg=" . urlencode($MSG->coursecreateerror));
            }
            if (!$name) {
                return self::respond(['success' => false, 'msg' => $MSG->coursenameemptyerror], $CFG->wwwroot . "/course/new?msg=" . urlencode($MSG->coursenameemptyerror));
            }
            if (!$crsid) {
                return self::respond(['success' => false, 'msg' => $MSG->coursecrsidemptyerror], $CFG->wwwroot . "/course/new?msg=" . urlencode($MSG->coursecrsidemptyerror));
            }
            if (!$categoryId) {
                return self::respond(['success' => false, 'msg' => $MSG->coursecategoryemptyerror], $CFG->wwwroot . "/course/new?msg=" . urlencode($MSG->coursecategoryemptyerror));
            }
            return self::respond(['success' => false, 'msg' => $MSG->baddata], $CFG->wwwroot . "/course/new?msg=" . urlencode($MSG->baddata));
        }

        return self::respond(['success' => false, 'msg' => $MSG->badrequest], $CFG->wwwroot . "/course?msg=" . urlencode($MSG->badrequest));
    }

    public static function edit($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $id = $request['route'][0] ?? NULL;
        if (!$id) {
            $request['get']['msg'] = $MSG->idnotgiven;
            return self::index($request);
        }

        $teacher = Teacher::getTeacher(id: $id);
        if (!$teacher) {
            $request['get']['msg'] = $MSG->baddata;
            return self::index($request);
        }

        $times = json_decode(Setting::getSetting('Times Information'), true)['times'] ?? [];
        $courses = Course::getCourse(mode: 'all');
        $user = User::getUser($teacher['user_id']);
        $mdlUser = NULL;
        if (!empty($user['mdl_id'])) {
            $mdlUser = Moodle::getUser('id', $user['mdl_id']);
        }
        $msg = $request['get']['msg'] ?? NULL;
        return include __DIR__ . '/../views/teachers/edit.php';
    }

    public static function update($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . "/teacher?msg=" . urlencode($MSG->notallowed));
        }

        $id = $request['route'][0] ?? NULL;
        $times = $request['post']['times'] ?? [];

        if (!$id) {
            return self::respond(['success' => false, 'msg' => $MSG->idnotgiven], $CFG->wwwroot . "/teacher?msg=" . urlencode($MSG->idnotgiven));
        }

        $teacher = Teacher::getTeacher(id: $id);
        if (!$teacher) {
            return self::respond(['success' => false, 'msg' => $MSG->baddata], $CFG->wwwroot . "/teacher?msg=" . urlencode($MSG->baddata));
        }

        $timesInfo = json_decode(Setting::getSetting('Times Information'), true);
        $validTimes = array_map('strval', array_column($timesInfo['times'] ?? [], 'id'));
        $times = is_array($times) ? $times : [];
        $times = array_values(array_filter($times, function ($t) use ($validTimes) {
            return in_array((string)$t, $validTimes, true);
        }));

        $result = Teacher::updateTimes($id, $times);
        if ($result) {
            $msg = 'تغییرات معلم با موفقیت ذخیره شد.';
            return self::respond(['success' => true, 'msg' => $msg], $CFG->wwwroot . "/teacher?msg=" . urlencode($msg));
        }

        return self::respond(['success' => false, 'msg' => $MSG->unknownerror], $CFG->wwwroot . "/teacher/edit/$id?msg=" . urlencode($MSG->unknownerror));
    }

    public static function editTimes($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'msg' => $MSG->notallowed]);
            exit();
        }

        $teacherId = $request['post']['id'] ?? NULL;
        $times = $request['post']['times'] ?? [];

        if (!$teacherId) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'msg' => $MSG->idnotgiven]);
            exit();
        }

        $teacher = Teacher::getTeacher(id: $teacherId);
        if (!$teacher) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'msg' => $MSG->baddata]);
            exit();
        }

        $timesInfo = json_decode(Setting::getSetting('Times Information'), true);
        $validTimes = array_map('strval', array_column($timesInfo['times'] ?? [], 'id'));
        $times = is_array($times) ? $times : [];
        $times = array_values(array_filter($times, function ($t) use ($validTimes) {
            return in_array((string)$t, $validTimes, true);
        }));

        $result = Teacher::updateTimes($teacherId, $times);
        if ($result) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'msg' => 'زمان‌ها با موفقیت بروزرسانی شدند.', 'times' => $times]);
            exit();
        }

        if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
        echo json_encode(['success' => false, 'msg' => $MSG->unknownerror]);
        exit();
    }

    public static function assignCourse($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'msg' => $MSG->notallowed]);
            exit();
        }

        $teacherId = $request['post']['teacher_id'] ?? NULL;
        $courseId = $request['post']['course_id'] ?? NULL;

        if (!$teacherId || !$courseId) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'msg' => $MSG->baddata]);
            exit();
        }

        $teacher = Teacher::getTeacher(id: $teacherId);
        $course = Course::getCourse(id: $courseId);
        if (!$teacher || !$course) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'msg' => $MSG->baddata]);
            exit();
        }

        $result = Teacher::assignCourse($teacherId, $courseId);
        if ($result) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'msg' => 'دوره با موفقیت اضافه شد.']);
            exit();
        }

        if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
        echo json_encode(['success' => false, 'msg' => $MSG->unknownerror]);
        exit();
    }

    public static function removeCourse($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'msg' => $MSG->notallowed]);
            exit();
        }

        $teacherId = $request['post']['teacher_id'] ?? NULL;
        $courseId = $request['post']['course_id'] ?? NULL;

        if (!$teacherId || !$courseId) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'msg' => $MSG->baddata]);
            exit();
        }

        $teacher = Teacher::getTeacher(id: $teacherId);
        if (!$teacher) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'msg' => $MSG->baddata]);
            exit();
        }

        $result = Teacher::removeCourse($teacherId, $courseId);
        if ($result) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'msg' => 'دوره حذف شد.']);
            exit();
        }

        if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
        echo json_encode(['success' => false, 'msg' => $MSG->unknownerror]);
        exit();
    }

    public static function delete($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], '');
        }

        $id = $request['route'][0] ?? NULL;
        if (!$id) return self::respond(['success' => false, 'msg' => $MSG->idnotgiven], '');

        $course = Course::getCourse(id: $id);
        if (!$course) return self::respond(['success' => false, 'msg' => $MSG->coursenotfound], '');

        // Soft delete -> فقط فیلد deleted = 1
        $result = Course::softDelete($id);
        if ($result) return self::respond(['success' => true, 'msg' => $MSG->coursedeleted, 'id' => $id], '');
        return self::respond(['success' => false, 'msg' => $MSG->coursedeleteerror], '');
    }

    public static function confirmDelete($request) {
        global $CFG;
        header('Location: ' . $CFG->wwwroot . '/teacher');
        exit();
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
        header("Location: $redirectUrl");
        exit();
    }
}



