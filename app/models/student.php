<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../models/user.php';       // مدل یوزر
require_once __DIR__ . '/../models/setting.php';    // مدل تنظیمات
require_once __DIR__ . '/../models/term.php';       // مدل ترم


class Student {
    public static function createStudent($mdl_id, $cohort = null, $is_alumnus = 0, $english = null, $opentime = null, $closetime = null, $msg = null, $suspend = 0) {
        global $CFG;

        $user_id = User::createUser($mdl_id) ?? NULL;

        if (!$user_id) {
            return false;
        }

        $cohort = date('Y') - $CFG->yearofestablishmentgregorian;
        $english = $CFG->defaultenglish;
        $opentime = self::getOpentime(null, 'last');
        $closetime = self::getClosetime(null, 'last');

        $id = DB::execute("
            INSERT INTO {$CFG->studentstable} (user_id, cohort, is_alumnus, english, opentime, closetime, msg)
            VALUES (:user_id, :cohort, :is_alumnus, :english, :opentime, :closetime, :msg)
        ", [
            ':user_id' => $user_id,
            ':cohort' => $cohort,
            ':is_alumnus' => $is_alumnus,
            ':english' => $english,
            ':opentime' => $opentime,
            ':closetime' => $closetime,
            ':msg' => $msg
        ]);
        
        return $id ?? false;
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
    public static function getStudent($id = NULL, $mdl_id = NULL) {
        global $CFG;
        return false;
    }
}