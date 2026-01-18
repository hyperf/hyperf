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

namespace HyperfTest\Database\PgSQL\Cases;

use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\PgSQL\Query\Grammars\PostgresGrammar;
use Hyperf\Database\PgSQL\Query\Processors\PostgresProcessor;
use Hyperf\Database\Query\Builder;
use Mockery as m;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class QueryBuilderTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testWhereJsonContainsKey(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereJsonContainsKey('users.options->languages');
        $this->assertSame('select * from "users" where coalesce(("users"."options")::jsonb ?? \'languages\', false)', $builder->toSql());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereJsonContainsKey('options->language->primary');
        $this->assertSame('select * from "users" where coalesce(("options"->\'language\')::jsonb ?? \'primary\', false)', $builder->toSql());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('id', '=', 1)->orWhereJsonContainsKey('options->languages');
        $this->assertSame('select * from "users" where "id" = ? or coalesce(("options")::jsonb ?? \'languages\', false)', $builder->toSql());
    }

    public function testWhereJsonDoesntContainKey(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->whereJsonDoesntContainKey('options->languages');
        $this->assertSame('select * from "users" where not coalesce(("options")::jsonb ?? \'languages\', false)', $builder->toSql());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->where('id', '=', 1)->orWhereJsonDoesntContainKey('options->languages');
        $this->assertSame('select * from "users" where "id" = ? or not coalesce(("options")::jsonb ?? \'languages\', false)', $builder->toSql());
    }

    protected function getBuilder(): Builder
    {
        return new Builder(m::mock(ConnectionInterface::class), new PostgresGrammar(), new PostgresProcessor());
    }
}
