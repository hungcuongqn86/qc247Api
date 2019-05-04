<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::group(['prefix' => 'order', 'namespace' => 'Modules\Order\Http\Controllers'], function () {
            Route::get('/search', 'OrderController@search');
            Route::get('/myorder', 'OrderController@myOrder');
            Route::get('/status', 'OrderController@status');
            Route::post('/create', 'OrderController@create');
            Route::post('/baogia', 'OrderController@baogia');
            Route::post('/datcoc', 'OrderController@datcoc');
            Route::get('/detail/{id}', 'OrderController@detail');
            Route::group(['prefix' => 'history'], function () {
                Route::get('/types', 'HistoryController@types');
                Route::post('/create', 'HistoryController@create');
            });
            Route::group(['prefix' => 'package'], function () {
                Route::post('/create', 'PackageController@create');
                Route::post('/update', 'PackageController@update');
            });
            Route::group(['prefix' => 'complain'], function () {
                Route::get('/search', 'ComplainController@getByOrder');
                Route::get('/detail/{id}', 'ComplainController@detail');
                Route::get('/types', 'ComplainController@types');
                Route::post('/create', 'ComplainController@create');
                Route::post('/update', 'ComplainController@update');
            });
        });
    });
});

