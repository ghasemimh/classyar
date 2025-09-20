<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

Router::get('', 'categories@create');
Router::get('category/show/{id}', 'categories@index');
Router::get('category/show', 'categories@index');
Router::get('category', 'categories@index');
Router::get('category/new', 'categories@create');
Router::post('category/new', 'categories@store');
Router::get('category/edit/{id}', 'categories@edit');
Router::get('category/edit', 'categories@edit');
Router::post('category/edit/{id}', 'categories@update');
Router::get('category/delete/{id}', 'categories@confirmDelete');
Router::post('category/delete/{id}', 'categories@delete');



Router::get('room/show/{id}', 'rooms@index');
Router::get('room/show', 'rooms@index');
Router::get('room', 'rooms@index');
Router::get('room/new', 'rooms@create');
Router::post('room/new', 'rooms@store');
Router::get('room/edit/{id}', 'rooms@edit');
Router::get('room/edit', 'rooms@edit');
Router::post('room/edit/{id}', 'rooms@update');
Router::get('room/delete/{id}', 'rooms@confirmDelete');
Router::post('room/delete/{id}', 'rooms@delete');



Router::get('course/show/{id}', 'courses@index');
Router::get('course/show', 'courses@index');
Router::get('course', 'courses@index');
Router::get('course/new', 'courses@create');
Router::post('course/new', 'courses@store');
Router::get('course/edit/{id}', 'courses@edit');
Router::get('course/edit', 'courses@edit');
Router::post('course/edit/{id}', 'courses@update');
Router::get('course/delete/{id}', 'courses@confirmDelete');
Router::post('course/delete/{id}', 'courses@delete');