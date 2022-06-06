<?php

return [
    'seat_inventory_low_stock_levels' => [
        'label' => 'inventory::inventory.seat_inventory_low_stock_level_notification',
        'handlers' => [
            'mail' => \RecursiveTree\Seat\Inventory\Notifications\StockLevelNotification::class,
            'slack' => \RecursiveTree\Seat\Inventory\Notifications\StockLevelNotification::class,
        ],
    ]
];