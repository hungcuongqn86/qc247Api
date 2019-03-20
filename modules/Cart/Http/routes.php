<?php

Route::group(['middleware' => 'web', 'prefix' => 'cart', 'namespace' => 'Modules\Cart\Http\Controllers'], function()
{
    Route::get('/', 'CartController@index');
});
