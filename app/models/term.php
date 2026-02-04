<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class Term {
    public static function hasOverlap($startTs, $endTs, $excludeId = null) {
        global $CFG;
        if (!$startTs || !$endTs) return false;
        $params = [':start' => $startTs, ':end' => $endTs];
        $sql = "
            SELECT id FROM {$CFG->termstable}
            WHERE `deleted` = 0
              AND NOT (`end` < :start OR `start` > :end)
        ";
        if ($excludeId) {
            $sql .= " AND `id` <> :id";
            $params[':id'] = (int)$excludeId;
        }
        $sql .= " LIMIT 1";
        return (bool) DB::getRow($sql, $params);
    }
    public static function getTerm($id = NULL, $name = NULL, $mode = 'auto', $deleted = 0) {
        global $CFG;

        if ($id) {
            try {
                $id = (int)$id;
            } catch (Exception $e) {
                return NULL;
            }
            return DB::getRow("
                SELECT * FROM {$CFG->termstable} 
                WHERE `id` = $id AND `deleted` = $deleted
                LIMIT 1
            ");
        }

        if ($name) {
            return DB::getRow("
                SELECT * FROM {$CFG->termstable} 
                WHERE `name` = :name AND `deleted` = $deleted
                LIMIT 1
            ", [':name' => $name]);
        }

        if ($mode === 'active') {
            $now = time();
            return DB::getRow("
                SELECT * FROM {$CFG->termstable} 
                WHERE `deleted` = $deleted
                  AND `start` <= :now
                  AND `end` >= :now
                ORDER BY `start` DESC, `id` DESC
                LIMIT 1
            ", [':now' => $now]);
        }

        if ($mode === 'all') {
            return DB::getAll("
                SELECT * FROM {$CFG->termstable} 
                WHERE `deleted` = $deleted
                ORDER BY id DESC
            ");
        }

        return null;
    }

    public static function create($name, $start, $end, $first_open_time = null, $close_time = null, $editable = 1) {
        global $CFG;
        if (!$name || !$start || !$end) {
            return false;
        }

        return DB::insert($CFG->termstable, [
            'name' => $name,
            'start' => $start,
            'end' => $end,
            'first_open_time' => $first_open_time ?: null,
            'close_time' => $close_time ?: null,
            'editable' => (int)$editable,
            'deleted' => 0
        ]);
    }

    public static function update($id, $name, $start, $end, $first_open_time = null, $close_time = null, $editable = 1) {
        global $CFG;
        if (!$id || !$name || !$start || !$end) {
            return false;
        }
        try {
            $id = (int)$id;
        } catch (Exception $e) {
            return false;
        }

        return DB::update($CFG->termstable, [
            'name' => $name,
            'start' => $start,
            'end' => $end,
            'first_open_time' => $first_open_time ?: null,
            'close_time' => $close_time ?: null,
            'editable' => (int)$editable
        ], "`id` = $id");
    }

    public static function softDelete($id) {
        global $CFG;
        if (!$id) {
            return false;
        }
        try {
            $id = (int)$id;
        } catch (Exception $e) {
            return false;
        }

        return DB::update($CFG->termstable, [
            'deleted' => 1
        ], "`id` = $id");
    }
}
