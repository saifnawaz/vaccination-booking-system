<?php

/*
 * Thresholds and limits for schedules, stock and time slots.
 */
return [

    /*
     * A due dose turns "overdue" this many days after its effective due date,
     * unless a vaccine dose sets its own grace_days in config/epi.php.
     */
    'default_grace_days' => 28,

    /*
     * Default minimum gap between two doses of the same vaccine, in days.
     */
    'default_min_interval_days' => 28,

    /*
     * Children older than this cannot be registered.
     */
    'max_child_age_years' => 12,

    'stock' => [
        // A vaccine is "low stock" at a hospital when its usable (unexpired,
        // unreserved) doses across all batches fall below this number.
        'low_threshold' => 10,

        // A batch is "expiring soon" when it expires within this many days.
        'expiring_soon_days' => 30,
    ],

    'slots' => [
        // Bulk creation: longest date range and most slots per request.
        'bulk_max_range_days' => 62,
        'bulk_max_slots' => 1000,

        // Slot lengths offered in the bulk form, in minutes.
        'lengths' => [15, 20, 30, 45, 60],

        'max_capacity' => 50,
    ],

];
