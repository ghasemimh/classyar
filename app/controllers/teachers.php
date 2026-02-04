<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../services/moodleAPI.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../models/setting.php';
require_once __DIR__ . '/../models/teacher.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/course.php';
require_once __DIR__ . '/../models/category.php';
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

    public static function create($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $categories = Category::getCategory(mode: 'all');
        $msg = $request['get']['msg'] ?? NULL;
        return include_once __DIR__ . '/../views/teachers/create.php';
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
            $msg = 'ØªØºÛŒÛŒØ±Ø§Øª Ù…Ø¹Ù„Ù… Ø¨Ø§ Ù…ÙˆÙÙ‚ÛŒØª Ø°Ø®ÛŒØ±Ù‡ Ø´Ø¯.';
            return self::respond(['success' => true, 'msg' => $msg], $CFG->wwwroot . "/teacher?msg=" . urlencode($msg));
        }

        return self::respond(['success' => false, 'msg' => $MSG->unknownerror], $CFG->wwwroot . "/teacher/edit/$id?msg=" . urlencode($MSG->unknownerror));
    }

    public static function editTimes($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'msg' => $MSG->notallowed]);
            exit();
        }

        $teacherId = $request['post']['id'] ?? NULL;
        $times = $request['post']['times'] ?? [];

        if (!$teacherId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'msg' => $MSG->idnotgiven]);
            exit();
        }

        $teacher = Teacher::getTeacher(id: $teacherId);
        if (!$teacher) {
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
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'msg' => 'Ø²Ù…Ø§Ù†â€ŒÙ‡Ø§ Ø¨Ø§ Ù…ÙˆÙÙ‚ÛŒØª Ø¨Ø±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ Ø´Ø¯Ù†Ø¯.', 'times' => $times]);
            exit();
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'msg' => $MSG->unknownerror]);
        exit();
    }

    public static function assignCourse($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'msg' => $MSG->notallowed]);
            exit();
        }

        $teacherId = $request['post']['teacher_id'] ?? NULL;
        $courseId = $request['post']['course_id'] ?? NULL;

        if (!$teacherId || !$courseId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'msg' => $MSG->baddata]);
            exit();
        }

        $teacher = Teacher::getTeacher(id: $teacherId);
        $course = Course::getCourse(id: $courseId);
        if (!$teacher || !$course) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'msg' => $MSG->baddata]);
            exit();
        }

        $result = Teacher::assignCourse($teacherId, $courseId);
        if ($result) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'msg' => 'Ø¯ÙˆØ±Ù‡ Ø¨Ø§ Ù…ÙˆÙÙ‚ÛŒØª Ø§Ø¶Ø§ÙÙ‡ Ø´Ø¯.']);
            exit();
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'msg' => $MSG->unknownerror]);
        exit();
    }

    public static function removeCourse($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'msg' => $MSG->notallowed]);
            exit();
        }

        $teacherId = $request['post']['teacher_id'] ?? NULL;
        $courseId = $request['post']['course_id'] ?? NULL;

        if (!$teacherId || !$courseId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'msg' => $MSG->baddata]);
            exit();
        }

        $teacher = Teacher::getTeacher(id: $teacherId);
        if (!$teacher) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'msg' => $MSG->baddata]);
            exit();
        }

        $result = Teacher::removeCourse($teacherId, $courseId);
        if ($result) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'msg' => 'Ø¯ÙˆØ±Ù‡ Ø­Ø°Ù Ø´Ø¯.']);
            exit();
        }

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

        // Soft delete â†’ ÙÙ‚Ø· ÙÛŒÙ„Ø¯ deleted = 1
        $result = Course::softDelete($id);
        if ($result) return self::respond(['success' => true, 'msg' => $MSG->coursedeleted, 'id' => $id], '');
        return self::respond(['success' => false, 'msg' => $MSG->coursedeleteerror], '');
    }

    public static function confirmDelete($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $id = $request['route'][0] ?? NULL;
        if (!$id) {
            $msg = $MSG->idnotgiven;
            $courses = Course::getCourse(mode: 'all');
            return include_once __DIR__ . '/../views/teachers/index.php';
        }

        $course = Course::getCourse(id: $id);
        if (!$course) {
            $msg = $MSG->coursenotfound;
            $courses = Course::getCourse(mode: 'all');
            return include_once __DIR__ . '/../views/teachers/index.php';
        }

        $msg = $request['get']['msg'] ?? NULL;
        include __DIR__ . '/../views/teachers/confirm_delete.php';
    }

    private static function respond($data, $redirectUrl) {
        if (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        }
        header("Location: $redirectUrl");
        exit();
    }
}
