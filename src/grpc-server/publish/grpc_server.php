<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    // Whether to support services by rpc-server
    // Required `hyperf/rpc` and `hyperf/rpc-server`.
    'rpc' => [
        // The port name
        'grpc' => [
            'enable' => false,
        ],
    ],
];
