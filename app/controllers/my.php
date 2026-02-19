<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../controllers/users.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../models/enroll.php';
require_once __DIR__ . '/../models/term.php';
require_once __DIR__ . '/../models/category.php';

class My {
    private static function buildStudentSolarData($student): array {
        if (!$student) {
            return ['items' => [], 'total' => 0, 'term' => null, 'messages' => []];
        }

        $term = Term::getTerm(mode: 'active');
        if (!$term) {
            $all = Term::getTerm(mode: 'all');
            $term = $all[0] ?? null;
        }
        if (!$term) {
            return ['items' => [], 'total' => 0, 'term' => null, 'messages' => []];
        }

        $program = Enroll::getProgram((int)$student['id'], (int)$term['id']);

        $times = Enroll::getTimes();
        $timesMap = [];
        foreach ($times as $t) {
            if (!empty($t['id'])) {
                $timesMap[(string)$t['id']] = (string)($t['label'] ?? ('زنگ ' . $t['id']));
            }
        }

        $categories = Category::getCategory(mode: 'all');
        $categoriesMap = [];
        foreach ($categories as $c) {
            $categoriesMap[(int)$c['id']] = (string)($c['name'] ?? '');
        }
        $messages = Enroll::getMessages($student, (int)$term['id'], $times, $categoriesMap);

        $groups = [];
        $total = 0;
        foreach ($program as $row) {
            $catName = trim((string)($row['category_name'] ?? ''));
            if ($catName === '') {
                $catName = 'بدون دسته‌بندی';
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
                'time' => (string)($row['time'] ?? ''),
                'time_label' => self::buildTimeLabel((string)($row['time'] ?? ''), $timesMap)
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
            ],
            'messages' => $messages
        ];
    }

    private static function buildTimeLabel(string $timeCsv, array $timesMap): string {
        $parts = array_filter(array_map('trim', explode(',', $timeCsv)), fn($x) => $x !== '');
        if (empty($parts)) return '-';

        $labels = [];
        foreach ($parts as $p) {
            $labels[] = $timesMap[$p] ?? ('زنگ ' . $p);
        }
        return implode('، ', $labels);
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
            $subtitle = 'منظومه دروس من';
            return include_once __DIR__ . '/../views/my/student.php';
        }
    }
}

