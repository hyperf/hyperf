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

namespace Hyperf\Phar;

use Hyperf\Framework\Logger\StdoutLogger;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                LoggerInterface::class => StdoutLogger::class,
            ],
            'commands' => [
                BuildCommand::class,
            ],
        ];
    }
}
