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
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Contract\PaginatorInterface;
use Hyperf\Database\Connection;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolver;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Connectors\MySqlConnector;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Database\MySqlBitConnection;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Column;
use Hyperf\Database\Schema\MySqlBuilder;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Container;
use Hyperf\Engine\Channel;
use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Paginator\Paginator;
use HyperfTest\Database\Stubs\ContainerStub;
use HyperfTest\Database\Stubs\Model\TestModel;
use HyperfTest\Database\Stubs\Model\TestVersionModel;
use HyperfTest\Database\Stubs\Model\User;
use HyperfTest\Database\Stubs\Model\UserBit;
use HyperfTest\Database\Stubs\Model\UserExt;
use HyperfTest\Database\Stubs\Model\UserExtCamel;
use HyperfTest\Database\Stubs\Model\UserRole;
use HyperfTest\Database\Stubs\Model\UserRoleMorphPivot;
use HyperfTest\Database\Stubs\Model\UserRolePivot;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;

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

    protected function setUp(): void
    {
        $this->channel = new Channel(999);
    }

    protected function tearDown(): void
    {
        $container = $this->getContainer();
        /** @var ConnectionInterface $conn */
        $conn = $container->get(ConnectionResolverInterface::class)->connection();
        $conn->statement('DROP TABLE IF EXISTS `test`;');
        $conn->statement('DROP TABLE IF EXISTS `test_full_text_index`;');
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

    public function testForceIndexes()
    {
        $this->getContainer();

        User::query()->get();
        User::query()->forceIndexes(['PRIMARY'])->where('id', '>', 1)->get();

        $sqls = [
            ['select * from `user`', []],
            ['select * from `user` force index (`PRIMARY`) where `id` > ?', [1]],
        ];
        while ($event = $this->channel->pop(0.001)) {
            if ($event instanceof QueryExecuted) {
                $this->assertSame([$event->sql, $event->bindings], array_shift($sqls));
            }
        }
    }

    public function testIncrement()
    {
        $this->getContainer();

        /** @var UserExt $ext */
        $ext = UserExt::query()->find(1);
        $ext->timestamps = false;

        $this->assertFalse($ext->isDirty());

        $ext->increment('count', 1);
        $this->assertFalse($ext->isDirty());
        $this->assertArrayHasKey('count', $ext->getChanges());
        $this->assertSame(1, count($ext->getChanges()));

        $ext->increment('count', 1, [
            'str' => uniqid(),
        ]);
        $this->assertTrue($ext->save());
        $this->assertFalse($ext->isDirty());
        $this->assertArrayHasKey('str', $ext->getChanges());
        $this->assertArrayHasKey('count', $ext->getChanges());
        $this->assertSame(2, count($ext->getChanges()));

        // Don't effect.
        $ext->str = uniqid();
        $this->assertTrue($ext->isDirty('str'));

        $ext->increment('count', 1, [
            'float_num' => (string) ($ext->float_num + 1),
        ]);
        $this->assertTrue($ext->isDirty('str'));
        $this->assertArrayHasKey('count', $ext->getChanges());
        $this->assertArrayHasKey('float_num', $ext->getChanges());

        $this->assertSame(2, count($ext->getChanges()));
        $this->assertTrue($ext->save());
        $this->assertArrayHasKey('str', $ext->getChanges());
        $this->assertSame(1, count($ext->getChanges()));

        $ext->float_num = (string) ($ext->float_num + 1);
        $this->assertTrue($ext->save());
        $this->assertArrayHasKey('float_num', $ext->getChanges());
        $this->assertSame(1, count($ext->getChanges()));

        $sqls = [
            'select * from `user_ext` where `user_ext`.`id` = ? limit 1',
            'update `user_ext` set `count` = `count` + 1 where `id` = ?',
            'update `user_ext` set `count` = `count` + 1, `str` = ? where `id` = ?',
            'update `user_ext` set `count` = `count` + 1, `float_num` = ? where `id` = ?',
            'update `user_ext` set `str` = ? where `id` = ?',
            'update `user_ext` set `float_num` = ? where `id` = ?',
        ];
        while ($event = $this->channel->pop(0.001)) {
            if ($event instanceof QueryExecuted) {
                $this->assertSame($event->sql, array_shift($sqls));
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

    public function testGetColumnListing()
    {
        $container = $this->getContainer();
        $connection = $container->get(ConnectionResolverInterface::class)->connection();
        /** @var MySqlBuilder $builder */
        $builder = $connection->getSchemaBuilder('default');
        $columns = $builder->getColumnListing('user_ext');
        foreach ($columns as $column) {
            $this->assertSame($column, strtolower($column));
        }
    }

    public function testGetColumnTypeListing()
    {
        $container = $this->getContainer();
        $connection = $container->get(ConnectionResolverInterface::class)->connection();
        /** @var MySqlBuilder $builder */
        $builder = $connection->getSchemaBuilder('default');
        $columns = $builder->getColumnTypeListing('user_ext');
        $column = $columns[0];
        foreach ($column as $key => $value) {
            $this->assertSame($key, strtolower($key));
        }
    }

    public function testGetColumns()
    {
        $container = $this->getContainer();
        $connection = $container->get(ConnectionResolverInterface::class)->connection();
        /** @var MySqlBuilder $builder */
        $builder = $connection->getSchemaBuilder('default');
        $columns = $builder->getColumns();
        foreach ($columns as $column) {
            if ($column->getTable() === 'book') {
                break;
            }
        }
        $this->assertInstanceOf(Column::class, $column);
        $this->assertSame('hyperf', $column->getSchema());
        $this->assertSame('book', $column->getTable());
        $this->assertSame('id', $column->getName());
        $this->assertSame(1, $column->getPosition());
        $this->assertSame(null, $column->getDefault());
        $this->assertSame(false, $column->isNullable());
        $this->assertSame('bigint', $column->getType());
        $this->assertSame('', $column->getComment());
    }

    public function testUpsert()
    {
        $container = $this->getContainer();
        /** @var ConnectionInterface $conn */
        $conn = $container->get(ConnectionResolverInterface::class)->connection();
        $conn->statement('CREATE TABLE `test` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `uid` bigint(20) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        $res = TestModel::query()->insert(['user_id' => 1, 'uid' => 1]);
        $this->assertTrue($res);

        $model = TestModel::query()->find(1);
        $this->assertSame(1, $model->uid);

        $res = TestModel::query()->upsert(['user_id' => 1, 'uid' => 2], []);
        $this->assertSame(2, $res);

        $model = TestModel::query()->find(1);
        $this->assertSame(2, $model->uid);
    }

    public function testRewriteSetKeysForSaveQuery()
    {
        $container = $this->getContainer();
        /** @var ConnectionInterface $conn */
        $conn = $container->get(ConnectionResolverInterface::class)->connection();
        $conn->statement('CREATE TABLE `test` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `uid` bigint(20) unsigned NOT NULL,
  `version` bigint(20) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        $res = TestVersionModel::query()->insert(['user_id' => 1, 'uid' => 1, 'version' => 2]);
        $this->assertTrue($res);

        /** @var TestVersionModel $model */
        $model = TestVersionModel::query()->first();
        $model->user_id = 2;
        $model->version = 1;
        $model->save();

        $this->assertSame(2, TestVersionModel::query()->first()->user_id);

        $model->mustVersion = true;
        $model->user_id = 3;
        $model->version = 0;
        $model->save();

        $this->assertSame(2, TestVersionModel::query()->first()->user_id);

        $model->user_id = 4;
        $model->version = 2;
        $model->save();

        $this->assertSame(4, TestVersionModel::query()->first()->user_id);

        $sqls = [
            'update `test` set `user_id` = ?, `version` = ?, `test`.`updated_at` = ? where `id` = ?',
            'update `test` set `user_id` = ?, `version` = ?, `test`.`updated_at` = ? where `id` = ? and `version` <= ?',
            'update `test` set `user_id` = ?, `version` = ?, `test`.`updated_at` = ? where `id` = ? and `version` <= ?',
        ];
        while ($event = $this->channel->pop(0.001)) {
            if ($event instanceof QueryExecuted && str_starts_with($event->sql, 'update')) {
                $this->assertSame($event->sql, array_shift($sqls));
            }
        }
    }

    public function testBigIntInsertAndGet()
    {
        $container = $this->getContainer();
        /** @var ConnectionInterface $conn */
        $conn = $container->get(ConnectionResolverInterface::class)->connection();
        $conn->statement('CREATE TABLE `test` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `uid` bigint(20) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        $sql = 'INSERT INTO test(`user_id`, `uid`) VALUES (?,?)';
        $this->assertTrue($conn->insert($sql, [PHP_INT_MAX, 1]));

        $binds = [
            [PHP_INT_MAX, 1],
            [(string) PHP_INT_MAX, 1],
            [PHP_INT_MAX, (string) 1],
            [(string) PHP_INT_MAX, (string) 1],
        ];
        $sql = 'SELECT * FROM test WHERE user_id = ? AND uid = ?';
        foreach ($binds as $bind) {
            $res = $conn->select($sql, $bind);
            $this->assertNotEmpty($res);
        }

        $binds = [
            [1, PHP_INT_MAX],
            [1, (string) PHP_INT_MAX],
            [(string) 1, PHP_INT_MAX],
            [(string) 1, (string) PHP_INT_MAX],
        ];
        $sql = 'SELECT * FROM test WHERE uid = ? AND user_id = ?';
        foreach ($binds as $bind) {
            $res = $conn->select($sql, $bind);
            $this->assertNotEmpty($res);
        }
    }

    public function testSimplePaginate()
    {
        $container = $this->getContainer();
        $container->shouldReceive('make')->with(PaginatorInterface::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new Paginator(...array_values($args));
        });
        $container->shouldReceive('get')->with(Db::class)->andReturn(new Db($container));
        $res = Db::table('user')->simplePaginate(1);
        $this->assertTrue($res->hasMorePages());
        while ($event = $this->channel->pop(0.001)) {
            if ($event instanceof QueryExecuted) {
                $this->assertSame('select * from `user` limit 2 offset 0', $event->sql);
            }
        }

        $res = User::query()->simplePaginate(1);
        $this->assertTrue($res->hasMorePages());
        while ($event = $this->channel->pop(0.001)) {
            if ($event instanceof QueryExecuted) {
                $this->assertSame('select * from `user` limit 2 offset 0', $event->sql);
            }
        }
    }

    public function testChunkById()
    {
        $container = $this->getContainer();
        $container->shouldReceive('make')->with(PaginatorInterface::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new Paginator(...array_values($args));
        });
        $container->shouldReceive('get')->with(Db::class)->andReturn(new Db($container));
        Context::set($key = 'chunk.by.id.' . uniqid(), 0);
        Db::table('user')->chunkById(2, function ($data) use ($key) {
            $id = $data->first()->id;
            $this->assertNotSame($id, Context::get($key));
            Context::set($key, $id);
        });
    }

    public function testChunkByIdButNotFound()
    {
        $container = $this->getContainer();
        $container->shouldReceive('make')->with(PaginatorInterface::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new Paginator(...array_values($args));
        });
        $container->shouldReceive('get')->with(Db::class)->andReturn(new Db($container));

        $this->expectException(RuntimeException::class);

        Db::table('user')->chunkById(1, fn () => 1, 'created_at');
    }

    public function testPaginationCountQuery()
    {
        $container = $this->getContainer();
        $container->shouldReceive('make')->with(LengthAwarePaginatorInterface::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new LengthAwarePaginator(...array_values($args));
        });
        User::query()->select('gender')->groupBy('gender')->paginate(10, ['*'], 'page', 0);
        $sqls = [
            'select count(*) as aggregate from (select `gender` from `user` group by `gender`) as `aggregate_table`',
            'select `gender` from `user` group by `gender` limit 10 offset 0',
        ];
        while ($event = $this->channel->pop(0.001)) {
            if ($event instanceof QueryExecuted) {
                $this->assertSame($event->sql, array_shift($sqls));
            }
        }
    }

    public function testSaveBitValue()
    {
        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('get')->with('db.connector.mysql.bit')->andReturn(new MySqlConnector());
        $connector = new ConnectionFactory($container);

        Connection::resolverFor('mysql.bit', static function ($connection, $database, $prefix, $config) {
            return new MySqlBitConnection($connection, $database, $prefix, $config);
        });

        $dbConfig = [
            'driver' => 'mysql.bit',
            'host' => '127.0.0.1',
            'database' => 'hyperf',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ];

        $connection = $connector->make($dbConfig);

        $resolver = new ConnectionResolver(['default' => $connection]);

        $container->shouldReceive('get')->with(ConnectionResolverInterface::class)->andReturn($resolver);
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn(null);

        /** @var UserBit $model */
        $model = UserBit::query()->find(1);
        $model->bit = (int) (! $model->bit);
        $this->assertTrue($model->save());

        $model->bit = ! $model->bit;
        $this->assertTrue($model->save());
    }

    public function testSelectForBindingIntegerWhenUsingVarcharIndex()
    {
        $container = $this->getContainer();
        $container->shouldReceive('get')->with(Db::class)->andReturn(new Db($container));
        $res = Db::select('EXPLAIN SELECT * FROM `user` WHERE `name` = ?;', ['1']);
        $this->assertSame('ref', $res[0]->type);
        $res = Db::select('EXPLAIN SELECT * FROM `user` WHERE `name` = ?;', [1]);
        $this->assertSame('ref', $res[0]->type);
    }

    public function testBeforeExecuting()
    {
        $container = $this->getContainer();
        $container->shouldReceive('get')->with(Db::class)->andReturn(new Db($container));

        $res = Db::selectOne('SELECT * FROM `user` WHERE id = ?;', [1]);
        $this->assertSame('Hyperf', $res->name);

        try {
            $chan = new Channel(2);
            Db::beforeExecuting(function (string $sql, array $bindings, Connection $connection) use ($chan) {
                $this->assertSame(null, $connection->getConfig('name'));
                $chan->push(1);
            });
            Db::beforeExecuting(function (string $sql, array $bindings, Connection $connection) use ($chan) {
                $this->assertSame('SELECT * FROM `user` WHERE id = ?;', $sql);
                $this->assertSame([1], $bindings);
                $chan->push(2);
            });

            $res = Db::selectOne('SELECT * FROM `user` WHERE id = ?;', [1]);
            $this->assertSame('Hyperf', $res->name);
            $this->assertSame(1, $chan->pop(1));
            $this->assertSame(2, $chan->pop(1));
        } finally {
            Connection::clearBeforeExecutingCallbacks();
        }
    }

    public function testWhereFullText()
    {
        $container = $this->getContainer();
        $container->shouldReceive('get')->with(Db::class)->andReturn(new Db($container));

        Schema::create('test_full_text_index', function (Blueprint $table) {
            $table->id('id');
            $table->string('title', 200);
            $table->text('body');
            $table->fullText(['title', 'body']);
        });

        Db::table('test_full_text_index')->insert([
            ['title' => 'MySQL Tutorial', 'body' => 'DBMS stands for DataBase ...'],
            ['title' => 'How To Use MySQL Well', 'body' => 'After you went through a ...'],
            ['title' => 'Optimizing MySQL', 'body' => 'In this tutorial, we show ...'],
            ['title' => '1001 MySQL Tricks', 'body' => '1. Never run mysqld as root. 2. ...'],
            ['title' => 'MySQL vs. YourSQL', 'body' => 'In the following database comparison ...'],
            ['title' => 'MySQL Security', 'body' => 'When configured properly, MySQL ...'],
        ]);

        $result = Db::table('test_full_text_index')->whereFullText(['title', 'body'], 'database')->get();
        $this->assertCount(2, $result);
        $this->assertSame('MySQL Tutorial', $result[0]->title);
        $this->assertSame('MySQL vs. YourSQL', $result[1]->title);

        // boolean mode
        $result = Db::table('test_full_text_index')->whereFullText(['title', 'body'], '+MySQL -YourSQL', ['mode' => 'boolean'])->get();
        $this->assertCount(5, $result);

        // expanded query
        $result = Db::table('test_full_text_index')->whereFullText(['title', 'body'], 'database', ['expanded' => true])->get();
        $this->assertCount(6, $result);
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
