<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class Term {
    public static function getTerm($mode = 'auto') {
        global $CFG;
        if ($mode === 'last') {
            return DB::getRow("
                SELECT * FROM {$CFG->termstable} ORDER BY id DESC LIMIT 1
            ");
        }
        return null;
    }

}