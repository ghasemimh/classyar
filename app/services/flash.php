<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class Flash {
    private const KEY = '_flash_message';

    public static function set(string $message, string $type = 'info'): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION[self::KEY] = [
            'message' => $message,
            'type' => $type
        ];
    }

    public static function get(): ?array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION[self::KEY])) {
            return null;
        }
        $msg = $_SESSION[self::KEY];
        unset($_SESSION[self::KEY]);
        return $msg;
    }
}
