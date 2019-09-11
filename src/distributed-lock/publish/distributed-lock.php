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
    'prefix'    => 'lock',
    'ttl'       => 10,
    'separator' => ':',
    'driver'    => 'redis',
    'redis'     => [
        'drift_factor' => 0.01,
        'retry'        => 0,
        'retry_delay'  => 200, // time in ms
        'pools'        => [
            'default',
        ],
    ],
    'consul'    => [
        'retry'       => 0,
        'retry_delay' => 200, // time in ms
    ],
];
