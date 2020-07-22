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
    'enable' => false,
    'use_standalone_process' => true,
    'server' => 'http://localhost:8080',
    'appid' => 'test',
    'cluster' => 'default',
    'namespaces' => [
        'application',
    ],
    'interval' => 5,
    'strict_mode' => false,
];
