<?php

	use Illuminate\Http\Request;

	/*
	|--------------------------------------------------------------------------
	| API Routes
	|--------------------------------------------------------------------------
	|
	| Here is where you can register API routes for your application. These
	| routes are loaded by the RouteServiceProvider within a group which
	| is assigned the "api" middleware group. Enjoy building your API!
	|
	*/

	Route::middleware('auth:api')->get('/user', function (Request $request) {
		return $request->user();
	});

	Route::post('/calculatemoney', 'Api\GameApiController@calculateMoney');
	Route::post('/readyfornextgame', 'Api\GameApiController@readyForNextGame');
	Route::post('/cancelfornextgame', 'Api\GameApiController@cancelForNextGame');
	Route::get('/getuserinfo', 'Api\UserApiController@getUserinfo');
	Route::get('/getgameinfo', 'Api\GameApiController@getGameinfo');
	Route::post('/cancelcalculatemoney', 'Api\GameApiController@cancelCalculateMoney');