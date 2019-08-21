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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Snowflake\ConfigInterface as SnowflakeConfigInterface;
use Hyperf\Snowflake\MetaGenerator\RandomMilliSecondMetaGenerator;
use Psr\Container\ContainerInterface;

class MetaGeneratorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $beginSecond = $config->get('snowflake.begin_second', MetaGeneratorInterface::DEFAULT_BEGIN_SECOND);

        return make(RandomMilliSecondMetaGenerator::class, [
            $container->get(SnowflakeConfigInterface::class),
            $beginSecond,
        ]);
    }
}
