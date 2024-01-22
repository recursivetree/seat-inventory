<?php

return [
    'seat_inventory_low_stock_levels' => [
        'label' => 'inventory::config.seat_inventory_low_stock_level_notification',
        'handlers' => [
            'mail' => \RecursiveTree\Seat\Inventory\Notifications\StockLevelNotificationMail::class,
            'slack' => \RecursiveTree\Seat\Inventory\Notifications\StockLevelNotificationSlack::class,
            'discord' => \RecursiveTree\Seat\Inventory\Notifications\StockLevelNotificationDiscord::class
        ],
    ]
];