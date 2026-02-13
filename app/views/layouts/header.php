<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

global $CFG, $MDL, $MSG;
require_once __DIR__ . '/../../models/term.php';

$msg = $msg ?? null;
$subtitle = $subtitle ?? $CFG->sitedescription;

$userRole = $_SESSION['USER']->role ?? 'guest';
$currentUserName = $_SESSION['USER']->fullname ?? 'کاربر مهمان';
$currentUserImage = $_SESSION['USER']->profileimage ?? ($CFG->assets . '/images/icon.png');
$activeTermName = null;
try {
    $lastTerm = Term::getTerm(mode: 'active');
    if ($lastTerm) {
        $nowTs = time();
        $startTs = intval($lastTerm['start'] ?? 0);
        $endTs = intval($lastTerm['end'] ?? 0);
        if ($startTs && $endTs && $nowTs >= $startTs && $nowTs <= $endTs) {
            $activeTermName = $lastTerm['name'] ?? null;
        }
    }
} catch (Throwable $e) {
    $activeTermName = null;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= $CFG->sitetitle . ' | ' . $subtitle ?></title>
    <meta name="description" content="<?= $subtitle ?>">
    <meta property="og:title" content="<?= $CFG->sitetitle . ' | ' . $subtitle ?>" />
    <meta property="og:description" content="<?= $subtitle ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= $CFG->wwwroot ?>" />
    <meta property="og:image" content="<?= $CFG->assets ?>/images/og-image.png" />

    <link rel="icon" type="image/x-icon" href="<?= $CFG->siteiconurl ?>">
    <script src="<?= $CFG->assets ?>/js/jquery-3.7.1.js"></script>
    <link rel="stylesheet" href="<?= $CFG->assets ?>/css/style.css">
    <link rel="stylesheet" href="<?= $CFG->assets ?>/css/jalalidatepicker.min.css">
    <script src="<?= $CFG->assets ?>/js/tailwindcss.js"></script>
    <script src="<?= $CFG->assets ?>/js/jalalidatepicker.min.js"></script>
<style>
:root {
    --bg: #f8f5f0;
    --bg-soft: #eef6f4;
    --ink: #1f2937;
    --accent: #0f766e;
    --accent-2: #f59e0b;
}
html, body {
    font-family: "Vazirmatn", system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", "Apple Color Emoji", "Segoe UI Emoji";
    color: var(--ink);
    margin: 0;
    padding: 0;
    min-height: 100%;
}
.glass-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.6);
    box-shadow: 0 10px 30px rgba(15, 118, 110, 0.08);
}
.loader-wrapper {
    position: fixed;
    inset: 0;
    z-index: 10000;
    background-color: #f8f5f0;
    display: flex;
    justify-content: center;
    align-items: center;
}
.loader {
    display: inline-block;
    width: 30px;
    height: 30px;
    position: relative;
    border: 4px solid #0f766e;
    animation: loader 2s infinite ease;
}
.loader-inner {
    vertical-align: top;
    display: inline-block;
    width: 100%;
    background-color: #0f766e;
    animation: loader-inner 2s infinite ease-in;
}
@keyframes loader {
    0% { transform: rotate(0deg); }
    25% { transform: rotate(180deg); }
    50% { transform: rotate(180deg); }
    75% { transform: rotate(360deg); }
    100% { transform: rotate(360deg); }
}
@keyframes loader-inner {
    0% { height: 0%; }
    25% { height: 0%; }
    50% { height: 100%; }
    75% { height: 100%; }
    100% { height: 0%; }
}
</style>
<script>
$(document).ready(function() {
    $(".loader-wrapper").fadeOut("slow");
});
(function() {
    function showLoader() {
        $(".loader-wrapper").stop(true, true).fadeIn(100);
    }
    function hideLoader() {
        $(".loader-wrapper").stop(true, true).fadeOut(300);
    }

    $(window).on('load', hideLoader);

    $(document).on('click', 'a[href]:not([target="_blank"]):not([href^="#"]):not([data-no-loader])', function() {
        const href = $(this).attr('href');
        try {
            const url = new URL(href, location.href);
            if (url.origin !== location.origin) return;
        } catch (err) {}
        showLoader();
    });

    (function(history) {
        const pushState = history.pushState;
        const replaceState = history.replaceState;
        history.pushState = function() {
            showLoader();
            return pushState.apply(history, arguments);
        };
        history.replaceState = function() {
            showLoader();
            return replaceState.apply(history, arguments);
        };
        window.addEventListener('popstate', function() {
            showLoader();
        });
    })(window.history);

    window.addEventListener('beforeunload', function() {
        showLoader();
    });
})();
</script>
</head>
<body class="text-slate-800">
<div class="fixed inset-0 -z-10">
    <div class="absolute inset-0 bg-gradient-to-b from-[#f8f5f0] via-[#f3f7f5] to-[#eef6f4]"></div>
    <div class="absolute inset-0 opacity-40" style="background-image: radial-gradient(rgba(15, 118, 110, 0.15) 1px, transparent 1px); background-size: 24px 24px;"></div>
</div>

<div class="loader-wrapper">
    <span class="loader"><span class="loader-inner"></span></span>
</div>

<header class="sticky top-0 z-50 bg-white/70 backdrop-blur-xl border-b border-white/60 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <a href="<?= $CFG->wwwroot ?>" class="font-extrabold text-xl sm:text-2xl flex items-center gap-3">
            <img src="<?= $CFG->siteiconurl ?>" alt="<?= $CFG->sitename ?>" class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl object-cover ring-2 ring-white/70 shadow-md">
            <span class="hidden sm:inline-block"><?= $CFG->sitename ?></span>
            <?php if ($activeTermName): ?>
                <span class="hidden lg:inline-flex items-center gap-2 text-xs font-bold px-3 py-1 rounded-full bg-teal-100 text-teal-700 border border-teal-200">
                    ترم فعال: <?= htmlspecialchars($activeTermName) ?>
                </span>
            <?php endif; ?>
        </a>

        <nav class="hidden md:flex items-center gap-5 text-base font-semibold">
            <?php if ($userRole === 'admin'): ?>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/dashboard">داشبورد</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/category">دسته‌ها</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/room">مکان‌ها</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/course">دوره‌ها</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/teacher">معلمان</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/term">ترم‌ها</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/program">چیدمان</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/enroll/admin">ثبت‌نام</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/settings">تنظیمات</a>
            <?php elseif ($userRole === 'teacher'): ?>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/courses">دروس من</a>
            <?php elseif ($userRole === 'student'): ?>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/enroll">ثبت‌نام</a>
            <?php else: ?>
                <a class="hover:bg-teal-700 text-white bg-teal-600 rounded-xl px-4 py-2 transition" href="<?= $MDL->wwwroot ?>/login">ورود</a>
            <?php endif; ?>
        </nav>

        <div class="flex items-center gap-3">
            <?php if ($userRole !== 'guest'): ?>
                <p class="text-base font-semibold text-slate-700 hidden sm:block"><?= htmlspecialchars($currentUserName) ?></p>
                <img src="<?= htmlspecialchars($currentUserImage) ?>" class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl object-cover hidden sm:block ring-2 ring-white/70 shadow-md" alt="User Profile">
            <?php endif; ?>
            <button id="mobileMenuToggle" type="button" class="md:hidden inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white/70 px-3 py-2 text-sm">منو</button>
        </div>
    </div>
    <nav id="mobileMenu" class="md:hidden hidden border-t border-white/70 bg-white/85 backdrop-blur-xl px-4 py-3">
        <div class="grid grid-cols-2 gap-2 text-sm font-semibold">
            <?php if ($userRole === 'admin'): ?>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/dashboard">داشبورد</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/category">دسته‌ها</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/room">مکان‌ها</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/course">دوره‌ها</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/teacher">معلمان</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/term">ترم‌ها</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/program">چیدمان</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/enroll/admin">ثبت‌نام</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50 col-span-2" href="<?= $CFG->wwwroot ?>/settings">تنظیمات</a>
            <?php elseif ($userRole === 'teacher'): ?>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/courses">دروس من</a>
            <?php elseif ($userRole === 'student'): ?>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/enroll">ثبت‌نام</a>
            <?php else: ?>
                <a class="rounded-xl px-3 py-2 text-white bg-teal-600 hover:bg-teal-700" href="<?= $MDL->wwwroot ?>/login">ورود</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<?php
$flash = Flash::get();
if (!$flash && !empty($_GET['msg'])) {
    $flash = [
        'message' => $_GET['msg'],
        'type' => ($_GET['type'] ?? 'info')
    ];
}
if (!empty($flash['message'])) {
    $flashType = $flash['type'] ?? 'info';
    $flashClass = 'bg-slate-100 text-slate-800 border-slate-200';
    if ($flashType === 'success') $flashClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
    if ($flashType === 'error') $flashClass = 'bg-rose-100 text-rose-800 border-rose-200';
    echo '<div id="global-flash" class="fixed top-20 right-6 z-[9999] px-4 py-3 rounded-2xl border ' . $flashClass . ' shadow-lg text-sm">';
    echo htmlspecialchars((string)$flash['message']);
    echo '</div>';
    echo '<script>setTimeout(function(){ $("#global-flash").fadeOut(300); }, 3500);</script>';
}
?>
<main class="app-main">
<script>
$(function() {
    $('#mobileMenuToggle').on('click', function() {
        $('#mobileMenu').toggleClass('hidden');
    });
});
</script>
