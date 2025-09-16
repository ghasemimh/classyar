<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../services/moodleAPI.php'; // جایی که کلاس Moodle رو نوشتی
require_once __DIR__ . '/auth.php'; // احراز هویت و دسترسی ها
require_once __DIR__ . '/../models/category.php';

class Categories {
    public static function index($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'guide')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $id = $request['route'][0] ?? NULL;

        if ($id) {
            $category = Category::getCategory($id);
            if ($category) {
                $msg = $request['get']['msg'] ?? NULL;
                return include_once __DIR__ . '/../views/categories/single.php';
            }
            $msg = $MSG->categorynotfound;
            $categories = Category::getCategory(mode: 'all');
            return include_once __DIR__ . '/../views/categories/index.php';
        }
        $categories = Category::getCategory(mode: 'all');
        $msg = $request['get']['msg'] ?? NULL;
        return include_once __DIR__ . '/../views/categories/index.php';
        
        
    }

    public static function create($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }
        $msg = $request['get']['msg'] ?? NULL;
        return include_once __DIR__ . '/../views/categories/create.php';
    }

    public static function store($request) {
        global $CFG, $MSG;
        $post = $request['post'] ?? NULL;

        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . "/category?msg=" . urlencode($MSG->notallowed));
        }

        if ($post) {
            $name = trim($post['name'] ?? NULL);
            if ($name) {
                $result = Category::create($name);
                if ($result) {
                    return self::respond(['success' => true, 'msg' => $MSG->categorycreated, 'id' => $result], $CFG->wwwroot . "/category?msg=" . urlencode($MSG->categorycreated));
                }
                return self::respond(['success' => false, 'msg' => $MSG->categorycreateerror], $CFG->wwwroot . "/category/new?msg=" . urlencode($MSG->categorycreateerror));
            }
            return self::respond(['success' => false, 'msg' => $MSG->categorynameerror], $CFG->wwwroot . "/category/new?msg=" . urlencode($MSG->categorynameerror));
        }

        return self::respond(['success' => false, 'msg' => $MSG->badrequest], $CFG->wwwroot . "/category?msg=" . urlencode($MSG->badrequest));
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
            $categories = Category::getCategory(mode: 'all');
            return include_once __DIR__ . '/../views/categories/index.php';
        }

        $category = Category::getCategory(id: $id);
        if (!$category) {
            $msg = $MSG->categorynotfound;
            $categories = Category::getCategory(mode: 'all');
            return include_once __DIR__ . '/../views/categories/index.php';
        }
        $msg = $request['get']['msg'] ?? NULL;
        return include __DIR__ . '/../views/categories/edit.php';
    }

    public static function update($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . "/category?msg=" . urlencode($MSG->notallowed));
        }

        $id = $request['route'][0] ?? NULL;
        $name = trim($request['post']['name'] ?? NULL);

        if ($id && $name) {
            $result = Category::update($id, $name);
            if ($result) {
                return self::respond(['success' => true, 'msg' => $MSG->categoryedited], $CFG->wwwroot . "/category?msg=" . urlencode($MSG->categoryedited));
            }
            return self::respond(['success' => false, 'msg' => $MSG->categoryediterror], $CFG->wwwroot . "/category/edit/$id?msg=" . urlencode($MSG->categoryediterror));
        }

        if (!$id) {
            return self::respond(['success' => false, 'msg' => $MSG->idnotgiven], $CFG->wwwroot . "/category?msg=" . urlencode($MSG->idnotgiven));
        }
        if (!$name) {
            return self::respond(['success' => false, 'msg' => $MSG->categorynameerror], $CFG->wwwroot . "/category/edit/$id?msg=" . urlencode($MSG->categorynameerror));
        }
    }


    public static function delete($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], '');
        }

        $id = $request['route'][0] ?? NULL;
        $name = trim($request['post']['name'] ?? NULL);

        if (!$id) return self::respond(['success' => false, 'msg' => $MSG->idnotgiven], '');
        $category = Category::getCategory(id: $id);
        if (!$category) return self::respond(['success' => false, 'msg' => $MSG->categorynotfound], '');

        if ($name && $category['name'] === $name) {
            $result = Category::delete($id);
            if ($result) return self::respond(['success' => true, 'msg' => $MSG->categorydeleted, 'id' => $id], '');
            return self::respond(['success' => false, 'msg' => $MSG->categorydeleteerror], '');
        }

        return self::respond(['success' => false, 'msg' => $MSG->categorydeleteconfirmationerror], '');
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
            $categories = Category::getCategory(mode: 'all');
            return include_once __DIR__ . '/../views/categories/index.php';
        }

        $category = Category::getCategory(id: $id);
        if (!$category) {
            $msg = $MSG->categorynotfound;
            $categories = Category::getCategory(mode: 'all');
            return include_once __DIR__ . '/../views/categories/index.php';
        }
        $msg = $request['get']['msg'] ?? NULL;
        include __DIR__ . '/../views/categories/confirm_delete.php';
    }


    private static function respond($data, $redirectUrl) {
        // اگر درخواست AJAX بود
        if (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        }

        // حالت عادی → redirect
        header("Location: $redirectUrl");
        exit();
    }

}
