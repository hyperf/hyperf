<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

return [
    'prefix' => 'lock:',
    'ttl'    => 10,
    'driver' => 'redis',
    'redis'  => [
        'drift_factor' => 0.01, // time in ms
        // the max number of times Redlock will attempt
        // to lock a resource before erroring
        'retry'        => 10,
        // the time in ms between attempts
        'retry_delay'  => 200, // time in ms
        'pools'        => [
            'default',
            'lock',
        ],
    ],
];
