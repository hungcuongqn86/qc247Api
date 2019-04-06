<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::group(['prefix' => 'order', 'namespace' => 'Modules\Order\Http\Controllers'], function () {
            Route::get('/search', 'OrderController@search');
            Route::get('/myorder', 'OrderController@myOrder');
            Route::get('/status', 'OrderController@status');
            Route::post('/create', 'OrderController@create');
            Route::get('/detail/{id}', 'OrderController@detail');
        });
    });
});

