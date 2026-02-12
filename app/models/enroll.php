<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/setting.php';
require_once __DIR__ . '/term.php';
require_once __DIR__ . '/student.php';

class Enroll {
    public static function getRequiredCategoryIds() {
        $raw = Setting::getSetting('Necessary Categories');
        if (!$raw) {
            return [];
        }
        $parts = preg_split('/\s*,\s*/', trim($raw));
        $ids = [];
        foreach ($parts as $p) {
            if ($p === '') continue;
            $v = (int)$p;
            if ($v > 0) $ids[] = $v;
        }
        return array_values(array_unique($ids));
    }

    public static function getTimes() {
        $timesInfo = json_decode(Setting::getSetting('Times Information'), true);
        if (!empty($timesInfo['times']) && is_array($timesInfo['times'])) {
            return $timesInfo['times'];
        }

        global $CFG;
        $rows = DB::getAll("SELECT `time` FROM {$CFG->classestable} WHERE `deleted` = 0");
        $ids = [];
        foreach ($rows as $r) {
            foreach (self::splitTimes($r['time'] ?? '') as $t) {
                $ids[$t] = true;
            }
        }
        $out = [];
        $keys = array_keys($ids);
        sort($keys, SORT_NUMERIC);
        foreach ($keys as $k) {
            $out[] = ['id' => (string)$k, 'label' => 'زنگ ' . $k];
        }
        return $out;
    }

    public static function splitTimes($timeStr) {
        $timeStr = trim((string)$timeStr);
        if ($timeStr === '') return [];
        $parts = array_map('trim', explode(',', $timeStr));
        $parts = array_filter($parts, fn($v) => $v !== '');
        return array_values(array_unique($parts));
    }

    public static function getStudentByUser($userSession) {
        $student = null;
        if (!empty($userSession->id)) {
            $student = Student::getStudent(user_id: (int)$userSession->id);
        }
        if (!$student && !empty($userSession->mdl_id)) {
            $student = Student::getStudent(mdl_id: (int)$userSession->mdl_id);
        }
        return $student;
    }

    public static function getSeatColumnForStudent($student, $userSession = null) {
        $idnumber = $userSession->idnumber ?? null;
        $seatCol = Student::getSeatColumn($student, $idnumber);
        if (!preg_match('/^seat[789]$/', $seatCol)) {
            $seatCol = 'seat7';
        }
        return $seatCol;
    }

    public static function getTermClassesForStudent($student, $termId, $timeId = null, $userSession = null) {
        global $CFG;

        $params = [':term_id' => (int)$termId];
        $timeSql = '';
        if ($timeId !== null && $timeId !== '') {
            $timeSql = " AND FIND_IN_SET(:time_id, c.`time`) > 0 ";
            $params[':time_id'] = (string)$timeId;
        }

        $sql = "
            SELECT 
                c.*,
                (COALESCE(c.$seatCol, 0) - COALESCE(ec.taken, 0)) AS seat_left,
                cr.name AS course_name,
                cr.crsid AS course_crsid,
                cr.category_id,
                cat.name AS category_name,
                r.name AS room_name,
                pr.req_text AS prerequisite_text,
                t.user_id AS teacher_user_id,
                u.mdl_id AS teacher_mdl_id
            FROM {$CFG->classestable} c
            JOIN {$CFG->coursestable} cr ON cr.id = c.course_id AND cr.deleted = 0
            LEFT JOIN {$CFG->categoriestable} cat ON cat.id = cr.category_id AND cat.deleted = 0
            LEFT JOIN {$CFG->roomstable} r ON r.id = c.room_id AND r.deleted = 0
            LEFT JOIN (
                SELECT p.class_id, GROUP_CONCAT(COALESCE(pc.name, p.alternative_text) SEPARATOR ' | ') AS req_text
                FROM {$CFG->prerequisitestable} p
                LEFT JOIN {$CFG->coursestable} pc ON pc.id = p.course_id AND pc.deleted = 0
                WHERE p.deleted = 0
                GROUP BY p.class_id
            ) pr ON pr.class_id = c.id
            LEFT JOIN {$CFG->teacherstable} t ON t.id = c.teacher_id
            LEFT JOIN {$CFG->userstable} u ON u.id = t.user_id
            LEFT JOIN (
                SELECT e.class_id, COUNT(*) AS taken
                FROM {$CFG->enrollstable} e
                WHERE e.deleted = 0
                GROUP BY e.class_id
            ) ec ON ec.class_id = c.id
            WHERE c.deleted = 0
              AND c.term_id = :term_id
              $timeSql
              AND (COALESCE(c.$seatCol, 0) - COALESCE(ec.taken, 0)) > 0
            ORDER BY cr.name ASC
        ";
        return DB::getAll($sql, $params);
    }

    private static function getClassRemainingSeat($classId, $termId, $seatCol, $forUpdate = false) {
        global $CFG;
        $lock = $forUpdate ? " FOR UPDATE" : "";
        $row = DB::getRow("
            SELECT (COALESCE(c.$seatCol, 0) - COALESCE(ec.taken, 0)) AS seat_left
            FROM {$CFG->classestable} c
            LEFT JOIN (
                SELECT e.class_id, COUNT(*) AS taken
                FROM {$CFG->enrollstable} e
                WHERE e.deleted = 0
                GROUP BY e.class_id
            ) ec ON ec.class_id = c.id
            WHERE c.id = :class_id
              AND c.term_id = :term_id
              AND c.deleted = 0
            LIMIT 1
            $lock
        ", [
            ':class_id' => (int)$classId,
            ':term_id' => (int)$termId
        ]);

        return (int)($row['seat_left'] ?? 0);
    }

    public static function getProgram($studentId, $termId) {
        global $CFG;
        return DB::getAll("
            SELECT
                e.id AS enroll_id,
                e.class_id,
                e.timestamp,
                c.time,
                c.price,
                cr.id AS course_id,
                cr.name AS course_name,
                cr.category_id,
                cat.name AS category_name,
                r.name AS room_name,
                t.user_id AS teacher_user_id,
                u.mdl_id AS teacher_mdl_id
            FROM {$CFG->enrollstable} e
            JOIN {$CFG->classestable} c ON c.id = e.class_id AND c.deleted = 0
            JOIN {$CFG->coursestable} cr ON cr.id = c.course_id AND cr.deleted = 0
            LEFT JOIN {$CFG->categoriestable} cat ON cat.id = cr.category_id AND cat.deleted = 0
            LEFT JOIN {$CFG->roomstable} r ON r.id = c.room_id AND r.deleted = 0
            LEFT JOIN {$CFG->teacherstable} t ON t.id = c.teacher_id
            LEFT JOIN {$CFG->userstable} u ON u.id = t.user_id
            WHERE e.deleted = 0
              AND e.student_id = :student_id
              AND c.term_id = :term_id
            ORDER BY cr.name ASC
        ", [
            ':student_id' => (int)$studentId,
            ':term_id' => (int)$termId
        ]);
    }

    private static function getClassById($classId, $termId) {
        global $CFG;
        return DB::getRow("
            SELECT c.*, cr.id AS course_id, cr.category_id
            FROM {$CFG->classestable} c
            JOIN {$CFG->coursestable} cr ON cr.id = c.course_id AND cr.deleted = 0
            WHERE c.deleted = 0 AND c.id = :id AND c.term_id = :term_id
            LIMIT 1
        ", [
            ':id' => (int)$classId,
            ':term_id' => (int)$termId
        ]);
    }

    public static function addClass($student, $classId, $termId, $userSession = null) {
        global $CFG;
        $classId = (int)$classId;
        $termId = (int)$termId;
        if ($classId <= 0 || $termId <= 0) {
            return ['success' => false, 'msg' => 'کلاس نامعتبر است.'];
        }

        $cls = self::getClassById($classId, $termId);
        if (!$cls) {
            return ['success' => false, 'msg' => 'کلاس یافت نشد.'];
        }

        $studentId = (int)$student['id'];
        $requiredCats = self::getRequiredCategoryIds();

        $program = self::getProgram($studentId, $termId);

        foreach ($program as $row) {
            if ((int)$row['class_id'] === $classId) {
                return ['success' => false, 'msg' => 'این کلاس را قبلا انتخاب کرده‌اید.'];
            }
            if ((int)$row['course_id'] === (int)$cls['course_id']) {
                return ['success' => false, 'msg' => 'این دوره را قبلا انتخاب کرده‌اید.'];
            }
            if (!empty($requiredCats) && in_array((int)$cls['category_id'], $requiredCats, true)) {
                if ((int)$row['category_id'] === (int)$cls['category_id']) {
                    return ['success' => false, 'msg' => 'از این دسته اجباری فقط یک کلاس مجاز است.'];
                }
            }
        }

        $newTimes = self::splitTimes($cls['time']);
        foreach ($program as $row) {
            $oldTimes = self::splitTimes($row['time']);
            if (count(array_intersect($newTimes, $oldTimes)) > 0) {
                return ['success' => false, 'msg' => 'این کلاس با یکی از کلاس‌های شما هم‌زمان است.'];
            }
        }

        DB::query('START TRANSACTION');
        try {
            $remaining = self::getClassRemainingSeat($classId, $termId, $seatCol, true);
            if ($remaining <= 0) {
                DB::query('ROLLBACK');
            if ($e instanceof PDOException && $e->getCode() === '23000') {
                return ['success' => false, 'msg' => 'این کلاس را قبلا انتخاب کرده‌اید.'];
            }
            return ['success' => false, 'msg' => 'خطا در افزودن کلاس.'];
            }

            $existsDeleted = DB::getRow("
                SELECT id FROM {$CFG->enrollstable}
                WHERE student_id = :student_id AND class_id = :class_id
                LIMIT 1
            ", [':student_id' => $studentId, ':class_id' => $classId]);

            if ($existsDeleted) {
                DB::query("
                    UPDATE {$CFG->enrollstable}
                    SET deleted = 0, `timestamp` = :ts
                    WHERE id = :id
                ", [
                    ':ts' => time(),
                    ':id' => (int)$existsDeleted['id']
                ]);
            } else {
                DB::insert($CFG->enrollstable, [
                    'student_id' => $studentId,
                    'class_id' => $classId,
                    'timestamp' => time(),
                    'deleted' => 0
                ]);
            }

            DB::query('COMMIT');
            return ['success' => true, 'msg' => 'کلاس افزوده شد.'];
        } catch (Throwable $e) {
            DB::query('ROLLBACK');
            if ($e instanceof PDOException && $e->getCode() === '23000') {
                return ['success' => false, 'msg' => 'این کلاس را قبلا انتخاب کرده‌اید.'];
            }
            return ['success' => false, 'msg' => 'خطا در افزودن کلاس.'];
        }
    }

    public static function removeClass($student, $classId, $termId, $userSession = null) {
        global $CFG;
        $classId = (int)$classId;
        $termId = (int)$termId;
        if ($classId <= 0 || $termId <= 0) {
            return ['success' => false, 'msg' => 'کلاس نامعتبر است.'];
        }

        $cls = self::getClassById($classId, $termId);
        if (!$cls) {
            return ['success' => false, 'msg' => 'کلاس یافت نشد.'];
        }

        $studentId = (int)$student['id'];

        DB::query('START TRANSACTION');
        try {
            $affected = DB::query("
                UPDATE {$CFG->enrollstable}
                SET deleted = 1
                WHERE student_id = :student_id AND class_id = :class_id AND deleted = 0
            ", [':student_id' => $studentId, ':class_id' => $classId])->rowCount();

            if ($affected <= 0) {
                DB::query('ROLLBACK');
            if ($e instanceof PDOException && $e->getCode() === '23000') {
                return ['success' => false, 'msg' => 'این کلاس را قبلا انتخاب کرده‌اید.'];
            }
            return ['success' => false, 'msg' => 'خطا در افزودن کلاس.'];
            }
            DB::query('COMMIT');
            return ['success' => true, 'msg' => 'کلاس حذف شد.'];
        } catch (Throwable $e) {
            DB::query('ROLLBACK');
            if ($e instanceof PDOException && $e->getCode() === '23000') {
                return ['success' => false, 'msg' => 'این کلاس را قبلا انتخاب کرده‌اید.'];
            }
            return ['success' => false, 'msg' => 'خطا در افزودن کلاس.'];
        }
    }

    public static function getMessages($student, $termId, $times, $categoriesMap = []) {
        $program = self::getProgram((int)$student['id'], (int)$termId);
        $requiredCats = self::getRequiredCategoryIds();

        $takenCats = [];
        $takenTimes = [];
        foreach ($program as $p) {
            $takenCats[(int)$p['category_id']] = true;
            foreach (self::splitTimes($p['time']) as $t) {
                $takenTimes[(string)$t] = true;
            }
        }

        $missingCategoryNames = [];
        foreach ($requiredCats as $catId) {
            if (empty($takenCats[$catId])) {
                $missingCategoryNames[] = $categoriesMap[$catId] ?? ('دسته #' . $catId);
            }
        }

        $allTimes = [];
        foreach ($times as $t) {
            if (!empty($t['id'])) $allTimes[(string)$t['id']] = true;
        }
        $freeTimes = array_values(array_diff(array_keys($allTimes), array_keys($takenTimes)));
        sort($freeTimes, SORT_NUMERIC);

        return [
            'missing_categories' => $missingCategoryNames,
            'free_times' => $freeTimes,
            'finished' => (empty($missingCategoryNames) && empty($freeTimes))
        ];
    }
}





