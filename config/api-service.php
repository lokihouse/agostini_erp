<?php

return [
    'navigation' => [
        'token' => [
            'cluster' => \App\Filament\Clusters\Sistema::class, // 'sistema
            'group' => "Segurança",
            'sort' => 0,
            'icon' => 'heroicon-o-key',
        ],
    ],
    'models' => [
        'token' => [
            'enable_policy' => true,
        ],
    ],
    'route' => [
        'panel_prefix' => false,
        'use_resource_middlewares' => false,
    ],
    'tenancy' => [
        'enabled' => false,
        'awareness' => false,
    ],
];
