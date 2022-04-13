<?php

Route::group([
    'namespace'  => 'RecursiveTree\Seat\Inventory\Http\Controllers',
    'middleware' => ['web', 'auth'],
    'prefix' => 'inventory',
], function () {

    Route::get('/about', [
        'as'   => 'inventory.about',
        'uses' => 'InventoryController@about',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/dashboard', [
        'as'   => 'inventory.dashboard',
        'uses' => 'InventoryController@dashboard',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/dashboard/categories', [
        'as'   => 'inventory.getCategories',
        'uses' => 'InventoryController@getCategories',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/dashboard/filter/location/suggestions', [
        'as'   => 'inventory.mainFilterLocationSuggestions',
        'uses' => 'InventoryController@mainFilterLocationSuggestions',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::post('/categories/save', [
        'as'   => 'inventory.saveCategory',
        'uses' => 'InventoryController@saveCategory',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::post('/categories/delete', [
        'as'   => 'inventory.deleteCategory',
        'uses' => 'InventoryController@deleteCategory',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::post('/categories/stocks/remove', [
        'as'   => 'inventory.removeStockFromCategory',
        'uses' => 'InventoryController@removeStockFromCategory',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::get('/dashboard/stocks/suggestions', [
        'as'   => 'inventory.addStockSuggestion',
        'uses' => 'InventoryController@addStockSuggestion',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/tracking', [
        'as'   => 'inventory.tracking',
        'uses' => 'TrackingController@tracking',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::post('/tracking/corporations/add', [
        'as'   => 'inventory.addTrackingCorporation',
        'uses' => 'TrackingController@addTrackingCorporation',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::post('/tracking/alliances/add', [
        'as'   => 'inventory.addTrackingAlliance',
        'uses' => 'TrackingController@addTrackingAlliance',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::post('/tracking/corporations/delete', [
        'as'   => 'inventory.deleteTrackingCorporation',
        'uses' => 'TrackingController@deleteTrackingCorporation',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::post('/tracking/alliances/delete', [
        'as'   => 'inventory.deleteTrackingAlliance',
        'uses' => 'TrackingController@deleteTrackingAlliance',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::get('/tracking/corporations/suggestions', [
        'as'   => 'inventory.trackingCorporationSuggestions',
        'uses' => 'TrackingController@trackingCorporationSuggestions',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/tracking/alliances/suggestions', [
        'as'   => 'inventory.trackingAllianceSuggestions',
        'uses' => 'TrackingController@trackingAllianceSuggestions',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/locations/suggestions', [
        'as'   => 'inventory.locationSuggestions',
        'uses' => 'LegacyController@locationSuggestions',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/stocks/plugin/fittings/suggestions', [
        'as'   => 'inventory.fittingPluginFittingsSuggestions',
        'uses' => 'LegacyController@fittingPluginFittingsSuggestions',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/stocks', [
        'as'   => 'inventory.stocks',
        'uses' => 'LegacyController@stocks',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::post('/stocks/save', [
        'as'   => 'inventory.saveStock',
        'uses' => 'LegacyController@saveStockPost',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::get('/stocks/view/{id}', [
        'as'   => 'inventory.viewStock',
        'uses' => 'LegacyController@viewStock',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/stocks/edit/{id}', [
        'as'   => 'inventory.editStock',
        'uses' => 'LegacyController@editStock',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::get('/stocks/new', [
        'as'   => 'inventory.newStock',
        'uses' => 'LegacyController@newStock',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::get('/stocks/icon/{id}', [
        'as'   => 'inventory.stockIcon',
        'uses' => 'InventoryController@stockIcon',
        'middleware' => ['can:inventory.view_inventory','\Illuminate\Http\Middleware\SetCacheHeaders:public;max_age=604800;etag']
    ]);

    Route::post('/stocks/delete/{id}', [
        'as'   => 'inventory.deleteStock',
        'uses' => 'LegacyController@deleteStockPost',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::get('/stocks/availability', [
        'as'   => 'inventory.stockAvailability',
        'uses' => 'LegacyController@stockAvailability',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/stocks/suggestions', [
        'as'   => 'inventory.stockSuggestions',
        'uses' => 'LegacyController@stockSuggestions',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/itembrowser', [
        'as'   => 'inventory.itemBrowser',
        'uses' => 'LegacyController@itemBrowser',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/items/suggestions', [
        'as'   => 'inventory.itemTypeSuggestions',
        'uses' => 'LegacyController@itemTypeSuggestions',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/sources/moving', [
        'as'   => 'inventory.movingItems',
        'uses' => 'LegacyController@getMovingItems',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::post('/sources/moving/add', [
        'as'   => 'inventory.addMovingItems',
        'uses' => 'LegacyController@addMovingItems',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::post('/sources/moving/remove', [
        'as'   => 'inventory.removeMovingItems',
        'uses' => 'LegacyController@removeMovingItems',
        'middleware' => 'can:inventory.edit_inventory'
    ]);
});