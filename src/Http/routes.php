<?php

Route::group([
    'namespace'  => 'RecursiveTree\Seat\TerminusInventory\Http\Controllers',
    'middleware' => ['web', 'auth'],
    'prefix' => 'terminusinventory',
], function () {

    Route::get('/about', [
        'as'   => 'terminusinv.about',
        'uses' => 'TerminusInventoryController@about',
        'middleware' => 'can:terminusinv.view_inventory'
    ]);

    Route::get('/tracking', [
        'as'   => 'terminusinv.tracking',
        'uses' => 'TerminusInventoryController@tracking',
        'middleware' => 'can:terminusinv.view_inventory'
    ]);

    Route::post('/tracking/corporations/add', [
        'as'   => 'terminusinv.addTrackingCorporation',
        'uses' => 'TerminusInventoryController@addTrackingCorporation',
        'middleware' => 'can:terminusinv.edit_inventory'
    ]);

    Route::post('/tracking/corporations/delete', [
        'as'   => 'terminusinv.deleteTrackingCorporation',
        'uses' => 'TerminusInventoryController@deleteTrackingCorporation',
        'middleware' => 'can:terminusinv.edit_inventory'
    ]);

    Route::get('/tracking/corporations/suggestions', [
        'as'   => 'terminusinv.trackingCorporationSuggestions',
        'uses' => 'TerminusInventoryController@trackingCorporationSuggestions',
        'middleware' => 'can:terminusinv.view_inventory'
    ]);

    Route::get('/locations/suggestions', [
        'as'   => 'terminusinv.locationSuggestions',
        'uses' => 'TerminusInventoryController@locationSuggestions',
        'middleware' => 'can:terminusinv.view_inventory'
    ]);

    Route::get('/stocks/plugin/fittings/suggestions', [
        'as'   => 'terminusinv.fittingPluginFittingsSuggestions',
        'uses' => 'TerminusInventoryController@fittingPluginFittingsSuggestions',
        'middleware' => 'can:terminusinv.view_inventory'
    ]);

    Route::get('/stocks', [
        'as'   => 'terminusinv.stocks',
        'uses' => 'TerminusInventoryController@stocks',
        'middleware' => 'can:terminusinv.view_inventory'
    ]);

    Route::post('/stocks/add', [
        'as'   => 'terminusinv.addStock',
        'uses' => 'TerminusInventoryController@addStockPost',
        'middleware' => 'can:terminusinv.edit_inventory'
    ]);

    Route::get('/stocks/edit/{id}', [
        'as'   => 'terminusinv.editStock',
        'uses' => 'TerminusInventoryController@editStock',
        'middleware' => 'can:terminusinv.view_inventory'
    ]);

    Route::post('/stocks/delete/{id}', [
        'as'   => 'terminusinv.deleteStock',
        'uses' => 'TerminusInventoryController@deleteStockPost',
        'middleware' => 'can:terminusinv.edit_inventory'
    ]);

    Route::get('/stocks/availability', [
        'as'   => 'terminusinv.stockAvailability',
        'uses' => 'TerminusInventoryController@stockAvailability',
        'middleware' => 'can:terminusinv.view_inventory'
    ]);

    Route::get('/stocks/suggestions', [
        'as'   => 'terminusinv.stockSuggestions',
        'uses' => 'TerminusInventoryController@stockSuggestions',
        'middleware' => 'can:terminusinv.view_inventory'
    ]);

    Route::get('/itembrowser', [
        'as'   => 'terminusinv.itemBrowser',
        'uses' => 'TerminusInventoryController@itemBrowser',
        'middleware' => 'can:terminusinv.view_inventory'
    ]);

    Route::get('/items/suggestions', [
        'as'   => 'terminusinv.itemTypeSuggestions',
        'uses' => 'TerminusInventoryController@itemTypeSuggestions',
        'middleware' => 'can:terminusinv.view_inventory'
    ]);
});