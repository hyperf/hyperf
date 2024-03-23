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

namespace HyperfTest\Devtool\Stub;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Mockery;

class ContainerStub
{
    public static function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturnUsing(function () {
            return new Config([
                'middlewares' => [
                    'http' => [
                    ],
                ],
            ]);
        });
        return $container;
    }
}
