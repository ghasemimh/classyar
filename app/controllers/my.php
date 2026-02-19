<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../controllers/users.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../models/enroll.php';
require_once __DIR__ . '/../models/term.php';

class My {
    private static function buildStudentSolarData($student): array {
        if (!$student) {
            return ['items' => [], 'total' => 0, 'term' => null];
        }

        $term = Term::getTerm(mode: 'active');
        if (!$term) {
            $all = Term::getTerm(mode: 'all');
            $term = $all[0] ?? null;
        }
        if (!$term) {
            return ['items' => [], 'total' => 0, 'term' => null];
        }

        $program = Enroll::getProgram((int)$student['id'], (int)$term['id']);
        $groups = [];
        $total = 0;

        foreach ($program as $row) {
            $catName = trim((string)($row['category_name'] ?? ''));
            if ($catName === '') {
                $catName = 'Ø¨Ø¯ÙˆÙ† Ø¯Ø³ØªÙ‡â€ŒØ¨Ù†Ø¯ÛŒ';
            }
            if (!isset($groups[$catName])) {
                $groups[$catName] = [
                    'category' => $catName,
                    'count' => 0,
                    'classes' => []
                ];
            }
            $groups[$catName]['count']++;
            $groups[$catName]['classes'][] = [
                'class_id' => (int)($row['class_id'] ?? 0),
                'course_name' => (string)($row['course_name'] ?? ''),
                'time' => (string)($row['time'] ?? '')
            ];
            $total++;
        }

        $items = array_values($groups);
        usort($items, function ($a, $b) {
            $cmp = ((int)$b['count']) <=> ((int)$a['count']);
            if ($cmp !== 0) return $cmp;
            return strcmp((string)$a['category'], (string)$b['category']);
        });

        return [
            'items' => $items,
            'total' => $total,
            'term' => [
                'id' => (int)$term['id'],
                'name' => (string)($term['name'] ?? '')
            ]
        ];
    }

    public static function index($request) {
        global $CFG, $MSG;

        if (Auth::checkRole(role: 'admin')) {
            Users::showUnregisteredMdlUsers($request);
        }

        if (Auth::checkRole(role: 'student')) {
            $sessionUser = $_SESSION['USER'] ?? (object)[];
            $student = Enroll::getStudentByUser($sessionUser);
            $solar = self::buildStudentSolarData($student);
            $subtitle = 'Ù…Ù†Ø¸ÙˆÙ…Ù‡ Ø¯Ø±ÙˆØ³ Ù…Ù†';
            return include_once __DIR__ . '/../views/my/student.php';
        }
    }
}


