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
use function Hyperf\Support\env;

return [
    # Etcd Client
    'uri' => env('ETCD_URI', 'http://127.0.0.1:2379'),
    'version' => env('ETCD_VERSION', 'v3'),
    'auth' => [
        'name' => env('ETCD_NAME', ''),
        'password' => env('ETCD_PASSWORD', ''),
    ],
    'options' => [
        'timeout' => 10,
    ],
];
