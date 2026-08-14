<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Simulated online status
    |--------------------------------------------------------------------------
    |
    | Real activity always decides the online badge first. This value only
    | controls the fallback for profiles whose owner is not actually online:
    | the share (in percent) of them that is shown as online, so a young site
    | does not look deserted.
    |
    | Set to 0 to switch the simulation off entirely and show the badge only
    | for genuinely active users.
    |
    */

    'online_simulation_percent' => (int) env('ONLINE_SIMULATION_PERCENT', 30),

];
