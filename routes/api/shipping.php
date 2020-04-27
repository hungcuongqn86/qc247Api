<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::group(['prefix' => 'shipping', 'namespace' => 'Modules\Order\Http\Controllers'], function () {
            Route::get('/search', 'ShippingController@search');
            Route::get('/myorder', 'ShippingController@myOrder');
            Route::post('/create', 'ShippingController@create');
            Route::put('/update', 'ShippingController@update');
            Route::get('/detail/{id}', 'ShippingController@detail');
        });
    });
});

