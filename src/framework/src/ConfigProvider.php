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

namespace Hyperf\Framework;

use Hyperf\Contract\ApplicationInterface;
use Hyperf\Framework\Contract\StdoutLoggerInterface;
use Hyperf\Framework\Logger\StdoutLogger;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ApplicationInterface::class => ApplicationFactory::class,
                StdoutLoggerInterface::class => StdoutLogger::class,
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
        ];
    }
}
