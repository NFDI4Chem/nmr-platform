<?php

return [
    // 'start', 'end' - Position of the cookie consent banner
    'position' => 'end',

    'consent_button' => [
        'size' => 'sm',
        'color' => 'primary', // Using your primary gray color
    ],

    'privacy_policy_button' => [
        'enabled' => true,
        'href' => '/privacy-policy',
        'size' => 'sm',
        'color' => 'gray',
        'target' => '_blank',
    ],

    'terms_of_use_button' => [
        'enabled' => true,
        'href' => '/terms-of-use',
        'size' => 'sm',
        'color' => 'gray',
        'target' => '_blank',
    ],
];
