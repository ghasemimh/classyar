<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

global $CFG, $MDL, $MSG;

$msg = $msg ?? NULL;

$subtitle = $subtitle ?? $CFG->sitedescription;

$userRole = $_SESSION['USER']->role ?? 'guest';


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
    <link rel="stylesheet" href="<?= $CFG->assets ?>/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html, body { 
            font-family: "Vazirmatn", system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", "Apple Color Emoji","Segoe UI Emoji"; 
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">


<header class="sticky top-0 z-50 bg-white/80 backdrop-blur border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <a href="<?= $CFG->wwwroot ?>" class="font-extrabold text-xl"><img src="<?= $CFG->siteiconurl ?>" alt="<?= $CFG->sitename ?>" class="w-12 h-12 rounded-full object-cover"></a>
        <a href="<?= $CFG->wwwroot ?>" class="font-extrabold text-xl"><?= $CFG->sitename ?></a>
        <nav class="hidden md:flex items-center gap-6">            
            <?php if ($userRole === 'admin'): ?>
                <a class="hover:text-gray-900 text-gray-600" href="<?= $CFG->wwwroot ?>/category">مدیریت دسته‌ها</a>
                <a class="hover:text-gray-900 text-gray-600" href="<?= $CFG->wwwroot ?>/room">مدیریت مکان‌ها</a>
                <a class="hover:text-gray-900 text-gray-600" href="<?= $CFG->wwwroot ?>/course">مدیریت دوره‌ها</a>
            <?php elseif ($userRole === 'teacher'): ?>
                <a class="hover:text-gray-900 text-gray-600" href="<?= $CFG->wwwroot ?>/courses">دروس من</a>
            <?php elseif ($userRole === 'student'): ?>
                <a class="hover:text-gray-900 text-gray-600" href="<?= $CFG->wwwroot ?>/dashboard">پنل دانش‌آموز</a>
            <?php else: ?>
                <a class="hover:bg-gray-700 text-white bg-gray-900 rounded-xl px-4 py-2" href="<?= $MDL->wwwroot ?>/login">ورود</a>
            <?php endif; ?>
        </nav>
        <p class=""><?= $_SESSION['USER']->fullname ?></p>
        <img src="<?= $_SESSION['USER']->profileimage ?>" class="w-10 h-10 rounded-full object-cover md:block hidden" alt="User Profile">
        <button class="md:hidden inline-flex items-center justify-center rounded-xl border px-3 py-2">منو</button>
    </div>
</header>
