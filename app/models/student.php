<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../models/user.php';       // مدل یوزر
require_once __DIR__ . '/../models/setting.php';    // مدل تنظیمات
require_once __DIR__ . '/../models/term.php';       // مدل ترم


class Student {
    public static function createStudent($mdl_id, $cohort = null, $english = null, $quantile = null, $msg = null, $suspend = 0) {
        global $CFG;

        $user_id = User::createUser($mdl_id, suspend: $suspend) ?? NULL;

        if (!$user_id) {
            return false;
        }

        if (!$cohort) {
            $cohort = date('Y') - $CFG->yearofestablishmentgregorian;
        }
        if (!$english) {
            $english = $CFG->defaultenglish;
        }
        if (!$quantile) {
            $quantile = Setting::getSetting('Quantiles Count');
        }

        $id = DB::execute("
            INSERT INTO {$CFG->studentstable} (user_id, cohort, english, quantile, msg)
            VALUES (:user_id, :cohort, :english, :quantile, :msg)
        ", [
            ':user_id' => $user_id,
            ':cohort' => $cohort,
            ':english' => $english,
            ':quantile' => $quantile,
            ':msg' => $msg
        ]);
        
        return $id ?? false;
    }


    public static function getOpentime($std_id = NULL, $mode = 'auto') {
        global $CFG;
        if ($mode === 'last') {
            $term = Term::getTerm(mode: 'active');
            $opentime = $term['first_open_time'];
            $opentime = $opentime + (intval(Setting::getSetting('Quantiles Count')) * intval(Setting::getSetting('Quantiles Duration')));
            return $opentime;
        }
    }

    public static function getClosetime($std_id = NULL, $mode = 'auto') {
        global $CFG;
        if ($mode === 'last') {
            $term = Term::getTerm(mode: 'active');
            $closetime = $term['close_time'];
            return intval($closetime);
        }
    }
    public static function getStudent($id = NULL, $mdl_id = NULL) {
        global $CFG;
        return false;
    }
}