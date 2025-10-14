<?php

return [
    'debuggers' => [
        'horizon',
    ],

    'authorization' => true,

    'permissions' => [
        'horizon' => 'view_any_exception',
        'pulse' => 'view_any_exception',
    ],

    'group' => 'Debuggers',

    'url' => [
        'horizon' => env('HORIZON_PATH', 'horizon'),
    ],
];
