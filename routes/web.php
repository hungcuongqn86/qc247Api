<?php
// exit;
Route::group(['prefix' => 'maintainer', 'namespace' => 'Modules\Order\Http\Controllers'], function () {
	// Route::get('/order/status/count', 'CommentController@maintainerStatusCount');
	// Route::get('/order/comment/count', 'CommentController@maintainerCommentCount');
});

Route::group(['prefix' => 'order', 'namespace' => 'Modules\Order\Http\Controllers'], function () {
    Route::get('/download/{filename}', 'OrderController@download');
});
