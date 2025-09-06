<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');



class Category {
    public static function getCategory($id = NULL, $name = NULL, $mode = 'auto', $deleted = 0) {
        global $CFG;
        if ($id) {
            try {
                $id = (int)$id;
            } catch (Exception $e) {
                return NULL;
            }
            return DB::getRow("
                SELECT * FROM {$CFG->categoriestable} WHERE `id` = $id AND `deleted` = $deleted LIMIT 1
            ");
        }
        if ($name) {
            return DB::getRow("
                SELECT * FROM {$CFG->categoriestable} LIKE `name` = '$name' WHERE `deleted` = $deleted LIMIT 1
            ");
        }

        if ($mode === 'all') {
            return DB::getAll("
                SELECT * FROM {$CFG->categoriestable} WHERE `deleted` = $deleted ORDER BY `id` DESC
            ");
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