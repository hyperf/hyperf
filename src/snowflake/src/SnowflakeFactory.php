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
use Psr\Container\ContainerInterface;

class SnowflakeFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $level = $config->get('snowflake.level', IdGeneratorInterface::LEVEL_MILLISECOND);
        $beginSecond = $config->get('snowflake.begin_second', IdGeneratorInterface::DEFAULT_SECOND);

        return make(Snowflake::class, [
            'level' => $level,
            'beginSecond' => $beginSecond,
        ]);
    }
}
