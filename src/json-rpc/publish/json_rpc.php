<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

return [
    'transporter' => [
        'tcp' => [
            'connect_timeout' => 5.0,
            'options' => [
                // 'open_eof_check' => true,
                // 'package_eof' => "\r\n",

                // 'open_length_check' => true,
                // 'package_length_type' => 'N',
                // 'package_length_offset' => 0,
                // 'package_body_offset' => 4,

                'package_max_length' => 1024 * 1024 * 2,
            ],
            'pool' => [
                'min_connections' => 1,
                'max_connections' => 32,
                'connect_timeout' => 10.0,
                'wait_timeout' => 3.0,
                'heartbeat' => -1,
                'max_idle_time' => 60.0,
            ],
            'recv_timeout' => 5.0,
        ],
    ],
];
