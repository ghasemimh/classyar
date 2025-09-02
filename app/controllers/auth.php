<?php
defined('CLASSYAR_APP') || die('No direct access allowed!');

require_once __DIR__ . '/../services/moodleAPI.php'; // جایی که کلاس Moodle رو نوشتی
require_once __DIR__ . '/../models/user.php';   // مدل یوزر
require_once __DIR__ . '/../models/student.php';   // مدل دانش آموز


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

        $mdlSession = $_SESSION; // اطلاعات سشن مودل
        session_write_close();

        // 2. حالا سشن برنامه ما رو آماده کن
        session_name($CFG->sessionname);
        session_save_path($CFG->sessionpath);
        session_start();

        if (empty($_SESSION['USER']) || $_SESSION['USER']->mdl_id != $mdlSession['USER']->id || true) {
            session_write_close();
            // سشن پروژه وجود نداره یا ناقصه → بازسازی کن
            self::buildSession($mdlSession);
        }
        
    }


    private static function buildSession($mdlSession) {
        global $CFG;
        global $MDL;

        session_name($CFG->sessionname);
        session_save_path($CFG->sessionpath);
        session_start();
        
        $mdlUserId = $mdlSession['USER']->id;
        // اطلاعات مودل رو از API بگیر
        $mdlUser = Moodle::getUser('id', $mdlUserId);

        // اگر کاربر غیرفعال شده باشد یا اطلاعات کاربر پیدا نشود
        if($mdlUser['suspended'] == 1 || $mdlUser['confirmed'] == 0 || $mdlUser == NULL) {
            session_write_close();
            header('Location: ' . $MDL->wwwroot);
            exit();
        }
        
        
        // اطلاعات کاربر در دیتابیس خودمون
        // اگر کاربر وجود نداشت، به عنوان دانش آموز ثبت نام می شود
        if (!($user = User::getUserByMoodleId($mdlUserId))) {
            // ایجاد دانش آموز
            Student::createStudent($mdlUserId);
            // گرفتن مجدد اطلاعات
            $user = User::getUserByMoodleId($mdlUserId);
        }
        
        
        // ریختن اطلاعات در سشن
        $_SESSION['USER'] = new stdClass();

        $_SESSION['USER']->role         = $user['role']; // student, teacher, guide, admin
        $_SESSION['USER']->id           = $user['id'];
        $_SESSION['USER']->mdl_id       = $user['mdl_id']; // moodle id
        $_SESSION['USER']->timecreated  = $mdlSession['USER']->timecreated;
        $_SESSION['USER']->email        = $mdlSession['USER']->email ?? $mdlUser['email'];
        $_SESSION['USER']->username     = $mdlSession['USER']->username ?? $mdlUser['username'];
        $_SESSION['USER']->firstname    = $mdlSession['USER']->firstname ?? $mdlUser['firstname'];
        $_SESSION['USER']->lastname     = $mdlSession['USER']->lastname ?? $mdlUser['lastname'];
        $_SESSION['USER']->fullname     = $mdlSession['USER']->fullname ?? $mdlUser['fullname'];
        $_SESSION['USER']->idnumber     = $mdlSession['USER']->idnumber ?? $mdlUser['idnumber'];
        $_SESSION['USER']->profileimage = $mdlUser['profileimageurl'];


        if ($user['role'] === 'admin' || $user['role'] === 'guide') {

        }

        if ($user['role'] === 'teacher') {
            $teacher = Teacher::getTeacher($user['id']);
            $_SESSION['USER']->times = $teacher['times'];
            $_SESSION['USER']->phone = $mdlSession['USER']->phone1 ?? $mdlSession['USER']->phone2;
        }

        if ($user['role'] === 'student') {
            $_SESSION['USER']->mphone = $mdlSession['USER']->phone1; // mother phone
            $_SESSION['USER']->fphone = $mdlSession['USER']->phone2; // father phone
        }

    }

}