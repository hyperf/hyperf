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

namespace HyperfTest\Database\SQLite;

use Hyperf\Database\Connection;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Processors\Processor;
use Hyperf\Database\SQLite\Query\Grammars\SQLiteGrammar;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class QueryBuilderTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testToRawSql()
    {
        $connection = m::mock(Connection::class);
        $connection->shouldReceive('escape')->with('foo', false)->andReturn("'foo'");
        $grammar = new SQLiteGrammar();

        $bindings = array_map(fn ($value) => $connection->escape($value, false), ['foo']);

        $query = $grammar->substituteBindingsIntoRawSql(
            'select * from "users" where \'Hello\'\'World?\' IS NOT NULL AND "email" = ?',
            $bindings,
        );

        $this->assertSame('select * from "users" where \'Hello\'\'World?\' IS NOT NULL AND "email" = \'foo\'', $query);
    }

    public function testWhereJsonContainsKeySqlite()
    {
        $builder = $this->getSQLiteBuilder();
        $builder->select('*')->from('users')->whereJsonContainsKey('users.options->languages');
        $this->assertSame('select * from "users" where json_type("users"."options", \'$."languages"\') is not null', $builder->toSql());

        $builder = $this->getSQLiteBuilder();
        $builder->select('*')->from('users')->whereJsonContainsKey('options->language->primary');
        $this->assertSame('select * from "users" where json_type("options", \'$."language"."primary"\') is not null', $builder->toSql());

        $builder = $this->getSQLiteBuilder();
        $builder->select('*')->from('users')->where('id', '=', 1)->orWhereJsonContainsKey('options->languages');
        $this->assertSame('select * from "users" where "id" = ? or json_type("options", \'$."languages"\') is not null', $builder->toSql());
    }

    public function testWhereJsonDoesntContainKeySqlite()
    {
        $builder = $this->getSQLiteBuilder();
        $builder->select('*')->from('users')->whereJsonDoesntContainKey('options->languages');
        $this->assertSame('select * from "users" where not json_type("options", \'$."languages"\') is not null', $builder->toSql());

        $builder = $this->getSQLiteBuilder();
        $builder->select('*')->from('users')->where('id', '=', 1)->orWhereJsonDoesntContainKey('options->languages');
        $this->assertSame('select * from "users" where "id" = ? or not json_type("options", \'$."languages"\') is not null', $builder->toSql());
    }

    protected function getSQLiteBuilder(): Builder
    {
        return new Builder(m::mock(ConnectionInterface::class), new SQLiteGrammar(), new Processor());
    }
}
