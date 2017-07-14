<?php
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: GET, POST, REQUEST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Auth-Token, X-Requested-With, X-Xsrf-Token');

use Illuminate\Support\Facades\Log;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Moonlight\Middleware\GuestMiddleware;
use Moonlight\Middleware\AuthMiddleware;
use Moonlight\Middleware\MobileMiddleware;
use Moonlight\Middleware\HistoryMiddleware;
use Moonlight\Main\LoggedUser;
use Moonlight\Main\Element;
use Moonlight\Utils\UserJwtCodec;

Route::group(['prefix' => 'moonlight/api'], function() {
    Route::get('/', ['as' => 'home', 'uses' => 'Moonlight\Controllers\HomeController@show']);
    
    Route::post('auth', 'Moonlight\Controllers\UserController@auth');
    
    Route::post('login', 'Moonlight\Controllers\LoginController@login');
    
    Route::post('restore', 'Moonlight\Controllers\LoginController@restore');
    
    Route::group(['middleware' => [
        AuthMiddleware::class,
    ]], function () {
        Route::get('token', 'Moonlight\Controllers\UserController@token');
        
        Route::get('parameters', 'Moonlight\Controllers\ProfileController@parameters');
        
        Route::get('profile', 'Moonlight\Controllers\ProfileController@edit');
        
        Route::post('profile', 'Moonlight\Controllers\ProfileController@save');
        
        Route::post('password', 'Moonlight\Controllers\ProfileController@savePassword');
       
        Route::get('groups', 'Moonlight\Controllers\GroupController@groups');
        
        Route::post('groups', 'Moonlight\Controllers\GroupController@add');

        Route::get('groups/{id}', 'Moonlight\Controllers\GroupController@group')->
            where('id', '[0-9]+');

        Route::post('groups/{id}', 'Moonlight\Controllers\GroupController@save')->
            where('id', '[0-9]+');

        Route::delete('groups/{id}', 'Moonlight\Controllers\GroupController@delete')->
            where('id', '[0-9]+');
        
        Route::get('groups/permissions/items/{id}', 'Moonlight\Controllers\PermissionController@itemPermissions')->
            where('id', '[0-9]+');
        
        Route::post('groups/permissions/items/{id}', 'Moonlight\Controllers\PermissionController@saveItemPermission')->
            where('id', '[0-9]+');
        
        Route::get('groups/permissions/elements/{id}/{class}', 'Moonlight\Controllers\PermissionController@elementPermissions')->
            where('id', '[0-9]+');
        
        Route::post('groups/permissions/elements/{id}', 'Moonlight\Controllers\PermissionController@saveElementPermission')->
            where('id', '[0-9]+');

        Route::get('users', 'Moonlight\Controllers\UserController@users');

        Route::get('users/{id}', 'Moonlight\Controllers\UserController@user')->
            where('id', '[0-9]+');

        Route::post('users', 'Moonlight\Controllers\UserController@add');

        Route::post('users/{id}', 'Moonlight\Controllers\UserController@save')->
            where('id', '[0-9]+');

        Route::delete('users/{id}', 'Moonlight\Controllers\UserController@delete')->
            where('id', '[0-9]+');
        
        Route::get('log/form', 'Moonlight\Controllers\LogController@form');
        
        Route::get('log', 'Moonlight\Controllers\LogController@log');
        
        Route::get('favorites', 'Moonlight\Controllers\HomeController@favorites');
        
        Route::post('favorite', 'Moonlight\Controllers\HomeController@favorite');
        
        Route::get('tree/{classId?}', 'Moonlight\Controllers\TreeController@node');

        Route::post('tree/open/{classId}', 'Moonlight\Controllers\TreeController@open');

        Route::post('tree/close/{classId}', 'Moonlight\Controllers\TreeController@close');
        
        Route::get('search/items', 'Moonlight\Controllers\SearchController@items');
        
        Route::get('search/items/first', 'Moonlight\Controllers\SearchController@first');
        
        Route::get('search/items/{class}', 'Moonlight\Controllers\SearchController@item');
        
        Route::post('search/active/{class}/{name}', 'Moonlight\Controllers\SearchController@active');
        
        Route::get('search/list', 'Moonlight\Controllers\SearchController@elements');
        
        Route::get('trash/items', 'Moonlight\Controllers\TrashController@items');
        
        Route::get('trash/items/first', 'Moonlight\Controllers\TrashController@first');
        
        Route::get('trash/count/{class}', 'Moonlight\Controllers\TrashController@count');
        
        Route::get('trash/items/{class}', 'Moonlight\Controllers\TrashController@item');
        
        Route::post('trash/active/{class}/{name}', 'Moonlight\Controllers\TrashController@active');
        
        Route::get('trash/list', 'Moonlight\Controllers\TrashController@elements');
        
        Route::get('/elements/count', 'Moonlight\Controllers\BrowseController@count');
        
        Route::get('elements/list', 'Moonlight\Controllers\BrowseController@elements');
        
        Route::get('elements/open', 'Moonlight\Controllers\BrowseController@open');
        
        Route::get('elements/close', 'Moonlight\Controllers\BrowseController@close');
        
        Route::get('elements/autocomplete', 'Moonlight\Controllers\BrowseController@autocomplete');
        
        Route::get('elements/{classId}', 'Moonlight\Controllers\BrowseController@element');
        
        Route::get('browse/root', 'Moonlight\Controllers\BrowseController@root');
        
        Route::get('browse/{classId}', 'Moonlight\Controllers\BrowseController@browse')->
            where(['classId' => '[A-Za-z0-9\.]+']);
        
        Route::get('create/{classId}/{class}', 'Moonlight\Controllers\EditController@create');
        
        Route::post('add/{class}', 'Moonlight\Controllers\EditController@add');
        
        Route::get('edit/{classId}', 'Moonlight\Controllers\EditController@edit');
        
        Route::post('edit/{classId}', 'Moonlight\Controllers\EditController@save');
        
        Route::delete('edit/{classId}', 'Moonlight\Controllers\EditController@delete');
    });
});