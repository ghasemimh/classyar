<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class DB {
    private static $pdo = null;

    private static function connect() {
        global $CFG;
        if (self::$pdo === null) {
            $dsn = "{$CFG->dbtype}:host={$CFG->dbhost};dbname={$CFG->dbname};charset={$CFG->dbcharset}";
            self::$pdo = new PDO($dsn, $CFG->dbuser, $CFG->dbpass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        }
        return self::$pdo;
    }

    public static function query($sql, $params = []) {
        $stmt = self::connect()->prepare($sql); // sql injection
        $stmt->execute($params);
        return $stmt;
    }

    public static function getRow($sql, $params = []) {
        return self::query($sql, $params)->fetch();
    }

    public static function getAll($sql, $params = []) {
        return self::query($sql, $params)->fetchAll();
    }

    public static function execute($sql, $params = []) {
        self::query($sql, $params);
        return self::connect()->lastInsertId();
    }

    public static function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        self::query($sql, $data);
        return self::connect()->lastInsertId();
    }

    public static function update($table, $data, $where) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
        }
        $set = implode(", ", $set);
        $sql = "UPDATE $table SET $set WHERE $where";
        self::query($sql, $data);
        return true;
    }
}
