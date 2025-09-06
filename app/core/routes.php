<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

Router::get('category/show/{id}', 'categories@index');
Router::get('category/show', 'categories@index');
Router::get('category/new/{id}', 'categories@create');
Router::get('category/edit/{id}', 'categories@edit');