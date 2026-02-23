<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class DashboardModel {
    public static function stats(?array $activeTerm = null): array {
        global $CFG;

        $students = (int)(DB::getRow("SELECT COUNT(*) AS c FROM {$CFG->studentstable} WHERE deleted = 0")['c'] ?? 0);
        $classes = (int)(DB::getRow("SELECT COUNT(*) AS c FROM {$CFG->classestable} WHERE deleted = 0")['c'] ?? 0);
        $enrolls = (int)(DB::getRow("SELECT COUNT(*) AS c FROM {$CFG->enrollstable} WHERE deleted = 0")['c'] ?? 0);
        $teachers = (int)(DB::getRow("SELECT COUNT(*) AS c FROM {$CFG->teacherstable} WHERE deleted = 0")['c'] ?? 0);
        $courses = (int)(DB::getRow("SELECT COUNT(*) AS c FROM {$CFG->coursestable} WHERE deleted = 0")['c'] ?? 0);

        $termStats = [
            'classes' => 0,
            'enrolls' => 0,
            'active_students' => 0,
            'active_teachers' => 0,
            'avg_enroll_per_student' => 0.0,
        ];

        if (!empty($activeTerm['id'])) {
            $termId = (int)$activeTerm['id'];

            $termStats['classes'] = (int)(DB::getRow(
                "SELECT COUNT(*) AS c FROM {$CFG->classestable} WHERE deleted = 0 AND term_id = :term_id",
                [':term_id' => $termId]
            )['c'] ?? 0);

            $termStats['enrolls'] = (int)(DB::getRow(
                "SELECT COUNT(*) AS c
                 FROM {$CFG->enrollstable} e
                 JOIN {$CFG->classestable} c ON c.id = e.class_id
                 WHERE e.deleted = 0 AND c.deleted = 0 AND c.term_id = :term_id",
                [':term_id' => $termId]
            )['c'] ?? 0);

            $termStats['active_students'] = (int)(DB::getRow(
                "SELECT COUNT(DISTINCT e.student_id) AS c
                 FROM {$CFG->enrollstable} e
                 JOIN {$CFG->classestable} c ON c.id = e.class_id
                 WHERE e.deleted = 0 AND c.deleted = 0 AND c.term_id = :term_id",
                [':term_id' => $termId]
            )['c'] ?? 0);

            $termStats['active_teachers'] = (int)(DB::getRow(
                "SELECT COUNT(DISTINCT teacher_id) AS c
                 FROM {$CFG->classestable}
                 WHERE deleted = 0 AND term_id = :term_id",
                [':term_id' => $termId]
            )['c'] ?? 0);

            if ($termStats['active_students'] > 0) {
                $termStats['avg_enroll_per_student'] = round($termStats['enrolls'] / $termStats['active_students'], 2);
            }
        }

        return [
            'students' => $students,
            'classes' => $classes,
            'enrolls' => $enrolls,
            'teachers' => $teachers,
            'courses' => $courses,
            'term' => $termStats,
        ];
    }
}
