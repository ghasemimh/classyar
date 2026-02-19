<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../models/user.php';       // Ù…Ø¯Ù„ ÛŒÙˆØ²Ø±


class Teacher {
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

    public static function getTeacher($id = NULL, $mode = 'auto', $suspend = 0) {
        global $CFG;

        if ($id) {
            try {
                $id = (int)$id;
            } catch (Exception $e) {
                return NULL;
            }

            $teacher = DB::getRow("
                SELECT * FROM {$CFG->teacherstable} 
                WHERE `id` = $id
                LIMIT 1
            ");

            if ($teacher) {
                $teacher['courses'] = DB::getAll("
                    SELECT `course_id` FROM {$CFG->teacherclassestable} 
                    WHERE `teacher_id` = {$teacher['id']} AND `deleted` = 0
                    ORDER BY `id` ASC
                ");
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
                    WHERE `teacher_id` = {$t['id']} AND `deleted` = 0
                    ORDER BY `id` ASC
                ");

                // ÙÙ‚Ø· Ù…Ù‚Ø§Ø¯ÛŒØ± course_id Ø±Ùˆ Ø¬Ø¯Ø§ Ù…ÛŒâ€ŒÚ©Ù†ÛŒÙ…
                $t['courses'] = array_column($courses, 'course_id');

                // ØªØ¨Ø¯ÛŒÙ„ times Ø§Ø² Ø±Ø´ØªÙ‡ Ø¨Ù‡ Ø¢Ø±Ø§ÛŒÙ‡ ÙˆØ§Ù‚Ø¹ÛŒ
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

        return DB::getAll("
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
