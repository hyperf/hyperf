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

        $users = UserModel::findManyFromCache([1, 2]);
        $expects = UserModel::query()->findMany([1, 2]);

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
    }
}
