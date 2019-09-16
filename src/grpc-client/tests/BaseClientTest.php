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

namespace HyperfTest\GrpcClient;

use Hyperf\Di\Container;
use Hyperf\GrpcClient\BaseClient;
use Hyperf\GrpcClient\Exception\GrpcClientException;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\ChannelPool;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class BaseClientTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testGrpcClientStartFailed()
    {
        $this->getContainer();

        $this->expectException(GrpcClientException::class);
        $this->expectExceptionMessage('Grpc client start failed with error code 61 when connect to 127.0.0.1:1111');

        $client = new BaseClient('127.0.0.1:1111');
    }

    public function getContainer()
    {
        $container = \Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(ChannelPool::class)->andReturn(new ChannelPool());

        ApplicationContext::setContainer($container);

        return $container;
    }
}
