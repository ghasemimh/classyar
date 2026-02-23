<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../models/room.php';
require_once __DIR__ . '/../services/validator.php';

class Rooms {
    public static function index($request) {
        global $MSG;
        if (!Auth::hasPermission(role: 'guide')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $rooms = Room::getRoom(mode: 'all');
        return include_once __DIR__ . '/../views/rooms/index.php';
    }

    public static function create($request) {
        global $CFG;
        header('Location: ' . $CFG->wwwroot . '/room');
        exit();
    }

    public static function store($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . '/room');
        }
        $name = Validator::str($request['post']['name'] ?? null, 150);
        if ($name === '') {
            return self::respond(['success' => false, 'msg' => $MSG->roomnameemptyerror], $CFG->wwwroot . '/room');
        }

        $result = Room::create($name);
        if ($result) {
            return self::respond(['success' => true, 'msg' => $MSG->roomcreated, 'id' => $result], $CFG->wwwroot . '/room');
        }
        return self::respond(['success' => false, 'msg' => $MSG->roomcreateerror], $CFG->wwwroot . '/room');
    }

    public static function edit($request) {
        global $CFG;
        header('Location: ' . $CFG->wwwroot . '/room');
        exit();
    }

    public static function update($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . '/room');
        }
        $id = Validator::positiveInt($request['route'][0] ?? null);
        $name = Validator::str($request['post']['name'] ?? null, 150);
        if (!$id) {
            return self::respond(['success' => false, 'msg' => $MSG->idnotgiven], $CFG->wwwroot . '/room');
        }
        if ($name === '') {
            return self::respond(['success' => false, 'msg' => $MSG->roomnameemptyerror], $CFG->wwwroot . '/room');
        }
        $result = Room::update($id, $name);
        if ($result) {
            return self::respond(['success' => true, 'msg' => $MSG->roomedited], $CFG->wwwroot . '/room');
        }
        return self::respond(['success' => false, 'msg' => $MSG->roomediterror], $CFG->wwwroot . '/room');
    }

    public static function delete($request) {
        global $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], '');
        }
        $id = Validator::positiveInt($request['route'][0] ?? null);
        $name = Validator::str($request['post']['name'] ?? null, 150);
        if (!$id) {
            return self::respond(['success' => false, 'msg' => $MSG->idnotgiven], '');
        }
        $room = Room::getRoom(id: $id);
        if (!$room) {
            return self::respond(['success' => false, 'msg' => $MSG->roomnotfound], '');
        }
        if ($name !== (string)$room['name']) {
            return self::respond(['success' => false, 'msg' => $MSG->roomdeleteconfirmationerror], '');
        }

        $blockers = Room::getDeleteBlockers($id);
        if (!empty($blockers['classes'])) {
            $count = (int)$blockers['classes'];
            return self::respond([
                'success' => false,
                'blocked' => true,
                'msg' => "این مکان در {$count} کلاس استفاده شده و قابل حذف نیست."
            ], '');
        }

        $result = Room::delete($id);
        if ($result) {
            return self::respond(['success' => true, 'msg' => $MSG->roomdeleted, 'id' => $id], '');
        }
        return self::respond(['success' => false, 'msg' => $MSG->roomdeleteerror], '');
    }

    public static function confirmDelete($request) {
        global $CFG;
        header('Location: ' . $CFG->wwwroot . '/room');
        exit();
    }

    private static function respond($data, $redirectUrl) {
        if (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            if (ob_get_length()) {
                ob_clean();
            }
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        }
        if (!empty($data['msg'])) {
            $type = (!empty($data['success']) && $data['success']) ? 'success' : 'error';
            Flash::set($data['msg'], $type);
        }
        if ($redirectUrl !== '') {
            header("Location: $redirectUrl");
            exit();
        }
    }
}
