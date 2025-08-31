<?php
define('CLASSYAR_APP', true);
require_once("config/config.php");

session_name($CFG->mdlsessionname);
session_save_path($CFG->mdldataroot . '/sessions');
session_start();



// فعال کردن گزارش خطاهای PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$token = $MDL->token;
$domainname = $CFG;
$restformat = 'json';

// تابع مورد نظر برای دریافت اطلاعات کاربر
$functionname = 'core_user_get_users';
$serverurl = $domainname . '/webservice/rest/server.php?wstoken=' . $token . '&wsfunction=' . $functionname . '&moodlewsrestformat=' . $restformat;

// پارامترهای درخواست: جستجو بر اساس شناسه کاربری (ID)
$users = array(
    'criteria' => array(
        array(
            'key' => 'id', // کلید جستجو: شناسه کاربری
            'value' => $_SESSION['USER']->id // مقدار مورد نظر: شناسه کاربر مورد نظر (مثلاً 2)
        )
    )
);

// ارسال درخواست با cURL
$ch = curl_init($serverurl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($users));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

// بررسی خطاهای cURL
if (curl_errno($ch)) {
    echo 'خطای cURL: ' . curl_error($ch);
}

curl_close($ch);

// نمایش خروجی خام و رمزگشایی‌شده
echo "<h2>خروجی خام</h2>";
echo "<pre>";
echo htmlspecialchars($response);
echo "</pre>";

$result = json_decode($response, true);
echo "<h2>خروجی رمزگشایی‌شده (JSON)</h2>";
echo "<pre>";
print_r($result);
echo "</pre>";