<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'order', 'namespace' => 'Modules\Cart\Http\Controllers'], function () {
        Route::get('/search', 'OrderController@search');
        Route::post('/create', 'OrderController@create');
    });
});

