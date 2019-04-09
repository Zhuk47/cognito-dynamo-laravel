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
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::post('/create-note', 'HomeController@create')->name('create');
Route::post('/delete-note', 'HomeController@delete')->name('delete');
Route::post('/update-note', 'HomeController@update')->name('update');
//Auth::routes();
//
//Route::get('/home', 'HomeController@index')->name('home');
