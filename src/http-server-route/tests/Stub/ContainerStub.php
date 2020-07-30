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
namespace HyperfTest\HttpServerRoute\Stub;

use Hyperf\Contract\ContainerInterface;
use Hyperf\Utils\ApplicationContext;

class ContainerStub
{
    public static function getContainer()
    {
        $container = \Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        return $container;
    }
}
