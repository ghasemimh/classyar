<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/../services/moodleAPI.php'; // جایی که کلاس Moodle رو نوشتی
require_once __DIR__ . '/auth.php'; // احراز هویت و دسترسی ها
require_once __DIR__ . '/../models/room.php';

class Rooms {
    public static function index($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'guide')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $id = $request['route'][0] ?? NULL;

        if ($id) {
            $room = Room::getRoom($id);
            if ($room) {
                $msg = $request['get']['msg'] ?? NULL;
                return include_once __DIR__ . '/../views/rooms/single.php';
            }
            $msg = $MSG->roomnotfound;
            $rooms = Room::getRoom(mode: 'all');
            return include_once __DIR__ . '/../views/rooms/index.php';
        }
        $rooms = Room::getRoom(mode: 'all');
        $msg = $request['get']['msg'] ?? NULL;
        return include_once __DIR__ . '/../views/rooms/index.php';
        
        
    }

    public static function create($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }
        $msg = $request['get']['msg'] ?? NULL;
        return include_once __DIR__ . '/../views/rooms/create.php';
    }

    public static function store($request) {
        global $CFG, $MSG;
        $post = $request['post'] ?? NULL;

        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . "/room?msg=" . urlencode($MSG->notallowed));
        }

        if ($post) {
            $name = trim($post['name'] ?? NULL);
            if ($name) {
                $result = Room::create($name);
                if ($result) {
                    return self::respond(['success' => true, 'msg' => $MSG->roomcreated, 'id' => $result], $CFG->wwwroot . "/room?msg=" . urlencode($MSG->roomcreated));
                }
                return self::respond(['success' => false, 'msg' => $MSG->roomcreateerror], $CFG->wwwroot . "/room/new?msg=" . urlencode($MSG->roomcreateerror));
            }
            return self::respond(['success' => false, 'msg' => $MSG->roomnameerror], $CFG->wwwroot . "/room/new?msg=" . urlencode($MSG->roomnameerror));
        }

        return self::respond(['success' => false, 'msg' => $MSG->badrequest], $CFG->wwwroot . "/room?msg=" . urlencode($MSG->badrequest));
    }


    public static function edit($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }
        $id = $request['route'][0] ?? NULL;
        if (!$id) {
            $msg = $MSG->idnotgiven;
            $rooms = Room::getRoom(mode: 'all');
            return include_once __DIR__ . '/../views/rooms/index.php';
        }

        $room = Room::getRoom(id: $id);
        if (!$room) {
            $msg = $MSG->roomnotfound;
            $rooms = Room::getRoom(mode: 'all');
            return include_once __DIR__ . '/../views/rooms/index.php';
        }
        $msg = $request['get']['msg'] ?? NULL;
        return include __DIR__ . '/../views/rooms/edit.php';
    }

    public static function update($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . "/room?msg=" . urlencode($MSG->notallowed));
        }

        $id = $request['route'][0] ?? NULL;
        $name = trim($request['post']['name'] ?? NULL);

        if ($id && $name) {
            $result = Room::update($id, $name);
            if ($result) {
                return self::respond(['success' => true, 'msg' => $MSG->roomedited], $CFG->wwwroot . "/room?msg=" . urlencode($MSG->roomedited));
            }
            return self::respond(['success' => false, 'msg' => $MSG->roomediterror], $CFG->wwwroot . "/room/edit/$id?msg=" . urlencode($MSG->roomediterror));
        }

        if (!$id) {
            return self::respond(['success' => false, 'msg' => $MSG->idnotgiven], $CFG->wwwroot . "/room?msg=" . urlencode($MSG->idnotgiven));
        }
        if (!$name) {
            return self::respond(['success' => false, 'msg' => $MSG->roomnameerror], $CFG->wwwroot . "/room/edit/$id?msg=" . urlencode($MSG->roomnameerror));
        }
    }


    public static function delete($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], '');
        }

        $id = $request['route'][0] ?? NULL;
        $name = trim($request['post']['name'] ?? NULL);

        if (!$id) return self::respond(['success' => false, 'msg' => $MSG->idnotgiven], '');
        $room = Room::getRoom(id: $id);
        if (!$room) return self::respond(['success' => false, 'msg' => $MSG->roomnotfound], '');

        if ($name && $room['name'] === $name) {
            $result = Room::delete($id);
            if ($result) return self::respond(['success' => true, 'msg' => $MSG->roomdeleted, 'id' => $id], '');
            return self::respond(['success' => false, 'msg' => $MSG->roomdeleteerror], '');
        }

        return self::respond(['success' => false, 'msg' => $MSG->roomdeleteconfirmationerror], '');
    }


    public static function confirmDelete($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }
        $id = $request['route'][0] ?? NULL;
        if (!$id) {
            $msg = $MSG->idnotgiven;
            $rooms = Room::getRoom(mode: 'all');
            return include_once __DIR__ . '/../views/rooms/index.php';
        }

        $room = Room::getRoom(id: $id);
        if (!$room) {
            $msg = $MSG->roomnotfound;
            $rooms = Room::getRoom(mode: 'all');
            return include_once __DIR__ . '/../views/rooms/index.php';
        }
        $msg = $request['get']['msg'] ?? NULL;
        include __DIR__ . '/../views/rooms/confirm_delete.php';
    }


    private static function respond($data, $redirectUrl) {
        // اگر درخواست AJAX بود
        if (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        }

        // حالت عادی → redirect
        header("Location: $redirectUrl");
        exit();
    }

}
