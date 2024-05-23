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

use Hyperf\Database\Connection;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\PgSQL\Query\Grammars\PostgresGrammar as PostgresQueryGrammar;
use Hyperf\Database\PgSQL\Query\Processors\PostgresProcessor;
use Hyperf\Database\PgSQL\Schema\Grammars\PostgresGrammar;
use Hyperf\Database\PgSQL\Schema\PostgresBuilder;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;
use Hyperf\Stringable\Str;
use HyperfTest\Database\PgSQL\Stubs\ContainerStub;
use Mockery as m;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class DatabasePostgresBuilderTest extends TestCase
{
    protected function tearDown(): void
    {
        ContainerStub::getContainer();
        Schema::dropIfExists('test_full_text_index');
        m::close();
    }

    #[RequiresPhpExtension('swoole', '< 6.0')]
    public function testCreateDatabase()
    {
        $grammar = new PostgresGrammar();

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('getConfig')->once()->with('charset')->andReturn('utf8');
        $connection->shouldReceive('getSchemaGrammar')->once()->andReturn($grammar);
        $connection->shouldReceive('statement')->once()->with(
            'create database "my_temporary_database" encoding "utf8"'
        )->andReturn(true);

        $builder = $this->getBuilder($connection);
        $this->assertEquals(true, $builder->createDatabase('my_temporary_database'));
    }

    #[RequiresPhpExtension('swoole', '< 6.0')]
    public function testDropDatabaseIfExists()
    {
        $grammar = new PostgresGrammar();

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('getSchemaGrammar')->once()->andReturn($grammar);
        $connection->shouldReceive('statement')->once()->with(
            'drop database if exists "my_database_a"'
        )->andReturn(true);

        $builder = $this->getBuilder($connection);
        $this->assertEquals(true, $builder->dropDatabaseIfExists('my_database_a'));
    }

    #[RequiresPhpExtension('swoole', '< 6.0')]
    public function testWhereFullText()
    {
        $builder = $this->getPostgresBuilderWithProcessor();
        $builder->select('*')->from('users')->whereFullText('body', 'Hello World');
        $this->assertSame('select * from "users" where (to_tsvector(\'english\', "body")) @@ plainto_tsquery(\'english\', ?)', $builder->toSql());
        $this->assertEquals(['Hello World'], $builder->getBindings());

        $builder = $this->getPostgresBuilderWithProcessor();
        $builder->select('*')->from('users')->whereFullText('body', 'Hello World', ['language' => 'simple']);
        $this->assertSame('select * from "users" where (to_tsvector(\'simple\', "body")) @@ plainto_tsquery(\'simple\', ?)', $builder->toSql());
        $this->assertEquals(['Hello World'], $builder->getBindings());

        $builder = $this->getPostgresBuilderWithProcessor();
        $builder->select('*')->from('users')->whereFullText('body', 'Hello World', ['mode' => 'plain']);
        $this->assertSame('select * from "users" where (to_tsvector(\'english\', "body")) @@ plainto_tsquery(\'english\', ?)', $builder->toSql());
        $this->assertEquals(['Hello World'], $builder->getBindings());

        $builder = $this->getPostgresBuilderWithProcessor();
        $builder->select('*')->from('users')->whereFullText('body', 'Hello World', ['mode' => 'phrase']);
        $this->assertSame('select * from "users" where (to_tsvector(\'english\', "body")) @@ phraseto_tsquery(\'english\', ?)', $builder->toSql());
        $this->assertEquals(['Hello World'], $builder->getBindings());

        $builder = $this->getPostgresBuilderWithProcessor();
        $builder->select('*')->from('users')->whereFullText('body', '+Hello -World', ['mode' => 'websearch']);
        $this->assertSame('select * from "users" where (to_tsvector(\'english\', "body")) @@ websearch_to_tsquery(\'english\', ?)', $builder->toSql());
        $this->assertEquals(['+Hello -World'], $builder->getBindings());

        $builder = $this->getPostgresBuilderWithProcessor();
        $builder->select('*')->from('users')->whereFullText('body', 'Hello World', ['language' => 'simple', 'mode' => 'plain']);
        $this->assertSame('select * from "users" where (to_tsvector(\'simple\', "body")) @@ plainto_tsquery(\'simple\', ?)', $builder->toSql());
        $this->assertEquals(['Hello World'], $builder->getBindings());

        $builder = $this->getPostgresBuilderWithProcessor();
        $builder->select('*')->from('users')->whereFullText(['body', 'title'], 'Car Plane');
        $this->assertSame('select * from "users" where (to_tsvector(\'english\', "body") || to_tsvector(\'english\', "title")) @@ plainto_tsquery(\'english\', ?)', $builder->toSql());
        $this->assertEquals(['Car Plane'], $builder->getBindings());
    }

    #[RequiresPhpExtension('swoole', '< 6.0')]
    public function testJoinLateralPostgres()
    {
        $builder = $this->getPostgresBuilderWithProcessor();
        $builder->getConnection()->shouldReceive('getDatabaseName');
        $builder->from('users')->joinLateral(function ($q) {
            $q->from('contacts')->whereColumn('contracts.user_id', 'users.id');
        }, 'sub');
        $this->assertSame('select * from "users" inner join lateral (select * from "contacts" where "contracts"."user_id" = "users"."id") as "sub" on true', $builder->toSql());
    }

    #[RequiresPhpExtension('swoole', '< 6.0')]
    public function testJoinLateralTest(): void
    {
        $container = ContainerStub::getContainer();
        $container->shouldReceive('get')->with(Db::class)->andReturn(new Db($container));
        Schema::dropIfExists('join_posts');
        Schema::dropIfExists('join_users');
        Schema::create('join_users', static function (Blueprint $table) {
            $table->id('id');
            $table->string('name');
        });

        Schema::create('join_posts', static function (Blueprint $table) {
            $table->id('id');
            $table->string('title');
            $table->integer('rating');
            $table->unsignedBigInteger('user_id');
        });
        Db::table('join_users')->insert([
            ['name' => Str::random()],
            ['name' => Str::random()],
        ]);

        Db::table('join_posts')->insert([
            ['title' => Str::random(), 'rating' => 1, 'user_id' => 1],
            ['title' => Str::random(), 'rating' => 3, 'user_id' => 1],
            ['title' => Str::random(), 'rating' => 7, 'user_id' => 1],
        ]);
        $subquery = Db::table('join_posts')
            ->select('title as best_post_title', 'rating as best_post_rating')
            ->whereColumn('user_id', 'join_users.id')
            ->orderBy('rating', 'desc')
            ->limit(2);

        $userWithPosts = Db::table('join_users')
            ->where('id', 1)
            ->joinLateral($subquery, 'best_post')
            ->get();

        $this->assertCount(2, $userWithPosts);
        $this->assertEquals(7, $userWithPosts[0]['best_post_rating']);
        $this->assertEquals(3, $userWithPosts[1]['best_post_rating']);

        $userWithoutPosts = Db::table('join_users')
            ->where('id', 2)
            ->joinLateral($subquery, 'best_post')
            ->get();

        $this->assertCount(0, $userWithoutPosts);

        $subquery = Db::table('join_posts')
            ->select('title as best_post_title', 'rating as best_post_rating')
            ->whereColumn('user_id', 'join_users.id')
            ->orderBy('rating', 'desc')
            ->limit(2);

        $userWithPosts = Db::table('join_users')
            ->where('id', 1)
            ->leftJoinLateral($subquery, 'best_post')
            ->get();

        $this->assertCount(2, $userWithPosts);
        $this->assertEquals(7, $userWithPosts[0]['best_post_rating']);
        $this->assertEquals(3, $userWithPosts[1]['best_post_rating']);

        $userWithoutPosts = Db::table('join_users')
            ->where('id', 2)
            ->leftJoinLateral($subquery, 'best_post')
            ->get();

        $this->assertCount(1, $userWithoutPosts);
        $this->assertNull($userWithoutPosts[0]['best_post_title']);
        $this->assertNull($userWithoutPosts[0]['best_post_rating']);
    }

    #[RequiresPhpExtension('swoole', '< 6.0')]
    public function testWhereFullTextForReal()
    {
        $container = ContainerStub::getContainer();
        $container->shouldReceive('get')->with(Db::class)->andReturn(new Db($container));

        Schema::create('test_full_text_index', function (Blueprint $table) {
            $table->id('id');
            $table->string('title', 200);
            $table->text('body');
            $table->fullText(['title', 'body']);
        });

        Db::table('test_full_text_index')->insert([
            ['title' => 'PostgreSQL Tutorial', 'body' => 'DBMS stands for DataBase ...'],
            ['title' => 'How To Use PostgreSQL Well', 'body' => 'After you went through a ...'],
            ['title' => 'Optimizing PostgreSQL', 'body' => 'In this tutorial, we show ...'],
            ['title' => '1001 PostgreSQL Tricks', 'body' => '1. Never run mysqld as root. 2. ...'],
            ['title' => 'PostgreSQL vs. YourSQL', 'body' => 'In the following database comparison ...'],
            ['title' => 'PostgreSQL Security', 'body' => 'When configured properly, PostgreSQL ...'],
        ]);

        $result = Db::table('test_full_text_index')->whereFulltext(['title', 'body'], 'database')->orderBy('id')->get();
        $this->assertCount(2, $result);
        $this->assertSame('PostgreSQL Tutorial', $result[0]['title']);
        $this->assertSame('PostgreSQL vs. YourSQL', $result[1]['title']);

        $result = Db::table('test_full_text_index')->whereFulltext(['title', 'body'], '+PostgreSQL -YourSQL', ['mode' => 'websearch'])->get();
        $this->assertCount(5, $result);

        $result = Db::table('test_full_text_index')->whereFulltext(['title', 'body'], 'PostgreSQL tutorial', ['mode' => 'plain'])->get();
        $this->assertCount(2, $result);

        $result = Db::table('test_full_text_index')->whereFulltext(['title', 'body'], 'PostgreSQL tutorial', ['mode' => 'phrase'])->get();
        $this->assertCount(1, $result);
    }

    #[RequiresPhpExtension('swoole', '< 6.0')]
    public function testPostgresInsertOrIgnoreUsingMethod()
    {
        $builder = $this->getPostgresBuilderWithProcessor();
        /**
         * @var ConnectionInterface&m\MockInterface $connection
         */
        $connection = $builder->getConnection();
        $connection->allows('affectingStatement')->andReturnUsing(function ($query, $bindings) {
            $this->assertEquals('insert into "table1" ("foo") select "bar" from "table2" where "foreign_id" = ? on conflict do nothing', $query);
            $this->assertEquals([5], $bindings);
            return 1;
        });
        $result = $builder->from('table1')->insertOrIgnoreUsing(
            ['foo'],
            function (Builder $query) {
                $query->select(['bar'])->from('table2')->where('foreign_id', '=', 5);
            }
        );

        $this->assertEquals(1, $result);
    }

    protected function getBuilder($connection): PostgresBuilder
    {
        return new PostgresBuilder($connection);
    }

    protected function getPostgresBuilderWithProcessor(): Builder
    {
        $grammar = new PostgresQueryGrammar();
        $processor = new PostgresProcessor();

        return new Builder(m::mock(ConnectionInterface::class), $grammar, $processor);
    }
}
