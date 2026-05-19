<?php

/*
|--------------------------------------------------------------------------
| Advertising Module – Admin Routes
|--------------------------------------------------------------------------
|
| All routes in this file are loaded inside the ['web', 'auth', 'is_admin']
| middleware group defined in RouteServiceProvider.
|
*/

Route::prefix('admin/advertising')->group(function () {
    Route::get('/', 'AdminAdController@dashboard')->name('admin.ads.dashboard');

    // Banners
    Route::get('/banners', 'AdminAdController@banners')->name('admin.ads.banners');
    Route::post('/banners/{id}/approve', 'AdminAdController@approveBanner')->name('admin.ads.banners.approve');
    Route::post('/banners/{id}/reject', 'AdminAdController@rejectBanner')->name('admin.ads.banners.reject');
    Route::post('/banners/{id}/remove', 'AdminAdController@removeBanner')->name('admin.ads.banners.remove');

    // Links
    Route::get('/links', 'AdminAdController@links')->name('admin.ads.links');
    Route::post('/links/{id}/approve', 'AdminAdController@approveLink')->name('admin.ads.links.approve');
    Route::post('/links/{id}/reject', 'AdminAdController@rejectLink')->name('admin.ads.links.reject');

    // Payments
    Route::get('/payments', 'AdminPaymentController@index')->name('admin.ads.payments');
    Route::post('/payments/{id}/confirm', 'AdminPaymentController@confirm')->name('admin.ads.payments.confirm');
    Route::post('/payments/{id}/fail', 'AdminPaymentController@fail')->name('admin.ads.payments.fail');

    // Settings
    Route::get('/settings', 'AdminAdController@settings')->name('admin.ads.settings');
    Route::post('/settings', 'AdminAdController@saveSettings')->name('admin.ads.settings.save');
});
