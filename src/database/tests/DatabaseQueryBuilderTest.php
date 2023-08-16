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

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Exception\InvalidBindingException;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Expression as Raw;
use Hyperf\Database\Query\Grammars\Grammar;
use Hyperf\Database\Query\Grammars\MySqlGrammar;
use Hyperf\Database\Query\Processors\MySqlProcessor;
use Hyperf\Database\Query\Processors\Processor;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use TypeError;

/**
 * @internal
 * @coversNothing
 */
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

    protected function getMockQueryBuilder(): m\MockInterface
    {
        return m::mock(Builder::class, [
            m::mock(ConnectionInterface::class),
            new Grammar(),
            m::mock(Processor::class),
        ])->makePartial();
    }
}
