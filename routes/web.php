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

use App\Test;

Route::group(['before' => 'guest'], function() {
	Route::get('/signup', ['as' => 'signup', 'uses' => 'SignupController@index']);
	Route::get('/login', ['as' => 'login', 'uses' => 'LoginController@index']);
	Route::post('/login', ['as' => 'login', 'uses' => 'LoginController@login']);
	Route::get('/restore', ['as' => 'restore', 'uses' => 'RestoreController@index']);
});

Route::group(['before' => 'auth'], function() {

	Route::get('/logout', ['as' => 'logout', 'uses' => 'LoginController@logout']);

	Route::get('/home', ['as' => 'home', 'uses' => 'HomeController@index']);

	Route::get('/test_{id}', ['as' => 'test', 'uses' => 'TestController@index']);
});

Route::get('/', ['as' => 'welcome', 'uses' => 'WelcomeController@index']);
