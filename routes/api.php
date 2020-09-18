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

//Setting
Route::group(['namespace' => 'Api'], function() {
    //Auth
    Route::group(['prefix' => 'auth'], function() {
        Route::post('register', 'AuthController@register');
        Route::post('login', 'AuthController@login');
        Route::get('logout', 'AuthController@logout')->middleware('jwt.verify');
        Route::get('profile', 'AuthController@profile')->middleware('jwt.verify');
    });

    //Aset
    Route::group(['prefix' => 'aset', 'middleware' => ['jwt.verify']], function() { 
        Route::get('getdata', 'AsetController@getData');
        Route::post('insert', 'AsetController@insert');
        Route::get('show/{id}', 'AsetController@show');
        Route::put('update/{id}', 'AsetController@update');
        Route::delete('delete/{id}', 'AsetController@delete');
    });
});