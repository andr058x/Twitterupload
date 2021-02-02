<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'App\Http\Controllers\IndexController@index');
Route::get('/login', 'App\Http\Controllers\IndexController@login');
Route::get('/auth', 'App\Http\Controllers\IndexController@auth');

Route::post('/create-tweet', 'App\Http\Controllers\IndexController@createTweet');

Route::get('/logout', function() {
    session()->flush();
    return view('login');
});
