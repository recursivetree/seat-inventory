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



    //ui routes

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


    //locations
    Route::get('/location/lookup', [
        'as'   => 'inventory.locationLookup',
        'uses' => 'InventoryController@locationLookup',
        'middleware' => 'can:inventory.view_inventory'
    ]);


    //category routes

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



    //stock routes

    Route::post('/stocks/delete', [
        'as'   => 'inventory.deleteStock',
        'uses' => 'InventoryController@deleteStock',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    //hard coded url
    Route::get('/stocks/icon/{id}', [
        'as'   => 'inventory.stockIcon',
        'uses' => 'InventoryController@stockIcon',
        'middleware' => ['can:inventory.view_inventory','\Illuminate\Http\Middleware\SetCacheHeaders:public;max_age=604800;etag']
    ]);

    Route::post('/stocks/save', [
        'as'   => 'inventory.saveStock',
        'uses' => 'InventoryController@saveStock',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::get('/stocks/lookup', [
        'as'   => 'inventory.stockSuggestion',
        'uses' => 'InventoryController@stockSuggestion',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::post('/stocks/items/', [
        'as'   => 'inventory.exportItems',
        'uses' => 'InventoryController@exportItems',
        'middleware' => 'can:inventory.view_inventory'
    ]);



    //seat-fitting related routes
    Route::get('/doctrines/lookup', [
        'as'   => 'inventory.doctrineLookup',
        'uses' => 'InventoryController@doctrineLookup',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/fittings/lookup', [
        'as'   => 'inventory.fittingsLookup',
        'uses' => 'InventoryController@fittingsLookup',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    //tracking routes

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



    //Delivery Routes
    Route::post('/deliveries/add', [
        'as'   => 'inventory.addDeliveries',
        'uses' => 'DeliveriesController@addDeliveries',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::post('/deliveries/list', [
        'as'   => 'inventory.listDeliveries',
        'uses' => 'DeliveriesController@listDeliveries',
        'middleware' => 'can:inventory.edit_inventory'
    ]);

    Route::post('/deliveries/remove', [
        'as'   => 'inventory.deleteDeliveries',
        'uses' => 'DeliveriesController@deleteDeliveries',
        'middleware' => 'can:inventory.edit_inventory'
    ]);



    //Legacy routes

    Route::get('/legacy/locations/suggestions', [
        'as'   => 'inventory.legacyLocationSuggestions',
        'uses' => 'LegacyController@locationSuggestions',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/legacy/itembrowser', [
        'as'   => 'inventory.itemBrowser',
        'uses' => 'LegacyController@itemBrowser',
        'middleware' => 'can:inventory.view_inventory'
    ]);

    Route::get('/legacy/items/suggestions', [
        'as'   => 'inventory.itemTypeSuggestions',
        'uses' => 'LegacyController@itemTypeSuggestions',
        'middleware' => 'can:inventory.view_inventory'
    ]);
});