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

namespace Hyperf\Snowflake;

use Hyperf\Snowflake\IdGenerator\SnowflakeIdGenerator;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                IdGeneratorInterface::class => SnowflakeIdGenerator::class,
                MetaGeneratorInterface::class => MetaGeneratorFactory::class,
                ConfigurationInterface::class => Configuration::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of snowflake.',
                    'source' => __DIR__ . '/../publish/snowflake.php',
                    'destination' => BASE_PATH . '/config/autoload/snowflake.php',
                ],
            ],
        ];
    }
}
