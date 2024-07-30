<?php

return [
    'debuggers' => [
        'horizon',
        'telescope',
    ],

    'authorization' => false,

    'permissions' => [
        'horizon' => 'view_any_exception',
        'telescope' => 'view_any_exception',
    ],

    'group' => 'Settings',

    'url' => [
        'horizon' => env('HORIZON_PATH', 'horizon'),
        'telescope' => env('TELESCOPE_PATH', 'telescope'),
    ],
];
