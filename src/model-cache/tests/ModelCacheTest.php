<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\ModelCache;

use Hyperf\Redis\RedisProxy;
use HyperfTest\ModelCache\Stub\ContainerStub;
use HyperfTest\ModelCache\Stub\UserModel;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ModelCacheTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testFindByCache()
    {
        ContainerStub::mockContainer();

        $user = UserModel::findFromCache(1);
        $expect = UserModel::query()->find(1);

        $this->assertEquals($expect, $user);
    }

    public function testFindManyByCache()
    {
        ContainerStub::mockContainer();

        $users = UserModel::findManyFromCache([1, 2, 3]);
        $expects = UserModel::query()->findMany([1, 2, 3]);

        $this->assertTrue(count($users) == 2);
        $this->assertEquals([1, 2], array_keys($users->getDictionary()));
        $this->assertEquals($expects, $users);
    }

    public function testDeleteByBuilder()
    {
        $container = ContainerStub::mockContainer();

        $ids = [200, 201, 202];
        foreach ($ids as $id) {
            UserModel::query()->firstOrCreate(['id' => $id], [
                'name' => uniqid(),
                'gender' => 1,
            ]);
        }

        UserModel::findManyFromCache($ids);
        /** @var \Redis $redis */
        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);
        foreach ($ids as $id) {
            $this->assertEquals(1, $redis->exists('{mc:default:m:user}:id:' . $id));
        }

        UserModel::query(true)->whereIn('id', $ids)->delete();
        foreach ($ids as $id) {
            $this->assertEquals(0, $redis->exists('{mc:default:m:user}:id:' . $id));
        }

        foreach ($ids as $id) {
            $this->assertNull(UserModel::query()->find($id));
        }
    }

    public function testUpdateByBuilder()
    {
        $container = ContainerStub::mockContainer();

        $ids = [203, 204, 205];
        foreach ($ids as $id) {
            UserModel::query()->firstOrCreate(['id' => $id], [
                'name' => uniqid(),
                'gender' => 1,
            ]);
        }

        UserModel::findManyFromCache($ids);
        /** @var \Redis $redis */
        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);
        foreach ($ids as $id) {
            $this->assertEquals(1, $redis->exists('{mc:default:m:user}:id:' . $id));
        }

        UserModel::query(true)->whereIn('id', $ids)->update(['gender' => 2]);
        foreach ($ids as $id) {
            $this->assertEquals(0, $redis->exists('{mc:default:m:user}:id:' . $id));
        }

        foreach ($ids as $id) {
            $this->assertSame(2, UserModel::query()->find($id)->gender);
        }

        UserModel::query(true)->whereIn('id', $ids)->delete();
    }

    public function testIncr()
    {
        $container = ContainerStub::mockContainer();

        $id = 206;
        UserModel::query()->firstOrCreate(['id' => $id], [
            'name' => uniqid(),
            'gender' => 1,
        ]);

        $model = UserModel::findFromCache($id);
        /** @var \Redis $redis */
        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);
        $this->assertEquals(1, $redis->exists('{mc:default:m:user}:id:' . $id));

        $this->assertEquals(1, $model->increment('gender', 1));
        $this->assertEquals(1, $redis->exists('{mc:default:m:user}:id:' . $id));
        $this->assertEquals(2, $redis->hGet('{mc:default:m:user}:id:' . $id, 'gender'));
        $this->assertEquals(2, UserModel::findFromCache($id)->gender);
        $this->assertEquals(2, UserModel::query()->find($id)->gender);

        UserModel::query(true)->where('id', $id)->delete();
    }

    public function testFindNullBeforeCreate()
    {
        $container = ContainerStub::mockContainer();

        $id = 207;

        $model = UserModel::findFromCache($id);
        /** @var \Redis $redis */
        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);
        $this->assertEquals(1, $redis->exists('{mc:default:m:user}:id:' . $id));
        $this->assertNull($model);

        $this->assertEquals(1, $redis->del('{mc:default:m:user}:id:' . $id));
        UserModel::query(true)->where('id', $id)->delete();
    }

    public function testIncrNotExist()
    {
        $container = ContainerStub::mockContainer();

        $id = 206;
        UserModel::query()->firstOrCreate(['id' => $id], [
            'name' => uniqid(),
            'gender' => 1,
        ]);

        $model = UserModel::query()->find($id);
        /** @var \Redis $redis */
        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);
        $this->assertEquals(0, $redis->exists('{mc:default:m:user}:id:' . $id));

        $this->assertEquals(1, $model->increment('gender', 1));
        $this->assertEquals(0, $redis->exists('{mc:default:m:user}:id:' . $id));
        $this->assertEquals(2, UserModel::query()->find($id)->gender);
        $this->assertEquals(2, UserModel::findFromCache($id)->gender);

        UserModel::query(true)->where('id', $id)->delete();
    }
}
