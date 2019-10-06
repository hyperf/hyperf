<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE.md
 */

return [
    'enable' => false,
    'packer' => Hyperf\Utils\Packer\JsonPacker::class,
    'namespaces' => [
        'application',
    ],
    'mapping' => [
    ],
    'interval' => 5,
];
