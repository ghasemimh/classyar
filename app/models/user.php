<?php
defined('CLASSYAR_APP') || die('No direct access allowed!');

class User {

    public static function getUserByMoodleId($mdlId) {
        global $CFG;

        // جدول users
        $row = DB::getRow("SELECT id, mdl_id, `role` FROM {$CFG->userstable} WHERE mdl_id = :mdl_id LIMIT 1",
            [':mdl_id' => $mdlId]);
        if ($row) return $row;

        // جدول teachers
        $row = DB::getRow("SELECT id, mdl_id, 'teacher' AS role FROM {$CFG->teacherstable} WHERE mdl_id = :mdl_id LIMIT 1",
            [':mdl_id' => $mdlId]);
        if ($row) return $row;

        // جدول students
        $row = DB::getRow("SELECT id, mdl_id, 'student' AS role FROM {$CFG->studentstable} WHERE mdl_id = :mdl_id LIMIT 1",
            [':mdl_id' => $mdlId]);
        if ($row) return $row;

        // هیچی پیدا نشد
        return null;
    }

}
