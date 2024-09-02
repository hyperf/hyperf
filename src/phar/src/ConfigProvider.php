<?php

declare(strict_types=1);
/**
 * This file is part of Anyon.
 *
 * @Link https://thinkadmin.top
 * @Contact Anyon<zoujingli@qq.com>
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
