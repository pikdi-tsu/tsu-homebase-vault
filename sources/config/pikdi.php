<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Custom Config Settings
    |--------------------------------------------------------------------------
    |
    */

    'key' => [
        'emergency' => env('PIKDI_EMERGENCY_SECRET', 'pikdiemergency@TSU25'),
        'rescue' => env('PIKDI_RESCUE_SECRET'),
        'sync' => env('PIKDI_SYNC_SECRET'),
    ],

];
