<?php
// config/config.php

// جلوگیری از دسترسی مستقیم به فایل
if (!defined('CLASSYAR_APP')) {
    die('No direct access allowed!');
}

$MDL = new stdClass();

$MDL->getUsers          = 'core_user_get_users'              // search for users matching the parameters
$MDL->createUsers       = 'core_user_create_users'           // Create users
$MDL->createCourses     = 'core_course_create_courses'       // Create new courses
$MDL->getCourses        = 'core_course_get_courses'          // Return course details
$MDL->getCoursesByField = 'core_course_get_courses_by_field' // Get courses matching a specific field (id/s, shortname, idnumber, category)
$MDL->updateCourses     = 'core_course_update_courses'       // Update courses
$MDL->getEnrolledUsers  = 'core_enrol_get_enrolled_users'    // Get enrolled users by course id
