<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::group(['prefix' => 'complain', 'namespace' => 'Modules\Order\Http\Controllers'], function () {
            Route::get('/search', 'ComplainController@search');
            Route::get('/detail/{id}', 'ComplainController@detail');
            Route::get('/types', 'ComplainController@types');
        });
    });
});

