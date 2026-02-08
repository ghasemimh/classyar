<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class SchoolClass {
    private static function normalizeTimes($times) {
        if (!is_array($times)) {
            $times = [];
        }
        $times = array_map('trim', $times);
        $times = array_filter($times, function ($t) {
            return $t !== '' && $t !== null;
        });
        $times = array_values(array_unique($times));
        sort($times, SORT_NUMERIC);
        return $times;
    }

    public static function getAll($deleted = 0) {
        global $CFG;
        $rows = DB::getAll("
            SELECT * FROM {$CFG->classestable}
            WHERE `deleted` = $deleted
            ORDER BY `id` DESC
        ");
        foreach ($rows as &$row) {
            $row['time_list'] = $row['time'] ? explode(',', $row['time']) : [];
        }
        return $rows;
    }

    public static function getById($id, $deleted = 0) {
        global $CFG;
        $id = (int)$id;
        $row = DB::getRow("
            SELECT * FROM {$CFG->classestable}
            WHERE `id` = $id AND `deleted` = $deleted
            LIMIT 1
        ");
        if ($row) {
            $row['time_list'] = $row['time'] ? explode(',', $row['time']) : [];
        }
        return $row;
    }

    public static function findConflicts($termId, $teacherId, $roomId, $times, $excludeId = null) {
        global $CFG;
        $times = self::normalizeTimes($times);
        if (empty($times)) {
            return [];
        }

        $params = [
            ':term_id' => (int)$termId,
            ':teacher_id' => (int)$teacherId,
            ':room_id' => (int)$roomId
        ];
        $sql = "
            SELECT * FROM {$CFG->classestable}
            WHERE `deleted` = 0
              AND `term_id` = :term_id
              AND (`teacher_id` = :teacher_id OR `room_id` = :room_id)
        ";
        if ($excludeId) {
            $sql .= " AND `id` <> :id";
            $params[':id'] = (int)$excludeId;
        }
        $rows = DB::getAll($sql, $params);

        $conflicts = [];
        foreach ($rows as $row) {
            $rowTimes = $row['time'] ? explode(',', $row['time']) : [];
            $overlap = array_values(array_intersect($times, $rowTimes));
            if (!empty($overlap)) {
                $isTeacher = ((int)$row['teacher_id'] === (int)$teacherId);
                $isRoom = ((int)$row['room_id'] === (int)$roomId);
                if ($isTeacher) {
                    $conflicts[] = ['id' => $row['id'], 'type' => 'teacher', 'times' => $overlap];
                }
                if ($isRoom) {
                    $conflicts[] = ['id' => $row['id'], 'type' => 'room', 'times' => $overlap];
                }
            }
        }

        return $conflicts;
    }

    public static function create($data) {
        global $CFG;
        $times = self::normalizeTimes($data['time'] ?? []);
        if (empty($times)) return false;

        return DB::insert($CFG->classestable, [
            'term_id' => (int)$data['term_id'],
            'course_id' => (int)$data['course_id'],
            'teacher_id' => (int)$data['teacher_id'],
            'room_id' => (int)$data['room_id'],
            'time' => implode(',', $times),
            'price' => ($data['price'] === '' ? null : $data['price']),
            'seat7' => ($data['seat7'] === '' ? null : $data['seat7']),
            'seat8' => ($data['seat8'] === '' ? null : $data['seat8']),
            'seat9' => ($data['seat9'] === '' ? null : $data['seat9']),
            'deleted' => 0
        ]);
    }

    public static function update($id, $data) {
        global $CFG;
        $id = (int)$id;
        $times = self::normalizeTimes($data['time'] ?? []);
        if (empty($times)) return false;

        return DB::update($CFG->classestable, [
            'term_id' => (int)$data['term_id'],
            'course_id' => (int)$data['course_id'],
            'teacher_id' => (int)$data['teacher_id'],
            'room_id' => (int)$data['room_id'],
            'time' => implode(',', $times),
            'price' => ($data['price'] === '' ? null : $data['price']),
            'seat7' => ($data['seat7'] === '' ? null : $data['seat7']),
            'seat8' => ($data['seat8'] === '' ? null : $data['seat8']),
            'seat9' => ($data['seat9'] === '' ? null : $data['seat9'])
        ], "`id` = $id");
    }

    public static function softDelete($id) {
        global $CFG;
        $id = (int)$id;
        return DB::update($CFG->classestable, ['deleted' => 1], "`id` = $id");
    }
}
