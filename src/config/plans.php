<?php

return [
    'free' => [
        'name' => 'Free',
        'amount' => 0,
        'features' => [],
        'display_group' => 'Free',
        'display_features' => [
            'Unlimited users',
            'Unlimited routes',
            'Unlimited PIREPS',
            'Leaderboard system',
            'Basic support',
        ],
    ],
    'cadet_monthly' => [
        'name' => 'Cadet Monthly',
        'amount' => 500,
        'interval' => 'monthly',
        'display_group' => 'Cadet',
        'display_features' => [
            'Everything in Free',
            'Events management',
            'Custom logo branding',
            'Custom fields',
            'Email support',
        ],
    ],
    'cadet_yearly' => [
        'name' => 'Cadet Yearly',
        'amount' => 5000,
        'interval' => 'yearly',
        'display_group' => 'Cadet',
        'display_features' => [
            'Everything in Free',
            'Events management',
            'Custom logo branding',
            'Custom fields',
            'Email support',
        ],
    ],
    'captain_monthly' => [
        'name' => 'Captain Monthly',
        'amount' => 1000,
        'interval' => 'monthly',
        'display_group' => 'Captain',
        'display_features' => [
            'Everything in Cadet',
            'Discord integration',
            'Priority support',
            'Advanced analytics',
            'API access',
        ],
    ],
    'captain_yearly' => [
        'name' => 'Captain Yearly',
        'amount' => 10000,
        'interval' => 'yearly',
        'display_group' => 'Captain',
        'display_features' => [
            'Everything in Cadet',
            'Discord integration',
            'Priority support',
            'Advanced analytics',
            'API access',
        ],
    ],
];