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
    'packer' => Hyperf\Utils\Packer\JsonPacker::class,
    'use_standalone_process' => true,
    'namespaces' => [
        '/application',
    ],
    'mapping' => [
        '/application/test' => 'test',
    ],
    'interval' => 5,
];
