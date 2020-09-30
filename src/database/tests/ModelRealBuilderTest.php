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

use Carbon\Carbon;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Database\Model\Events\Saved;
use HyperfTest\Database\Stubs\ContainerStub;
use HyperfTest\Database\Stubs\Model\User;
use HyperfTest\Database\Stubs\Model\UserExtCamel;
use HyperfTest\Database\Stubs\Model\UserRole;
use HyperfTest\Database\Stubs\Model\UserRoleMorphPivot;
use HyperfTest\Database\Stubs\Model\UserRolePivot;
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

    public function testPivot()
    {
        $this->getContainer();

        $user = User::query()->find(1);
        $role = $user->roles->first();
        $this->assertSame(1, $role->id);
        $this->assertSame('author', $role->name);

        $this->assertInstanceOf(UserRolePivot::class, $role->pivot);
        $this->assertSame(1, $role->pivot->user_id);
        $this->assertSame(1, $role->pivot->role_id);

        $role->pivot->updated_at = $now = Carbon::now()->toDateTimeString();
        $role->pivot->save();

        $pivot = UserRole::query()->find(1);
        $this->assertSame($now, $pivot->updated_at->toDateTimeString());

        while ($event = $this->channel->pop(0.001)) {
            if ($event instanceof Saved) {
                $this->assertSame($event->getModel(), $role->pivot);
                $hit = true;
            }
        }

        $this->assertTrue($hit);
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

    public function testCamelCaseGetModel()
    {
        $this->getContainer();

        /** @var UserExtCamel $ext */
        $ext = UserExtCamel::query()->find(1);
        $this->assertArrayHasKey('floatNum', $ext->toArray());
        $this->assertArrayHasKey('createdAt', $ext->toArray());
        $this->assertIsString($ext->updatedAt);
        $this->assertIsString($ext->toArray()['updatedAt']);

        $this->assertIsString($number = $ext->floatNum);

        $ext->increment('float_num', 1);

        $model = UserExtCamel::query()->find(1);
        $this->assertSame($ext->floatNum, $model->floatNum);

        $model->fill([
            'floatNum' => '1.20',
        ]);
        $model->save();

        $sqls = [
            'select * from `user_ext` where `user_ext`.`id` = ? limit 1',
            'update `user_ext` set `float_num` = `float_num` + 1, `user_ext`.`updated_at` = ? where `id` = ?',
            'select * from `user_ext` where `user_ext`.`id` = ? limit 1',
            'update `user_ext` set `float_num` = ?, `user_ext`.`updated_at` = ? where `id` = ?',
        ];
        while ($event = $this->channel->pop(0.001)) {
            if ($event instanceof QueryExecuted) {
                $this->assertSame($event->sql, array_shift($sqls));
            }
        }
    }

    public function testSaveMorphPivot()
    {
        $this->getContainer();
        $pivot = UserRoleMorphPivot::query()->find(1);
        $pivot->created_at = $now = Carbon::now();
        $pivot->save();

        $sqls = [
            ['select * from `user_role` where `user_role`.`id` = ? limit 1', [1]],
            ['update `user_role` set `created_at` = ?, `user_role`.`updated_at` = ? where `id` = ?', [$now->toDateTimeString(), $now->toDateTimeString(), 1]],
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
