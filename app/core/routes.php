<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

Router::get('', 'categories@index');
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



// Router::post('product/update/{id}/{subid}', 'ProductController@update');