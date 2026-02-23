<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');



class Category {
    public static function getCategory($id = NULL, $name = NULL, $mode = 'auto', $deleted = 0) {
        global $CFG;
        $deleted = (int)$deleted;
        if ($id) {
            $id = (int)$id;
            if ($id <= 0) {
                return NULL;
            }
            return DB::getRow("
                SELECT * FROM {$CFG->categoriestable} WHERE `id` = :id AND `deleted` = :deleted LIMIT 1
            ", [':id' => $id, ':deleted' => $deleted]);
        }
        if ($name) {
            $name = trim((string)$name);
            return DB::getRow("
                SELECT * FROM {$CFG->categoriestable} WHERE `name` = :name AND `deleted` = :deleted LIMIT 1
            ", [':name' => $name, ':deleted' => $deleted]);
        }

        if ($mode === 'all') {
            return DB::getAll("
                SELECT * FROM {$CFG->categoriestable} WHERE `deleted` = :deleted ORDER BY `id` DESC
            ", [':deleted' => $deleted]);
        }
    }

    public static function create($name = NULL) {
        global $CFG;
        if (!$name) {
            return false;
        }
        return DB::insert($CFG->categoriestable, [
            'name' => $name,
            'deleted' => 0
        ]);
    }

    public static function countAll($deleted = 0): int {
        global $CFG;
        $row = DB::getRow("SELECT COUNT(*) AS c FROM {$CFG->categoriestable} WHERE `deleted` = :deleted", [
            ':deleted' => (int)$deleted
        ]);
        return (int)($row['c'] ?? 0);
    }

    public static function getPaged(int $offset, int $limit, $deleted = 0): array {
        global $CFG;
        $offset = max(0, $offset);
        $limit = max(1, $limit);
        return DB::getAll("
            SELECT * FROM {$CFG->categoriestable}
            WHERE `deleted` = :deleted
            ORDER BY `id` DESC
            LIMIT {$limit} OFFSET {$offset}
        ", [':deleted' => (int)$deleted]);
    }

    public static function update($id = NULL, $name = NULL) {
        global $CFG;
        if (!$id || !$name) {
            return false;
        }
        try {
            $id = (int)$id;
        } catch (Exception $e) {
            return false;
        }

        return DB::update($CFG->categoriestable, [
            'name' => $name
        ], "`id` = $id");
    }

    public static function delete($id = NULL) {
        global $CFG;
        if (!$id) {
            return false;
        }
        try {
            $id = (int)$id;
        } catch (Exception $e) {
            return false;
        }

        return DB::update($CFG->categoriestable, [
            'deleted' => 1
        ], "`id` = $id");
    }
}
