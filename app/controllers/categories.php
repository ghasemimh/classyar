<?php
defined('CLASSYAR_APP') || die('No direct access allowed!');

// require_once __DIR__ . '/../services/moodleAPI.php'; // جایی که کلاس Moodle رو نوشتی
// require_once __DIR__ . 'auth.php' // احراز هویت و دسترسی ها


class Categories {
    public static function index($id) {
        echo 'show';
        var_dump($id);
    }
}