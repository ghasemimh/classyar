<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class Course {
    public static function getCourse($id = NULL, $crsid = NULL, $name = NULL, $mode = 'auto', $deleted = 0) {
        global $CFG;
        $deleted = (int)$deleted;

        if ($id) {
            $id = (int)$id;
            if ($id <= 0) {
                return NULL;
            }
            return DB::getRow("
                SELECT * FROM {$CFG->coursestable} 
                WHERE `id` = :id AND `deleted` = :deleted
                LIMIT 1
            ", [':id' => $id, ':deleted' => $deleted]);
        }

        if ($crsid) {
            $crsid = trim((string)$crsid);
            return DB::getRow("
                SELECT * FROM {$CFG->coursestable} 
                WHERE `crsid` = :crsid AND `deleted` = :deleted
                LIMIT 1
            ", [':crsid' => $crsid, ':deleted' => $deleted]);
        }
        
        if ($name) {
            $name = trim((string)$name);
            return DB::getRow("
                SELECT * FROM {$CFG->coursestable} 
                WHERE `name` = :name AND `deleted` = :deleted
                LIMIT 1
            ", [':name' => $name, ':deleted' => $deleted]);
        }

        if ($mode === 'all') {
            return DB::getAll("
                SELECT * FROM {$CFG->coursestable} 
                WHERE `deleted` = :deleted
                ORDER BY `id` DESC
            ", [':deleted' => $deleted]);
        }
    }

    public static function getCoursesByCategory($categoryId = NULL, $deleted = 0) {
        global $CFG;
        if (!$categoryId) {
            return [];
        }
        try {
            $categoryId = (int)$categoryId;
        } catch (Exception $e) {
            return [];
        }

        return DB::getAll("
            SELECT * FROM {$CFG->coursestable} 
            WHERE `category_id` = :category_id AND `deleted` = :deleted
            ORDER BY `id` DESC
        ", [':category_id' => $categoryId, ':deleted' => (int)$deleted]);
    }

    public static function countAll($deleted = 0, $categoryId = null): int {
        global $CFG;
        if ($categoryId !== null) {
            $row = DB::getRow("
                SELECT COUNT(*) AS c FROM {$CFG->coursestable}
                WHERE `deleted` = :deleted AND `category_id` = :category_id
            ", [
                ':deleted' => (int)$deleted,
                ':category_id' => (int)$categoryId
            ]);
            return (int)($row['c'] ?? 0);
        }
        $row = DB::getRow("
            SELECT COUNT(*) AS c FROM {$CFG->coursestable}
            WHERE `deleted` = :deleted
        ", [':deleted' => (int)$deleted]);
        return (int)($row['c'] ?? 0);
    }

    public static function getPaged(int $offset, int $limit, $deleted = 0, $categoryId = null): array {
        global $CFG;
        $offset = max(0, $offset);
        $limit = max(1, $limit);
        if ($categoryId !== null) {
            return DB::getAll("
                SELECT * FROM {$CFG->coursestable}
                WHERE `deleted` = :deleted AND `category_id` = :category_id
                ORDER BY `id` DESC
                LIMIT {$limit} OFFSET {$offset}
            ", [
                ':deleted' => (int)$deleted,
                ':category_id' => (int)$categoryId
            ]);
        }
        return DB::getAll("
            SELECT * FROM {$CFG->coursestable}
            WHERE `deleted` = :deleted
            ORDER BY `id` DESC
            LIMIT {$limit} OFFSET {$offset}
        ", [':deleted' => (int)$deleted]);
    }

    public static function create($crsid = NULL, $name = NULL, $categoryId = NULL) {
        global $CFG;
        if (!$crsid || !$name || !$categoryId) {
            return false;
        }
        try {
            $categoryId = (int)$categoryId;
        } catch (Exception $e) {
            return false;
        }

        return DB::insert($CFG->coursestable, [
            'crsid'       => $crsid,
            'name'        => $name,
            'category_id' => $categoryId,
            'deleted'     => 0
        ]);
    }

    public static function update($id = NULL, $crsid = NULL, $name = NULL, $categoryId = NULL) {
        global $CFG;
        if (!$id || !$crsid || !$name || !$categoryId) {
            return false;
        }
        try {
            $id = (int)$id;
            $categoryId = (int)$categoryId;
        } catch (Exception $e) {
            return false;
        }

        return DB::update($CFG->coursestable, [
            'crsid'       => $crsid,
            'name'        => $name,
            'category_id' => $categoryId
        ], "`id` = $id");
    }

    public static function softDelete($id = NULL) {
        global $CFG;
        if (!$id) {
            return false;
        }
        try {
            $id = (int)$id;
        } catch (Exception $e) {
            return false;
        }

        return DB::update($CFG->coursestable, [
            'deleted' => 1
        ], "`id` = $id");
    }
}
