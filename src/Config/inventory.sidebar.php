<?php
return [
    'inventory' => [
        'name'          => 'Inventory Managment',
        'icon'          => 'fas fa-box-open',
        'route_segment' => 'inventory',
        'permission' => 'inventory.view_inventory',
        'entries'       => [
            [
                'name'  => 'Dashboard',
                'icon'  => 'fas fa-space-shuttle',
                'route' => 'inventory.dashboard',
                'permission' => 'inventory.view_inventory',
            ],
            [
                'name'  => 'Tracking',
                'icon'  => 'fas fa-cog',
                'route' => 'inventory.tracking',
                'permission' => 'inventory.view_inventory',
            ],
            [
                'name'  => 'Item Browser',
                'icon'  => 'fas fa-list',
                'route' => 'inventory.itemBrowser',
                'permission' => 'inventory.view_inventory',
            ],
            [
                'name'  => 'In Transport',
                'icon'  => 'fas fa-truck',
                'route' => 'inventory.movingItems',
                'permission' => 'inventory.view_inventory',
            ],
            [
                'name'  => 'About',
                'icon'  => 'fas fa-info',
                'route' => 'inventory.about',
                'permission' => 'inventory.view_inventory',
            ],
        ]
    ]
];