<?php
return [
    'inventory' => [
        'label' => 'inventory::config.menu_label',
        'name' => 'Inventory Managment',
        'icon' => 'fas fa-box-open',
        'route_segment' => 'inventory',
        'permission' => 'inventory.view_inventory',
        'entries' => [
            [
                'label' => 'inventory::config.menu_dashboard_label',
                'name' => 'Dashboard',
                'icon' => 'fas fa-space-shuttle',
                'route' => 'inventory.dashboard',
                'permission' => 'inventory.view_inventory',
            ],
            [
                'label' => 'inventory::config.menu_items_label',
                'name' => 'Item Browser',
                'icon' => 'fas fa-list',
                'route' => 'inventory.itemBrowser',
                'permission' => 'inventory.view_inventory',
            ],
            [
                'label' => 'inventory::config.menu_settings_label',
                'name' => 'Settings',
                'icon' => 'fas fa-cog',
                'route' => 'inventory.settings',
                'permission' => 'inventory.view_inventory',
            ],
            [
                'label' => 'inventory::config.menu_about_label',
                'name' => 'About',
                'icon' => 'fas fa-info',
                'route' => 'inventory.about',
                'permission' => 'inventory.view_inventory',
            ],
        ]
    ]
];