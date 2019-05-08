<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'shop', 'namespace' => 'Modules\Shop\Http\Controllers'], function () {
        Route::get('/search', 'ShopController@search');
        Route::get('/detail/{id}', 'ShopController@detail');
        Route::post('/create', 'ShopController@create')->middleware('cors');
    });
});

