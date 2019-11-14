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

Route::group(['namespace' => 'Api'], function () {
    Route::group(['namespace' => 'Auth'], function () {
        Route::post('register', 'RegisterController');
        Route::post('login', 'LoginController');
        Route::post('logout', 'LogoutController')->middleware('auth:api');
    });
});
//отдаем список комнат(ленту)
Route::resource('/rooms', 'RoomsController');

Route::group(['namespace' => 'Auth'], function () {
    Route::get('/login/{provider}', 'SocialController@redirectToProvider');
    Route::get('/login/{provider}/callback', 'SocialController@handleProviderCallback');
    Route::get('/login/{provider}/getAuth', 'SocialController@getAuth');
});
Route::post('/upload_to_dropbox','DropboxController@uploadToDropboxFile');
Route::post('/search','RoomsController@search');
Route::post('/searchby', 'RoomsController@searchby');

Route::get('/dropbox', function () {
    $token = env('DROPBOX_TOKEN');
    $status = '200';
    $res = compact('token', 'status');
    return response()->json($res);
});
Route::post('/vkcallback', 'RoomsController@vkcallback');
