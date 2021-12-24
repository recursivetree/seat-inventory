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
                'name'  => 'Fittings',
                'icon'  => 'fas fa-space-shuttle',
                'route' => 'terminusinv.fittings',
            ],
            [
                'name'  => 'About',
                'icon'  => 'fas fa-info',
                'route' => 'terminusinv.about',
            ],
        ]
    ]
];