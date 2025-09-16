<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

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
$CFG->wwwroot        = 'http://localhost/moodle/app/classyar';   // آدرس پروژه
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
$CFG->sitetitle                    = 'کلاسیار'; // عنوان سایت
$CFG->sitename                    = 'کلاسیار'; // نام سایت  
$CFG->sitedescription              = 'سامانه مدیریت پویش'; // توضیحات سایت





unset($MDL);
global $MDL;

$MDL = new stdClass();


$MDL->wwwroot           = 'http://localhost/moodle';          // فولدر اصلی مودل
$MDL->dataroot          = 'C:\\xampp\\moodledata';            // مسیر دیتاهای مودل
$MDL->sessionpath       = $MDL->dataroot . '/sessions';       // مسیر سشن‌های مودل
$MDL->sessionname       = 'MoodleSession';                    // نام سشن‌های مودل
$MDL->token             = '113a72382e2a4b4ff41baea9000bc9a6'; // توکن مودل برای API ها

$MDL->getUsers          = 'core_user_get_users';              // search for users matching the parameters
$MDL->createUsers       = 'core_user_create_users';           // Create users
$MDL->createCourses     = 'core_course_create_courses';       // Create new courses
$MDL->getCourses        = 'core_course_get_courses';          // Return course details
$MDL->getCoursesByField = 'core_course_get_courses_by_field'; // Get courses matching a specific field (id/s, shortname, idnumber, category)
$MDL->updateCourses     = 'core_course_update_courses';       // Update courses
$MDL->getEnrolledUsers  = 'core_enrol_get_enrolled_users';    // Get enrolled users by course id
$MDL->enrollUsers       = 'enrol_manual_enrol_users';         // Manual enrol users
$MDL->unenrolUsers      = 'enrol_manual_unenrol_users';       // Manual unenrol users










unset($MSG);
global $MSG;

$MSG = new stdClass();

$MSG->pagenotfound            = 'صفحه‌ای که به دنبال آن بودید پیدا نشد!';

$MSG->notallowed              = 'دسترسی رد شد! شما اجازۀ دسترسی به این صفحه را ندارید.';
$MSG->idnotgiven              = 'یک مقدار ضروری {id} داده نشده!';
$MSG->badrequest              = 'درخواست نامعتبر! لطفاً دوباره تلاش کنید.';

$MSG->categorynotfound        = 'دسته‌بندی پیدا نشد!';
$MSG->categorycreateerror     = 'خطا در ایجاد دسته‌بندی! لطفاً دوباره تلاش کنید.';
$MSG->categorycreated         = 'دسته‌بندی با موفقیت ایجاد شد.';
$MSG->categorynameerror       = 'نام دسته‌بندی نمی‌تواند خالی باشد!';
$MSG->categoryedited          = 'دسته‌بندی با موفقیت ویرایش شد.';
$MSG->categoryediterror       = 'خطا در ویرایش دسته‌بندی! لطفاً دوباره تلاش کنید.';
$MSG->categorydeleteconfirmationerror = 'برای حذف، باید نام دسته بندی را دقیقاً وارد کنید.';
$MSG->categorydeleted         = 'دسته‌بندی با موفقیت حذف شد.';
$MSG->categorydeleteerror    = 'خطا در حذف دسته‌بندی! لطفاً دوباره تلاش کنید.';


$MSG->roomnotfound        = 'مکان پیدا نشد!';
$MSG->roomcreateerror     = 'خطا در ایجاد مکان! لطفاً دوباره تلاش کنید.';
$MSG->roomcreated         = 'مکان با موفقیت ایجاد شد.';
$MSG->roomnameerror       = 'نام مکان نمی‌تواند خالی باشد!';
$MSG->roomedited          = 'مکان با موفقیت ویرایش شد.';
$MSG->roomediterror       = 'خطا در ویرایش مکان! لطفاً دوباره تلاش کنید.';
$MSG->roomdeleteconfirmationerror = 'برای حذف، باید نام مکان را دقیقاً وارد کنید.';
$MSG->roomdeleted         = 'مکان با موفقیت حذف شد.';
$MSG->roomdeleteerror    = 'خطا در حذف مکان! لطفاً دوباره تلاش کنید.';