<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class Csrf {
    private const TOKEN_KEY = '_csrf_token';
    private const FIELD_KEY = '_csrf';
    private const HEADER_KEY = 'HTTP_X_CSRF_TOKEN';

    public static function token(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION[self::TOKEN_KEY]) || !is_string($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    public static function fieldName(): string {
        return self::FIELD_KEY;
    }

    public static function validateRequest(array $post = [], array $server = []): bool {
        $sessionToken = self::token();
        $postToken = (string)($post[self::FIELD_KEY] ?? '');
        $headerToken = (string)($server[self::HEADER_KEY] ?? '');
        $candidate = $postToken !== '' ? $postToken : $headerToken;
        if ($candidate === '') {
            return false;
        }
        return hash_equals($sessionToken, $candidate);
    }
}

