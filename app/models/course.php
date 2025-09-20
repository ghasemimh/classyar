<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class Course {
    public static function getCourse($id = NULL, $crsid = NULL, $mode = 'auto', $deleted = 0) {
        global $CFG;

        if ($id) {
            try {
                $id = (int)$id;
            } catch (Exception $e) {
                return NULL;
            }
            return DB::getRow("
                SELECT * FROM {$CFG->coursestable} 
                WHERE `id` = $id AND `deleted` = $deleted 
                LIMIT 1
            ");
        }

        if ($crsid) {
            return DB::getRow("
                SELECT * FROM {$CFG->coursestable} 
                WHERE `crsid` = '$crsid' AND `deleted` = $deleted 
                LIMIT 1
            ");
        }

        if ($mode === 'all') {
            return DB::getAll("
                SELECT * FROM {$CFG->coursestable} 
                WHERE `deleted` = $deleted 
                ORDER BY `id` DESC
            ");
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
            WHERE `category_id` = $categoryId AND `deleted` = $deleted
            ORDER BY `id` DESC
        ");
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
