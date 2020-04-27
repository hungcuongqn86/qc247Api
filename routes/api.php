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

// API Routes System
Route::group(['prefix' => 'v1'], function () {
    Route::group(['namespace' => 'Modules\Common\Http\Controllers'], function () {
        Route::post('login', 'PassportController@login');
        Route::post('register', 'PassportController@register');
        Route::get('auth/signup/activate/{token}', 'PassportController@signupActivate');
        Route::post('permissions', 'PassportController@setPermissions');
        Route::group(['middleware' => 'auth:api'], function () {
            Route::get('checklogin', 'PassportController@getDetails');
            Route::get('getnav', 'PassportController@getPermissions');
        });
    });
    Route::group(['middleware' => 'auth:api'], function () {
        Route::group(['prefix' => 'media'], function () {
            Route::group(['namespace' => 'Modules\Common\Http\Controllers'], function () {
                Route::post('/upload', 'MediaController@upload');
                Route::delete('/delete', 'MediaController@delete');
            });
        });
    });
});

// API Routes Pet
require __DIR__ . '/api/shop.php';
require __DIR__ . '/api/cart.php';
require __DIR__ . '/api/shipping.php';
require __DIR__ . '/api/order.php';
require __DIR__ . '/api/complain.php';
require __DIR__ . '/api/partner.php';
require __DIR__ . '/api/user.php';
require __DIR__ . '/api/bank_account.php';
require __DIR__ . '/api/setting.php';