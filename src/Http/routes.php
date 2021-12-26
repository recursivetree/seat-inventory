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

    Route::post('/tracking/corporations/add', [
        'as'   => 'terminusinv.addTrackingCorporation',
        'uses' => 'TerminusInventoryController@addTrackingCorporation'
    ]);

    Route::post('/tracking/corporations/delete', [
        'as'   => 'terminusinv.deleteTrackingCorporation',
        'uses' => 'TerminusInventoryController@deleteTrackingCorporation'
    ]);

    Route::get('/tracking/corporations/suggestions', [
        'as'   => 'terminusinv.trackingCorporationSuggestions',
        'uses' => 'TerminusInventoryController@trackingCorporationSuggestions'
    ]);

    Route::get('/stocks/locations/suggestions', [
        'as'   => 'terminusinv.stockLocationSuggestions',
        'uses' => 'TerminusInventoryController@stockLocationSuggestions'
    ]);

    Route::get('/stocks/plugin/fittings/suggestions', [
        'as'   => 'terminusinv.fittingPluginFittingsSuggestions',
        'uses' => 'TerminusInventoryController@fittingPluginFittingsSuggestions'
    ]);

    Route::get('/stocks', [
        'as'   => 'terminusinv.stocks',
        'uses' => 'TerminusInventoryController@stocks'
    ]);

    Route::post('/stocks/add', [
        'as'   => 'terminusinv.addStock',
        'uses' => 'TerminusInventoryController@addStockPost'
    ]);

    Route::get('/stocks/edit/{id}', [
        'as'   => 'terminusinv.editStock',
        'uses' => 'TerminusInventoryController@editStock'
    ]);

    Route::post('/stocks/delete/{id}', [
        'as'   => 'terminusinv.deleteStock',
        'uses' => 'TerminusInventoryController@deleteStockPost'
    ]);
});