<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');



class Category {
    public static function getCategory($id = NULL, $name = NULL, $mode = 'auto') {
        global $CFG;
        if ($id) {
            return DB::getAll("
                SELECT * FROM {$CFG->categoriestable} WHERE `id` = $id
            ");
        }
    }
}