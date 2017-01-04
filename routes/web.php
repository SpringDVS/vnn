<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('vnode');
});

Route::get('/virt/local/vn1/spring/', function () {
	return view('vn1');
});

Route::post('/virt/local/vn1/spring/', function () {
	return view('vn1');
});

Route::get('/dash/', 'DashboardController@overview');
Route::get('/bulletin/', 'BulletinController@overview');