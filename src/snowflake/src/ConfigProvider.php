<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Snowflake;

use Hyperf\Snowflake\MetaGenerator\RandomMilliSecondMetaGenerator;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                IdGeneratorInterface::class => SnowflakeFactory::class,
                MetaGeneratorInterface::class => RandomMilliSecondMetaGenerator::class,
                ConfigInterface::class => Config::class,
            ],
            'commands' => [
            ],
            'listeners' => [
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
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
