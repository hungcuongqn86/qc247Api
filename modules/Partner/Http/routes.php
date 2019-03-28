<?php

Route::group(['middleware' => 'web', 'prefix' => 'partner', 'namespace' => 'Modules\Partner\Http\Controllers'], function()
{
    Route::get('/', 'PartnerController@index');
});
