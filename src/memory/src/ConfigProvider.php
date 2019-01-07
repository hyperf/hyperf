<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Memory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [],
            'scan' => [
                'paths' => [
                    'vendor/hyperf/memory/src',
                    'vendor/hyperf/hyperf/src/memory/src'
                ],
            ]
        ];
    }
}
