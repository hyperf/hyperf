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

use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Register;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;
use Hyperf\Engine\Channel;
use Hyperf\Paginator\Cursor;
use HyperfTest\Database\Stubs\ContainerStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
class DatabaseModelCursorPaginateTest extends TestCase
{
    /**
     * @var array
     */
    protected $channel;

    protected function setUp(): void
    {
        $this->channel = new Channel(999);
        $container = ContainerStub::getContainer();
        $db = new Db($container);
        $container->shouldReceive('get')->with(Db::class)->andReturn($db);
        $connectionResolverInterface = $container->get(ConnectionResolverInterface::class);
        Register::setConnectionResolver($connectionResolverInterface);
        Schema::create('test_posts', static function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('test_users', static function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_posts');
        Schema::dropIfExists('test_users');
        Mockery::close();
    }

    public function testCursorPaginationOnTopOfColumns()
    {
        for ($i = 1; $i <= 50; ++$i) {
            TestPost::create([
                'title' => 'Title ' . $i,
            ]);
        }

        $this->assertCount(15, TestPost::cursorPaginate(15, ['id', 'title']));
    }

    public function testPaginationWithUnion()
    {
        TestPost::create(['title' => 'Hello world', 'user_id' => 1]);
        TestPost::create(['title' => 'GooDbye world', 'user_id' => 2]);
        TestPost::create(['title' => 'Howdy', 'user_id' => 3]);
        TestPost::create(['title' => '4th', 'user_id' => 4]);

        $table1 = TestPost::query()->whereIn('user_id', [1, 2]);
        $table2 = TestPost::query()->whereIn('user_id', [3, 4]);

        $result = $table1->unionAll($table2)
            ->orderBy('user_id', 'desc')
            ->cursorPaginate(1);

        $this->assertSame(['user_id'], $result->getOptions()['parameters']);
    }

    public function testPaginationWithDistinct()
    {
        for ($i = 1; $i <= 3; ++$i) {
            TestPost::create(['title' => 'Hello world']);
            TestPost::create(['title' => 'GooDbye world']);
        }

        $query = TestPost::query()->distinct();

        $this->assertEquals(6, $query->get()->count());
        $this->assertEquals(6, $query->count());
        $this->assertCount(6, $query->cursorPaginate()->items());
    }

    public function testPaginationWithWhereClause()
    {
        for ($i = 1; $i <= 3; ++$i) {
            TestPost::create(['title' => 'Hello world', 'user_id' => null]);
            TestPost::create(['title' => 'GooDbye world', 'user_id' => 2]);
        }

        $query = TestPost::query()->whereNull('user_id');

        $this->assertEquals(3, $query->get()->count());
        $this->assertEquals(3, $query->count());
        $this->assertCount(3, $query->cursorPaginate()->items());
    }

    public function testPaginationWithHasClause()
    {
        for ($i = 1; $i <= 3; ++$i) {
            TestUser::create();
            TestPost::create(['title' => 'Hello world', 'user_id' => null]);
            TestPost::create(['title' => 'GooDbye world', 'user_id' => 2]);
            TestPost::create(['title' => 'Howdy', 'user_id' => 3]);
        }

        $query = TestUser::query()->has('posts');

        $this->assertEquals(2, $query->get()->count());
        $this->assertEquals(2, $query->count());
        $this->assertCount(2, $query->cursorPaginate()->items());
    }

    public function testPaginationWithWhereHasClause()
    {
        for ($i = 1; $i <= 3; ++$i) {
            TestUser::create();
            TestPost::create(['title' => 'Hello world', 'user_id' => null]);
            TestPost::create(['title' => 'GooDbye world', 'user_id' => 2]);
            TestPost::create(['title' => 'Howdy', 'user_id' => 3]);
        }

        $query = TestUser::query()->whereHas('posts', function ($query) {
            $query->where('title', 'Howdy');
        });

        $this->assertEquals(1, $query->get()->count());
        $this->assertEquals(1, $query->count());
        $this->assertCount(1, $query->cursorPaginate()->items());
    }

    public function testPaginationWithWhereExistsClause()
    {
        for ($i = 1; $i <= 3; ++$i) {
            TestUser::create();
            TestPost::create(['title' => 'Hello world', 'user_id' => null]);
            TestPost::create(['title' => 'GooDbye world', 'user_id' => 2]);
            TestPost::create(['title' => 'Howdy', 'user_id' => 3]);
        }

        $query = TestUser::query()->whereExists(function ($query) {
            $query->select(Db::raw(1))
                ->from('test_posts')
                ->whereColumn('test_posts.user_id', 'test_users.id');
        });

        $this->assertEquals(2, $query->get()->count());
        $this->assertEquals(2, $query->count());
        $this->assertCount(2, $query->cursorPaginate()->items());
    }

    public function testPaginationWithMultipleWhereClauses()
    {
        for ($i = 1; $i <= 4; ++$i) {
            TestUser::create();
            TestPost::create(['title' => 'Hello world', 'user_id' => null]);
            TestPost::create(['title' => 'GooDbye world', 'user_id' => 2]);
            TestPost::create(['title' => 'Howdy', 'user_id' => 3]);
            TestPost::create(['title' => 'Howdy', 'user_id' => 4]);
        }

        $query = TestUser::query()->whereExists(function ($query) {
            $query->select(Db::raw(1))
                ->from('test_posts')
                ->whereColumn('test_posts.user_id', 'test_users.id');
        })->whereHas('posts', function ($query) {
            $query->where('title', 'Howdy');
        })->where('id', '<', 5)->orderBy('id');

        $clonedQuery = $query->clone();
        $anotherQuery = $query->clone();

        $this->assertEquals(2, $query->get()->count());
        $this->assertEquals(2, $query->count());
        $this->assertCount(2, $query->cursorPaginate()->items());
        $this->assertCount(1, $clonedQuery->cursorPaginate(1)->items());
        $this->assertCount(
            1,
            $anotherQuery->cursorPaginate(5, ['*'], 'cursor', new Cursor(['id' => 3]))
                ->items()
        );
    }

    public function testPaginationWithMultipleUnionAndMultipleWhereClauses(): void
    {
        TestPost::create(['title' => 'Post A', 'user_id' => 100]);
        TestPost::create(['title' => 'Post B', 'user_id' => 101]);

        $table1 = TestPost::select(['id', 'title', 'user_id'])->where('user_id', 100);
        $table2 = TestPost::select(['id', 'title', 'user_id'])->where('user_id', 101);
        $table3 = TestPost::select(['id', 'title', 'user_id'])->where('user_id', 101);

        $columns = ['id'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['id' => 1]);

        $result = $table1->toBase()
            ->union($table2->toBase())
            ->union($table3->toBase())
            ->orderBy('id', 'asc')
            ->cursorPaginate(1, $columns, $cursorName, $cursor);

        $this->assertSame(['id'], $result->getOptions()['parameters']);

        $postB = $table2->where('id', '>', 1)->first();
        $this->assertEquals('Post B', $postB->title, 'Expect `Post B` is the result of the second query');

        $this->assertCount(1, $result->items(), 'Expect cursor paginated query should have 1 result');
        $this->assertEquals('Post B', current($result->items())->title, 'Expect the paginated query would return `Post B`');
    }

    public function testPaginationWithMultipleAliases(): void
    {
        TestUser::create(['name' => 'A (user)']);
        TestUser::create(['name' => 'C (user)']);

        TestPost::create(['title' => 'B (post)']);
        TestPost::create(['title' => 'D (post)']);

        $table1 = TestPost::select(['title as alias']);
        $table2 = TestUser::select(['name as alias']);

        $columns = ['alias'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['alias' => 'A (user)']);

        $result = $table1->toBase()
            ->union($table2->toBase())
            ->orderBy('alias', 'asc')
            ->cursorPaginate(1, $columns, $cursorName, $cursor);

        $this->assertSame(['alias'], $result->getOptions()['parameters']);

        $this->assertCount(1, $result->items(), 'Expect cursor paginated query should have 1 result');
        $this->assertEquals('B (post)', current($result->items())->alias, 'Expect the paginated query would return `B (post)`');
    }

    public function testPaginationWithAliasedOrderBy(): void
    {
        for ($i = 1; $i <= 6; ++$i) {
            TestUser::create();
        }

        $query = TestUser::query()->select('id as user_id')->orderBy('user_id');
        $clonedQuery = $query->clone();
        $anotherQuery = $query->clone();

        $this->assertEquals(6, $query->get()->count());
        $this->assertEquals(6, $query->count());
        $this->assertCount(6, $query->cursorPaginate()->items());
        $this->assertCount(3, $clonedQuery->cursorPaginate(3)->items());
        $this->assertCount(
            4,
            $anotherQuery->cursorPaginate(10, ['*'], 'cursor', new Cursor(['user_id' => 2]))
                ->items()
        );
    }

    public function testPaginationWithDistinctColumnsAndSelect(): void
    {
        $this->assertTrue(true);
        // distinct is not supported column in MySQL
        /* for ($i = 1; $i <= 3; ++$i) {
             TestPost::create(['title' => 'Hello world']);
             TestPost::create(['title' => 'GooDbye world']);
         }

         $query = TestPost::query()->orderBy('title')->distinct('title')->select('title');

         $this->assertEquals(2, $query->get()->count());
         $this->assertEquals(2, $query->count());
         $this->assertCount(2, $query->cursorPaginate()->items());*/
    }

    public function testPaginationWithDistinctColumnsAndSelectAndJoin(): void
    {
        $this->assertTrue(true);
        // distinct is not supported column in MySQL
        /*for ($i = 1; $i <= 5; ++$i) {
            $user = TestUser::create();

            for ($j = 1; $j <= 10; ++$j) {
                TestPost::create([
                    'title' => 'Title ' . $i,
                    'user_id' => $user->id,
                ]);
            }
        }

        $query = TestUser::query()->join('test_posts', 'test_posts.user_id', '=', 'test_users.id')
            ->distinct('test_users.id')->select('test_users.*');

        $this->assertEquals(5, $query->get()->count());
        $this->assertEquals(5, $query->count());
        $this->assertCount(5, $query->cursorPaginate()->items());*/
    }

    protected function getContainer()
    {
        $dispatcher = Mockery::mock(EventDispatcherInterface::class);
        $dispatcher->allows('dispatch')->with(Mockery::any())->andReturnUsing(function ($event) {
            $this->channel->push($event);
        });
        $container = ContainerStub::getContainer(function ($conn) use ($dispatcher) {
            $conn->setEventDispatcher($dispatcher);
        });
        $container->allows('get')->with(EventDispatcherInterface::class)->andReturns($dispatcher);

        return $container;
    }
}

class TestPost extends Model
{
    protected array $guarded = [];
}

class TestUser extends Model
{
    protected array $guarded = [];

    public function posts()
    {
        return $this->hasMany(TestPost::class, 'user_id');
    }
}
