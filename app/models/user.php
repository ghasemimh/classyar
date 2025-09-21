<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class User {

    public static function getUserByMoodleId($mdlId) {
        global $CFG;

        // جدول users
        $row = DB::getRow("SELECT id, mdl_id, `suspend`, `role` FROM {$CFG->userstable} WHERE mdl_id = :mdl_id LIMIT 1",
            [':mdl_id' => $mdlId]);
        if ($row) return $row;

        // جدول teachers
        $row = DB::getRow("SELECT id, mdl_id, `suspend`, 'teacher' AS role FROM {$CFG->teacherstable} WHERE mdl_id = :mdl_id LIMIT 1",
            [':mdl_id' => $mdlId]);
        if ($row) return $row;

        // جدول students
        $row = DB::getRow("SELECT id, mdl_id, `suspend`, 'student' AS role FROM {$CFG->studentstable} WHERE mdl_id = :mdl_id LIMIT 1",
            [':mdl_id' => $mdlId]);
        if ($row) return $row;

        // هیچی پیدا نشد
        return null;
    }

}
