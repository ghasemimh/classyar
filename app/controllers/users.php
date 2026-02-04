<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../services/moodleAPI.php';
require_once __DIR__ . '/../models/student.php';
require_once __DIR__ . '/../models/teacher.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/auth.php';

class Users {

    public static function showUnregisteredMdlUsers($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $newUsers = self::getUnregisteredMdlUsers();
        $msg = $request['get']['msg'] ?? NULL;
        $subtitle = 'کاربران ثبت‌نام نشده';
        return include_once __DIR__ . '/../views/users/unregistered.php';
    }

    public static function getUnregisteredMdlUsers () {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            http_response_code(403);
            return;
        }

        // همه کاربران مودل
        $mdlUsers = Moodle::getUser(mode: 'all');

        // کاربران داخلی
        $existing = User::getUser(mode: 'all');
        $existingIds = array_column($existing, 'mdl_id');

        // فیلتر → فقط کاربرانی که هنوز ثبت نشده‌اند
        $newUsers = array_filter($mdlUsers, function($u) use ($existingIds) {
            return !in_array($u['id'], $existingIds);
        });
        return $newUsers;
    }

    public static function addUser($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $mdl_id = $request['route']['mdl_id'] ?? NULL;
        if (!$mdl_id) {
            return include_once __DIR__ . '/../views/errors/400.php';
        }
        $role = $request['route']['role'] ?? 'student';

        $mdlUser = Moodle::getUser('id', $mdl_id);
        if (!$mdlUser) {
            $msg = $MSG->usernotfound;
            return include_once __DIR__ . '/../views/errors/400.php';
        }
        $user = User::getUserByMoodleId($mdl_id);
        if ($user) {
            $msg = $MSG->useralreadyexists;
            $newUsers = self::getUnregisteredMdlUsers();
            $subtitle = 'کاربران ثبت‌نام نشده';
            return include_once __DIR__ . '/../views/users/unregistered.php';
        }

        

        if ($role == 'admin' || $role == 'guide') {
            $user = User::createUser($mdl_id, $role);
        }

        if ($role == 'teacher') {
            $user = Teacher::createTeacher($mdl_id);
        }


        if ($role == 'student') {
            $user = Student::createStudent($mdl_id);
        }

        if (!$user) {
            $msg = $MSG->usercreateerror;
            $newUsers = self::getUnregisteredMdlUsers();
            $subtitle = 'کاربران ثبت‌نام نشده';
            return include_once __DIR__ . '/../views/users/unregistered.php';
        }

        
        $msg = $MSG->usercreated;
        $newUsers = self::getUnregisteredMdlUsers();
        $subtitle = 'کاربران ثبت‌نام نشده';
        return include_once __DIR__ . '/../views/users/unregistered.php';
    }

}
