<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>Classyar</title>
    <link rel="stylesheet" href="<?= $CFG->assets ?>/css/style.css">
</head>
<body>
    <nav>
        <?php $role = $_SESSION['role'] ?? 'guest'; ?>
        <?php if ($role === 'admin'): ?>
            <a href="<?= $CFG->wwwroot ?>/categories">مدیریت دسته‌ها</a> |
            <a href="<?= $CFG->wwwroot ?>/users">مدیریت کاربران</a>
        <?php elseif ($role === 'teacher'): ?>
            <a href="<?= $CFG->wwwroot ?>/courses">دروس من</a>
        <?php elseif ($role === 'student'): ?>
            <a href="<?= $CFG->wwwroot ?>/dashboard">پنل دانش‌آموز</a>
        <?php else: ?>
            <a href="<?= $CFG->wwwroot ?>/login">ورود</a>
        <?php endif; ?>
    </nav>
<hr>
