<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../models/user.php';       // مدل یوزر


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
            ':times' => json_encode($times)
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
                    WHERE `teacher_id` = {$teacher['id']}
                    ORDER BY `id` ASC
                ");
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
                    WHERE `teacher_id` = {$t['id']}
                    ORDER BY `id` ASC
                ");

                // فقط مقادیر course_id رو جدا می‌کنیم
                $t['courses'] = array_column($courses, 'course_id');

                // تبدیل times از رشته به آرایه واقعی
                $t['times'] = json_decode($t['times'], true);
            }

            return $teachers;
        }
    }

}