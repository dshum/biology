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

Route::group(['before' => 'guest'], function() {
	Route::get('/register', ['as' => 'register', 'uses' => 'RegisterController@index']);
	Route::post('/register', ['as' => 'register', 'uses' => 'RegisterController@register']);

	Route::get('/register/success', ['as' => 'success', 'uses' => 'RegisterController@success']);
	Route::get('/register/activate', ['as' => 'activate', 'uses' => 'RegisterController@activate']);
	Route::get('/register/complete', ['as' => 'complete', 'uses' => 'RegisterController@complete']);

	Route::get('/login', ['as' => 'login', 'uses' => 'LoginController@index']);
	Route::post('/login', ['as' => 'login', 'uses' => 'LoginController@login']);

	Route::get('/restore', ['as' => 'restore', 'uses' => 'RestoreController@index']);
	Route::post('/restore', ['as' => 'restore', 'uses' => 'RestoreController@restore']);
});

Route::group(['before' => 'auth'], function() {
	Route::get('/logout', ['as' => 'logout', 'uses' => 'LoginController@logout']);

	Route::get('/home', ['as' => 'home', 'uses' => 'HomeController@index']);

	Route::get('/profile', ['as' => 'profile', 'uses' => 'HomeController@profile']);
	Route::post('/profile', ['as' => 'profile', 'uses' => 'HomeController@save']);

	Route::get('/test_{id}', ['as' => 'test', 'uses' => 'TestController@index']);
	Route::post('/test_{id}', ['as' => 'test', 'uses' => 'TestController@save']);
});

Route::get('/', ['as' => 'welcome', 'uses' => 'WelcomeController@index']);
