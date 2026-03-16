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

namespace Hyperf\Logger;

use Psr\Log\LoggerInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                LoggerInterface::class => fn ($container) => $container->get(LoggerFactory::class)->make(),
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for logger.',
                    'source' => __DIR__ . '/../publish/logger.php',
                    'destination' => BASE_PATH . '/config/autoload/logger.php',
                ],
            ],
        ];
    }
}
