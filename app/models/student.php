<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/setting.php';
require_once __DIR__ . '/../models/term.php';
require_once __DIR__ . '/../services/jalali/CalendarUtils.php';

use Morilog\Jalali\CalendarUtils;

class Student {
    private static $columns = null;
    private static $keyColumn = null;

    private static function getColumns() {
        if (self::$columns !== null) {
            return self::$columns;
        }
        global $CFG;
        $cols = DB::getAll("SHOW COLUMNS FROM {$CFG->studentstable}");
        self::$columns = array_map(fn($c) => $c['Field'], $cols ?: []);
        return self::$columns;
    }

    private static function getKeyColumn() {
        if (self::$keyColumn !== null) {
            return self::$keyColumn;
        }
        $cols = self::getColumns();
        self::$keyColumn = in_array('user_id', $cols, true) ? 'user_id' : 'mdl_id';
        return self::$keyColumn;
    }

    public static function createStudent($mdl_id, $cohort = null, $english = null, $quantile = null, $msg = null, $suspend = 0) {
        global $CFG;

        $keyColumn = self::getKeyColumn();
        $columns = self::getColumns();

        $user_id = User::createUser($mdl_id, suspend: $suspend) ?? NULL;
        if (!$user_id && $keyColumn === 'user_id') {
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

        $data = [];
        if ($keyColumn === 'user_id') {
            $data['user_id'] = $user_id;
        } else {
            $data['mdl_id'] = $mdl_id;
        }
        if (in_array('cohort', $columns, true)) {
            $data['cohort'] = $cohort;
        }
        if (in_array('english', $columns, true)) {
            $data['english'] = $english;
        }
        if (in_array('quantile', $columns, true)) {
            $data['quantile'] = $quantile;
        }
        if (in_array('msg', $columns, true)) {
            $data['msg'] = $msg;
        }
        if (in_array('suspend', $columns, true)) {
            $data['suspend'] = $suspend;
        }
        if (in_array('deleted', $columns, true)) {
            $data['deleted'] = 0;
        }

        if (empty($data)) {
            return false;
        }

        return DB::insert($CFG->studentstable, $data) ?? false;
    }

    public static function getStudent($id = NULL, $mdl_id = NULL, $user_id = NULL) {
        global $CFG;
        $keyColumn = self::getKeyColumn();

        if ($id) {
            $id = (int)$id;
            return DB::getRow("
                SELECT * FROM {$CFG->studentstable}
                WHERE `id` = :id
                LIMIT 1
            ", [':id' => $id]);
        }

        if ($keyColumn === 'user_id' && $user_id) {
            $user_id = (int)$user_id;
            return DB::getRow("
                SELECT * FROM {$CFG->studentstable}
                WHERE `user_id` = :user_id
                LIMIT 1
            ", [':user_id' => $user_id]);
        }

        if ($keyColumn === 'mdl_id' && $mdl_id) {
            $mdl_id = (int)$mdl_id;
            return DB::getRow("
                SELECT * FROM {$CFG->studentstable}
                WHERE `mdl_id` = :mdl_id
                LIMIT 1
            ", [':mdl_id' => $mdl_id]);
        }

        return null;
    }

    public static function getAll($deleted = 0) {
        global $CFG;
        $columns = self::getColumns();
        $keyColumn = self::getKeyColumn();
        $where = [];
        if (in_array('deleted', $columns, true)) {
            $where[] = "`deleted` = " . (int)$deleted;
        }
        if (in_array('suspend', $columns, true)) {
            $where[] = "`suspend` = 0";
        }
        $whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        $students = DB::getAll("
            SELECT * FROM {$CFG->studentstable}
            $whereSql
            ORDER BY `id` DESC
        ");

        if ($keyColumn === 'user_id') {
            $userIds = array_values(array_filter(array_column($students, 'user_id')));
            if ($userIds) {
                $in = implode(',', array_map('intval', $userIds));
                $users = DB::getAll("SELECT id, mdl_id FROM {$CFG->userstable} WHERE id IN ($in)");
                $map = [];
                foreach ($users as $u) {
                    $map[$u['id']] = $u['mdl_id'];
                }
                foreach ($students as &$s) {
                    $s['mdl_id'] = $map[$s['user_id']] ?? null;
                }
                unset($s);
            }
        }

        return $students;
    }

    public static function updateQuantile($studentId, $quantile) {
        global $CFG;
        $columns = self::getColumns();
        if (!in_array('quantile', $columns, true)) {
            return false;
        }
        $studentId = (int)$studentId;
        return DB::update($CFG->studentstable, [
            'quantile' => $quantile
        ], "`id` = $studentId");
    }

    public static function updateOpenClose($studentId, $openTime, $closeTime) {
        global $CFG;
        $columns = self::getColumns();
        if (!in_array('opentime', $columns, true) || !in_array('closetime', $columns, true)) {
            return false;
        }
        $studentId = (int)$studentId;
        return DB::update($CFG->studentstable, [
            'opentime' => $openTime,
            'closetime' => $closeTime
        ], "`id` = $studentId");
    }

    public static function getComputedOpenClose($student, $term) {
        $firstOpen = isset($term['first_open_time']) ? (int)$term['first_open_time'] : 0;
        $closeTime = isset($term['close_time']) ? (int)$term['close_time'] : 0;
        $quantile = isset($student['quantile']) ? (int)$student['quantile'] : 1;
        $quantileCount = (int)Setting::getSetting('Quantiles Count');
        $quantileDuration = (int)Setting::getSetting('Quantiles Duration');

        if ($quantileCount <= 0) {
            $quantileCount = 1;
        }
        if ($quantile <= 0) {
            $quantile = 1;
        }
        if ($quantile > $quantileCount) {
            $quantile = $quantileCount;
        }

        $openTime = $firstOpen ? ($firstOpen + ($quantile - 1) * $quantileDuration) : 0;

        $columns = self::getColumns();
        if (in_array('opentime', $columns, true) && in_array('closetime', $columns, true)) {
            $overrideOpen = isset($student['opentime']) ? (int)$student['opentime'] : 0;
            $overrideClose = isset($student['closetime']) ? (int)$student['closetime'] : 0;
            if ($overrideOpen) {
                $openTime = $overrideOpen;
            }
            if ($overrideClose) {
                $closeTime = $overrideClose;
            }
        }

        return [$openTime, $closeTime];
    }

    public static function getGrade($student, $idnumber = null) {
        $cohort = isset($student['cohort']) ? (int)$student['cohort'] : 0;
        if ($cohort <= 0 && !empty($idnumber)) {
            $cohort = self::extractCohortFromIdNumber($idnumber);
        }
        if ($cohort <= 0) {
            return 0;
        }

        $currentSeventhCohort = self::getCurrentSeventhCohort();
        if ($currentSeventhCohort <= 0) {
            return 0;
        }

        $grade = 7 + ($currentSeventhCohort - $cohort);
        if ($grade < 7 || $grade > 9) {
            return 0;
        }
        return (int)$grade;
    }

    public static function getSeatColumn($student, $idnumber = null) {
        $grade = self::getGrade($student, $idnumber);
        if (!in_array($grade, [7, 8, 9], true)) {
            return 'seat7';
        }
        return 'seat' . $grade;
    }

    private static function extractCohortFromIdNumber($idnumber) {
        $idnumber = (string)$idnumber;
        if ($idnumber === '') {
            return 0;
        }
        if (preg_match('/(\d{2})/', $idnumber, $m)) {
            return (int)$m[1];
        }
        return 0;
    }

    private static function getCurrentSeventhCohort() {
        global $CFG;
        $now = time();
        $gy = (int)date('Y', $now);
        $gm = (int)date('n', $now);
        $gd = (int)date('j', $now);
        $j = CalendarUtils::toJalali($gy, $gm, $gd);
        $jYear = (int)($j[0] ?? 0);
        $jMonth = (int)($j[1] ?? 0);
        if ($jYear <= 0) {
            return 0;
        }

        $cohort = $jYear - (int)$CFG->yearofestablishmentiran;
        if ($jMonth > 0 && $jMonth < 4) {
            $cohort -= 1;
        }
        return $cohort > 0 ? $cohort : 0;
    }
}
