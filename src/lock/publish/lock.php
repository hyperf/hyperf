<?php

return [
    'default' => [
        'driver' => 'redis',
        // lock expired time (millisecond)
        'lock_expired' => 100,
        // with retry lock time (millisecond)
        'with_time' => 300,
        'retry' => 5,
    ],
];
