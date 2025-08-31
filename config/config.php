<?php
// config/config.php

// جلوگیری از دسترسی مستقیم به فایل
if (!defined('CLASSYAR_APP')) {
    die('No direct access allowed!');
}

$CFG = new stdClass();

// تنظیمات دیتابیس
$CFG->dbtype = 'mysql';           // یا 'mariadb' اگر ماریادبی داری
$CFG->dbhost = 'localhost';
$CFG->dbname = 'classyar';        // اسم دیتابیس پروژه‌
$CFG->dbuser = 'root';            // یوزر دیتابیس
$CFG->dbpass = '';                // پسورد دیتابیس
$CFG->dbport = 3306;
$CFG->dbcharset = 'utf8mb4';
$CFG->dbcollation = 'utf8mb4_unicode_ci';

// تنظیمات مسیرها
$CFG->wwwroot        = 'http://localhost/app/classyar';   // آدرس پروژه
$CFG->dataroot       = __DIR__ . '/../data';              // مسیر فولدر data
$CFG->assets         = $CFG->wwwroot . '/assets';         // مسیر فایل‌های استاتیک
$CFG->sessionpath    = $CFG->dataroot . '/sessions';      // مسیر سشن‌ها
$CFG->sessionName    = 'classyar';                        // نام سشن‌های برنامه
$CFG->mdlroot        = 'http://localhost/moodle';         // فولدر اصلی مودل
$CFG->mdldataroot    = 'C:\\xampp\\moodledata';           // مسیر دیتاهای مودل
$CFG->mdlsessionname = 'MoodleSession';                   // نام سشن‌های مودل

// مجوز فولدرها
$CFG->directorypermissions = 0775;
