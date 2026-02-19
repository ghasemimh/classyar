<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../models/course.php';
require_once __DIR__ . '/../models/category.php';
require_once __DIR__ . '/../services/validator.php';

class Courses {
    public static function index($request) {
        global $MSG;
        if (!Auth::hasPermission(role: 'guide')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $categoryId = Validator::positiveInt($request['get']['category_id'] ?? null);
        $courses = Course::getCourse(mode: 'all');
        $categories = Category::getCategory(mode: 'all');
        $msg = $request['get']['msg'] ?? null;
        return include_once __DIR__ . '/../views/courses/index.php';
    }

    public static function create($request) {
        global $CFG;
        header('Location: ' . $CFG->wwwroot . '/course');
        exit();
    }

    public static function store($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . '/course');
        }

        $crsid = Validator::positiveInt($request['post']['crsid'] ?? null);
        $name = Validator::str($request['post']['name'] ?? null, 200);
        $categoryId = Validator::positiveInt($request['post']['category_id'] ?? null);

        if (!$name) {
            return self::respond(['success' => false, 'msg' => $MSG->coursenameemptyerror], $CFG->wwwroot . '/course');
        }
        if (!$crsid) {
            return self::respond(['success' => false, 'msg' => $MSG->coursecrsidemptyerror], $CFG->wwwroot . '/course');
        }
        if (!$categoryId) {
            return self::respond(['success' => false, 'msg' => $MSG->coursecategoryemptyerror], $CFG->wwwroot . '/course');
        }

        if (Course::getCourse(crsid: $crsid)) {
            return self::respond(['success' => false, 'msg' => $MSG->coursecrsidexisterror], $CFG->wwwroot . '/course');
        }
        if (Course::getCourse(name: $name)) {
            return self::respond(['success' => false, 'msg' => $MSG->coursenameexisterror], $CFG->wwwroot . '/course');
        }

        $result = Course::create($crsid, $name, $categoryId);
        if ($result) {
            return self::respond(['success' => true, 'msg' => $MSG->coursecreated, 'id' => $result], $CFG->wwwroot . '/course');
        }
        return self::respond(['success' => false, 'msg' => $MSG->coursecreateerror], $CFG->wwwroot . '/course');
    }

    public static function edit($request) {
        global $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $id = Validator::positiveInt($request['route'][0] ?? null);
        if (!$id) {
            $msg = $MSG->idnotgiven;
            return self::index(['get' => [], 'route' => []]);
        }

        $course = Course::getCourse(id: $id);
        if (!$course) {
            $msg = $MSG->coursenotfound;
            return self::index(['get' => [], 'route' => []]);
        }

        $categories = Category::getCategory(mode: 'all');
        $msg = $request['get']['msg'] ?? null;
        return include __DIR__ . '/../views/courses/edit.php';
    }

    public static function update($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . '/course');
        }

        $id = Validator::positiveInt($request['route'][0] ?? null);
        $crsid = Validator::positiveInt($request['post']['crsid'] ?? null);
        $name = Validator::str($request['post']['name'] ?? null, 200);
        $categoryId = Validator::positiveInt($request['post']['category_id'] ?? null);

        if (!$id) {
            return self::respond(['success' => false, 'msg' => $MSG->idnotgiven], $CFG->wwwroot . '/course');
        }
        if (!$name) {
            return self::respond(['success' => false, 'msg' => $MSG->coursenameemptyerror], $CFG->wwwroot . "/course/edit/$id");
        }
        if (!$crsid) {
            return self::respond(['success' => false, 'msg' => $MSG->coursecrsidemptyerror], $CFG->wwwroot . "/course/edit/$id");
        }
        if (!$categoryId) {
            return self::respond(['success' => false, 'msg' => $MSG->coursecategoryemptyerror], $CFG->wwwroot . "/course/edit/$id");
        }

        $crsidExists = Course::getCourse(crsid: $crsid);
        if ($crsidExists && (int)$crsidExists['id'] !== $id) {
            return self::respond(['success' => false, 'msg' => $MSG->coursecrsidexisterror], $CFG->wwwroot . "/course/edit/$id");
        }
        $nameExists = Course::getCourse(name: $name);
        if ($nameExists && (int)$nameExists['id'] !== $id) {
            return self::respond(['success' => false, 'msg' => $MSG->coursenameexisterror], $CFG->wwwroot . "/course/edit/$id");
        }

        $result = Course::update($id, $crsid, $name, $categoryId);
        if ($result) {
            return self::respond(['success' => true, 'msg' => $MSG->courseedited], $CFG->wwwroot . '/course');
        }
        return self::respond(['success' => false, 'msg' => $MSG->courseediterror], $CFG->wwwroot . "/course/edit/$id");
    }

    public static function delete($request) {
        global $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], '');
        }
        $id = Validator::positiveInt($request['route'][0] ?? null);
        if (!$id) {
            return self::respond(['success' => false, 'msg' => $MSG->idnotgiven], '');
        }
        $course = Course::getCourse(id: $id);
        if (!$course) {
            return self::respond(['success' => false, 'msg' => $MSG->coursenotfound], '');
        }
        $result = Course::softDelete($id);
        if ($result) {
            return self::respond(['success' => true, 'msg' => $MSG->coursedeleted, 'id' => $id], '');
        }
        return self::respond(['success' => false, 'msg' => $MSG->coursedeleteerror], '');
    }

    public static function confirmDelete($request) {
        global $CFG;
        header('Location: ' . $CFG->wwwroot . '/course');
        exit();
    }

    private static function respond($data, $redirectUrl) {
        if (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            if (ob_get_length()) {
                ob_clean();
            }
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        }
        if (!empty($data['msg'])) {
            $type = (!empty($data['success']) && $data['success']) ? 'success' : 'error';
            Flash::set($data['msg'], $type);
        }
        if ($redirectUrl !== '') {
            header("Location: $redirectUrl");
            exit();
        }
    }
}
