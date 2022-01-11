<?php
return [
    'terminusinv' => [
        'name'          => 'Inventory Managment',
        'icon'          => 'fas fa-box-open',
        'route_segment' => 'info',
        'entries'       => [
            [
                'name'  => 'Tracking',
                'icon'  => 'fas fa-cog',
                'route' => 'terminusinv.tracking',
            ],
            [
                'name'  => 'Fits&Stocks',
                'icon'  => 'fas fa-space-shuttle',
                'route' => 'terminusinv.stocks',
            ],
            [
                'name'  => 'Fits&Stocks Availability',
                'icon'  => 'fas fa-clipboard',
                'route' => 'terminusinv.stockAvailability',
            ],
            [
                'name'  => 'Item Browser',
                'icon'  => 'fas fa-list',
                'route' => 'terminusinv.itemBrowser',
            ],
            [
                'name'  => 'About',
                'icon'  => 'fas fa-info',
                'route' => 'terminusinv.about',
            ],
        ]
    ]
];