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

use Hyperf\Collection\Collection;
use Hyperf\Context\ApplicationContext;
use Hyperf\Database\Connection;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Exception\InvalidBindingException;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Expression as Raw;
use Hyperf\Database\Query\Grammars\Grammar;
use Hyperf\Database\Query\Grammars\MySqlGrammar;
use Hyperf\Database\Query\Processors\MySqlProcessor;
use Hyperf\Database\Query\Processors\Processor;
use Hyperf\Paginator\Cursor;
use Hyperf\Paginator\CursorPaginator;
use Hyperf\Paginator\Paginator;
use InvalidArgumentException;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RuntimeException;
use TypeError;

use function Hyperf\Collection\collect;
use function Hyperf\Support\now;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class DatabaseQueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ApplicationContext::setContainer(m::mock(ContainerInterface::class));
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testBasicSelect(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users');
        $this->assertSame('select * from "users"', $builder->toSql());
    }

    public function testBasicSelectWithGetColumns(): void
    {
        $builder = $this->getBuilder();
        $builder->getProcessor()->shouldReceive('processSelect');
        $builder->getConnection()->shouldReceive('select')->once()->andReturnUsing(function ($sql) {
            $this->assertSame('select * from "users"', $sql);
            return [];
        });
        $builder->getConnection()->shouldReceive('select')->once()->andReturnUsing(function ($sql) {
            $this->assertSame('select "foo", "bar" from "users"', $sql);
            return [];
        });
        $builder->getConnection()->shouldReceive('select')->once()->andReturnUsing(function ($sql) {
            $this->assertSame('select "baz" from "users"', $sql);
            return [];
        });

        $builder->from('users')->get();
        $this->assertNull($builder->columns);

        $builder->from('users')->get(['foo', 'bar']);
        $this->assertNull($builder->columns);

        $builder->from('users')->get('baz');
        $this->assertNull($builder->columns);

        $this->assertSame('select * from "users"', $builder->toSql());
        $this->assertNull($builder->columns);
    }

    public function testBasicSelectUseWritePdo(): void
    {
        $builder = $this->getMySqlBuilderWithProcessor();
        $builder->getConnection()->shouldReceive('select')->once()
            ->with('select * from `users`', [], false);
        $builder->useWritePdo()->select('*')->from('users')->get();

        $builder = $this->getMySqlBuilderWithProcessor();
        $builder->getConnection()->shouldReceive('select')->once()
            ->with('select * from `users`', [], true);
        $builder->select('*')->from('users')->get();

        $this->assertTrue(true);
    }

    public function testBasicTableWrappingProtectsQuotationMarks(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('some"table');
        $this->assertSame('select * from "some""table"', $builder->toSql());
    }

    public function testAliasWrappingAsWholeConstant(): void
    {
        $builder = $this->getBuilder();
        $builder->select('x.y as foo.bar')->from('baz');
        $this->assertSame('select "x"."y" as "foo.bar" from "baz"', $builder->toSql());
    }

    public function testAliasWrappingWithSpacesInDatabaseName(): void
    {
        $builder = $this->getBuilder();
        $builder->select('w x.y.z as foo.bar')->from('baz');
        $this->assertSame('select "w x"."y"."z" as "foo.bar" from "baz"', $builder->toSql());
    }

    public function testAddingSelects(): void
    {
        $builder = $this->getBuilder();
        $builder->select('foo')->addSelect('bar')->addSelect(['baz', 'boom'])->from('users');
        $this->assertSame('select "foo", "bar", "baz", "boom" from "users"', $builder->toSql());
    }

    public function testBasicSelectWithPrefix(): void
    {
        $builder = $this->getBuilder();
        $builder->getGrammar()->setTablePrefix('prefix_');
        $builder->select('*')->from('users');
        $this->assertSame('select * from "prefix_users"', $builder->toSql());
    }

    public function testUseIndex(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->useIndex('index1');
        $this->assertSame('select * from `users` use index (index1)', $builder->toSql());

        $builder->select('*')->from('users')->useIndex('index2');
        $this->assertSame('select * from `users` use index (index2)', $builder->toSql());

        $builder->select('*')->from('users')->useIndex('index1,index2');
        $this->assertSame('select * from `users` use index (index1,index2)', $builder->toSql());

        $builder = $this->getMySqlBuilder()->select('*')->from('users');
        $this->assertSame('select * from `users`', $builder->toSql());
    }

    public function testForceIndex(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder1 = (clone $builder)->select('*')->from('users')->where('username', 'xxx')->forceIndex('index1');
        $foeceses = (clone $builder)->select('*')->from('users')->where('username', 'xxx')->forceIndexes(['index1']);

        $this->assertSame('select * from `users` force index (index1) where `username` = ?', $builder1->toSql());
        $this->assertSame('select * from `users` force index (`index1`) where `username` = ?', $foeceses->toSql());

        $builder2 = (clone $builder);
        $builder2->select('*')->from('users')->where('username', 'xxx')->forceIndex('index2');
        $foeceses = (clone $builder);
        $foeceses->select('*')->from('users')->where('username', 'xxx')->forceIndexes(['index2']);
        $this->assertSame('select * from `users` force index (index2) where `username` = ?', $builder2->toSql());
        $this->assertSame('select * from `users` force index (`index2`) where `username` = ?', $foeceses->toSql());

        $builder3 = (clone $builder);
        $foeceses = (clone $builder);
        $builder3->select('*')->from('users')->where('username', 'xxx')->forceIndex('index1,index2');
        $foeceses->select('*')->from('users')->where('username', 'xxx')->forceIndexes(['index1', 'index2']);
        $this->assertSame('select * from `users` force index (index1,index2) where `username` = ?', $builder3->toSql());
        $this->assertSame('select * from `users` force index (`index1`,`index2`) where `username` = ?', $foeceses->toSql());
    }

    public function testIgnoreIndex(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->ignoreIndex('index1');
        $this->assertSame('select * from `users` ignore index (index1)', $builder->toSql());
        $builder->select('*')->from('users')->ignoreIndex('index2');
        $this->assertSame('select * from `users` ignore index (index2)', $builder->toSql());
        $builder->select('*')->from('users')->ignoreIndex('index1,index2');
        $this->assertSame('select * from `users` ignore index (index1,index2)', $builder->toSql());
    }

    public function testBasicSelectDistinct(): void
    {
        $builder = $this->getBuilder();
        $builder->distinct()->select('foo', 'bar')->from('users');
        $this->assertSame('select distinct "foo", "bar" from "users"', $builder->toSql());
    }

    public function testBasicSelectDistinctOnColumns(): void
    {
        $builder = $this->getBuilder();
        $builder->distinct('foo')->select('foo', 'bar')->from('users');
        $this->assertSame('select distinct "foo", "bar" from "users"', $builder->toSql());
    }

    public function testBasicAlias(): void
    {
        $builder = $this->getBuilder();
        $builder->select('foo as bar')->from('users');
        $this->assertSame('select "foo" as "bar" from "users"', $builder->toSql());
    }

    public function testAliasWithPrefix(): void
    {
        $builder = $this->getBuilder();
        $builder->getGrammar()->setTablePrefix('prefix_');
        $builder->select('*')->from('users as people');
        $this->assertSame('select * from "prefix_users" as "prefix_people"', $builder->toSql());
    }

    public function testJoinAliasesWithPrefix(): void
    {
        $builder = $this->getBuilder();
        $builder->getGrammar()->setTablePrefix('prefix_');
        $builder->select('*')->from('services')->join('translations AS t', 't.item_id', '=', 'services.id');
        $this->assertSame('select * from "prefix_services" inner join "prefix_translations" as "prefix_t" on "prefix_t"."item_id" = "prefix_services"."id"', $builder->toSql());
    }

    public function testBasicTableWrapping(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('public.users');
        $this->assertSame('select * from "public"."users"', $builder->toSql());
    }

    public function testWhenCallback(): void
    {
        $callback = function ($query, $condition) {
            $this->assertTrue($condition);

            $query->where('id', '=', 1);
        };

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->when(true, $callback)->where('email', 'foo');
        $this->assertSame('select * from "users" where "id" = ? and "email" = ?', $builder->toSql());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->when(false, $callback)->where('email', 'foo');
        $this->assertSame('select * from "users" where "email" = ?', $builder->toSql());
    }

    public function testWhenValueOfCallback(): void
    {
        $callback = function (Builder $query, $condition) {
            $this->assertTrue($condition);

            $query->where('id', '=', 1);
        };

        $builder = $this->getBuilder();
        $builder->select('*')
            ->from('users')
            ->when(fn (Builder $query) => true, $callback)
            ->where('email', 'foo');
        $this->assertSame('select * from "users" where "id" = ? and "email" = ?', $builder->toSql());

        $builder = $this->getBuilder();
        $builder->select('*')
            ->from('users')
            ->when(fn (Builder $query) => false, $callback)
            ->where('email', 'foo');
        $this->assertSame('select * from "users" where "email" = ?', $builder->toSql());
    }

    public function testWhenCallbackWithReturn(): void
    {
        $callback = function ($query, $condition) {
            $this->assertTrue($condition);

            return $query->where('id', '=', 1);
        };

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->when(true, $callback)->where('email', 'foo');
        $this->assertSame('select * from "users" where "id" = ? and "email" = ?', $builder->toSql());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->when(false, $callback)->where('email', 'foo');
        $this->assertSame('select * from "users" where "email" = ?', $builder->toSql());
    }

    public function testWhenCallbackWithDefault(): void
    {
        $callback = function ($query, $condition) {
            $this->assertEquals($condition, 'truthy');

            $query->where('id', '=', 1);
        };

        $default = function ($query, $condition) {
            $this->assertEquals($condition, 0);

            $query->where('id', '=', 2);
        };

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->when('truthy', $callback, $default)->where('email', 'foo');
        $this->assertSame('select * from "users" where "id" = ? and "email" = ?', $builder->toSql());
        $this->assertEquals([0 => 1, 1 => 'foo'], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->when(0, $callback, $default)->where('email', 'foo');
        $this->assertSame('select * from "users" where "id" = ? and "email" = ?', $builder->toSql());
        $this->assertEquals([0 => 2, 1 => 'foo'], $builder->getBindings());
    }

    public function testUnlessCallback(): void
    {
        $callback = function ($query, $condition) {
            $this->assertFalse($condition);

            $query->where('id', '=', 1);
        };

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->unless(false, $callback)->where('email', 'foo');
        $this->assertSame('select * from "users" where "id" = ? and "email" = ?', $builder->toSql());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->unless(true, $callback)->where('email', 'foo');
        $this->assertSame('select * from "users" where "email" = ?', $builder->toSql());
    }

    public function testUnlessValueOfCallback(): void
    {
        $callback = function (Builder $query, $condition) {
            $this->assertFalse($condition);

            $query->where('id', '=', 1);
        };

        $builder = $this->getBuilder();
        $builder->select('*')
            ->from('users')
            ->unless(fn (Builder $query) => true, $callback)
            ->where('email', 'foo');
        $this->assertSame('select * from "users" where "email" = ?', $builder->toSql());

        $builder = $this->getBuilder();
        $builder->select('*')
            ->from('users')
            ->unless(fn (Builder $query) => false, $callback)
            ->where('email', 'foo');
        $this->assertSame('select * from "users" where "id" = ? and "email" = ?', $builder->toSql());
    }

    public function testUnlessCallbackWithReturn(): void
    {
        $callback = function ($query, $condition) {
            $this->assertFalse($condition);

            return $query->where('id', '=', 1);
        };

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->unless(false, $callback)->where('email', 'foo');
        $this->assertSame('select * from "users" where "id" = ? and "email" = ?', $builder->toSql());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->unless(true, $callback)->where('email', 'foo');
        $this->assertSame('select * from "users" where "email" = ?', $builder->toSql());
    }

    public function testUnlessCallbackWithDefault(): void
    {
        $callback = function ($query, $condition) {
            $this->assertEquals($condition, 0);

            $query->where('id', '=', 1);
        };

        $default = function ($query, $condition) {
            $this->assertEquals($condition, 'truthy');

            $query->where('id', '=', 2);
        };

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->unless(0, $callback, $default)->where('email', 'foo');
        $this->assertSame('select * from "users" where "id" = ? and "email" = ?', $builder->toSql());
        $this->assertEquals([0 => 1, 1 => 'foo'], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->unless('truthy', $callback, $default)->where('email', 'foo');
        $this->assertSame('select * from "users" where "id" = ? and "email" = ?', $builder->toSql());
        $this->assertEquals([0 => 2, 1 => 'foo'], $builder->getBindings());
    }

    public function testTapCallback(): void
    {
        $callback = function ($query) {
            return $query->where('id', '=', 1);
        };

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->tap($callback)->where('email', 'foo');
        $this->assertSame('select * from "users" where "id" = ? and "email" = ?', $builder->toSql());
    }

    public function testBasicWheres(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('id', '=', 1);
        $this->assertSame('select * from "users" where "id" = ?', $builder->toSql());
        $this->assertEquals([0 => 1], $builder->getBindings());
    }

    public function testWhereWithArrayValue(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('id', 12);
        $this->assertSame('select * from "users" where "id" = ?', $builder->toSql());
        $this->assertEquals([0 => 12], $builder->getBindings());

        $this->expectException(InvalidBindingException::class);
        $this->expectExceptionMessage('The value of column id is invalid.');

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('id', [12]);
    }

    public function testWhereBetweenWithArrayValue(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereBetween('id', [1, 100]);
        $this->assertSame('select * from "users" where "id" between ? and ?', $builder->toSql());
        $this->assertEquals([0 => 1, 1 => 100], $builder->getBindings());

        $this->expectException(InvalidBindingException::class);
        $this->expectExceptionMessage('The value length of column id is not equal with 2.');

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereBetween('id', [1, 2, 3]);
    }

    public function testWhereBetweenWithoutArrayValue(): void
    {
        $this->expectException(TypeError::class);

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereBetween('id', 1);
    }

    public function testMySqlWrappingProtectsQuotationMarks(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder->select('*')->From('some`table');
        $this->assertSame('select * from `some``table`', $builder->toSql());
    }

    public function testDateBasedWheresAcceptsTwoArguments(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereDate('created_at', 1);
        $this->assertSame('select * from `users` where date(`created_at`) = ?', $builder->toSql());

        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereDay('created_at', 1);
        $this->assertSame('select * from `users` where day(`created_at`) = ?', $builder->toSql());

        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereMonth('created_at', 1);
        $this->assertSame('select * from `users` where month(`created_at`) = ?', $builder->toSql());

        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereYear('created_at', 1);
        $this->assertSame('select * from `users` where year(`created_at`) = ?', $builder->toSql());
    }

    public function testDateBasedOrWheresAcceptsTwoArguments(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->where('id', 1)->orWhereDate('created_at', 1);
        $this->assertSame('select * from `users` where `id` = ? or date(`created_at`) = ?', $builder->toSql());

        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->where('id', 1)->orWhereDay('created_at', 1);
        $this->assertSame('select * from `users` where `id` = ? or day(`created_at`) = ?', $builder->toSql());

        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->where('id', 1)->orWhereMonth('created_at', 1);
        $this->assertSame('select * from `users` where `id` = ? or month(`created_at`) = ?', $builder->toSql());

        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->where('id', 1)->orWhereYear('created_at', 1);
        $this->assertSame('select * from `users` where `id` = ? or year(`created_at`) = ?', $builder->toSql());
    }

    public function testDateBasedWheresExpressionIsNotBound(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereDate('created_at', new Raw('NOW()'))->where('admin', true);
        $this->assertEquals([true], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereDay('created_at', new Raw('NOW()'));
        $this->assertEquals([], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereMonth('created_at', new Raw('NOW()'));
        $this->assertEquals([], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereYear('created_at', new Raw('NOW()'));
        $this->assertEquals([], $builder->getBindings());
    }

    public function testWhereDateMySql(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereDate('created_at', '=', '2015-12-21');
        $this->assertSame('select * from `users` where date(`created_at`) = ?', $builder->toSql());
        $this->assertEquals([0 => '2015-12-21'], $builder->getBindings());

        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereDate('created_at', '=', new Raw('NOW()'));
        $this->assertSame('select * from `users` where date(`created_at`) = NOW()', $builder->toSql());
    }

    public function testWhereDayMySql(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereDay('created_at', '=', 1);
        $this->assertSame('select * from `users` where day(`created_at`) = ?', $builder->toSql());
        $this->assertEquals([0 => 1], $builder->getBindings());
    }

    public function testOrWhereDayMySql(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereDay('created_at', '=', 1)->orWhereDay('created_at', '=', 2);
        $this->assertSame('select * from `users` where day(`created_at`) = ? or day(`created_at`) = ?', $builder->toSql());
        $this->assertEquals([0 => 1, 1 => 2], $builder->getBindings());
    }

    public function testWhereMonthMySql(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereMonth('created_at', '=', 5);
        $this->assertSame('select * from `users` where month(`created_at`) = ?', $builder->toSql());
        $this->assertEquals([0 => 5], $builder->getBindings());
    }

    public function testOrWhereMonthMySql(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereMonth('created_at', '=', 5)->orWhereMonth('created_at', '=', 6);
        $this->assertSame('select * from `users` where month(`created_at`) = ? or month(`created_at`) = ?', $builder->toSql());
        $this->assertEquals([0 => 5, 1 => 6], $builder->getBindings());
    }

    public function testWhereYearMySql(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereYear('created_at', '=', 2014);
        $this->assertSame('select * from `users` where year(`created_at`) = ?', $builder->toSql());
        $this->assertEquals([0 => 2014], $builder->getBindings());
    }

    public function testOrWhereYearMySql(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereYear('created_at', '=', 2014)->orWhereYear('created_at', '=', 2015);
        $this->assertSame('select * from `users` where year(`created_at`) = ? or year(`created_at`) = ?', $builder->toSql());
        $this->assertEquals([0 => 2014, 1 => 2015], $builder->getBindings());
    }

    public function testWhereTimeMySql(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereTime('created_at', '>=', '22:00');
        $this->assertSame('select * from `users` where time(`created_at`) >= ?', $builder->toSql());
        $this->assertEquals([0 => '22:00'], $builder->getBindings());
    }

    public function testWhereTimeMySqlWithArrayValue(): void
    {
        $this->expectException(InvalidBindingException::class);
        $this->expectExceptionMessage('The value of column created_at is invalid.');

        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereTime('created_at', '>=', ['22:00', '10:00']);
    }

    public function testWhereTimeOperatorOptionalMySql(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereTime('created_at', '22:00');
        $this->assertSame('select * from `users` where time(`created_at`) = ?', $builder->toSql());
        $this->assertEquals([0 => '22:00'], $builder->getBindings());
    }

    public function testWhereBetweens(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereBetween('id', [1, 2]);
        $this->assertSame('select * from "users" where "id" between ? and ?', $builder->toSql());
        $this->assertEquals([0 => 1, 1 => 2], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereNotBetween('id', [1, 2]);
        $this->assertSame('select * from "users" where "id" not between ? and ?', $builder->toSql());
        $this->assertEquals([0 => 1, 1 => 2], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereBetween('id', [new Raw(1), new Raw(2)]);
        $this->assertSame('select * from "users" where "id" between 1 and 2', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    public function testBasicOrWheres(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('id', '=', 1)->orWhere('email', '=', 'foo');
        $this->assertSame('select * from "users" where "id" = ? or "email" = ?', $builder->toSql());
        $this->assertEquals([0 => 1, 1 => 'foo'], $builder->getBindings());
    }

    public function testRawWheres(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereRaw('id = ? or email = ?', [1, 'foo']);
        $this->assertSame('select * from "users" where id = ? or email = ?', $builder->toSql());
        $this->assertEquals([0 => 1, 1 => 'foo'], $builder->getBindings());
    }

    public function testRawOrWheres(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('id', '=', 1)->orWhereRaw('email = ?', ['foo']);
        $this->assertSame('select * from "users" where "id" = ? or email = ?', $builder->toSql());
        $this->assertEquals([0 => 1, 1 => 'foo'], $builder->getBindings());
    }

    public function testBasicWhereIns(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereIn('id', [1, 2, 3]);
        $this->assertSame('select * from "users" where "id" in (?, ?, ?)', $builder->toSql());
        $this->assertEquals([0 => 1, 1 => 2, 2 => 3], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('id', '=', 1)->orWhereIn('id', [1, 2, 3]);
        $this->assertSame('select * from "users" where "id" = ? or "id" in (?, ?, ?)', $builder->toSql());
        $this->assertEquals([0 => 1, 1 => 1, 2 => 2, 3 => 3], $builder->getBindings());
    }

    public function testBasicWhereNotIns(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereNotIn('id', [1, 2, 3]);
        $this->assertSame('select * from "users" where "id" not in (?, ?, ?)', $builder->toSql());
        $this->assertEquals([0 => 1, 1 => 2, 2 => 3], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('id', '=', 1)->orWhereNotIn('id', [1, 2, 3]);
        $this->assertSame('select * from "users" where "id" = ? or "id" not in (?, ?, ?)', $builder->toSql());
        $this->assertEquals([0 => 1, 1 => 1, 2 => 2, 3 => 3], $builder->getBindings());
    }

    public function testRawWhereIns(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereIn('id', [new Raw(1)]);
        $this->assertSame('select * from "users" where "id" in (1)', $builder->toSql());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('id', '=', 1)->orWhereIn('id', [new Raw(1)]);
        $this->assertSame('select * from "users" where "id" = ? or "id" in (1)', $builder->toSql());
        $this->assertEquals([0 => 1], $builder->getBindings());
    }

    public function testEmptyWhereIns(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereIn('id', []);
        $this->assertSame('select * from "users" where 0 = 1', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('id', '=', 1)->orWhereIn('id', []);
        $this->assertSame('select * from "users" where "id" = ? or 0 = 1', $builder->toSql());
        $this->assertEquals([0 => 1], $builder->getBindings());
    }

    public function testEmptyWhereNotIns(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereNotIn('id', []);
        $this->assertSame('select * from "users" where 1 = 1', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('id', '=', 1)->orWhereNotIn('id', []);
        $this->assertSame('select * from "users" where "id" = ? or 1 = 1', $builder->toSql());
        $this->assertEquals([0 => 1], $builder->getBindings());
    }

    public function testWhereIntegerInRaw(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereIntegerInRaw('id', ['1a', 2]);
        $this->assertSame('select * from "users" where "id" in (1, 2)', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    public function testWhereIntegerNotInRaw(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereIntegerNotInRaw('id', ['1a', 2]);
        $this->assertSame('select * from "users" where "id" not in (1, 2)', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    public function testEmptyWhereIntegerInRaw(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereIntegerInRaw('id', []);
        $this->assertSame('select * from "users" where 0 = 1', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    public function testExplain(): void
    {
        $builder = $this->getBuilder();
        /**
         * @var ConnectionInterface|MockInterface $connection
         */
        $connection = $builder->getConnection();
        $connection->allows('select')
            ->once()
            ->withArgs(function ($sql, $bindings) {
                $this->assertSame($sql, 'EXPLAIN select * from "users" where 0 = 1');
                $this->assertIsArray($bindings);
                $this->assertCount(0, $bindings);
                return $sql === 'EXPLAIN select * from "users" where 0 = 1' && $bindings === [];
            })
            ->andReturn([]);
        $builder->select('*')->from('users')->whereIntegerInRaw('id', []);
        $this->assertCount(0, $builder->explain());

        $builder = $this->getBuilder();
        /**
         * @var ConnectionInterface|MockInterface $connection
         */
        $connection = $builder->getConnection();
        $connection->allows('select')
            ->once()
            ->withArgs(function ($sql, $bindings) {
                $this->assertSame($sql, 'EXPLAIN select * from "hyperf" where "id" in (?, ?, ?)');
                $this->assertIsArray($bindings);
                $this->assertCount(3, $bindings);
                return $sql === 'EXPLAIN select * from "hyperf" where "id" in (?, ?, ?)' && $bindings === [1, 2, 3];
            })
            ->andReturn([]);
        $builder->select('*')->from('hyperf')->whereIn('id', [1, 2, 3]);
        $this->assertCount(0, $builder->explain());
    }

    public function testEmptyWhereIntegerNotInRaw(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereIntegerNotInRaw('id', []);
        $this->assertSame('select * from "users" where 1 = 1', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    public function testBasicWhereColumn(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereColumn('first_name', 'last_name')->orWhereColumn('first_name', 'middle_name');
        $this->assertSame('select * from "users" where "first_name" = "last_name" or "first_name" = "middle_name"', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereColumn('updated_at', '>', 'created_at');
        $this->assertSame('select * from "users" where "updated_at" > "created_at"', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    public function testArrayWhereColumn(): void
    {
        $conditions = [
            ['first_name', 'last_name'],
            ['updated_at', '>', 'created_at'],
        ];

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereColumn($conditions);
        $this->assertSame('select * from "users" where ("first_name" = "last_name" and "updated_at" > "created_at")', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    public function testJoinSubWithPrefix()
    {
        $builder = $this->getBuilder();
        $builder->getGrammar()->setTablePrefix('prefix_');
        $builder->from('users')->joinSub('select * from "contacts"', 'sub', 'users.id', '=', 'sub.id');
        $this->assertEquals('select * from "prefix_users" inner join (select * from "contacts") as "prefix_sub" on "prefix_users"."id" = "prefix_sub"."id"', $builder->toSql());
    }

    public function testFromSubWithPrefix()
    {
        $builder = $this->getBuilder();
        $builder->getGrammar()->setTablePrefix('prefix_');
        $builder->fromSub(function ($query) {
            $query->select(new Raw('max(last_seen_at) as last_seen_at'))->from('user_sessions')->where('foo', '=', '1');
        }, 'sessions')->where('bar', '<', '10');
        $this->assertEquals('select * from (select max(last_seen_at) as last_seen_at from "prefix_user_sessions" where "foo" = ?) as "prefix_sessions" where "bar" < ?', $builder->toSql());
        $this->assertEquals(['1', '10'], $builder->getBindings());
    }

    public function testWhereFulltext()
    {
        $builder = $this->getMySqlBuilderWithProcessor();
        $builder->select('*')->from('users')->whereFullText('body', 'Hello World');
        $this->assertSame('select * from `users` where match (`body`) against (? in natural language mode)', $builder->toSql());
        $this->assertEquals(['Hello World'], $builder->getBindings());

        $builder = $this->getMySqlBuilderWithProcessor();
        $builder->select('*')->from('users')->whereFullText('body', 'Hello World', ['expanded' => true]);
        $this->assertSame('select * from `users` where match (`body`) against (? in natural language mode with query expansion)', $builder->toSql());
        $this->assertEquals(['Hello World'], $builder->getBindings());

        $builder = $this->getMySqlBuilderWithProcessor();
        $builder->select('*')->from('users')->whereFullText('body', '+Hello -World', ['mode' => 'boolean']);
        $this->assertSame('select * from `users` where match (`body`) against (? in boolean mode)', $builder->toSql());
        $this->assertEquals(['+Hello -World'], $builder->getBindings());

        $builder = $this->getMySqlBuilderWithProcessor();
        $builder->select('*')->from('users')->whereFullText('body', '+Hello -World', ['mode' => 'boolean', 'expanded' => true]);
        $this->assertSame('select * from `users` where match (`body`) against (? in boolean mode)', $builder->toSql());
        $this->assertEquals(['+Hello -World'], $builder->getBindings());

        $builder = $this->getMySqlBuilderWithProcessor();
        $builder->select('*')->from('users')->whereFullText(['body', 'title'], 'Car,Plane');
        $this->assertSame('select * from `users` where match (`body`, `title`) against (? in natural language mode)', $builder->toSql());
        $this->assertEquals(['Car,Plane'], $builder->getBindings());
    }

    public function testWhereAll()
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereAll(['last_name', 'email'], '%Otwell%');
        $this->assertSame('select * from "users" where ("last_name" = ? and "email" = ?)', $builder->toSql());
        $this->assertEquals(['%Otwell%', '%Otwell%'], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereAll(['last_name', 'email'], 'not like', '%Otwell%');
        $this->assertSame('select * from "users" where ("last_name" not like ? and "email" not like ?)', $builder->toSql());
        $this->assertEquals(['%Otwell%', '%Otwell%'], $builder->getBindings());
    }

    public function testOrWhereAll()
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('first_name', 'like', '%Taylor%')->orWhereAll(['last_name', 'email'], 'like', '%Otwell%');
        $this->assertSame('select * from "users" where "first_name" like ? or ("last_name" like ? and "email" like ?)', $builder->toSql());
        $this->assertEquals(['%Taylor%', '%Otwell%', '%Otwell%'], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('first_name', 'like', '%Taylor%')->whereAll(['last_name', 'email'], 'like', '%Otwell%', 'or');
        $this->assertSame('select * from "users" where "first_name" like ? or ("last_name" like ? and "email" like ?)', $builder->toSql());
        $this->assertEquals(['%Taylor%', '%Otwell%', '%Otwell%'], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('first_name', 'like', '%Taylor%')->orWhereAll(['last_name', 'email'], '%Otwell%');
        $this->assertSame('select * from "users" where "first_name" like ? or ("last_name" = ? and "email" = ?)', $builder->toSql());
        $this->assertEquals(['%Taylor%', '%Otwell%', '%Otwell%'], $builder->getBindings());
    }

    public function testWhereAny()
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereAny(['last_name', 'email'], 'like', '%Otwell%');
        $this->assertSame('select * from "users" where ("last_name" like ? or "email" like ?)', $builder->toSql());
        $this->assertEquals(['%Otwell%', '%Otwell%'], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereAny(['last_name', 'email'], '%Otwell%');
        $this->assertSame('select * from "users" where ("last_name" = ? or "email" = ?)', $builder->toSql());
        $this->assertEquals(['%Otwell%', '%Otwell%'], $builder->getBindings());
    }

    public function testWhereNone()
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereNone(['last_name', 'email'], 'like', '%Otwell%');
        $this->assertSame('select * from "users" where not ("last_name" like ? or "email" like ?)', $builder->toSql());
        $this->assertEquals(['%Otwell%', '%Otwell%'], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereNone(['last_name', 'email'], 'Otwell');
        $this->assertSame('select * from "users" where not ("last_name" = ? or "email" = ?)', $builder->toSql());
        $this->assertEquals(['Otwell', 'Otwell'], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('first_name', 'like', '%Taylor%')->whereNone(['last_name', 'email'], 'like', '%Otwell%');
        $this->assertSame('select * from "users" where "first_name" like ? and not ("last_name" like ? or "email" like ?)', $builder->toSql());
        $this->assertEquals(['%Taylor%', '%Otwell%', '%Otwell%'], $builder->getBindings());
    }

    public function testOrWhereNone()
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('first_name', 'like', '%Taylor%')->orWhereNone(['last_name', 'email'], 'like', '%Otwell%');
        $this->assertSame('select * from "users" where "first_name" like ? or not ("last_name" like ? or "email" like ?)', $builder->toSql());
        $this->assertEquals(['%Taylor%', '%Otwell%', '%Otwell%'], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('first_name', 'like', '%Taylor%')->whereNone(['last_name', 'email'], 'like', '%Otwell%', 'or');
        $this->assertSame('select * from "users" where "first_name" like ? or not ("last_name" like ? or "email" like ?)', $builder->toSql());
        $this->assertEquals(['%Taylor%', '%Otwell%', '%Otwell%'], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('first_name', 'like', '%Taylor%')->orWhereNone(['last_name', 'email'], '%Otwell%');
        $this->assertSame('select * from "users" where "first_name" like ? or not ("last_name" = ? or "email" = ?)', $builder->toSql());
        $this->assertEquals(['%Taylor%', '%Otwell%', '%Otwell%'], $builder->getBindings());
    }

    public function testInsertOrIgnoreUsingMethod(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not support');
        $builder = $this->getBuilder();
        $builder->from('users')->insertOrIgnoreUsing(['email' => 'foo'], 'bar');
    }

    public function testMySqlInsertOrIgnoreUsingMethod(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder->getConnection()->shouldReceive('getDatabaseName');
        $builder->getConnection()->shouldReceive('affectingStatement')->once()->with('insert ignore into `table1` (`foo`) select `bar` from `table2` where `foreign_id` = ?', [0 => 5])->andReturn(1);

        $result = $builder->from('table1')->insertOrIgnoreUsing(
            ['foo'],
            function (Builder $query) {
                $query->select(['bar'])->from('table2')->where('foreign_id', '=', 5);
            }
        );

        $this->assertEquals(1, $result);
    }

    public function testMySqlInsertOrIgnoreUsingWithEmptyColumns(): void
    {
        $builder = $this->getMySqlBuilder();
        /**
         * @var Connection&MockInterface $connection
         */
        $connection = $builder->getConnection();
        $connection->allows('getDatabaseName');
        $connection->allows('affectingStatement')
            ->once()
            ->andReturnUsing(function ($sql, $bindings) {
                $this->assertSame('insert ignore into `table1` select * from `table2` where `foreign_id` = ?', $sql);
                $this->assertSame([0 => 5], $bindings);
                return 1;
            });

        $result = $builder->from('table1')->insertOrIgnoreUsing(
            [],
            function (Builder $query) {
                $query->from('table2')->where('foreign_id', '=', 5);
            }
        );

        $this->assertEquals(1, $result);
    }

    public function testMySqlInsertOrIgnoreUsingInvalidSubquery(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->getMySqlBuilder();
        $builder->from('table1')->insertOrIgnoreUsing(['foo'], ['bar']);
    }

    public function testOrWhereAny()
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('first_name', 'like', '%Taylor%')->orWhereAny(['last_name', 'email'], 'like', '%Otwell%');
        $this->assertSame('select * from "users" where "first_name" like ? or ("last_name" like ? or "email" like ?)', $builder->toSql());
        $this->assertEquals(['%Taylor%', '%Otwell%', '%Otwell%'], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('first_name', 'like', '%Taylor%')->whereAny(['last_name', 'email'], 'like', '%Otwell%', 'or');
        $this->assertSame('select * from "users" where "first_name" like ? or ("last_name" like ? or "email" like ?)', $builder->toSql());
        $this->assertEquals(['%Taylor%', '%Otwell%', '%Otwell%'], $builder->getBindings());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('first_name', 'like', '%Taylor%')->orWhereAny(['last_name', 'email'], '%Otwell%');
        $this->assertSame('select * from "users" where "first_name" like ? or ("last_name" = ? or "email" = ?)', $builder->toSql());
        $this->assertEquals(['%Taylor%', '%Otwell%', '%Otwell%'], $builder->getBindings());
    }

    public function testWhereJsonOverlapsMySql(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereJsonOverlaps('options', ['en', 'fr']);
        $this->assertSame('select * from `users` where json_overlaps(`options`, ?)', $builder->toSql());
        $this->assertEquals(['["en","fr"]'], $builder->getBindings());

        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereJsonOverlaps('users.options->languages', ['en', 'fr']);
        $this->assertSame('select * from `users` where json_overlaps(`users`.`options`, ?, \'$."languages"\')', $builder->toSql());
        $this->assertEquals(['["en","fr"]'], $builder->getBindings());

        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->where('id', '=', 1)->orWhereJsonOverlaps('options->languages', new Raw("'[\"en\", \"fr\"]'"));
        $this->assertSame('select * from `users` where `id` = ? or json_overlaps(`options`, \'["en", "fr"]\', \'$."languages"\')', $builder->toSql());
        $this->assertEquals([1], $builder->getBindings());
    }

    public function testWhereJsonDoesntOverlapMySql(): void
    {
        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->whereJsonDoesntOverlap('options->languages', ['en', 'fr']);
        $this->assertSame('select * from `users` where not json_overlaps(`options`, ?, \'$."languages"\')', $builder->toSql());
        $this->assertEquals(['["en","fr"]'], $builder->getBindings());

        $builder = $this->getMySqlBuilder();
        $builder->select('*')->from('users')->where('id', '=', 1)->orWhereJsonDoesntOverlap('options->languages', new Raw("'[\"en\", \"fr\"]'"));
        $this->assertSame('select * from `users` where `id` = ? or not json_overlaps(`options`, \'["en", "fr"]\', \'$."languages"\')', $builder->toSql());
        $this->assertEquals([1], $builder->getBindings());
    }

    public function testJoinLateral()
    {
        $builder = $this->getMySqlBuilder();
        $builder->getConnection()->shouldReceive('getDatabaseName');
        $builder->from('users')->joinLateral('select * from `contacts` where `contracts`.`user_id` = `users`.`id`', 'sub');
        $this->assertSame('select * from `users` inner join lateral (select * from `contacts` where `contracts`.`user_id` = `users`.`id`) as `sub` on true', $builder->toSql());

        $builder = $this->getMySqlBuilder();
        $builder->getConnection()->shouldReceive('getDatabaseName');
        $builder->from('users')->joinLateral(function ($q) {
            $q->from('contacts')->whereColumn('contracts.user_id', 'users.id');
        }, 'sub');
        $this->assertSame('select * from `users` inner join lateral (select * from `contacts` where `contracts`.`user_id` = `users`.`id`) as `sub` on true', $builder->toSql());

        $builder = $this->getMySqlBuilder();
        $builder->getConnection()->shouldReceive('getDatabaseName');
        $sub = $this->getMySqlBuilder();
        $sub->getConnection()->shouldReceive('getDatabaseName');
        $eloquentBuilder = $sub->from('contacts')->whereColumn('contracts.user_id', 'users.id');
        $builder->from('users')->joinLateral($eloquentBuilder, 'sub');
        $this->assertSame('select * from `users` inner join lateral (select * from `contacts` where `contracts`.`user_id` = `users`.`id`) as `sub` on true', $builder->toSql());

        $sub1 = $this->getMySqlBuilder();
        $sub1->getConnection()->shouldReceive('getDatabaseName');
        $sub1 = $sub1->from('contacts')->whereColumn('contracts.user_id', 'users.id')->where('name', 'foo');

        $sub2 = $this->getMySqlBuilder();
        $sub2->getConnection()->shouldReceive('getDatabaseName');
        $sub2 = $sub2->from('contacts')->whereColumn('contracts.user_id', 'users.id')->where('name', 'bar');

        $builder = $this->getMySqlBuilder();
        $builder->getConnection()->shouldReceive('getDatabaseName');
        $builder->from('users')->joinLateral($sub1, 'sub1')->joinLateral($sub2, 'sub2');

        $expected = 'select * from `users` ';
        $expected .= 'inner join lateral (select * from `contacts` where `contracts`.`user_id` = `users`.`id` and `name` = ?) as `sub1` on true ';
        $expected .= 'inner join lateral (select * from `contacts` where `contracts`.`user_id` = `users`.`id` and `name` = ?) as `sub2` on true';

        $this->assertEquals($expected, $builder->toSql());
        $this->assertEquals(['foo', 'bar'], $builder->getRawBindings()['join']);
    }

    public function testJoinLateralWithPrefix()
    {
        $builder = $this->getMySqlBuilder();
        $builder->getConnection()->shouldReceive('getDatabaseName');
        $builder->getGrammar()->setTablePrefix('prefix_');
        $builder->from('users')->joinLateral('select * from `contacts` where `contracts`.`user_id` = `users`.`id`', 'sub');
        $this->assertSame('select * from `prefix_users` inner join lateral (select * from `contacts` where `contracts`.`user_id` = `users`.`id`) as `prefix_sub` on true', $builder->toSql());
    }

    public function testLeftJoinLateral()
    {
        $builder = $this->getMySqlBuilder();
        $builder->getConnection()->shouldReceive('getDatabaseName');

        $sub = $this->getMySqlBuilder();
        $sub->getConnection()->shouldReceive('getDatabaseName');

        $builder->from('users')
            ->leftJoinLateral($sub->from('contacts')->whereColumn('contracts.user_id', 'users.id'), 'sub');
        $this->assertSame('select * from `users` left join lateral (select * from `contacts` where `contracts`.`user_id` = `users`.`id`) as `sub` on true', $builder->toSql());
    }

    public function testIncrementManyArgumentValidation1()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Non-numeric value passed as increment amount for column: \'col\'.');
        $builder = $this->getBuilder();
        $builder->from('users')->incrementEach(['col' => 'a']);
    }

    public function testIncrementManyArgumentValidation2()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Non-associative array passed to incrementEach method.');
        $builder = $this->getBuilder();
        $builder->from('users')->incrementEach([11 => 11]);
    }

    public function testDecrementManyArgumentValidation1()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Non-numeric value passed as decrement amount for column: \'col\'.');
        $builder = $this->getBuilder();
        $builder->from('users')->decrementEach(['col' => 'a']);
    }

    public function testDecrementManyArgumentValidation2()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Non-associative array passed to decrementEach method.');
        $builder = $this->getBuilder();
        $builder->from('users')->decrementEach([11 => 11]);
    }

    public function testCursorPaginate()
    {
        $perPage = 16;
        $columns = ['test'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['test' => 'bar']);
        $builder = $this->getMockQueryBuilder();
        $builder->from('foobar')->orderBy('test');
        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor=' . $cursor->encode();

        $results = collect([['test' => 'foo'], ['test' => 'bar']]);

        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results) {
            $this->assertEquals(
                'select * from "foobar" where ("test" > ?) order by "test" asc limit 17',
                $builder->toSql()
            );
            $this->assertEquals(['bar'], $builder->bindings['where']);

            return $results;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate($perPage, $columns, $cursorName, $cursor);

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['test'],
        ]), $result);
    }

    public function testCursorPaginateMultipleOrderColumns()
    {
        $perPage = 16;
        $columns = ['test', 'another'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['test' => 'bar', 'another' => 'foo']);
        $builder = $this->getMockQueryBuilder();
        $builder->from('foobar')->orderBy('test')->orderBy('another');
        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor=' . $cursor->encode();

        $results = collect([['test' => 'foo', 'another' => 1], ['test' => 'bar', 'another' => 2]]);

        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results) {
            $this->assertEquals(
                'select * from "foobar" where ("test" > ? or ("test" = ? and ("another" > ?))) order by "test" asc, "another" asc limit 17',
                $builder->toSql()
            );
            $this->assertEquals(['bar', 'bar', 'foo'], $builder->bindings['where']);

            return $results;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate($perPage, $columns, $cursorName, $cursor);

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['test', 'another'],
        ]), $result);
    }

    public function testCursorPaginateWithDefaultArguments()
    {
        $perPage = 15;
        $cursorName = 'cursor';
        $cursor = new Cursor(['test' => 'bar']);
        $builder = $this->getMockQueryBuilder();
        $builder->from('foobar')->orderBy('test');
        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor=' . $cursor->encode();

        $results = collect([['test' => 'foo'], ['test' => 'bar']]);

        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results) {
            $this->assertEquals(
                'select * from "foobar" where ("test" > ?) order by "test" asc limit 16',
                $builder->toSql()
            );
            $this->assertEquals(['bar'], $builder->bindings['where']);

            return $results;
        });

        CursorPaginator::currentCursorResolver(function () use ($cursor) {
            return $cursor;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate();

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['test'],
        ]), $result);
    }

    public function testCursorPaginateWhenNoResults()
    {
        $perPage = 15;
        $cursorName = 'cursor';
        $builder = $this->getMockQueryBuilder()->orderBy('test');
        $path = 'http://foo.bar?cursor=3';

        $results = Collection::make([]);

        $builder->shouldReceive('get')->once()->andReturn($results);

        CursorPaginator::currentCursorResolver(function () {
            return null;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate();

        $this->assertEquals(new CursorPaginator($results, $perPage, null, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['test'],
        ]), $result);
    }

    public function testCursorPaginateWithSpecificColumns()
    {
        $perPage = 16;
        $columns = ['id', 'name'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['id' => 2]);
        $builder = $this->getMockQueryBuilder();
        $builder->from('foobar')->orderBy('id');
        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor=3';

        $results = collect([['id' => 3, 'name' => 'Taylor'], ['id' => 5, 'name' => 'Mohamed']]);

        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results) {
            $this->assertEquals(
                'select * from "foobar" where ("id" > ?) order by "id" asc limit 17',
                $builder->toSql()
            );
            $this->assertEquals([2], $builder->bindings['where']);

            return $results;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate($perPage, $columns, $cursorName, $cursor);

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['id'],
        ]), $result);
    }

    public function testCursorPaginateWithMixedOrders()
    {
        $perPage = 16;
        $columns = ['foo', 'bar', 'baz'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['foo' => 1, 'bar' => 2, 'baz' => 3]);
        $builder = $this->getMockQueryBuilder();
        $builder->from('foobar')->orderBy('foo')->orderByDesc('bar')->orderBy('baz');
        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor=' . $cursor->encode();

        $results = collect([['foo' => 1, 'bar' => 2, 'baz' => 4], ['foo' => 1, 'bar' => 1, 'baz' => 1]]);

        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results) {
            $this->assertEquals(
                'select * from "foobar" where ("foo" > ? or ("foo" = ? and ("bar" < ? or ("bar" = ? and ("baz" > ?))))) order by "foo" asc, "bar" desc, "baz" asc limit 17',
                $builder->toSql()
            );
            $this->assertEquals([1, 1, 2, 2, 3], $builder->bindings['where']);

            return $results;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate($perPage, $columns, $cursorName, $cursor);

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['foo', 'bar', 'baz'],
        ]), $result);
    }

    public function testCursorPaginateWithDynamicColumnInSelectRaw()
    {
        $perPage = 15;
        $cursorName = 'cursor';
        $cursor = new Cursor(['test' => 'bar']);
        $builder = $this->getMockQueryBuilder();
        $builder->from('foobar')->select('*')->selectRaw('(CONCAT(firstname, \' \', lastname)) as test')->orderBy('test');
        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor=' . $cursor->encode();

        $results = collect([['test' => 'foo'], ['test' => 'bar']]);

        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results) {
            $this->assertEquals(
                'select *, (CONCAT(firstname, \' \', lastname)) as test from "foobar" where ((CONCAT(firstname, \' \', lastname)) > ?) order by "test" asc limit 16',
                $builder->toSql()
            );
            $this->assertEquals(['bar'], $builder->bindings['where']);

            return $results;
        });

        CursorPaginator::currentCursorResolver(function () use ($cursor) {
            return $cursor;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate();

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['test'],
        ]), $result);
    }

    public function testCursorPaginateWithDynamicColumnWithCastInSelectRaw()
    {
        $perPage = 15;
        $cursorName = 'cursor';
        $cursor = new Cursor(['test' => 'bar']);
        $builder = $this->getMockQueryBuilder();
        $builder->from('foobar')->select('*')->selectRaw('(CAST(CONCAT(firstname, \' \', lastname) as VARCHAR)) as test')->orderBy('test');
        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor=' . $cursor->encode();

        $results = collect([['test' => 'foo'], ['test' => 'bar']]);

        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results) {
            $this->assertEquals(
                'select *, (CAST(CONCAT(firstname, \' \', lastname) as VARCHAR)) as test from "foobar" where ((CAST(CONCAT(firstname, \' \', lastname) as VARCHAR)) > ?) order by "test" asc limit 16',
                $builder->toSql()
            );
            $this->assertEquals(['bar'], $builder->bindings['where']);

            return $results;
        });

        CursorPaginator::currentCursorResolver(function () use ($cursor) {
            return $cursor;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate();

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['test'],
        ]), $result);
    }

    public function testCursorPaginateWithDynamicColumnInSelectSub()
    {
        $perPage = 15;
        $cursorName = 'cursor';
        $cursor = new Cursor(['test' => 'bar']);
        $builder = $this->getMockQueryBuilder();
        $builder->from('foobar')->select('*')->selectSub('CONCAT(firstname, \' \', lastname)', 'test')->orderBy('test');
        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor=' . $cursor->encode();

        $results = collect([['test' => 'foo'], ['test' => 'bar']]);

        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results) {
            $this->assertEquals(
                'select *, (CONCAT(firstname, \' \', lastname)) as "test" from "foobar" where ((CONCAT(firstname, \' \', lastname)) > ?) order by "test" asc limit 16',
                $builder->toSql()
            );
            $this->assertEquals(['bar'], $builder->bindings['where']);

            return $results;
        });

        CursorPaginator::currentCursorResolver(function () use ($cursor) {
            return $cursor;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate();

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['test'],
        ]), $result);
    }

    public function testCursorPaginateWithUnionWheres()
    {
        $ts = now()->toDateTimeString();

        $perPage = 16;
        $columns = ['test'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['created_at' => $ts]);
        $builder = $this->getMockQueryBuilder();
        $builder->select('id', 'start_time as created_at')->selectRaw("'video' as type")->from('videos');
        $builder->union($this->getBuilder()->select('id', 'created_at')->selectRaw("'news' as type")->from('news'));
        $builder->orderBy('created_at');

        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor=' . $cursor->encode();

        $results = collect([
            ['id' => 1, 'created_at' => now(), 'type' => 'video'],
            ['id' => 2, 'created_at' => now(), 'type' => 'news'],
        ]);

        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results, $ts) {
            $this->assertEquals(
                'select "id", "start_time" as "created_at", \'video\' as type from "videos" where ("start_time" > ?) union select "id", "created_at", \'news\' as type from "news" where ("created_at" > ?) order by "created_at" asc limit 17',
                $builder->toSql()
            );
            $this->assertEquals([$ts], $builder->bindings['where']);
            $this->assertEquals([$ts], $builder->bindings['union']);

            return $results;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate($perPage, $columns, $cursorName, $cursor);

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['created_at'],
        ]), $result);
    }

    public function testCursorPaginateWithMultipleUnionsAndMultipleWheres()
    {
        $ts = now()->toDateTimeString();

        $perPage = 16;
        $columns = ['test'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['created_at' => $ts]);
        $builder = $this->getMockQueryBuilder();
        $builder->select('id', 'start_time as created_at')->selectRaw("'video' as type")->from('videos');
        $builder->union($this->getBuilder()->select('id', 'created_at')->selectRaw("'news' as type")->from('news')->where('extra', 'first'));
        $builder->union($this->getBuilder()->select('id', 'created_at')->selectRaw("'podcast' as type")->from('podcasts')->where('extra', 'second'));
        $builder->orderBy('created_at');

        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor=' . $cursor->encode();

        $results = collect([
            ['id' => 1, 'created_at' => now(), 'type' => 'video'],
            ['id' => 2, 'created_at' => now(), 'type' => 'news'],
            ['id' => 3, 'created_at' => now(), 'type' => 'podcasts'],
        ]);
        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results, $ts) {
            $this->assertEquals(
                'select "id", "start_time" as "created_at", \'video\' as type from "videos" where ("start_time" > ?) union select "id", "created_at", \'news\' as type from "news" where "extra" = ? and ("created_at" > ?) union select "id", "created_at", \'podcast\' as type from "podcasts" where "extra" = ? and ("created_at" > ?) order by "created_at" asc limit 17',
                $builder->toSql()
            );
            $this->assertEquals([$ts], $builder->bindings['where']);
            $this->assertEquals(['first', $ts, 'second', $ts], $builder->bindings['union']);

            return $results;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate($perPage, $columns, $cursorName, $cursor);

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['created_at'],
        ]), $result);
    }

    public function testCursorPaginateWithUnionMultipleWheresMultipleOrders()
    {
        $ts = now()->toDateTimeString();

        $perPage = 16;
        $columns = ['id', 'created_at', 'type'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['id' => 1, 'created_at' => $ts, 'type' => 'news']);
        $builder = $this->getMockQueryBuilder();
        $builder->select('id', 'start_time as created_at', 'type')->from('videos')->where('extra', 'first');
        $builder->union($this->getBuilder()->select('id', 'created_at', 'type')->from('news')->where('extra', 'second'));
        $builder->union($this->getBuilder()->select('id', 'created_at', 'type')->from('podcasts')->where('extra', 'third'));
        $builder->orderBy('id')->orderByDesc('created_at')->orderBy('type');

        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor=' . $cursor->encode();

        $results = collect([
            ['id' => 1, 'created_at' => now()->addDay(), 'type' => 'video'],
            ['id' => 1, 'created_at' => now(), 'type' => 'news'],
            ['id' => 1, 'created_at' => now(), 'type' => 'podcast'],
            ['id' => 2, 'created_at' => now(), 'type' => 'podcast'],
        ]);

        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results, $ts) {
            $this->assertEquals(
                'select "id", "start_time" as "created_at", "type" from "videos" where "extra" = ? and ("id" > ? or ("id" = ? and ("start_time" < ? or ("start_time" = ? and ("type" > ?))))) union select "id", "created_at", "type" from "news" where "extra" = ? and ("id" > ? or ("id" = ? and ("start_time" < ? or ("start_time" = ? and ("type" > ?))))) union select "id", "created_at", "type" from "podcasts" where "extra" = ? and ("id" > ? or ("id" = ? and ("start_time" < ? or ("start_time" = ? and ("type" > ?))))) order by "id" asc, "created_at" desc, "type" asc limit 17',
                $builder->toSql()
            );
            $this->assertEquals(['first', 1, 1, $ts, $ts, 'news'], $builder->bindings['where']);
            $this->assertEquals(['second', 1, 1, $ts, $ts, 'news', 'third', 1, 1, $ts, $ts, 'news'], $builder->bindings['union']);

            return $results;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate($perPage, $columns, $cursorName, $cursor);

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['id', 'created_at', 'type'],
        ]), $result);
    }

    public function testCursorPaginateWithUnionWheresWithRawOrderExpression()
    {
        $ts = now()->toDateTimeString();

        $perPage = 16;
        $columns = ['test'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['created_at' => $ts]);
        $builder = $this->getMockQueryBuilder();
        $builder->select('id', 'is_published', 'start_time as created_at')->selectRaw("'video' as type")->where('is_published', true)->from('videos');
        $builder->union($this->getBuilder()->select('id', 'is_published', 'created_at')->selectRaw("'news' as type")->where('is_published', true)->from('news'));
        $builder->orderByRaw('case when (id = 3 and type="news" then 0 else 1 end)')->orderBy('created_at');

        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor=' . $cursor->encode();

        $results = collect([
            ['id' => 1, 'created_at' => now(), 'type' => 'video', 'is_published' => true],
            ['id' => 2, 'created_at' => now(), 'type' => 'news', 'is_published' => true],
        ]);

        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results, $ts) {
            $this->assertEquals(
                'select "id", "is_published", "start_time" as "created_at", \'video\' as type from "videos" where "is_published" = ? and ("start_time" > ?) union select "id", "is_published", "created_at", \'news\' as type from "news" where "is_published" = ? and ("created_at" > ?) order by case when (id = 3 and type="news" then 0 else 1 end), "created_at" asc limit 17',
                $builder->toSql()
            );
            $this->assertEquals([true, $ts], $builder->bindings['where']);
            $this->assertEquals([true, $ts], $builder->bindings['union']);

            return $results;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate($perPage, $columns, $cursorName, $cursor);

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['created_at'],
        ]), $result);
    }

    public function testCursorPaginateWithUnionWheresReverseOrder()
    {
        $ts = now()->toDateTimeString();

        $perPage = 16;
        $columns = ['test'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['created_at' => $ts], false);
        $builder = $this->getMockQueryBuilder();
        $builder->select('id', 'start_time as created_at')->selectRaw("'video' as type")->from('videos');
        $builder->union($this->getBuilder()->select('id', 'created_at')->selectRaw("'news' as type")->from('news'));
        $builder->orderBy('created_at');

        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor=' . $cursor->encode();

        $results = collect([
            ['id' => 1, 'created_at' => now(), 'type' => 'video'],
            ['id' => 2, 'created_at' => now(), 'type' => 'news'],
        ]);

        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results, $ts) {
            $this->assertEquals(
                'select "id", "start_time" as "created_at", \'video\' as type from "videos" where ("start_time" < ?) union select "id", "created_at", \'news\' as type from "news" where ("created_at" < ?) order by "created_at" desc limit 17',
                $builder->toSql()
            );
            $this->assertEquals([$ts], $builder->bindings['where']);
            $this->assertEquals([$ts], $builder->bindings['union']);

            return $results;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate($perPage, $columns, $cursorName, $cursor);

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['created_at'],
        ]), $result);
    }

    public function testCursorPaginateWithUnionWheresMultipleOrders(): void
    {
        $ts = now()->toDateTimeString();

        $perPage = 16;
        $columns = ['test'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['created_at' => $ts, 'id' => 1]);
        $builder = $this->getMockQueryBuilder();
        $builder->select('id', 'start_time as created_at')->selectRaw("'video' as type")->from('videos');
        $builder->union($this->getBuilder()->select('id', 'created_at')->selectRaw("'news' as type")->from('news'));
        $builder->orderByDesc('created_at')->orderBy('id');

        $builder->shouldReceive('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor=' . $cursor->encode();

        $results = collect([
            ['id' => 1, 'created_at' => now(), 'type' => 'video'],
            ['id' => 2, 'created_at' => now(), 'type' => 'news'],
        ]);
        $builder->shouldReceive('get')->once()->andReturnUsing(function () use ($builder, $results, $ts) {
            $this->assertEquals(
                'select "id", "start_time" as "created_at", \'video\' as type from "videos" where ("start_time" < ? or ("start_time" = ? and ("id" > ?))) union select "id", "created_at", \'news\' as type from "news" where ("created_at" < ? or ("created_at" = ? and ("id" > ?))) order by "created_at" desc, "id" asc limit 17',
                $builder->toSql()
            );
            $this->assertEquals([$ts, $ts, 1], $builder->bindings['where']);
            $this->assertEquals([$ts, $ts, 1], $builder->bindings['union']);

            return $results;
        });

        Paginator::currentPathResolver(static function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate($perPage, $columns, $cursorName, $cursor);

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['created_at', 'id'],
        ]), $result);
    }

    public function testCursorPaginateWithUnionWheresAndAliassedOrderColumns()
    {
        $ts = now()->toDateTimeString();

        $perPage = 16;
        $columns = ['test'];
        $cursorName = 'cursor-name';
        $cursor = new Cursor(['created_at' => $ts]);
        /**
         * @var Builder|MockInterface $builder
         */
        $builder = $this->getMockQueryBuilder();
        $builder->select('id', 'start_time as created_at')->selectRaw("'video' as type")->from('videos');
        $builder->union($this->getBuilder()->select('id', 'created_at')->selectRaw("'news' as type")->from('news'));
        $builder->union($this->getBuilder()->select('id', 'init_at as created_at')->selectRaw("'podcast' as type")->from('podcasts'));
        $builder->orderBy('created_at');

        $builder->allows('newQuery')->andReturnUsing(function () use ($builder) {
            return new Builder($builder->connection, $builder->grammar, $builder->processor);
        });

        $path = 'http://foo.bar?cursor=' . $cursor->encode();

        $results = collect([
            ['id' => 1, 'created_at' => now(), 'type' => 'video'],
            ['id' => 2, 'created_at' => now(), 'type' => 'news'],
            ['id' => 3, 'created_at' => now(), 'type' => 'podcast'],
        ]);
        $builder->expects('get')->andReturnUsing(function () use ($builder, $results, $ts) {
            $this->assertEquals(
                'select "id", "start_time" as "created_at", \'video\' as type from "videos" where ("start_time" > ?) union select "id", "created_at", \'news\' as type from "news" where ("created_at" > ?) union select "id", "init_at" as "created_at", \'podcast\' as type from "podcasts" where ("init_at" > ?) order by "created_at" asc limit 17',
                $builder->toSql()
            );
            $this->assertEquals([$ts], $builder->bindings['where']);
            $this->assertEquals([$ts, $ts], $builder->bindings['union']);

            return $results;
        });

        Paginator::currentPathResolver(function () use ($path) {
            return $path;
        });

        $result = $builder->cursorPaginate($perPage, $columns, $cursorName, $cursor);

        $this->assertEquals(new CursorPaginator($results, $perPage, $cursor, [
            'path' => $path,
            'cursorName' => $cursorName,
            'parameters' => ['created_at'],
        ]), $result);
    }

    protected function getBuilder(): Builder
    {
        $grammar = new Grammar();
        $processor = m::mock(Processor::class);

        return new Builder(m::mock(ConnectionInterface::class), $grammar, $processor);
    }

    protected function getMySqlBuilder(): Builder
    {
        $grammar = new MySqlGrammar();
        $processor = m::mock(Processor::class);

        return new Builder(m::mock(ConnectionInterface::class), $grammar, $processor);
    }

    protected function getMySqlBuilderWithProcessor(): Builder
    {
        $grammar = new MySqlGrammar();
        $processor = new MySqlProcessor();

        return new Builder(m::mock(ConnectionInterface::class), $grammar, $processor);
    }

    /**
     * @return Builder|MockInterface
     */
    protected function getMockQueryBuilder(): MockInterface
    {
        return m::mock(Builder::class, [
            m::mock(ConnectionInterface::class),
            new Grammar(),
            m::mock(Processor::class),
        ])->makePartial();
    }
}
