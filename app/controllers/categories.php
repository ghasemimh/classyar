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
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        if ($post) {
            $name = trim($post['name'] ?? NULL);
            if ($name) {
                $result = Category::create($name);
                if ($result) {
                    header("Location: " . $CFG->wwwroot . "/category?msg=" . urlencode($MSG->categorycreated));
                    exit();
                }
                header("Location: " . $CFG->wwwroot . "/category/new?msg=" . urlencode($MSG->categorycreateerror));
                exit();
            }
            header("Location: " . $CFG->wwwroot . "/category/new?msg=" . urlencode($MSG->categorynameerror));
            exit();
        }
        header("Location: " . $CFG->wwwroot . "/category?msg=" . urlencode($MSG->badrequest));
        exit();
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
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }
        $id = $request['route'][0] ?? NULL;
        $name = trim($request['post']['name'] ?? NULL);
        if ($id && $name) {
            $result = Category::update($id, $name);
            if ($result) {
                header("Location: " . $CFG->wwwroot . "/category?msg=" . urlencode($MSG->categoryedited));
                exit();
            }

            header("Location: " . $CFG->wwwroot . "/category/edit/$id?msg=" . urlencode($MSG->categoryediterror));
            exit();
        }
        if (!$id) {
            $msg = $MSG->idnotgiven;
            header("Location: " . $CFG->wwwroot . "/category?msg=" . urlencode($msg));
            exit();
        }
        if (!$name) {
            $msg = $MSG->categorynameerror;
            header("Location: " . $CFG->wwwroot . "/category/edit/$id?msg=" . urlencode($msg));
            exit();
        }
    }

    public static function delete($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }


        $id = $request['route'][0] ?? NULL;
        $name = trim($request['post']['name'] ?? NULL);
        if ($id) {
            $category = Category::getCategory(id: $id);
            if ($category) {
                if ($name && $category['name'] == $name) { // confirmation
                    $result = Category::delete($id);
                    if ($result) {
                        header("Location: " . $CFG->wwwroot . "/category?msg=" . urlencode($MSG->categorydeleted));
                        exit();
                    }
                    header("Location: " . $CFG->wwwroot . "/category/delete/$id?msg=" . urlencode($MSG->categorydeleteerror));
                    exit();
                }
                header("Location: " . $CFG->wwwroot . "/category/delete/$id?msg=" . urlencode($MSG->deleteconfirmationerror));
                exit();
            }
            header("Location: " . $CFG->wwwroot . "/category?msg=" . urlencode($MSG->categorynotfound));
            exit();

        }
        header("Location: " . $CFG->wwwroot . "/category?msg=" . urlencode($MSG->idnotgiven));
        exit();
        
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
}
