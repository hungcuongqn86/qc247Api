<?php

Route::group(['middleware' => 'web', 'prefix' => 'common', 'namespace' => 'modules\Common\Http\Controllers'], function()
{
    Route::get('/', 'CommonController@index');
});
