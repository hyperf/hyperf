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
    'enable' => true,
    'agent' => \Hyperf\ServerRegister\Agent\ConsulAgent::class,
    'servers' => [
        [
            'server' => 'http',
            'name' => env('APP_NAME', 'hyperf') . '.http-server',
            'meta' => [
                // Consul Check Params
                'check' => [
                    'DeregisterCriticalServiceAfter' => '60s',
                    'Interval' => '1s',
                ],
                // Consul Meta
                'meta' => [],
            ],
        ],
    ],
];
