<?php

Route::group([
    'namespace'  => 'RecursiveTree\Seat\TerminusInventory\Http\Controllers',
    'middleware' => ['web', 'auth'],
    'prefix' => 'terminusinventory',
], function () {

    // Your route definitions go here.
    Route::get('/', [
        'as'   => 'terminusinv.home',
        'uses' => 'TerminusInventoryController@home'
    ]);

    Route::get('/about', [
        'as'   => 'terminusinv.about',
        'uses' => 'TerminusInventoryController@about'
    ]);

    Route::get('/tracking', [
        'as'   => 'terminusinv.tracking',
        'uses' => 'TerminusInventoryController@tracking'
    ]);

    Route::post('/tracking/locations/add', [
        'as'   => 'terminusinv.addTrackingLocation',
        'uses' => 'TerminusInventoryController@addTrackingLocation'
    ]);

    Route::post('/tracking/corporations/add', [
        'as'   => 'terminusinv.addTrackingCorporation',
        'uses' => 'TerminusInventoryController@addTrackingCorporation'
    ]);

    Route::post('/tracking/locations/delete', [
        'as'   => 'terminusinv.deleteTrackingLocation',
        'uses' => 'TerminusInventoryController@deleteTrackingLocation'
    ]);

    Route::post('/tracking/corporations/delete', [
        'as'   => 'terminusinv.deleteTrackingCorporation',
        'uses' => 'TerminusInventoryController@deleteTrackingCorporation'
    ]);

    Route::get('/tracking/locations/suggestions', [
        'as'   => 'terminusinv.trackingLocationSuggestions',
        'uses' => 'TerminusInventoryController@trackingLocationSuggestions'
    ]);

    Route::get('/tracking/corporations/suggestions', [
        'as'   => 'terminusinv.trackingCorporationSuggestions',
        'uses' => 'TerminusInventoryController@trackingCorporationSuggestions'
    ]);

    Route::get('/tracking/corporations/suggestions', [
        'as'   => 'terminusinv.trackingCorporationSuggestions',
        'uses' => 'TerminusInventoryController@trackingCorporationSuggestions'
    ]);

    Route::get('/fittings/locations/suggestions', [
        'as'   => 'terminusinv.fittingStockLocationSuggestions',
        'uses' => 'TerminusInventoryController@fittingStockLocationSuggestions'
    ]);

    Route::get('/fittings/plugin/fittings/suggestions', [
        'as'   => 'terminusinv.fittingPluginFittingsSuggestions',
        'uses' => 'TerminusInventoryController@fittingPluginFittingsSuggestions'
    ]);

    Route::get('/fittings', [
        'as'   => 'terminusinv.fittings',
        'uses' => 'TerminusInventoryController@fittings'
    ]);

    Route::post('/fittings/add', [
        'as'   => 'terminusinv.addFitting',
        'uses' => 'TerminusInventoryController@addFittingPos'
    ]);
});