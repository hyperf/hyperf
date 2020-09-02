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
namespace HyperfTest\Database;

use Hyperf\Database\Events\QueryExecuted;
use HyperfTest\Database\Stubs\ContainerStub;
use HyperfTest\Database\Stubs\Model\User;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Coroutine\Channel;

/**
 * @internal
 * @coversNothing
 */
class ModelRealBuilderTest extends TestCase
{
    /**
     * @var array
     */
    protected $channel;

    protected function setUp()
    {
        $this->channel = new Channel(999);
    }

    protected function tearDown()
    {
        Mockery::close();
    }

    public function testForPageBeforeId()
    {
        $this->getContainer();

        User::query()->forPageBeforeId(2)->get();
        User::query()->forPageBeforeId(2, null)->get();
        User::query()->forPageBeforeId(2, 1)->get();

        $sqls = [
            ['select * from `user` where `id` < ? order by `id` desc limit 2', [0]],
            ['select * from `user` order by `id` desc limit 2', []],
            ['select * from `user` where `id` < ? order by `id` desc limit 2', [1]],
        ];
        while ($event = $this->channel->pop(0.001)) {
            if ($event instanceof QueryExecuted) {
                $this->assertSame([$event->sql, $event->bindings], array_shift($sqls));
            }
        }
    }

    public function testForPageAfterId()
    {
        $this->getContainer();

        User::query()->forPageAfterId(2)->get();
        User::query()->forPageAfterId(2, null)->get();
        User::query()->forPageAfterId(2, 1)->get();

        $sqls = [
            ['select * from `user` where `id` > ? order by `id` asc limit 2', [0]],
            ['select * from `user` order by `id` asc limit 2', []],
            ['select * from `user` where `id` > ? order by `id` asc limit 2', [1]],
        ];
        while ($event = $this->channel->pop(0.001)) {
            if ($event instanceof QueryExecuted) {
                $this->assertSame([$event->sql, $event->bindings], array_shift($sqls));
            }
        }
    }

    protected function getContainer()
    {
        $dispatcher = Mockery::mock(EventDispatcherInterface::class);
        $dispatcher->shouldReceive('dispatch')->with(Mockery::any())->andReturnUsing(function ($event) {
            $this->channel->push($event);
        });
        $container = ContainerStub::getContainer(function ($conn) use ($dispatcher) {
            $conn->setEventDispatcher($dispatcher);
        });
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn($dispatcher);

        return $container;
    }
}
