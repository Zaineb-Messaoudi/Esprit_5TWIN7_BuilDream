<?php

return [
    /*
    | Health score formula
    | Final score = sum(component score * component weight), clamped to [0, 100].
    | The weights should sum to 1.0. Each component score also ranges from 0 to 100.
    */
    'health_score' => [
        'weights' => [
            'age' => (float) env('MAINTENANCE_HEALTH_WEIGHT_AGE', 0.20),
            'damage' => (float) env('MAINTENANCE_HEALTH_WEIGHT_DAMAGE', 0.35),
            'maintenance' => (float) env('MAINTENANCE_HEALTH_WEIGHT_MAINTENANCE', 0.25),
            'rentals' => (float) env('MAINTENANCE_HEALTH_WEIGHT_RENTALS', 0.20),
        ],
        'age' => [
            'points_lost_per_year' => (float) env('MAINTENANCE_HEALTH_AGE_PENALTY_PER_YEAR', 5),
        ],
        'damage' => [
            // Inspection.damage_detected has no severity field yet; this is its configured default.
            'default_severity' => env('MAINTENANCE_HEALTH_DEFAULT_DAMAGE_SEVERITY', 'moderate'),
            'points_lost_by_severity' => [
                'minor' => 8,
                'moderate' => 20,
                'severe' => 40,
            ],
        ],
        'maintenance' => [
            'points_lost_per_day' => (float) env('MAINTENANCE_HEALTH_MAINTENANCE_PENALTY_PER_DAY', 0.20),
            // An older item with no maintenance history gets no maintenance sub-score.
            'never_maintained_under_days' => (int) env('MAINTENANCE_HEALTH_NEVER_MAINTAINED_GRACE_DAYS', 30),
        ],
        'rentals' => [
            'points_lost_per_rental' => (float) env('MAINTENANCE_HEALTH_RENTAL_PENALTY', 2),
        ],
        'badges' => [
            'healthy_minimum' => 75,
            'warning_minimum' => 40,
        ],
    ],
    'tco' => [
        'revenue_table' => env('MAINTENANCE_TCO_REVENUE_TABLE', 'rentals'),
        'equipment_column' => env('MAINTENANCE_TCO_EQUIPMENT_COLUMN', 'equipment_id'),
        'amount_column' => env('MAINTENANCE_TCO_AMOUNT_COLUMN', 'total_amount'),
    ],
    'ai' => [
        'api_key' => env('MAINTENANCE_AI_API_KEY'),
        'base_url' => env('MAINTENANCE_AI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('MAINTENANCE_AI_MODEL', 'gpt-4.1-mini'),
        'transcription_model' => env('MAINTENANCE_AI_TRANSCRIPTION_MODEL', 'whisper-1'),
        'timeout' => (int) env('MAINTENANCE_AI_TIMEOUT', 110),
    ],
    'cost_anomaly' => [
        'z_score_threshold' => (float) env('MAINTENANCE_COST_Z_SCORE_THRESHOLD', 2.0),
        'minimum_history' => (int) env('MAINTENANCE_COST_MINIMUM_HISTORY', 3),
    ],
    'repair_estimation' => [
        'default_cost' => (float) env('MAINTENANCE_DEFAULT_REPAIR_COST', 100),
        'default_minimum' => (float) env('MAINTENANCE_DEFAULT_REPAIR_MINIMUM', 80),
        'default_maximum' => (float) env('MAINTENANCE_DEFAULT_REPAIR_MAXIMUM', 120),
    ],
];
