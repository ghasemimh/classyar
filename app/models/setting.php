<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class Setting {
    public static function getSetting($name) {
        global $CFG;
        $row = DB::getRow("
            SELECT `value` FROM {$CFG->settingstable} WHERE name = :name LIMIT 1
        ", [':name' => $name]);

        return $row ? $row['value'] : null;
    }
}