<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../services/moodleAPI.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../models/course.php';
require_once __DIR__ . '/../models/category.php';

class Courses {
    public static function index($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'guide')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $categoryId = $request['get']['category_id'] ?? NULL;
        if ($categoryId) {
            $courses = Course::getCoursesByCategory($categoryId);
        } else {
            $courses = Course::getCourse(mode: 'all');
        }
        $categories = Category::getCategory(mode: 'all');
        $msg = $request['get']['msg'] ?? NULL;
        return include_once __DIR__ . '/../views/courses/index.php';
    }

    public static function create($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $categories = Category::getCategory(mode: 'all');
        $msg = $request['get']['msg'] ?? NULL;
        return include_once __DIR__ . '/../views/courses/create.php';
    }

    public static function store($request) {
        global $CFG, $MSG;
        $post = $request['post'] ?? NULL;

        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . "/course?msg=" . urlencode($MSG->notallowed));
        }

        if ($post) {
            $crsid = trim($post['crsid'] ?? NULL);
            $name = trim($post['name'] ?? NULL);
            $categoryId = intval($post['category_id'] ?? 0);

            if ($crsid && $name && $categoryId) {
                $result = Course::create($crsid, $name, $categoryId);
                if ($result) {
                    return self::respond(['success' => true, 'msg' => $MSG->coursecreated, 'id' => $result], $CFG->wwwroot . "/course?msg=" . urlencode($MSG->coursecreated));
                }
                return self::respond(['success' => false, 'msg' => $MSG->coursecreateerror], $CFG->wwwroot . "/course/new?msg=" . urlencode($MSG->coursecreateerror));
            }
            return self::respond(['success' => false, 'msg' => $MSG->coursedataerror], $CFG->wwwroot . "/course/new?msg=" . urlencode($MSG->coursedataerror));
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
            $msg = $MSG->idnotgiven;
            $courses = Course::getCourse(mode: 'all');
            return include_once __DIR__ . '/../views/courses/index.php';
        }

        $course = Course::getCourse(id: $id);
        if (!$course) {
            $msg = $MSG->coursenotfound;
            $courses = Course::getCourse(mode: 'all');
            return include_once __DIR__ . '/../views/courses/index.php';
        }

        $categories = Category::getCategory(mode: 'all');
        $msg = $request['get']['msg'] ?? NULL;
        return include __DIR__ . '/../views/courses/edit.php';
    }

    public static function update($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . "/course?msg=" . urlencode($MSG->notallowed));
        }

        $id = $request['route'][0] ?? NULL;
        $crsid = trim($request['post']['crsid'] ?? NULL);
        $name = trim($request['post']['name'] ?? NULL);
        $categoryId = intval($request['post']['category_id'] ?? 0);

        if ($id && $crsid && $name && $categoryId) {
            $result = Course::update($id, $crsid, $name, $categoryId);
            if ($result) {
                return self::respond(['success' => true, 'msg' => $MSG->courseedited], $CFG->wwwroot . "/course?msg=" . urlencode($MSG->courseedited));
            }
            return self::respond(['success' => false, 'msg' => $MSG->courseediterror], $CFG->wwwroot . "/course/edit/$id?msg=" . urlencode($MSG->courseediterror));
        }

        return self::respond(['success' => false, 'msg' => $MSG->coursedataerror], $CFG->wwwroot . "/course/edit/$id?msg=" . urlencode($MSG->coursedataerror));
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

        // Soft delete → فقط فیلد deleted = 1
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
            return include_once __DIR__ . '/../views/courses/index.php';
        }

        $course = Course::getCourse(id: $id);
        if (!$course) {
            $msg = $MSG->coursenotfound;
            $courses = Course::getCourse(mode: 'all');
            return include_once __DIR__ . '/../views/courses/index.php';
        }

        $msg = $request['get']['msg'] ?? NULL;
        include __DIR__ . '/../views/courses/confirm_delete.php';
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
