<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

Router::get('', 'my@index');
Router::get('dashboard', 'dashboard@index');


Router::get('category', 'categories@index');
Router::post('category/new', 'categories@store');
Router::post('category/edit/{id}', 'categories@update');
Router::post('category/delete/{id}', 'categories@delete');



Router::get('room', 'rooms@index');
Router::post('room/new', 'rooms@store');
Router::post('room/edit/{id}', 'rooms@update');
Router::post('room/delete/{id}', 'rooms@delete');



Router::get('course', 'courses@index');
Router::post('course/new', 'courses@store');
Router::post('course/edit/{id}', 'courses@update');
Router::post('course/delete/{id}', 'courses@delete');



Router::get('teacher/show/{id}', 'teachers@index');
Router::get('teacher/show', 'teachers@index');
Router::get('teacher', 'teachers@index');
Router::get('panel', 'teachers@dashboard');
Router::get('classroom/{id}', 'teachers@classView');
Router::get('classroom/{id}/csv', 'teachers@classCsv');
Router::get('teacher/edit/{id}', 'teachers@edit');
Router::get('teacher/edit', 'teachers@edit');
Router::post('teacher/edit/{id}', 'teachers@update');
Router::post('teacher/edit_times', 'teachers@editTimes');
Router::post('teacher/assign_course', 'teachers@assignCourse');
Router::post('teacher/remove_course', 'teachers@removeCourse');
Router::get('prints', 'teachers@printList');
Router::get('prints/class/{id}', 'teachers@printClassList');
Router::get('prints/{id}', 'teachers@printList');
Router::get('teacher/print', 'teachers@printList');
Router::get('teacher/print/class/{id}', 'teachers@printClassList');
Router::get('teacher/print/{id}', 'teachers@printList');

Router::get('term', 'terms@index');
Router::get('term/show/{id}', 'terms@index');
Router::get('term/show', 'terms@index');
Router::post('term/new', 'terms@store');
Router::post('term/edit/{id}', 'terms@update');
Router::post('term/delete/{id}', 'terms@delete');
Router::post('term/context/reset', 'terms@resetContext');
Router::post('term/context/{id}', 'terms@switchContext');

Router::get('program', 'program@index');
Router::get('program/export', 'program@exportCsv');
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
Router::get('enroll/admin/export', 'enrolls@exportAdminCsv');
Router::get('enroll/admin/student/{id}', 'enrolls@adminStudent');
Router::post('enroll/admin/student/{id}', 'enrolls@adminStudent');
Router::get('enroll/admin/student/{id}/{time}', 'enrolls@adminStudent');
Router::post('enroll/admin/student/{id}/{time}', 'enrolls@adminStudent');
Router::get('enroll', 'enrolls@index');
Router::post('enroll', 'enrolls@index');
Router::get('enroll/{time}', 'enrolls@index');
Router::post('enroll/{time}', 'enrolls@index');



Router::get('users', 'users@index');
Router::get('users/unregistered', 'users@showUnregisteredMdlUsers');
Router::post('users/add', 'users@addUser');
Router::post('users/role', 'users@changeRole');
Router::post('users/suspend', 'users@toggleSuspend');
Router::post('users/bulk', 'users@bulkUpdate');


Router::get('settings', 'settings@index');
Router::post('settings', 'settings@index');


