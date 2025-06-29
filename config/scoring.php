<?php
return [
    'profiles' => [
        'standard' => [
            'excellent' => ['min' => 8.0, 'label' => 'Giỏi'],
            'good' => ['min' => 6.0, 'label' => 'Khá'],
            'average' => ['min' => 4.0, 'label' => 'Trung bình'],
            'weak' => ['min' => 0.0, 'label' => 'Yếu'],
        ],
    ],
    'constraints' => ['min_score' => 0.0, 'max_score' => 10.0, 'decimal_places' => 2],
];
