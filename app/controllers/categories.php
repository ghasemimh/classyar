<?php
global $CFG;
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../services/moodleAPI.php'; // جایی که کلاس Moodle رو نوشتی
require_once __DIR__ . 'auth.php'; // احراز هویت و دسترسی ها


class Categories {
    public static function index($response) {
        echo 'show';
        var_dump($response);
    }
}