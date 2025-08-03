<?php
use Exchanger\Service\CurrencyLayer;

return [
    'services' => [
        CurrencyLayer::class => [
            'access_key' => env('EXCHANGER_CURRENCY_LAYER_API_KEY'),
        ],
    ],
];