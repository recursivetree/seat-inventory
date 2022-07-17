<?php

return [
    'corporation_hangar' => [
        'pooled'=>true,
        'virtual'=>false,
        'name'=>'Corporation Hangars'
    ],
    'contract' => [
        'pooled'=>false,
        'virtual'=>false,
        'name'=>'Contracts'
    ],
    'in_transport' => [
        'pooled'=>true,
        'virtual'=>true,
        'name'=>'Pending Deliveries'
    ],
    'fitted_ship' => [
        'pooled'=>false,
        'virtual'=>false,
        'name'=>'Fitted Ships (Hangar)'
    ],
    //just for the ui
    'item_pool' => [
        'pooled'=>true,
        'virtual'=>true,
        'name'=>'Repackaged (Multiple Locations)'
    ],
    'fake_fitted_ship' => [
        'pooled'=>false,
        'virtual'=>false,
        'name'=>'Fitted Ships (Hangar), dev-fake'
    ],
];