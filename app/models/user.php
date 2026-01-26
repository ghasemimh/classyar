<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class User {

    public static function getUserByMoodleId($mdlId) {
        global $CFG;

        // جدول users
        $row = DB::getRow("SELECT id, mdl_id, `suspend`, `role` FROM {$CFG->userstable} WHERE mdl_id = :mdl_id LIMIT 1",
            [':mdl_id' => $mdlId]);
        if ($row) return $row;

        // هیچی پیدا نشد
        return null;
    }


    public static function createUser($mdlId, $role = 'student', $suspend = 0) {
        global $CFG;
        
        $id = DB::execute("
            INSERT INTO {$CFG->userstable} (mdl_id, role, suspend)
            VALUES (:mdl_id, :role, :suspend)
        ", [
            ':mdl_id' => $mdlId,
            ':role' => $role,
            ':suspend' => $suspend
        ]);

        return $id ?? false;
    }


    public static function getUser($id = NULL, $role = NULL, $mode = 'auto', $suspend = 0) {
        global $CFG;

        if ($id) {
            try {
                $id = (int)$id;
            } catch (Exception $e) {
                return NULL;
            }

            $user = DB::getRow("
                SELECT * FROM {$CFG->usersstable} 
                WHERE `id` = $id
                LIMIT 1
            ");

            return $user ?? NULL;
        }

        if ($mode === 'all') {
            $users = DB::getAll("
                SELECT * FROM {$CFG->userstable} 
                ORDER BY `id` DESC
            ");

            return $users;
        }
    }


}
