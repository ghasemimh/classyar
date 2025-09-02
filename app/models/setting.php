<?php
defined('CLASSYAR_APP') || die('No direct access allowed!');

class Setting {
    public static function getSetting($name) {
        global $CFG;
        try {
            $dsn = "{$CFG->dbtype}:host={$CFG->dbhost};dbname={$CFG->dbname};charset={$CFG->dbcharset}";
            $pdo = new PDO($dsn, $CFG->dbuser, $CFG->dbpass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            $stmt = $pdo->prepare("SELECT `value` FROM {$CFG->settingstable} WHERE name = :name LIMIT 1");
            $stmt->execute(['name' => $name]);
            $row = $stmt->fetch();
            return $row ? $row['value'] : null;

        } catch (PDOException $e) {
            error_log("DB Error in Setting::getSetting - " . $e->getMessage());
            return null;
        }
    }
}