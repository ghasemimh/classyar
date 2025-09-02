<?php
defined('CLASSYAR_APP') || die('No direct access allowed!');

require_once __DIR__ . '/../services/moodleAPI.php'; // جایی که کلاس Moodle رو نوشتی
require_once __DIR__ . '/../models/user.php';   // مدل یوزر خودت
require_once __DIR__ . '/../models/student.php';   // مدل دانش آموز خودت


class Auth {
    
    public static function auth() {
        global $CFG, $MDL;

        // 1. اول بررسی کنیم کاربر توی مودل لاگین کرده یا نه
        session_name($MDL->sessionname);
        session_save_path($MDL->sessionpath);
        session_start();

        if (empty($_SESSION['USER']->username)) {
            // کاربر لاگین نیست → برگرد به لاگین مودل
            session_write_close();
            header('Location: ' . $MDL->wwwroot);
            exit();
        }

        $mdlUserId = $_SESSION['USER']->id;
        session_write_close();

        // 2. حالا سشن برنامه ما رو آماده کن
        session_name($CFG->sessionname);
        session_save_path($CFG->sessionpath);
        session_start();

        if (empty($_SESSION['USER']) || $_SESSION['USER']->mdl_id != $mdlUserId || true) {
            session_write_close();
            // سشن پروژه وجود نداره یا ناقصه → بازسازی کن
            self::buildSession($mdlUserId);
        }
    }


    private static function buildSession($mdlUserId) {
    global $CFG;
    global $MDL;

    session_name($CFG->sessionname);
    session_save_path($CFG->sessionpath);
    session_start();

    // اطلاعات مودل رو از API بگیر
    $mdlUser = Moodle::getUser('id', 2);
    

    // 
    if($mdlUser['suspended'] == 1 || $mdlUser == NULL) {
        session_write_close();
        header('Location: ' . $MDL->wwwroot);
        exit();
    }
    
    // اطلاعات کاربر در دیتابیس خودمون
    // اگر کاربر وجود نداشت، به عنوان دانش آموز ثبت نام می شود
    if (!$user = User::getUserByMoodleId($mdlUserId)) {
        // Student::createStudent();
        var_dump($user);
    }

    // حالا سشن نهایی
    $_SESSION['USER'] = new stdClass();
    // $_SESSION['USER']->id      = $user['id'];                 // id در اپ خودت
    $_SESSION['USER']->mdl_id  = $mdlUser['id'];              // id در مودل
    // $_SESSION['USER']->role    = $user['role'];               // student, teacher, guide, admin
    $_SESSION['USER']->name    = $mdlUser['fullname'] ?? $user['name'];
    $_SESSION['USER']->email   = $mdlUser['email'] ?? $user['email'];
}


}