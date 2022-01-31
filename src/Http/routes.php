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

    Route::get('/tracking', [
        'as'   => 'inventory.tracking',
        'uses' => 'InventoryController@tracking',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::post('/tracking/corporations/add', [
        'as'   => 'inventory.addTrackingCorporation',
        'uses' => 'InventoryController@addTrackingCorporation',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::post('/tracking/alliances/add', [
        'as'   => 'inventory.addTrackingAlliance',
        'uses' => 'InventoryController@addTrackingAlliance',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::post('/tracking/corporations/delete', [
        'as'   => 'inventory.deleteTrackingCorporation',
        'uses' => 'InventoryController@deleteTrackingCorporation',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::post('/tracking/alliances/delete', [
        'as'   => 'inventory.deleteTrackingAlliance',
        'uses' => 'InventoryController@deleteTrackingAlliance',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::get('/tracking/corporations/suggestions', [
        'as'   => 'inventory.trackingCorporationSuggestions',
        'uses' => 'InventoryController@trackingCorporationSuggestions',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/tracking/alliances/suggestions', [
        'as'   => 'inventory.trackingAllianceSuggestions',
        'uses' => 'InventoryController@trackingAllianceSuggestions',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/locations/suggestions', [
        'as'   => 'inventory.locationSuggestions',
        'uses' => 'InventoryController@locationSuggestions',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/stocks/plugin/fittings/suggestions', [
        'as'   => 'inventory.fittingPluginFittingsSuggestions',
        'uses' => 'InventoryController@fittingPluginFittingsSuggestions',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/stocks', [
        'as'   => 'inventory.stocks',
        'uses' => 'InventoryController@stocks',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::post('/stocks/save', [
        'as'   => 'inventory.saveStock',
        'uses' => 'InventoryController@saveStockPost',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::get('/stocks/view/{id}', [
        'as'   => 'inventory.viewStock',
        'uses' => 'InventoryController@viewStock',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/stocks/edit/{id}', [
        'as'   => 'inventory.editStock',
        'uses' => 'InventoryController@editStock',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::get('/stocks/new', [
        'as'   => 'inventory.newStock',
        'uses' => 'InventoryController@newStock',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::post('/stocks/delete/{id}', [
        'as'   => 'inventory.deleteStock',
        'uses' => 'InventoryController@deleteStockPost',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::get('/stocks/availability', [
        'as'   => 'inventory.stockAvailability',
        'uses' => 'InventoryController@stockAvailability',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/stocks/suggestions', [
        'as'   => 'inventory.stockSuggestions',
        'uses' => 'InventoryController@stockSuggestions',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/itembrowser', [
        'as'   => 'inventory.itemBrowser',
        'uses' => 'InventoryController@itemBrowser',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/items/suggestions', [
        'as'   => 'inventory.itemTypeSuggestions',
        'uses' => 'InventoryController@itemTypeSuggestions',
        'middleware' => 'can:inventory.view_inventory'
    ]);
});