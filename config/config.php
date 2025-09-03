<?php
// config/config.php

// جلوگیری از دسترسی مستقیم به فایل
if (!defined('CLASSYAR_APP')) {
    die('No direct access allowed!');
}

require_once __DIR__ . '/../app/models/db.php';

unset($CFG);
global $CFG;
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
$CFG->sessionname    = 'classyar';                        // نام سشن‌های برنامه

// مجوز فولدرها
$CFG->directorypermissions = 0775;


// نام جدول ها
$CFG->categoriestable    = 'categories';
$CFG->classestable       = 'classes';
$CFG->coursestable       = 'courses';
$CFG->enrollstable       = 'enrolls';
$CFG->feedbackstable     = 'feedbacks';
$CFG->prerequisitestable = 'prerequisites';
$CFG->roomstable         = 'rooms';
$CFG->settingstable      = 'settings';
$CFG->studentstable      = 'students';
$CFG->teacherstable      = 'teachers';
$CFG->teacherclassstable = 'teacherClasses';
$CFG->termstable         = 'terms';
$CFG->userstable         = 'users';



// سایر تنظیمات
$CFG->defaultenglish               = 4110; // Eng STARTER
$CFG->yearofestablishmentiran      = 1334; // The year the school was founded in the iranian calendar
$CFG->yearofestablishmentgregorian = 1955; // The year the school was founded in the gregorian calendar





unset($MDL);
global $MDL;

$MDL = new stdClass();


$MDL->wwwroot     = 'http://localhost/moodle';          // فولدر اصلی مودل
$MDL->dataroot    = 'C:\\xampp\\moodledata';            // مسیر دیتاهای مودل
$MDL->sessionpath = $MDL->dataroot . '/sessions';       // مسیر سشن‌های مودل
$MDL->sessionname = 'MoodleSession';                    // نام سشن‌های مودل
$MDL->token       = '113a72382e2a4b4ff41baea9000bc9a6'; // توکن مودل برای API ها


$MDL->getUsers          = 'core_user_get_users';              // search for users matching the parameters
$MDL->createUsers       = 'core_user_create_users';           // Create users
$MDL->createCourses     = 'core_course_create_courses';       // Create new courses
$MDL->getCourses        = 'core_course_get_courses';          // Return course details
$MDL->getCoursesByField = 'core_course_get_courses_by_field'; // Get courses matching a specific field (id/s, shortname, idnumber, category)
$MDL->updateCourses     = 'core_course_update_courses';       // Update courses
$MDL->getEnrolledUsers  = 'core_enrol_get_enrolled_users';    // Get enrolled users by course id
$MDL->enrollUsers       = 'enrol_manual_enrol_users';         // Manual enrol users
$MDL->unenrolUsers      = 'enrol_manual_unenrol_users';       // Manual unenrol users
