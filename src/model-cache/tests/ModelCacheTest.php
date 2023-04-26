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
namespace HyperfTest\ModelCache;

use DateInterval;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\DbConnection\Db;
use Hyperf\DbConnection\Listener\InitTableCollectorListener;
use Hyperf\Engine\Channel;
use Hyperf\ModelCache\EagerLoad\EagerLoader;
use Hyperf\ModelCache\InvalidCacheManager;
use Hyperf\ModelCache\Listener\EagerLoadListener;
use Hyperf\Redis\RedisProxy;
use Hyperf\Support\Reflection\ClassInvoker;
use HyperfTest\ModelCache\Stub\BookModel;
use HyperfTest\ModelCache\Stub\ContainerStub;
use HyperfTest\ModelCache\Stub\ImageModel;
use HyperfTest\ModelCache\Stub\UserExtModel;
use HyperfTest\ModelCache\Stub\UserHiddenModel;
use HyperfTest\ModelCache\Stub\UserModel;
use Mockery;
use PHPUnit\Framework\TestCase;
use Redis;
use stdClass;
use Throwable;

use function Hyperf\Coroutine\wait;

/**
 * @internal
 * @coversNothing
 */
class ModelCacheTest extends TestCase
{
    /**
     * @var array
     */
    protected $channel;

    protected function setUp(): void
    {
        $this->channel = new Channel(999);
    }

    protected function tearDown(): void
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

        $users = UserModel::findManyFromCache([1, 2, 999]);
        $expects = UserModel::query()->findMany([1, 2, 999]);

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
        /** @var Redis $redis */
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
        /** @var Redis $redis */
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
        /** @var Redis $redis */
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
        /** @var Redis $redis */
        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);
        $this->assertEquals(1, $redis->exists('{mc:default:m:user}:id:' . $id));
        $this->assertNull($model);

        $this->assertEquals(1, $redis->del('{mc:default:m:user}:id:' . $id));
        UserModel::query(true)->where('id', $id)->delete();
    }

    public function testFindManyNullBeforeCreate()
    {
        $container = ContainerStub::mockContainer(listenQueryExecuted: function (QueryExecuted $executed) {
            $this->channel->push($executed);
        });

        $id = 207;

        $models = UserModel::findManyFromCache([$id]);
        /** @var Redis $redis */
        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);
        $this->assertEquals(1, $redis->exists('{mc:default:m:user}:id:' . $id));
        $this->assertSame(0, $models->count());

        $this->assertEquals(1, $redis->del('{mc:default:m:user}:id:' . $id));
        $models = UserModel::findManyFromCache([$id]);
        $models = UserModel::findManyFromCache([$id]);
        $models = UserModel::findManyFromCache([$id]);
        $models = UserModel::findManyFromCache([$id]);
        $models = UserModel::findManyFromCache([$id]);
        $models = UserModel::findManyFromCache([$id]);

        $this->assertLessThanOrEqual(2, $this->channel->length());

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
        /** @var Redis $redis */
        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);
        $this->assertEquals(0, $redis->exists('{mc:default:m:user}:id:' . $id));

        $this->assertEquals(1, $model->increment('gender', 1));
        $this->assertEquals(0, $redis->exists('{mc:default:m:user}:id:' . $id));
        $this->assertEquals(2, UserModel::query()->find($id)->gender);
        $this->assertEquals(2, UserModel::findFromCache($id)->gender);

        UserModel::query(true)->where('id', $id)->delete();
    }

    public function testModelWithJson()
    {
        /** @var UserExtModel $model */
        $model = UserExtModel::query()->find(1);
        $model->deleteCache();
        $model2 = UserExtModel::findFromCache(1);
        $model3 = UserExtModel::findFromCache(1);

        $this->assertSame(1, $model->json['id']);
        $this->assertSame(1, $model2->json['id']);
        $this->assertSame(1, $model3->json['id']);
        $this->assertSame($model->toArray(), $model2->toArray());
        $this->assertSame($model->toArray(), $model3->toArray());
        $this->assertSame($model->getAttributes(), $model2->getAttributes());
        $this->assertEquals(array_keys($model->getAttributes()), array_keys($model3->getAttributes()));
    }

    public function testModelCacheExpireTime()
    {
        $container = ContainerStub::mockContainer();

        /** @var UserExtModel $model */
        $model = UserExtModel::query()->find(1);
        $model->deleteCache();

        /** @var Redis $redis */
        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);
        UserExtModel::findFromCache(1);
        $this->assertSame(86400, $redis->ttl('{mc:default:m:user_ext}:id:1'));
    }

    public function testModelCacheExpireTimeWithDateInterval()
    {
        $container = ContainerStub::mockContainer(new DateInterval('P1DT10S'));

        /** @var UserExtModel $model */
        $model = UserExtModel::query()->find(1);
        $model->deleteCache();

        /** @var Redis $redis */
        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);
        UserExtModel::findFromCache(1);
        $this->assertSame(86410, $redis->ttl('{mc:default:m:user_ext}:id:1'));
    }

    public function testModelCacheWithHidden()
    {
        ContainerStub::mockContainer();

        $model = UserModel::query()->find(3);
        $model->deleteCache();
        $model2 = UserModel::findFromCache(3);
        $model3 = UserModel::findFromCache(3);
        $this->assertSame($model->toArray(), $model2->toArray());
        $this->assertSame($model->toArray(), $model3->toArray());
        $this->assertSame($model->getAttributes(), $model2->getAttributes());
        // TODO: The retrieved attributes from cache are strings, so they are not exactly equal.
        $this->assertEquals(array_keys($model->getAttributes()), array_keys($model3->getAttributes()));

        $model = UserHiddenModel::query()->find(3);
        $model->deleteCache();
        $model2 = UserHiddenModel::findFromCache(3);
        $model3 = UserHiddenModel::findFromCache(3);
        $this->assertSame($model->toArray(), $model2->toArray());
        $this->assertSame($model->toArray(), $model3->toArray());
        $this->assertSame($model->getAttributes(), $model2->getAttributes());
        $this->assertEquals(array_keys($model->getAttributes()), array_keys($model3->getAttributes()));

        $model2->gender = (int) (! $model2->gender);
        $model2->save();

        $model = UserHiddenModel::query()->find(3);
        $model->deleteCache();
        $model2 = UserHiddenModel::findFromCache(3);
        $model3 = UserHiddenModel::findFromCache(3);
        $this->assertSame($model->toArray(), $model2->toArray());
        $this->assertSame($model->toArray(), $model3->toArray());
        $this->assertSame($model->getAttributes(), $model2->getAttributes());
        $this->assertEquals(array_keys($model->getAttributes()), array_keys($model3->getAttributes()));
    }

    public function testEagerLoad()
    {
        $container = ContainerStub::mockContainer();

        /** @var Redis $redis */
        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);
        $redis->del('{mc:default:m:user}:id:1', '{mc:default:m:user}:id:2');

        $this->assertSame(0, $redis->exists('{mc:default:m:user}:id:1', '{mc:default:m:user}:id:2'));
        $books = BookModel::query()->get();
        $loader = new EagerLoader();
        $loader->load($books, ['user']);

        $this->assertSame(2, $redis->exists('{mc:default:m:user}:id:1', '{mc:default:m:user}:id:2'));
    }

    public function testEagerLoadMacro()
    {
        $container = ContainerStub::mockContainer();
        $listener = new EagerLoadListener($container);
        $listener->process(new stdClass());

        /** @var Redis $redis */
        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);
        $redis->del('{mc:default:m:user}:id:1', '{mc:default:m:user}:id:2');

        $this->assertSame(0, $redis->exists('{mc:default:m:user}:id:1', '{mc:default:m:user}:id:2'));
        $books = BookModel::query()->get();
        $books->loadCache(['user']);

        $this->assertSame(2, $redis->exists('{mc:default:m:user}:id:1', '{mc:default:m:user}:id:2'));
    }

    public function testEagerLoadMorphTo()
    {
        ContainerStub::mockContainer();
        Relation::morphMap([
            'user' => UserModel::class,
            'book' => BookModel::class,
        ]);

        $images = ImageModel::findManyFromCache([1, 2, 3]);
        $loader = new EagerLoader();
        $loader->load($images, ['imageable']);

        $this->assertInstanceOf(UserModel::class, $images->shift()->imageable);
        $this->assertInstanceOf(UserModel::class, $images->shift()->imageable);
        $this->assertInstanceOf(BookModel::class, $images->shift()->imageable);
    }

    public function testWhenAddedNewColumn()
    {
        $container = ContainerStub::mockContainer();
        $listener = new InitTableCollectorListener($container);
        $listener->process((object) []);

        $model = UserHiddenModel::query()->find(1);
        $model->deleteCache();

        $model = UserModel::findFromCache(1);
        $model = UserModel::findFromCache(1);
        $this->assertArrayHasKey('gender', $model->toArray());

        /** @var Redis $redis */
        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);
        $redis->hDel('{mc:default:m:user}:id:1', 'gender');
        $model = UserModel::findFromCache(1);

        $this->assertArrayHasKey('gender', $model->toArray());
    }

    public function testModelSave()
    {
        $container = ContainerStub::mockContainer();
        /** @var Redis $redis */
        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);

        $id = 208;
        UserModel::query()->firstOrCreate(['id' => $id], [
            'name' => uniqid(),
            'gender' => 1,
        ]);

        $model = UserModel::findFromCache($id);
        $name = uniqid();
        $model->name = $name;
        $model->save();

        $this->assertSame(0, $redis->exists('{mc:default:m:user}:id:' . $id));

        $model = UserModel::findFromCache($id);
        $this->assertSame($name, $model->name);
        $connection = $model->getConnection();
        $connection->transaction(function () use ($id, $redis) {
            $model = UserModel::findFromCache($id);
            $name = uniqid();
            $model->name = $name;
            $model->save();
            UserModel::findFromCache($id);
            $this->assertSame(1, $redis->exists('{mc:default:m:user}:id:' . $id));
        });

        $this->assertSame(0, $redis->exists('{mc:default:m:user}:id:' . $id));

        $model->delete();
    }

    public function testModelCacheTTL()
    {
        $container = ContainerStub::mockContainer();
        $model = new BookModel();
        $this->assertSame(100, $model->getCacheTTL());

        /** @var Redis $redis */
        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);
        $redis->del('{mc:default:m:book}:id:1');
        BookModel::findFromCache(1);
        $this->assertSame(100, $redis->ttl('{mc:default:m:book}:id:1'));
    }

    public function testModelSaveInTransaction()
    {
        $container = ContainerStub::mockContainer();

        $id = 209;
        UserModel::query()->firstOrCreate(['id' => $id], [
            'name' => uniqid(),
            'gender' => 1,
        ]);

        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);

        wait(function () use ($redis, $id) {
            Db::beginTransaction();
            try {
                $model = UserModel::findFromCache($id);
                /* @var \Redis $redis */
                $this->assertEquals(1, $redis->exists('{mc:default:m:user}:id:' . $id));
                $model->gender = 2;
                $model->save();
                $this->assertEquals(1, $redis->hGet('{mc:default:m:user}:id:' . $id, 'gender'));
                $invoker = new ClassInvoker(InvalidCacheManager::instance());
                $this->assertSame(1, count($invoker->models));
                Db::commit();
            } catch (Throwable $exception) {
                Db::rollBack();
            }
        });

        $this->assertSame(0, $redis->exists('{mc:default:m:user}:id:' . $id));

        UserModel::query(true)->where('id', $id)->delete();
    }

    public function testModelIncrInTransaction()
    {
        $container = ContainerStub::mockContainer();

        $id = 209;
        UserModel::query()->firstOrCreate(['id' => $id], [
            'name' => uniqid(),
            'gender' => 1,
        ]);

        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);

        wait(function () use ($redis, $id) {
            Db::beginTransaction();
            try {
                $model = UserModel::findFromCache($id);
                /* @var \Redis $redis */
                $this->assertEquals(1, $redis->exists('{mc:default:m:user}:id:' . $id));
                $model->increment('gender');
                $this->assertEquals(1, $redis->hGet('{mc:default:m:user}:id:' . $id, 'gender'));
                $invoker = new ClassInvoker(InvalidCacheManager::instance());
                $this->assertSame(1, count($invoker->models));
                Db::commit();
            } catch (Throwable $exception) {
                Db::rollBack();
            }
        });

        $this->assertSame(0, $redis->exists('{mc:default:m:user}:id:' . $id));

        UserModel::query(true)->where('id', $id)->delete();
    }

    public function testModelDecrInTransaction()
    {
        $container = ContainerStub::mockContainer();

        $id = 209;
        UserModel::query()->firstOrCreate(['id' => $id], [
            'name' => uniqid(),
            'gender' => 1,
        ]);

        $redis = $container->make(RedisProxy::class, ['pool' => 'default']);

        wait(function () use ($redis, $id) {
            Db::beginTransaction();
            try {
                $model = UserModel::findFromCache($id);
                /* @var \Redis $redis */
                $this->assertEquals(1, $redis->exists('{mc:default:m:user}:id:' . $id));
                $model->decrement('gender');
                $this->assertEquals(1, $redis->hGet('{mc:default:m:user}:id:' . $id, 'gender'));
                $invoker = new ClassInvoker(InvalidCacheManager::instance());
                $this->assertSame(1, count($invoker->models));
                Db::commit();
            } catch (Throwable $exception) {
                Db::rollBack();
            }
        });

        $this->assertSame(0, $redis->exists('{mc:default:m:user}:id:' . $id));

        UserModel::query(true)->where('id', $id)->delete();
    }
}
