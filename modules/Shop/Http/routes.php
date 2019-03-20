<?php

Route::group(['middleware' => 'web', 'prefix' => 'shop', 'namespace' => 'Modules\Shop\Http\Controllers'], function()
{
    Route::get('/', 'ShopController@index');
});
