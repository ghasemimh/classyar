<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../models/user.php'; // User model


class Teacher {
    private static function splitTimes($timeCsv): array {
        $items = array_map('trim', explode(',', (string)$timeCsv));
        $items = array_values(array_filter($items, fn($v) => $v !== ''));
        $items = array_values(array_unique($items));
        sort($items, SORT_NUMERIC);
        return $items;
    }

    public static function createTeacher($mdl_id, $times = [], $suspend = 0) {
        global $CFG;

        $user_id = User::createUser($mdl_id, role: "teacher", suspend: $suspend) ?? NULL;

        if (!$user_id) {
            return false;
        }

        $id = DB::execute("
            INSERT INTO {$CFG->teacherstable} (user_id, times)
            VALUES (:user_id, :times)
        ", [
            ':user_id' => $user_id,
            ':times' => implode(',', $times)
        ]);
        
        return $id ?? false;
    }

    public static function ensureTeacherProfileByUserId(int $userId, array $times = []): bool {
        global $CFG;
        $userId = (int)$userId;
        if ($userId <= 0) {
            return false;
        }

        $times = array_values(array_filter(array_map('trim', $times), fn($t) => $t !== ''));
        $timesCsv = implode(',', array_values(array_unique($times)));
        $hasDeletedCol = (bool)DB::getRow("SHOW COLUMNS FROM {$CFG->teacherstable} LIKE 'deleted'");

        $selectDeleted = $hasDeletedCol ? ', deleted' : '';

        $existing = DB::getRow("
            SELECT id {$selectDeleted}
            FROM {$CFG->teacherstable}
            WHERE user_id = :user_id
            ORDER BY id DESC
            LIMIT 1
        ", [':user_id' => $userId]);

        if ($existing) {
            $updates = [];
            if ($hasDeletedCol && array_key_exists('deleted', $existing) && (int)($existing['deleted'] ?? 0) !== 0) {
                $updates['deleted'] = 0;
            }
            if (!empty($timesCsv)) {
                $updates['times'] = $timesCsv;
            }
            if (!empty($updates)) {
                DB::update($CFG->teacherstable, $updates, "`id` = " . (int)$existing['id']);
            }
            return true;
        }

        $insertData = ['user_id' => $userId];
        if (!empty($timesCsv)) {
            $insertData['times'] = $timesCsv;
        }
        if ($hasDeletedCol) {
            $insertData['deleted'] = 0;
        }

        return (bool)DB::insert($CFG->teacherstable, $insertData);
    }

    public static function getTeacher($id = NULL, $mode = 'auto', $suspend = 0) {
        global $CFG;

        if ($id) {
            $id = (int)$id;
            if ($id <= 0) {
                return NULL;
            }

            $teacher = DB::getRow("
                SELECT * FROM {$CFG->teacherstable} 
                WHERE `id` = :id
                LIMIT 1
            ", [':id' => $id]);

            if ($teacher) {
                $teacher['courses'] = DB::getAll("
                    SELECT `course_id` FROM {$CFG->teacherclassestable} 
                    WHERE `teacher_id` = :teacher_id AND `deleted` = 0
                    ORDER BY `id` ASC
                ", [':teacher_id' => (int)$teacher['id']]);
                $teacher['courses'] = array_column($teacher['courses'], 'course_id');
                $teacher['times'] = $teacher['times'] ? explode(',', $teacher['times']) : [];
            }

            return $teacher;
        }

        if ($mode === 'all') {
            $teachers = DB::getAll("
                SELECT * FROM {$CFG->teacherstable} 
                ORDER BY `id` DESC
            ");

            foreach ($teachers as &$t) {
                $courses = DB::getAll("
                    SELECT `course_id` FROM {$CFG->teacherclassestable} 
                    WHERE `teacher_id` = :teacher_id AND `deleted` = 0
                    ORDER BY `id` ASC
                ", [':teacher_id' => (int)$t['id']]);

                // Keep only course_id values.
                $t['courses'] = array_column($courses, 'course_id');

                // Convert times from CSV string to array.
                $t['times'] = $t['times'] ? explode(',', $t['times']) : [];
            }

            return $teachers;
        }
    }

    public static function getTeacherByUserId($userId = null) {
        global $CFG;
        if (!$userId) {
            return null;
        }
        try {
            $userId = (int)$userId;
        } catch (Exception $e) {
            return null;
        }
        if ($userId <= 0) {
            return null;
        }

        $teacher = DB::getRow("
            SELECT * FROM {$CFG->teacherstable}
            WHERE `user_id` = :user_id AND `deleted` = 0
            LIMIT 1
        ", [':user_id' => $userId]);

        if (!$teacher) {
            return null;
        }

        $courses = DB::getAll("
            SELECT `course_id` FROM {$CFG->teacherclassestable}
            WHERE `teacher_id` = :teacher_id AND `deleted` = 0
            ORDER BY `id` ASC
        ", [':teacher_id' => (int)$teacher['id']]);
        $teacher['courses'] = array_column($courses, 'course_id');
        $teacher['times'] = !empty($teacher['times']) ? explode(',', $teacher['times']) : [];
        return $teacher;
    }

    public static function getTeacherClasses($teacherId = null, $termId = null) {
        global $CFG;
        if (!$teacherId) {
            return [];
        }
        try {
            $teacherId = (int)$teacherId;
            $termId = ($termId === null) ? null : (int)$termId;
        } catch (Exception $e) {
            return [];
        }
        if ($teacherId <= 0) {
            return [];
        }

        $params = [':teacher_id' => $teacherId];
        $termSql = '';
        if (!empty($termId)) {
            $termSql = ' AND c.term_id = :term_id ';
            $params[':term_id'] = $termId;
        }

        $rows = DB::getAll("
            SELECT
                c.id,
                c.mdl_id,
                c.term_id,
                c.time,
                c.room_id,
                c.seat7,
                c.seat8,
                c.seat9,
                cr.id AS course_id,
                cr.name AS course_name,
                cr.crsid AS course_crsid,
                r.name AS room_name,
                t.name AS term_name,
                (
                    SELECT COUNT(*)
                    FROM {$CFG->enrollstable} e
                    WHERE e.class_id = c.id AND e.deleted = 0
                ) AS enrolled_count
            FROM {$CFG->classestable} c
            JOIN {$CFG->coursestable} cr ON cr.id = c.course_id AND cr.deleted = 0
            LEFT JOIN {$CFG->roomstable} r ON r.id = c.room_id AND r.deleted = 0
            LEFT JOIN {$CFG->termstable} t ON t.id = c.term_id AND t.deleted = 0
            WHERE c.deleted = 0
              AND c.teacher_id = :teacher_id
              $termSql
            ORDER BY cr.name ASC, c.time ASC, c.id ASC
        ", $params);

        foreach ($rows as &$row) {
            $row['time_ids'] = self::splitTimes($row['time'] ?? '');
        }
        unset($row);

        return $rows;
    }

    public static function getTeacherTermStats($teacherId = null, $termId = null): array {
        global $CFG;
        $teacherId = (int)$teacherId;
        $termId = (int)$termId;
        if ($teacherId <= 0 || $termId <= 0) {
            return [
                'classes_count' => 0,
                'enrollments_count' => 0,
                'students_count' => 0,
                'occupied_slots_count' => 0,
            ];
        }

        $classesCount = (int)(DB::getRow("
            SELECT COUNT(*) AS c
            FROM {$CFG->classestable}
            WHERE deleted = 0 AND teacher_id = :teacher_id AND term_id = :term_id
        ", [':teacher_id' => $teacherId, ':term_id' => $termId])['c'] ?? 0);

        $enrollmentsCount = (int)(DB::getRow("
            SELECT COUNT(*) AS c
            FROM {$CFG->enrollstable} e
            JOIN {$CFG->classestable} c ON c.id = e.class_id
            WHERE e.deleted = 0
              AND c.deleted = 0
              AND c.teacher_id = :teacher_id
              AND c.term_id = :term_id
        ", [':teacher_id' => $teacherId, ':term_id' => $termId])['c'] ?? 0);

        $studentsCount = (int)(DB::getRow("
            SELECT COUNT(DISTINCT e.student_id) AS c
            FROM {$CFG->enrollstable} e
            JOIN {$CFG->classestable} c ON c.id = e.class_id
            WHERE e.deleted = 0
              AND c.deleted = 0
              AND c.teacher_id = :teacher_id
              AND c.term_id = :term_id
        ", [':teacher_id' => $teacherId, ':term_id' => $termId])['c'] ?? 0);

        $slotRows = DB::getAll("
            SELECT c.time
            FROM {$CFG->classestable} c
            WHERE c.deleted = 0
              AND c.teacher_id = :teacher_id
              AND c.term_id = :term_id
        ", [':teacher_id' => $teacherId, ':term_id' => $termId]);

        $slots = [];
        foreach ($slotRows as $slotRow) {
            foreach (self::splitTimes($slotRow['time'] ?? '') as $slotId) {
                $slots[$slotId] = true;
            }
        }

        return [
            'classes_count' => $classesCount,
            'enrollments_count' => $enrollmentsCount,
            'students_count' => $studentsCount,
            'occupied_slots_count' => count($slots),
        ];
    }

    public static function getClassRoster($classId = null) {
        global $CFG;
        if (!$classId) {
            return [];
        }
        try {
            $classId = (int)$classId;
        } catch (Exception $e) {
            return [];
        }
        if ($classId <= 0) {
            return [];
        }

        return DB::getAll("
            SELECT
                s.id AS student_id,
                s.cohort,
                u.id AS user_id,
                u.mdl_id
            FROM {$CFG->enrollstable} e
            JOIN {$CFG->studentstable} s ON s.id = e.student_id AND s.deleted = 0
            JOIN {$CFG->userstable} u ON u.id = s.user_id AND u.suspend = 0
            WHERE e.deleted = 0
              AND e.class_id = :class_id
            ORDER BY s.id ASC
        ", [':class_id' => $classId]);
    }

    public static function getClassDetails($classId = null) {
        global $CFG;
        if (!$classId) {
            return null;
        }
        try {
            $classId = (int)$classId;
        } catch (Exception $e) {
            return null;
        }
        if ($classId <= 0) {
            return null;
        }

        return DB::getRow("
            SELECT
                c.id,
                c.mdl_id,
                c.term_id,
                c.time,
                c.room_id,
                c.teacher_id,
                c.seat7,
                c.seat8,
                c.seat9,
                cr.id AS course_id,
                cr.name AS course_name,
                cr.crsid AS course_crsid,
                r.name AS room_name,
                t.name AS term_name
            FROM {$CFG->classestable} c
            JOIN {$CFG->coursestable} cr ON cr.id = c.course_id AND cr.deleted = 0
            LEFT JOIN {$CFG->roomstable} r ON r.id = c.room_id AND r.deleted = 0
            LEFT JOIN {$CFG->termstable} t ON t.id = c.term_id AND t.deleted = 0
            WHERE c.id = :class_id
              AND c.deleted = 0
            LIMIT 1
        ", [':class_id' => $classId]);
    }

    public static function updateTimes($teacherId = NULL, $times = []) {
        global $CFG;
        if (!$teacherId) {
            return false;
        }
        try {
            $teacherId = (int)$teacherId;
        } catch (Exception $e) {
            return false;
        }

        if (!is_array($times)) {
            $times = [];
        }

        $times = array_map('trim', $times);
        $times = array_filter($times, function ($t) {
            return $t !== '' && $t !== null;
        });
        $times = array_values(array_unique($times));

        return DB::update($CFG->teacherstable, [
            'times' => implode(',', $times)
        ], "`id` = $teacherId");
    }

    public static function assignCourse($teacherId = NULL, $courseId = NULL) {
        global $CFG;
        if (!$teacherId || !$courseId) {
            return false;
        }
        try {
            $teacherId = (int)$teacherId;
            $courseId = (int)$courseId;
        } catch (Exception $e) {
            return false;
        }

        $existing = DB::getRow("
            SELECT `id`, `deleted` FROM {$CFG->teacherclassestable}
            WHERE `teacher_id` = :teacher_id AND `course_id` = :course_id
            LIMIT 1
        ", [':teacher_id' => $teacherId, ':course_id' => $courseId]);

        if ($existing) {
            if ((int)$existing['deleted'] === 0) {
                return true;
            }
            return DB::update($CFG->teacherclassestable, [
                'deleted' => 0
            ], "`id` = {$existing['id']}");
        }

        DB::execute("
            INSERT INTO {$CFG->teacherclassestable} (teacher_id, course_id, deleted)
            VALUES (:teacher_id, :course_id, 0)
        ", [':teacher_id' => $teacherId, ':course_id' => $courseId]);

        return true;
    }

    public static function removeCourse($teacherId = NULL, $courseId = NULL) {
        global $CFG;
        if (!$teacherId || !$courseId) {
            return false;
        }
        try {
            $teacherId = (int)$teacherId;
            $courseId = (int)$courseId;
        } catch (Exception $e) {
            return false;
        }

        return DB::update($CFG->teacherclassestable, [
            'deleted' => 1
        ], "`teacher_id` = $teacherId AND `course_id` = $courseId");
    }

}
