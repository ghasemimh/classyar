<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class DashboardModel {
    public static function stats(): array {
        global $CFG;

        $students = (int)(DB::getRow("SELECT COUNT(*) AS c FROM {$CFG->studentstable} WHERE deleted = 0")['c'] ?? 0);
        $classes = (int)(DB::getRow("SELECT COUNT(*) AS c FROM {$CFG->classestable} WHERE deleted = 0")['c'] ?? 0);
        $enrolls = (int)(DB::getRow("SELECT COUNT(*) AS c FROM {$CFG->enrollstable} WHERE deleted = 0")['c'] ?? 0);
        $teachers = (int)(DB::getRow("SELECT COUNT(*) AS c FROM {$CFG->teacherstable}")['c'] ?? 0);
        $courses = (int)(DB::getRow("SELECT COUNT(*) AS c FROM {$CFG->coursestable} WHERE deleted = 0")['c'] ?? 0);

        return [
            'students' => $students,
            'classes' => $classes,
            'enrolls' => $enrolls,
            'teachers' => $teachers,
            'courses' => $courses,
        ];
    }
}
