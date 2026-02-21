<?php

return [
    'war_score_divisor' => 50.0,
    'weights' => [
        'dps' => [
            'kills' => 5.0,
            'deaths' => -2.0,
            'revives' => 1.5,
            'war_score' => 1.0,
        ],
        'support' => [
            'kills' => 2.0,
            'deaths' => -1.0,
            'revives' => 5.0,
            'war_score' => 1.2,
        ],
        'tank' => [
            'kills' => 3.0,
            'deaths' => -0.5,
            'revives' => 2.5,
            'war_score' => 1.1,
        ],
    ],
    'min_score' => 0.0,
];
