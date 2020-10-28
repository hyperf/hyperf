<?php

return [
    'default' => [
        'driver' => 'redis',
        // lock expired time (second)
        'lock_expired' => 1,
        // with retry lock time (millisecond)
        'with_time' => 300,
        'retry' => 5,
    ],
];
