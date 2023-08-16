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
namespace HyperfTest\Redis\Lua;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Stringable\Str;
use HyperfTest\Redis\Stub\ContainerStub;
use HyperfTest\Redis\Stub\HGetAllMultipleStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Redis;

/**
 * @internal
 * @coversNothing
 */
class EvalTest extends TestCase
{
    protected function tearDown(): void
    {
        $container = ContainerStub::mockContainer();
        $redis = $container->get(Redis::class);
        $redis->flushDB();

        Mockery::close();
    }

    public function testEvalShaButNotExists()
    {
        $container = ContainerStub::mockContainer();
        $logger = $container->get(StdoutLoggerInterface::class);
        $logger->shouldReceive('warning')->once()->andReturnUsing(function ($message) {
            $this->assertSame('NOSCRIPT No matching script[HyperfTest\\Redis\\Stub\\HGetAllMultipleStub]. Use EVAL instead.', $message);
        });

        $redis = $container->get(Redis::class);
        $redis->hMSet('{hash}:1', ['id' => 1, 'name' => $name1 = 'Hyperf']);
        $redis->hMSet('{hash}:2', ['id' => 2, 'name' => $name2 = Str::random(16)]);

        $script = new HGetAllMultipleStub($container);
        $result = $script->eval(['{hash}:2', '{hash}:1'], false);
        $this->assertEquals(['id' => 2, 'name' => $name2], array_shift($result));
        $this->assertEquals(['id' => 1, 'name' => $name1], array_shift($result));

        $result = $script->eval(['{hash}:2', '{hash}:1']);
        $this->assertEquals(['id' => 2, 'name' => $name2], array_shift($result));
        $this->assertEquals(['id' => 1, 'name' => $name1], array_shift($result));
    }
}
