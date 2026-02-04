<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../controllers/users.php';
require_once __DIR__ . '/auth.php';

class My {
    public static function index($request) {
        global $CFG, $MSG;

        if (Auth::checkRole(role: 'admin')) {
            Users::showUnregisteredMdlUsers($request);
        }

        if (Auth::checkRole(role: 'student')) {
            return include_once __DIR__ . '/../views/my/student.php';
        }
    }
}