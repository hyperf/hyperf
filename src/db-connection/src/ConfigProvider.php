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

namespace Hyperf\DbConnection;

use Hyperf\DbConnection\Pool\DbPool;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                DbPool::class => DbPool::class
            ],
            'commands' => [
            ],
            'scan' => [
                'paths' => [],
            ],
        ];
    }
}
