<?php

/*
|--------------------------------------------------------------------------
| Advertising Module – Public Web Routes
|--------------------------------------------------------------------------
*/

Route::prefix('advertising')->group(function () {
    Route::get('/', 'PublicAdController@index')->name('ads.index');
    Route::get('/advertise', 'PublicAdController@advertise')->name('ads.advertise');

    Route::get('/order/banner', 'PublicAdController@orderBannerForm')->name('ads.order.banner');
    Route::post(
        '/order/banner',
        'PublicAdController@orderBannerPost'
    )->middleware('throttle:60,1')->name('ads.order.banner.post');

    Route::get('/order/link', 'PublicAdController@orderLinkForm')->name('ads.order.link');
    Route::post(
        '/order/link',
        'PublicAdController@orderLinkPost'
    )->middleware('throttle:60,1')->name('ads.order.link.post');

    Route::get('/pay', 'PublicAdController@pay')->name('ads.pay');
    Route::post('/pay', 'PublicAdController@payNoted')->name('ads.pay.noted');

    Route::get('/thank-you', 'PublicAdController@thankYou')->name('ads.thankyou');
});
