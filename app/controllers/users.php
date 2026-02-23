<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../services/moodleAPI.php';
require_once __DIR__ . '/../models/student.php';
require_once __DIR__ . '/../models/teacher.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/auth.php';

class Users {
    private const FILTER_SESSION_KEY = 'users_filters_v1';
    private const MOODLE_CACHE_SESSION_KEY = 'users_moodle_map_cache_v1';
    private const MOODLE_CACHE_TTL = 600;

    private static function lower(string $text): string {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($text, 'UTF-8');
        }
        return strtolower($text);
    }

    private static function moodleMatchedIds(array $moodleMap, string $search): array {
        $search = trim($search);
        if ($search === '') {
            return [];
        }
        $needle = self::lower($search);
        $matched = [];
        foreach ($moodleMap as $mdlId => $mdl) {
            $haystack = [
                (string)($mdl['fullname'] ?? ''),
                (string)($mdl['firstname'] ?? ''),
                (string)($mdl['lastname'] ?? ''),
                (string)($mdl['email'] ?? ''),
                (string)($mdl['username'] ?? ''),
            ];
            foreach ($haystack as $part) {
                if ($part === '') {
                    continue;
                }
                if (str_contains(self::lower($part), $needle)) {
                    $matched[] = (int)$mdlId;
                    break;
                }
            }
        }
        $matched = array_values(array_unique(array_filter($matched, fn($v) => $v > 0)));
        return $matched;
    }

    private static function validRole($role): bool {
        return in_array($role, ['admin', 'guide', 'teacher', 'student'], true);
    }

    private static function defaultFilterState(): array {
        return [
            'search' => '',
            'role' => '',
            'status' => '',
            'per_page' => 20,
            'page' => 1,
            'show_all' => 0,
        ];
    }

    private static function readFilterState(): array {
        $defaults = self::defaultFilterState();
        $stored = $_SESSION[self::FILTER_SESSION_KEY] ?? [];
        if (!is_array($stored)) {
            $stored = [];
        }
        $state = array_merge($defaults, $stored);
        $state['search'] = trim((string)$state['search']);
        $state['role'] = trim((string)$state['role']);
        $state['status'] = trim((string)$state['status']);
        $state['per_page'] = (int)$state['per_page'];
        $state['page'] = max(1, (int)$state['page']);
        $state['show_all'] = (int)!empty($state['show_all']);
        return $state;
    }

    private static function writeFilterState(array $state): void {
        $_SESSION[self::FILTER_SESSION_KEY] = $state;
    }

    private static function consumeFilterPost(array $post): array {
        $state = self::readFilterState();
        $action = (string)($post['users_action'] ?? '');
        if ($action === '') {
            return $state;
        }

        if ($action === 'reset') {
            $state = self::defaultFilterState();
            self::writeFilterState($state);
            return $state;
        }

        if ($action === 'toggle_all') {
            $state['show_all'] = !empty($state['show_all']) ? 0 : 1;
            $state['page'] = 1;
            self::writeFilterState($state);
            return $state;
        }

        if ($action === 'apply' || $action === 'page') {
            if ($action === 'apply') {
                $state['search'] = trim((string)($post['search'] ?? ''));
                $state['role'] = trim((string)($post['role'] ?? ''));
                $state['status'] = trim((string)($post['status'] ?? ''));
                $state['show_all'] = (int)!empty($post['show_all']);
                $state['page'] = 1;
            }

            $perPageOptions = [10, 20, 50, 100];
            $perPage = (int)($post['per_page'] ?? $state['per_page']);
            if (!in_array($perPage, $perPageOptions, true)) {
                $perPage = 20;
            }
            $state['per_page'] = $perPage;

            if ($action === 'page') {
                $state['page'] = max(1, (int)($post['page'] ?? 1));
            }

            self::writeFilterState($state);
        }

        return $state;
    }

    private static function respond(array $data, string $redirectUrl): void {
        if (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        }

        if (!empty($data['msg'])) {
            $type = (!empty($data['success']) && $data['success']) ? 'success' : 'error';
            Flash::set((string)$data['msg'], $type);
        }

        header('Location: ' . $redirectUrl);
        exit();
    }

    private static function moodleMap(): array {
        $cache = $_SESSION[self::MOODLE_CACHE_SESSION_KEY] ?? null;
        if (is_array($cache) && !empty($cache['ts']) && !empty($cache['map'])) {
            $age = time() - (int)$cache['ts'];
            if ($age >= 0 && $age < self::MOODLE_CACHE_TTL) {
                return (array)$cache['map'];
            }
        }

        $map = [];
        $users = Moodle::getUser(mode: 'all');
        if (!is_array($users)) {
            return $map;
        }
        foreach ($users as $u) {
            $mid = (int)($u['id'] ?? 0);
            if ($mid <= 0) continue;
            $map[$mid] = [
                'fullname' => trim((string)($u['firstname'] ?? '') . ' ' . (string)($u['lastname'] ?? '')),
                'firstname' => (string)($u['firstname'] ?? ''),
                'lastname' => (string)($u['lastname'] ?? ''),
                'email' => (string)($u['email'] ?? ''),
                'username' => (string)($u['username'] ?? ''),
                'profileimageurl' => (string)($u['profileimageurl'] ?? ''),
            ];
        }

        $_SESSION[self::MOODLE_CACHE_SESSION_KEY] = [
            'ts' => time(),
            'map' => $map,
        ];
        return $map;
    }

    public static function index($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'guide')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $total = User::countForAdmin([]);
        $users = User::listForAdmin([], max(1, $total), 0);
        $moodleMap = self::moodleMap();

        foreach ($users as &$u) {
            $mdl = $moodleMap[(int)($u['mdl_id'] ?? 0)] ?? [];
            $u['mdl_fullname'] = $mdl['fullname'] ?? '';
            $u['mdl_email'] = $mdl['email'] ?? '';
            $u['mdl_username'] = $mdl['username'] ?? '';
            $u['mdl_profileimageurl'] = $mdl['profileimageurl'] ?? '';
        }
        unset($u);

        $roleStats = User::roleStats();
        $suspendStats = User::suspendStats();

        $newUsers = self::getUnregisteredMdlUsers();

        $subtitle = 'مدیریت کاربران';
        return include_once __DIR__ . '/../views/users/index.php';
    }

    public static function showUnregisteredMdlUsers($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'guide')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $newUsers = self::getUnregisteredMdlUsers();
        $msg = $request['get']['msg'] ?? null;
        $subtitle = 'کاربران ثبت‌نام‌نشده';
        return include_once __DIR__ . '/../views/users/unregistered.php';
    }

    public static function getUnregisteredMdlUsers() {
        if (!Auth::hasPermission(role: 'guide')) {
            http_response_code(403);
            return [];
        }

        $mdlUsers = Moodle::getUser(mode: 'all');
        $existing = User::getUser(mode: 'all');
        if (!is_array($mdlUsers)) {
            return [];
        }
        if (!is_array($existing)) {
            $existing = [];
        }
        $existingIds = array_map('intval', array_column($existing, 'mdl_id'));

        return array_values(array_filter($mdlUsers, function($u) use ($existingIds) {
            return !in_array((int)($u['id'] ?? 0), $existingIds, true);
        }));
    }

    public static function addUser($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . '/users/unregistered');
        }

        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method !== 'POST') {
            return self::respond(['success' => false, 'msg' => 'درخواست نامعتبر است.'], $CFG->wwwroot . '/users/unregistered');
        }

        $mdl_id = (int)($request['post']['mdl_id'] ?? ($request['route']['mdl_id'] ?? 0));
        $role = trim((string)($request['post']['role'] ?? ($request['route']['role'] ?? 'student')));

        if ($mdl_id <= 0 || !self::validRole($role)) {
            return self::respond(['success' => false, 'msg' => $MSG->baddata], $CFG->wwwroot . '/users/unregistered');
        }

        $mdlUser = Moodle::getUser('id', $mdl_id);
        if (!$mdlUser) {
            return self::respond(['success' => false, 'msg' => $MSG->usernotfound], $CFG->wwwroot . '/users/unregistered');
        }

        $user = User::getUserByMoodleId($mdl_id);
        if ($user) {
            return self::respond(['success' => false, 'msg' => $MSG->useralreadyexists], $CFG->wwwroot . '/users/unregistered');
        }

        if ($role === 'admin' || $role === 'guide') {
            $created = User::createUser($mdl_id, $role);
        } elseif ($role === 'teacher') {
            $created = Teacher::createTeacher($mdl_id);
        } else {
            $created = Student::createStudent($mdl_id);
        }

        if (!$created) {
            return self::respond(['success' => false, 'msg' => $MSG->usercreateerror], $CFG->wwwroot . '/users/unregistered');
        }

        return self::respond(['success' => true, 'msg' => $MSG->usercreated], $CFG->wwwroot . '/users/unregistered');
    }

    public static function changeRole($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . '/users');
        }

        $userId = (int)($request['post']['user_id'] ?? 0);
        $newRole = trim((string)($request['post']['role'] ?? ''));
        if ($userId <= 0 || !self::validRole($newRole)) {
            return self::respond(['success' => false, 'msg' => $MSG->baddata], $CFG->wwwroot . '/users');
        }

        $actorId = (int)($_SESSION['USER']->id ?? 0);
        $result = User::changeRoleTransactional($userId, $newRole, $actorId);

        return self::respond($result, $CFG->wwwroot . '/users');
    }

    public static function toggleSuspend($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . '/users');
        }

        $userId = (int)($request['post']['user_id'] ?? 0);
        $suspend = (int)($request['post']['suspend'] ?? 0);
        if ($userId <= 0) {
            return self::respond(['success' => false, 'msg' => $MSG->baddata], $CFG->wwwroot . '/users');
        }

        $actorId = (int)($_SESSION['USER']->id ?? 0);
        $result = User::updateSuspendTransactional($userId, $suspend, $actorId);
        return self::respond($result, $CFG->wwwroot . '/users');
    }

    public static function bulkUpdate($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . '/users');
        }

        $ids = $request['post']['user_ids'] ?? [];
        if (!is_array($ids)) $ids = [];
        $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));
        if (empty($ids)) {
            return self::respond(['success' => false, 'msg' => 'هیچ کاربری انتخاب نشده است.'], $CFG->wwwroot . '/users');
        }

        $action = (string)($request['post']['bulk_action'] ?? '');
        $payload = ['role' => (string)($request['post']['bulk_role'] ?? '')];
        $actorId = (int)($_SESSION['USER']->id ?? 0);

        $res = User::bulkUpdate($ids, $action, $payload, $actorId);
        if (!empty($res['errors'])) {
            $res['msg'] .= ' | ' . implode(' ; ', array_slice($res['errors'], 0, 5));
        }
        return self::respond($res, $CFG->wwwroot . '/users');
    }
}
