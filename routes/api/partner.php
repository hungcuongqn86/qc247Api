<?php
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'v1'], function () {
        // API Routes Sim
        Route::group(['prefix' => 'mpartner', 'namespace' => 'Modules\Partner\Http\Controllers\V1'], function () {
            Route::group(['prefix' => 'partner'], function () {
                Route::get('/search', 'PartnerController@search');
                Route::get('/detail/{id}', 'PartnerController@detail');
                Route::post('/create', 'PartnerController@create');
                Route::put('/update', 'PartnerController@update');
                Route::delete('/delete', 'PartnerController@delete');
            });
        });
    });
});
