<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../models/category.php';
require_once __DIR__ . '/../services/validator.php';

class Categories {
    public static function index($request) {
        global $MSG;
        if (!Auth::hasPermission(role: 'guide')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $id = Validator::positiveInt($request['route'][0] ?? null);
        if ($id) {
            $category = Category::getCategory($id);
            if ($category) {
                $msg = $request['get']['msg'] ?? null;
                $subtitle = 'دسته بندی: ' . $category['name'];
                return include_once __DIR__ . '/../views/categories/single.php';
            }
            $msg = $MSG->categorynotfound;
        }

        $categories = Category::getCategory(mode: 'all');
        $subtitle = 'دسته بندی ها';
        return include_once __DIR__ . '/../views/categories/index.php';
    }

    public static function create($request) {
        global $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }
        $msg = $request['get']['msg'] ?? null;
        return include_once __DIR__ . '/../views/categories/create.php';
    }

    public static function store($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . '/category');
        }

        $name = Validator::str($request['post']['name'] ?? null, 150);
        if ($name === '') {
            return self::respond(['success' => false, 'msg' => $MSG->categorynameemptyerror], $CFG->wwwroot . '/category/new');
        }

        $result = Category::create($name);
        if ($result) {
            return self::respond(['success' => true, 'msg' => $MSG->categorycreated, 'id' => $result], $CFG->wwwroot . '/category');
        }
        return self::respond(['success' => false, 'msg' => $MSG->categorycreateerror], $CFG->wwwroot . '/category/new');
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

        $category = Category::getCategory(id: $id);
        if (!$category) {
            $msg = $MSG->categorynotfound;
            return self::index(['get' => [], 'route' => []]);
        }

        $msg = $request['get']['msg'] ?? null;
        return include __DIR__ . '/../views/categories/edit.php';
    }

    public static function update($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . '/category');
        }

        $id = Validator::positiveInt($request['route'][0] ?? null);
        $name = Validator::str($request['post']['name'] ?? null, 150);
        if (!$id) {
            return self::respond(['success' => false, 'msg' => $MSG->idnotgiven], $CFG->wwwroot . '/category');
        }
        if ($name === '') {
            return self::respond(['success' => false, 'msg' => $MSG->categorynameemptyerror], $CFG->wwwroot . "/category/edit/$id");
        }

        $result = Category::update($id, $name);
        if ($result) {
            return self::respond(['success' => true, 'msg' => $MSG->categoryedited], $CFG->wwwroot . '/category');
        }
        return self::respond(['success' => false, 'msg' => $MSG->categoryediterror], $CFG->wwwroot . "/category/edit/$id");
    }

    public static function delete($request) {
        global $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], '');
        }

        $id = Validator::positiveInt($request['route'][0] ?? null);
        $name = Validator::str($request['post']['name'] ?? null, 150);
        if (!$id) {
            return self::respond(['success' => false, 'msg' => $MSG->idnotgiven], '');
        }

        $category = Category::getCategory(id: $id);
        if (!$category) {
            return self::respond(['success' => false, 'msg' => $MSG->categorynotfound], '');
        }
        if ($name !== (string)$category['name']) {
            return self::respond(['success' => false, 'msg' => $MSG->categorydeleteconfirmationerror], '');
        }

        $result = Category::delete($id);
        if ($result) {
            return self::respond(['success' => true, 'msg' => $MSG->categorydeleted, 'id' => $id], '');
        }
        return self::respond(['success' => false, 'msg' => $MSG->categorydeleteerror], '');
    }

    public static function confirmDelete($request) {
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

        $category = Category::getCategory(id: $id);
        if (!$category) {
            $msg = $MSG->categorynotfound;
            return self::index(['get' => [], 'route' => []]);
        }
        $msg = $request['get']['msg'] ?? null;
        include __DIR__ . '/../views/categories/confirm_delete.php';
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
