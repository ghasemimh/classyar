<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../models/term.php';
require_once __DIR__ . '/../models/room.php';
require_once __DIR__ . '/../models/course.php';
require_once __DIR__ . '/../models/teacher.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/setting.php';
require_once __DIR__ . '/../models/class.php';
require_once __DIR__ . '/../models/prerequisite.php';
require_once __DIR__ . '/../services/moodleAPI.php';

class Program {
    private static function isTermActive($term) {
        if (!$term) return false;
        $now = time();
        $start = (int)($term['start'] ?? 0);
        $end = (int)($term['end'] ?? 0);
        return ($start && $end && $now >= $start && $now <= $end);
    }
    private static function respond($data, $redirectUrl) {
        if (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        }
        if (!empty($data['msg'])) {
            $type = (!empty($data['success']) && $data['success']) ? 'success' : 'error';
            Flash::set($data['msg'], $type);
        }
        header("Location: $redirectUrl");
        exit();
    }

    public static function index($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $terms = Term::getTerm(mode: 'all');
        $activeTerm = Term::getTerm(mode: 'active');
        $activeTermId = $activeTerm ? (int)$activeTerm['id'] : null;
        $courses = Course::getCourse(mode: 'all');
        $rooms = Room::getRoom(mode: 'all');
        $teachers = Teacher::getTeacher(mode: 'all');
        $users = User::getUser(mode: 'all');
        $Mdl_users = Moodle::getUser(mode: 'all');
        $timesInfo = json_decode(Setting::getSetting('Times Information'), true);
        $times = $timesInfo['times'] ?? [];

        $classes = SchoolClass::getAll();
        $prereqs = Prerequisite::getAll();
        $msg = $request['get']['msg'] ?? NULL;
        $subtitle = 'چیدمان';

        return include_once __DIR__ . '/../views/program/index.php';
    }

    public static function store($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . "/program?msg=" . urlencode($MSG->notallowed));
        }

        $post = $request['post'] ?? [];
        $termId = (int)($post['term_id'] ?? 0);
        $courseId = (int)($post['course_id'] ?? 0);
        $teacherId = (int)($post['teacher_id'] ?? 0);
        $roomId = (int)($post['room_id'] ?? 0);
        $price = trim($post['price'] ?? '');
        $seat7 = $post['seat7'] ?? null;
        $seat8 = $post['seat8'] ?? null;
        $seat9 = $post['seat9'] ?? null;
        $times = $post['times'] ?? [];
        $prereqsRaw = $post['prereqs'] ?? '[]';
        $prereqs = json_decode($prereqsRaw, true);
        if (!is_array($prereqs)) $prereqs = [];
        $prereqs = array_map(function ($p) {
            $type = $p['type'] ?? null;
            if ($type === 'none') return [];
            if ($type === 'text') {
                return ['alternative_text' => trim($p['text'] ?? '')];
            }
            if ($type === 'course') {
                return ['course_id' => (int)($p['course_id'] ?? 0)];
            }
            return $p;
        }, $prereqs);

        if (!$termId || !$courseId || !$teacherId || !$roomId || empty($times)) {
            return self::respond(['success' => false, 'msg' => $MSG->baddata], $CFG->wwwroot . "/program?msg=" . urlencode($MSG->baddata));
        }

        $term = Term::getTerm(id: $termId);
        if (!self::isTermActive($term)) {
            return self::respond(['success' => false, 'msg' => 'ترم انتخاب‌شده غیرفعال است.'], $CFG->wwwroot . "/program?msg=" . urlencode('ترم انتخاب‌شده غیرفعال است.'));
        }

        $conflicts = SchoolClass::findConflicts($termId, $teacherId, $roomId, $times);
        if (!empty($conflicts)) {
            $teacherTimes = [];
            $roomTimes = [];
            foreach ($conflicts as $c) {
                if ($c['type'] === 'teacher') $teacherTimes = array_merge($teacherTimes, $c['times']);
                if ($c['type'] === 'room') $roomTimes = array_merge($roomTimes, $c['times']);
            }
            $teacherTimes = array_values(array_unique($teacherTimes));
            $roomTimes = array_values(array_unique($roomTimes));
            $parts = [];
            if (!empty($teacherTimes)) {
                $parts[] = 'تداخل با معلم در زنگ‌های: ' . implode(', ', $teacherTimes);
            }
            if (!empty($roomTimes)) {
                $parts[] = 'تداخل با مکان در زنگ‌های: ' . implode(', ', $roomTimes);
            }
            $msg = implode(' | ', $parts);
            return self::respond(['success' => false, 'msg' => $msg], $CFG->wwwroot . "/program?msg=" . urlencode($msg));
        }

        $id = SchoolClass::create([
            'term_id' => $termId,
            'course_id' => $courseId,
            'teacher_id' => $teacherId,
            'room_id' => $roomId,
            'time' => $times,
            'price' => $price,
            'seat7' => $seat7,
            'seat8' => $seat8,
            'seat9' => $seat9
        ]);

        if ($id) {
            $prereqs = array_values(array_filter($prereqs, function ($p) use ($courseId) {
                return empty($p['course_id']) || (int)$p['course_id'] !== (int)$courseId;
            }));
            Prerequisite::createMany($id, $prereqs);
            $row = SchoolClass::getById($id);
            return self::respond(['success' => true, 'msg' => 'کلاس با موفقیت ایجاد شد.', 'data' => $row], $CFG->wwwroot . "/program?msg=" . urlencode('کلاس با موفقیت ایجاد شد.'));
        }

        return self::respond(['success' => false, 'msg' => $MSG->unknownerror], $CFG->wwwroot . "/program?msg=" . urlencode($MSG->unknownerror));
    }

    public static function update($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . "/program?msg=" . urlencode($MSG->notallowed));
        }

        $id = (int)($request['route'][0] ?? 0);
        if (!$id) {
            return self::respond(['success' => false, 'msg' => $MSG->idnotgiven], $CFG->wwwroot . "/program?msg=" . urlencode($MSG->idnotgiven));
        }

        $post = $request['post'] ?? [];
        $termId = (int)($post['term_id'] ?? 0);
        $courseId = (int)($post['course_id'] ?? 0);
        $teacherId = (int)($post['teacher_id'] ?? 0);
        $roomId = (int)($post['room_id'] ?? 0);
        $price = trim($post['price'] ?? '');
        $seat7 = $post['seat7'] ?? null;
        $seat8 = $post['seat8'] ?? null;
        $seat9 = $post['seat9'] ?? null;
        $times = $post['times'] ?? [];
        $prereqsRaw = $post['prereqs'] ?? '[]';
        $prereqs = json_decode($prereqsRaw, true);
        if (!is_array($prereqs)) $prereqs = [];
        $prereqs = array_map(function ($p) {
            $type = $p['type'] ?? null;
            if ($type === 'none') return [];
            if ($type === 'text') {
                return ['alternative_text' => trim($p['text'] ?? '')];
            }
            if ($type === 'course') {
                return ['course_id' => (int)($p['course_id'] ?? 0)];
            }
            return $p;
        }, $prereqs);

        if (!$termId || !$courseId || !$teacherId || !$roomId || empty($times)) {
            return self::respond(['success' => false, 'msg' => $MSG->baddata], $CFG->wwwroot . "/program?msg=" . urlencode($MSG->baddata));
        }

        $classRow = SchoolClass::getById($id);
        if (!$classRow) {
            return self::respond(['success' => false, 'msg' => $MSG->baddata], $CFG->wwwroot . "/program?msg=" . urlencode($MSG->baddata));
        }

        $term = Term::getTerm(id: $termId);
        if (!self::isTermActive($term)) {
            return self::respond(['success' => false, 'msg' => 'ترم انتخاب‌شده غیرفعال است.'], $CFG->wwwroot . "/program?msg=" . urlencode('ترم انتخاب‌شده غیرفعال است.'));
        }

        $conflicts = SchoolClass::findConflicts($termId, $teacherId, $roomId, $times, $id);
        if (!empty($conflicts)) {
            $teacherTimes = [];
            $roomTimes = [];
            foreach ($conflicts as $c) {
                if ($c['type'] === 'teacher') $teacherTimes = array_merge($teacherTimes, $c['times']);
                if ($c['type'] === 'room') $roomTimes = array_merge($roomTimes, $c['times']);
            }
            $teacherTimes = array_values(array_unique($teacherTimes));
            $roomTimes = array_values(array_unique($roomTimes));
            $parts = [];
            if (!empty($teacherTimes)) {
                $parts[] = 'تداخل با معلم در زنگ‌های: ' . implode(', ', $teacherTimes);
            }
            if (!empty($roomTimes)) {
                $parts[] = 'تداخل با مکان در زنگ‌های: ' . implode(', ', $roomTimes);
            }
            $msg = implode(' | ', $parts);
            return self::respond(['success' => false, 'msg' => $msg], $CFG->wwwroot . "/program?msg=" . urlencode($msg));
        }

        $ok = SchoolClass::update($id, [
            'term_id' => $termId,
            'course_id' => $courseId,
            'teacher_id' => $teacherId,
            'room_id' => $roomId,
            'time' => $times,
            'price' => $price,
            'seat7' => $seat7,
            'seat8' => $seat8,
            'seat9' => $seat9
        ]);

        if ($ok) {
            $prereqs = array_values(array_filter($prereqs, function ($p) use ($courseId) {
                return empty($p['course_id']) || (int)$p['course_id'] !== (int)$courseId;
            }));
            Prerequisite::deleteByClass($id);
            Prerequisite::createMany($id, $prereqs);
            $row = SchoolClass::getById($id);
            return self::respond(['success' => true, 'msg' => 'کلاس با موفقیت بروزرسانی شد.', 'data' => $row], $CFG->wwwroot . "/program?msg=" . urlencode('کلاس با موفقیت بروزرسانی شد.'));
        }

        return self::respond(['success' => false, 'msg' => $MSG->unknownerror], $CFG->wwwroot . "/program?msg=" . urlencode($MSG->unknownerror));
    }

    public static function delete($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], '');
        }

        $id = (int)($request['route'][0] ?? 0);
        if (!$id) {
            return self::respond(['success' => false, 'msg' => $MSG->idnotgiven], '');
        }

        $classRow = SchoolClass::getById($id);
        if (!$classRow) {
            return self::respond(['success' => false, 'msg' => $MSG->baddata], '');
        }
        $term = Term::getTerm(id: (int)$classRow['term_id']);
        if (!self::isTermActive($term)) {
            return self::respond(['success' => false, 'msg' => 'ترم انتخاب‌شده غیرفعال است.'], '');
        }

        $ok = SchoolClass::softDelete($id);
        if ($ok) {
            return self::respond(['success' => true, 'msg' => 'کلاس حذف شد.', 'id' => $id], '');
        }

        return self::respond(['success' => false, 'msg' => $MSG->unknownerror], '');
    }
}




