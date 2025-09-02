<?php
defined('CLASSYAR_APP') || die('No direct access allowed!');

class Term {
    public static function getTerm ($mode = 'auto') {
        global $CFG;
        if ($mode === 'last') {
            $dsn = "{$CFG->dbtype}:host={$CFG->dbhost};dbname={$CFG->dbname};charset={$CFG->dbcharset}";
            $pdo = new PDO($dsn, $CFG->dbuser, $CFG->dbpass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            $stmt = $pdo->prepare("SELECT * FROM {$CFG->termstable} ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            return $stmt->fetch();
        }
    }
}