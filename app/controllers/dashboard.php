<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../models/dashboard.php';
require_once __DIR__ . '/../models/term.php';

class Dashboard {
    public static function index($request) {
        global $MSG;
        if (!Auth::hasPermission(role: 'guide')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $subtitle = 'داشبورد مدیریت';
        $activeTermRaw = Term::getTerm(mode: 'active');
        $activeTerm = (is_array($activeTermRaw) && !empty($activeTermRaw['id'])) ? $activeTermRaw : null;
        $stats = DashboardModel::stats($activeTerm);

        return include_once __DIR__ . '/../views/dashboard/index.php';
    }
}
