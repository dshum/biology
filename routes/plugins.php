<?php

Route::group(['prefix' => 'moonlight/api'], function() {
	Route::get('/plugins/welcome', ['uses' => 'Plugins\WelcomeController@index']);

	Route::get('/plugins/testloader', ['uses' => 'Plugins\TestLoaderController@index']);
	Route::post('/plugins/testloader', ['uses' => 'Plugins\TestLoaderController@load']);

	Route::get('/plugins/answers/{questionId}', ['uses' => 'Plugins\AnswersController@index']);
	Route::post('/plugins/answers/{answerId}', ['uses' => 'Plugins\AnswersController@setCorrect']);
});
