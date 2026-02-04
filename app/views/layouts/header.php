<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

global $CFG, $MDL, $MSG;
require_once __DIR__ . '/../../models/term.php';

$msg = $msg ?? NULL;

$subtitle = $subtitle ?? $CFG->sitedescription;

$userRole = $_SESSION['USER']->role ?? 'guest';
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
    <!-- OpenGraph Porotol -->
    <meta property="og:title" content="<?= $CFG->sitetitle . ' | ' . $subtitle ?>" />
    <meta property="og:description" content="<?= $subtitle ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= $CFG->wwwroot ?>" />
    <meta property="og:image" content="<?= $CFG->assets ?>/images/og-image.png" />

    <link rel="icon" type="image/x-icon" href="<?= $CFG->siteiconurl ?>">
    <script src="<?= $CFG->assets ?>/js/jquery-3.7.1.js"></script>
    <link rel="stylesheet" href="<?= $CFG->assets ?>/css/style.css">
    <link rel="stylesheet" href="<?= $CFG->assets ?>/css/jalalidatepicker.min.css">
    <!-- <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;600;800&display=swap" rel="stylesheet"> -->
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
            font-family: "Vazirmatn", system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", "Apple Color Emoji","Segoe UI Emoji"; 
            color: var(--ink);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 10px 30px rgba(15, 118, 110, 0.08);
        }
    </style>
</head>
<body class="text-slate-800">

<div class="fixed inset-0 -z-10">
    <div class="absolute inset-0 bg-gradient-to-b from-[#f8f5f0] via-[#f3f7f5] to-[#eef6f4]"></div>
    <div class="absolute inset-0 opacity-40" style="background-image: radial-gradient(rgba(15, 118, 110, 0.15) 1px, transparent 1px); background-size: 24px 24px;"></div>
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
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/category">دسته‌ها</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/room">مکان‌ها</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/course">دوره‌ها</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/teacher">معلمان</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/term">ترم‌ها</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/program">چیدمان</a>
            <?php elseif ($userRole === 'teacher'): ?>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/courses">دروس من</a>
            <?php elseif ($userRole === 'student'): ?>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/dashboard">پنل دانش‌آموز</a>
            <?php else: ?>
                <a class="hover:bg-teal-700 text-white bg-teal-600 rounded-xl px-4 py-2 transition" href="<?= $MDL->wwwroot ?>/login">ورود</a>
            <?php endif; ?>
        </nav>
        <div class="flex items-center gap-3">
            <p class="text-base font-semibold text-slate-700 hidden sm:block"><?= $_SESSION['USER']->fullname ?></p>
            <img src="<?= $_SESSION['USER']->profileimage ?>" class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl object-cover hidden sm:block ring-2 ring-white/70 shadow-md" alt="User Profile">
            <button class="md:hidden inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white/70 px-3 py-2 text-sm">منو</button>
        </div>
    </div>
</header>
