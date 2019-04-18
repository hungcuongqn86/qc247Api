<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'v1'], function () {
        // API Routes Sim
        Route::group(['prefix' => 'bankaccount', 'namespace' => 'Modules\Common\Http\Controllers'], function () {
            Route::get('/search', 'BankAccountController@search');
            Route::get('/detail/{id}', 'BankAccountController@detail');
        });
    });
});
