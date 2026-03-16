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

namespace HyperfTest\GrpcClient;

use Hyperf\Context\ApplicationContext;
use Hyperf\Coroutine\Channel\Pool as ChannelPool;
use Hyperf\Di\Container;
use HyperfTest\GrpcClient\Stub\UserServiceClient;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use UserService\UserId;

/**
 * @internal
 * protoc --php_out=./tests ./tests/Golang/pb/user/user.proto --proto_path=./tests/Golang/pb/user/
 * @coversNothing
 */
#[CoversNothing]
class GoUserServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(ChannelPool::class)->andReturn(new ChannelPool());
        $container->shouldReceive('has')->andReturn(false);
        ApplicationContext::setContainer($container);
    }

    public function testGrpcUserInfo()
    {
        $client = new UserServiceClient('127.0.0.1:50052', ['retry_attempts' => 0]);

        $userId = new UserId();
        $userId->setId($id = rand(10000, 9999999));
        [$userInfo] = $client->info($userId);
        $this->assertSame($id, $userInfo->getId());
        $this->assertSame('Hyperf', $userInfo->getName());
    }
}
