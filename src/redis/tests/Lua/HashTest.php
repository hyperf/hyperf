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

use Hyperf\Redis\Lua\Hash\HGetAllMultiple;
use Hyperf\Redis\Lua\Hash\HIncrByFloatIfExists;
use Hyperf\Stringable\Str;
use HyperfTest\Redis\Stub\ContainerStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Redis;

/**
 * @internal
 * @coversNothing
 */
class HashTest extends TestCase
{
    protected function tearDown(): void
    {
        $container = ContainerStub::mockContainer();
        $redis = $container->get(Redis::class);
        $redis->flushDB();

        Mockery::close();
    }

    public function testEvalHGetAllMultiple()
    {
        $container = ContainerStub::mockContainer();
        $redis = $container->get(Redis::class);
        $redis->hMSet('{hash}:1', ['id' => 1, 'name' => $name1 = 'Hyperf']);
        $redis->hMSet('{hash}:2', ['id' => 2, 'name' => $name2 = Str::random(16)]);
        $redis->hMSet('{hash}:3', ['id' => 3, 'name' => $name3 = uniqid()]);
        $script = new HGetAllMultiple($container);
        $result = $script->eval(['{hash}:1', '{hash}:2', '{hash}:3']);

        $this->assertEquals(['id' => 1, 'name' => $name1], array_shift($result));
        $this->assertEquals(['id' => 2, 'name' => $name2], array_shift($result));
        $this->assertEquals(['id' => 3, 'name' => $name3], array_shift($result));
        $this->assertSame([], $result);
    }

    public function testEvalHIncrByFloatIfExists()
    {
        $container = ContainerStub::mockContainer();
        $redis = $container->get(Redis::class);
        $redis->hMSet('{hash}:1', ['id' => 1, 'name' => 'Hyperf', 'incr' => 0]);

        $script = new HIncrByFloatIfExists($container);
        $this->assertEquals(2.2, $script->eval(['{hash}:1', 'incr', 2.2]));

        $script = new HIncrByFloatIfExists($container);
        $this->assertSame(null, $script->eval(['{hash}:2', 'incr', 1]));
    }
}
