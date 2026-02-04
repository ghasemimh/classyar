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
