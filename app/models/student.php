<?php
defined('CLASSYAR_APP') || die('No direct access allowed!');

require_once __DIR__ . '/../models/setting.php';   // مدل تنظیمات
require_once __DIR__ . '/../models/term.php';   // مدل ترم


class Student {
    public static function createStudent($mdl_id, $cohort = NULL, $is_alumnus = 0, $english = NULL, $opentime = NULL, $closetime = NULL, $msg = NULL, $suspend = 0) {
        global $CFG;
        $cohort = date('Y') - $CFG->yearofestablishmentgregorian;
        $english = $CFG->defaultenglish;
        $opentime = self::getOpentime(Null, 'last');
        $closetime = self::getClosetime(Null, 'last');



        // Create the student in the database
        $dsn = "{$CFG->dbtype}:host={$CFG->dbhost};dbname={$CFG->dbname};charset={$CFG->dbcharset}";
        $pdo = new PDO($dsn, $CFG->dbuser, $CFG->dbpass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $stmt = $pdo->prepare("INSERT INTO {$CFG->studentstable} (mdl_id, cohort, is_alumnus, english, opentime, closetime, msg, `suspend`) VALUES (:mdl_id, :cohort, :is_alumnus, :english, :opentime, :closetime, :msg, :suspend)");
        $stmt->execute([
            ':mdl_id' => $mdl_id,
            ':cohort' => $cohort,
            ':is_alumnus' => $is_alumnus,
            ':english' => $english,
            ':opentime' => $opentime,
            ':closetime' => $closetime,
            ':msg' => $msg,
            ':suspend' => $suspend
        ]);

        return $pdo->lastInsertId();
    }

    public static function getOpentime($std_id = NULL, $mode = 'auto') {
        global $CFG;
        if ($mode === 'last') {
            $term = Term::getTerm('last');
            $opentime = $term['first_open_time'];
            $opentime = $opentime + (intval(Setting::getSetting('Quantiles Count')) * intval(Setting::getSetting('Quantiles Duration')));
            return $opentime;
        }
    }

    public static function getClosetime($std_id = NULL, $mode = 'auto') {
        global $CFG;
        if ($mode === 'last') {
            $term = Term::getTerm('last');
            $closetime = $term['close_time'];
            return intval($closetime);
        }
    }
}