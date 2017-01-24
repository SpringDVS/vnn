<?php

use App\Http\Controllers\SpringNodeController;

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

Route::any('/virt/{regional}/{spring}/spring/', 'SpringNodeController@spring');

Route::get('/frame/', function () {
		return view('frame');
});

Route::get('/frame/api/', function () {
		return view('frame_api');
});
	

Route::get('/dash/', 'DashboardController@overview');
Route::get('/bulletin/', 'BulletinController@overview');