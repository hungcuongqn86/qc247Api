<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'v1'], function () {
        // API Routes Sim
        Route::group(['prefix' => 'setting', 'namespace' => 'Modules\Common\Http\Controllers'], function () {
            Route::get('/search', 'SettingController@search');
            Route::get('/detail/{id}', 'SettingController@detail');
            Route::post('/create', 'SettingController@create');
            Route::put('/update', 'SettingController@update');
        });
    });
});
