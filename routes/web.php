<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/conversation/{userId}', 'MessageController@conversation')->name('message.conversation');
Route::post('send-message', 'MessageController@sendmessage')->name('message.send-message');

Route::resource('group-message', 'GroupMessageController');
Route::post('send-group-message', 'MessageController@send_group_message')->name('message.send-group-message');