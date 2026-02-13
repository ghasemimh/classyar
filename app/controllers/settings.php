<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../services/updater.php';

class Settings {
    public static function index($request) {
        global $MSG;

        if (!Auth::hasPermission(role: 'admin')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $subtitle = 'تنظیمات';
        $message = null;
        $messageType = 'info';

        $result = Updater::checkForUpdate();

        if (!empty($request['post']['action']) && $request['post']['action'] === 'check') {
            $result = Updater::checkForUpdate();
            $message = $result['message'] ?? null;
            $messageType = ($result['ok'] ?? false) ? 'success' : 'error';
        }

        if (!empty($request['post']['action']) && $request['post']['action'] === 'update') {
            $install = Updater::installUpdate();
            $message = $install['message'] ?? null;
            $messageType = ($install['ok'] ?? false) ? 'success' : 'error';
            $result = Updater::checkForUpdate();
        }

        $localVersion = $result['local'] ?? Updater::getLocalVersion();
        $remoteVersion = $result['remote'] ?? null;
        $hasUpdate = !empty($result['has_update']);

        return include_once __DIR__ . '/../views/settings/index.php';
    }
}


