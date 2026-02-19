<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

Router::get('', 'my@index');
Router::get('dashboard', 'dashboard@index');


Router::get('category/show/{id}', 'categories@index');
Router::get('category/show', 'categories@index');
Router::get('category', 'categories@index');
Router::post('category/new', 'categories@store');
Router::get('category/edit/{id}', 'categories@edit');
Router::get('category/edit', 'categories@edit');
Router::post('category/edit/{id}', 'categories@update');
Router::post('category/delete/{id}', 'categories@delete');



Router::get('room/show/{id}', 'rooms@index');
Router::get('room/show', 'rooms@index');
Router::get('room', 'rooms@index');
Router::post('room/new', 'rooms@store');
Router::get('room/edit/{id}', 'rooms@edit');
Router::get('room/edit', 'rooms@edit');
Router::post('room/edit/{id}', 'rooms@update');
Router::post('room/delete/{id}', 'rooms@delete');



Router::get('course/show/{id}', 'courses@index');
Router::get('course/show', 'courses@index');
Router::get('course', 'courses@index');
Router::post('course/new', 'courses@store');
Router::get('course/edit/{id}', 'courses@edit');
Router::get('course/edit', 'courses@edit');
Router::post('course/edit/{id}', 'courses@update');
Router::post('course/delete/{id}', 'courses@delete');



Router::get('teacher/show/{id}', 'teachers@index');
Router::get('teacher/show', 'teachers@index');
Router::get('teacher', 'teachers@index');
Router::get('teacher/edit/{id}', 'teachers@edit');
Router::get('teacher/edit', 'teachers@edit');
Router::post('teacher/edit/{id}', 'teachers@update');
Router::post('teacher/edit_times', 'teachers@editTimes');
Router::post('teacher/assign_course', 'teachers@assignCourse');
Router::post('teacher/remove_course', 'teachers@removeCourse');
Router::get('teacher/print', 'teachers@printList');
Router::get('teacher/print/class/{id}', 'teachers@printClassList');
Router::get('teacher/print/{id}', 'teachers@printList');

Router::get('term', 'terms@index');
Router::get('term/show/{id}', 'terms@index');
Router::get('term/show', 'terms@index');
Router::post('term/new', 'terms@store');
Router::post('term/edit/{id}', 'terms@update');
Router::post('term/delete/{id}', 'terms@delete');

Router::get('program', 'program@index');
Router::post('program/new', 'program@store');
Router::post('program/edit/{id}', 'program@update');
Router::post('program/delete/{id}', 'program@delete');
Router::post('program/sync_moodle', 'program@syncMoodle');
Router::post('program/sync_moodle_teachers', 'program@syncMoodleTeachers');
Router::post('program/sync_moodle_students', 'program@syncMoodleStudents');

Router::get('sync', 'sync@index');
Router::get('sync/data', 'sync@data');
Router::post('sync/run', 'sync@run');
Router::post('sync/run_bulk', 'sync@runBulk');
Router::post('sync/delete', 'sync@delete');

Router::get('enroll/admin', 'enrolls@admin');
Router::get('enroll/admin/student/{id}', 'enrolls@adminStudent');
Router::post('enroll/admin/student/{id}', 'enrolls@adminStudent');
Router::get('enroll/admin/student/{id}/{time}', 'enrolls@adminStudent');
Router::post('enroll/admin/student/{id}/{time}', 'enrolls@adminStudent');
Router::get('enroll', 'enrolls@index');
Router::post('enroll', 'enrolls@index');
Router::get('enroll/{time}', 'enrolls@index');
Router::post('enroll/{time}', 'enrolls@index');



Router::get('user/add/{role}/{mdl_id}', 'users@addUser');
Router::get('user/add/{mdl_id}', 'users@addUser');


Router::get('settings', 'settings@index');
Router::post('settings', 'settings@index');

// API
Router::get('api', '');

