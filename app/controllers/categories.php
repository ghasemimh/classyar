<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../services/moodleAPI.php'; // جایی که کلاس Moodle رو نوشتی
require_once __DIR__ . '/auth.php'; // احراز هویت و دسترسی ها
require_once __DIR__ . '/../models/category.php';

class Categories {
    public static function index($response) {
        $id = $response['route'][0];
        var_dump($id);
        echo Category::getCategory($id)[0]['name'];
    }

    public static function create() {
        include __DIR__ . '/../views/categories/create.php';
        echo 'create';
    }

    public static function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            if ($name !== '') {
                Category::create(['name' => $name]);
            }
            header("Location: /categories");
            exit;
        }
    }

    public static function edit($id) {
        $category = Category::find($id);
        include __DIR__ . '/../views/categories/edit.php';
        echo 'edit';
    }

    public static function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            if ($name !== '') {
                Category::update($id, ['name' => $name]);
            }
            header("Location: /categories");
            exit;
        }
    }

    public static function delete($id) {
        Category::delete($id);
        header("Location: /categories");
        exit;
    }
}
