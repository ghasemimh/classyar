<?php
defined('CLASSYAR_APP') || die('No direct access allowed!');

class User {

    public static function getUserByMoodleId($mdlId) {
        global $CFG;

        try {
            $dsn = "{$CFG->dbtype}:host={$CFG->dbhost};dbname={$CFG->dbname};charset={$CFG->dbcharset}";
            $pdo = new PDO($dsn, $CFG->dbuser, $CFG->dbpass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // جدول users
            $stmt = $pdo->prepare("SELECT id, mdl_id, `role` FROM users WHERE mdl_id = :mdl_id LIMIT 1");
            $stmt->execute(['mdl_id' => $mdlId]);
            $row = $stmt->fetch();
            if ($row) return $row;

            // جدول teachers
            $stmt = $pdo->prepare("SELECT id, mdl_id, 'teacher' AS role FROM teachers WHERE mdl_id = :mdl_id LIMIT 1");
            $stmt->execute(['mdl_id' => $mdlId]);
            $row = $stmt->fetch();
            if ($row) return $row;

            // جدول students
            $stmt = $pdo->prepare("SELECT id, mdl_id, 'student' AS role FROM students WHERE mdl_id = :mdl_id LIMIT 1");
            $stmt->execute(['mdl_id' => $mdlId]);
            $row = $stmt->fetch();
            if ($row) return $row;

            // هیچی پیدا نشد
            return null;

        } catch (PDOException $e) {
            error_log("DB Error in User::getUserByMoodleId - " . $e->getMessage());
            return null;
        }
    }
}
