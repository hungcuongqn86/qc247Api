<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::group(['prefix' => 'order', 'namespace' => 'Modules\Order\Http\Controllers'], function () {
            Route::get('/search', 'OrderController@search');
            Route::get('/export', 'OrderController@export');
            Route::get('/count', 'OrderController@countByStatus');
            Route::get('/comments', 'OrderController@comments');
            Route::get('/allcomments', 'OrderController@allcomments');
            Route::get('/mycount', 'OrderController@myCountByStatus');
            Route::get('/myorder', 'OrderController@myOrder');
            Route::get('/status', 'OrderController@status');
            Route::post('/create', 'OrderController@create');
            Route::put('/update', 'OrderController@update');
            Route::post('/baogia', 'OrderController@baogia');
            Route::post('/datcoc', 'OrderController@datcoc');
            Route::get('/detail/{id}', 'OrderController@detail');
            Route::group(['prefix' => 'history'], function () {
                Route::get('/types', 'HistoryController@types');
                Route::post('/create', 'HistoryController@create');
            });
            Route::group(['prefix' => 'package'], function () {
                Route::get('/search', 'PackageController@search');
                Route::get('/status', 'PackageController@status');
                Route::post('/create', 'PackageController@create');
                Route::post('/update', 'PackageController@update');
            });
            Route::group(['prefix' => 'warehouse'], function () {
                Route::get('/wait', 'WarehouseController@wait');
                Route::get('/bills', 'WarehouseController@bills');
                Route::get('/bill/status', 'WarehouseController@billStatus');
                Route::post('/bill/create', 'WarehouseController@billCreate');
                Route::post('/bill/delete', 'WarehouseController@billDelete');
                Route::post('/bill/confirm', 'WarehouseController@billConfirm');
                Route::get('/bill/detail/{id}', 'WarehouseController@billDetail');
            });
            Route::group(['prefix' => 'comment'], function () {
                Route::get('/getall', 'CommentController@getall');
                Route::post('/create', 'CommentController@create');
                Route::post('/isread', 'CommentController@isread');
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

