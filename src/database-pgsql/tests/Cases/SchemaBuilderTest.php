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

use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;
use HyperfTest\Database\PgSQL\Stubs\ContainerStub;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

use function Hyperf\Collection\collect;

/**
 * @internal
 * @coversNothing
 */
#[RequiresPhpExtension('swoole', '< 6.0')]
class SchemaBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        $container = ContainerStub::getContainer();
        $container->allows('get')->with(Db::class)->andReturns(new Db($container));
    }

    public function testGetTables(): void
    {
        Schema::create('foo', static function (Blueprint $table) {
            $table->comment('This is a comment');
            $table->id();
        });

        Schema::create('bar', static function (Blueprint $table) {
            $table->id('name');
        });

        Schema::create('baz', static function (Blueprint $table) {
            $table->id('votes');
        });

        $tables = Schema::getTables();
        $this->assertEmpty(array_diff(['foo', 'bar', 'baz'], array_column($tables, 'name')));
        $this->assertNotEmpty(array_filter($tables, static function ($table) {
            return $table['name'] === 'foo';
        }));
        Schema::drop('foo');
        Schema::drop('bar');
        Schema::drop('baz');
    }

    public function testView(): void
    {
        Schema::create('view_1', static function (Blueprint $table) {
            $table->comment('This is a comment');
            $table->increments('id');
        });
        Db::table('view_1')->insert(['id' => 1]);
        Db::statement('create view demo1 as select id from view_1');
        $this->assertNotEmpty(array_filter(Schema::getViews(), function ($table) {
            return $table['name'] === 'demo1';
        }));
        $this->assertTrue(Schema::hasView('demo1'));
        /*
        Schema::dropAllViews();*/
        Db::statement('DROP VIEW IF EXISTS demo1 CASCADE;');
        $this->assertFalse(Schema::hasView('demo1'));
        $this->assertEmpty(array_filter(Schema::getViews(), function ($table) {
            return $table['name'] === 'demo1';
        }));
        Schema::drop('view_1');
    }

    public function testWhenTableHasColumn(): void
    {
        Schema::create('foo', static function (Blueprint $table) {
            $table->comment('This is a comment');
            $table->increments('id');
        });
        Schema::whenTableDoesntHaveColumn('foo', 'name', static function (Blueprint $table) {
            $table->string('name');
        });
        $this->assertTrue(Schema::hasColumn('foo', 'name'));
        Schema::whenTableHasColumn('foo', 'name', static function (Blueprint $table) {
            $table->dropColumn('name');
        });
        $this->assertFalse(Schema::hasColumn('foo', 'name'));
        Schema::drop('foo');
    }

    public function testColumn(): void
    {
        Schema::create('column_1', static function (Blueprint $table) {
            $table->comment('This is a comment');
            $table->increments('id');
            $table->string('name');
            $table->integer('ranking');
        });

        $this->assertTrue(Schema::hasColumn('column_1', 'name'));
        $this->assertTrue(Schema::hasColumn('column_1', 'naMe'));
        $this->assertFalse(Schema::hasColumn('column_1', 'names'));
        $this->assertTrue(Schema::hasColumn('column_1', 'ranking'));
        $this->assertFalse(Schema::hasColumn('column_1', 'rankings'));
        $this->assertTrue(Schema::hasColumns('column_1', ['name', 'ranking']));
        $this->assertFalse(Schema::hasColumns('column_1', ['names', 'ranking']));
        $this->assertSame('string', Schema::getColumnType('column_1', 'name'));
        $this->assertSame('integer', Schema::getColumnType('column_1', 'ranking'));
        $columns = Schema::getColumnTypeListing('column_1');
        $this->assertSame(['id', 'name', 'ranking'], array_column($columns, 'column_name'));
        Schema::drop('column_1');
    }

    public function index(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('name');
            $table->string('email');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['name', 'email'], 'index1');
        });

        $indexes = Schema::getIndexListing('users');

        $this->assertContains('index1', $indexes);
        $this->assertNotContains('index2', $indexes);

        Schema::table('users', function (Blueprint $table) {
            $table->renameIndex('index1', 'index2');
        });

        $this->assertFalse(Schema::hasIndex('users', 'index1'));
        $this->assertTrue(collect(Schema::getIndexes('users'))->contains(
            fn ($index) => $index['name'] === 'index2' && $index['columns'] === ['name', 'email']
        ));
        Schema::create('foo', function (Blueprint $table) {
            $table->id();
            $table->string('bar');
            $table->integer('baz');

            $table->unique(['baz', 'bar']);
        });

        $indexes = Schema::getIndexes('foo');

        $this->assertCount(2, $indexes);
        $this->assertTrue(collect($indexes)->contains(
            fn ($index) => $index['columns'] === ['id'] && $index['primary']
        ));
        $this->assertTrue(collect($indexes)->contains(
            fn ($index) => $index['name'] === 'foo_baz_bar_unique' && $index['columns'] === ['baz', 'bar'] && $index['unique']
        ));
        $this->assertTrue(Schema::hasIndex('foo', 'foo_baz_bar_unique'));
        $this->assertTrue(Schema::hasIndex('foo', 'foo_baz_bar_unique', 'unique'));
        $this->assertTrue(Schema::hasIndex('foo', ['baz', 'bar']));
        $this->assertTrue(Schema::hasIndex('foo', ['baz', 'bar'], 'unique'));
        $this->assertFalse(Schema::hasIndex('foo', ['baz', 'bar'], 'primary'));
        Schema::drop('foo');
        Schema::create('foo', function (Blueprint $table) {
            $table->string('bar')->index('my_index');
        });

        $indexes = Schema::getIndexes('foo');

        $this->assertCount(1, $indexes);
        $this->assertTrue(
            $indexes[0]['name'] === 'my_index'
            && $indexes[0]['columns'] === ['bar']
            && ! $indexes[0]['unique']
            && ! $indexes[0]['primary']
        );
        $this->assertTrue(Schema::hasIndex('foo', 'my_index'));
        $this->assertTrue(Schema::hasIndex('foo', ['bar']));
        $this->assertFalse(Schema::hasIndex('foo', 'my_index', 'primary'));
        $this->assertFalse(Schema::hasIndex('foo', ['bar'], 'unique'));
        Schema::drop('foo');
        Schema::create('foo', function (Blueprint $table) {
            $table->unsignedBigInteger('key');
            $table->string('bar')->unique();
            $table->integer('baz');

            $table->primary(['baz', 'key']);
        });

        $indexes = Schema::getIndexes('foo');

        $this->assertCount(2, $indexes);
        $this->assertTrue(collect($indexes)->contains(
            fn ($index) => $index['columns'] === ['baz', 'key'] && $index['primary']
        ));
        $this->assertTrue(collect($indexes)->contains(
            fn ($index) => $index['name'] === 'foo_bar_unique' && $index['columns'] === ['bar'] && $index['unique']
        ));
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('body');

            $table->fulltext(['body', 'title']);
        });

        $indexes = Schema::getIndexes('articles');

        $this->assertCount(2, $indexes);
        $this->assertTrue(collect($indexes)->contains(fn ($index) => $index['columns'] === ['id'] && $index['primary']));
        $this->assertTrue(collect($indexes)->contains('name', 'articles_body_title_fulltext'));
    }

    public function testGetForeignKeys()
    {
        Schema::create('users_copy', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('posts_copy', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users_copy')->cascadeOnUpdate()->nullOnDelete();
        });

        $foreignKeys = Schema::getForeignKeys('posts_copy');

        $this->assertCount(1, $foreignKeys);
        $this->assertTrue(collect($foreignKeys)->contains(
            fn ($foreign) => $foreign['columns'] === ['user_id']
                && $foreign['foreign_table'] === 'users_copy' && $foreign['foreign_columns'] === ['id']
                && $foreign['on_update'] === 'cascade' && $foreign['on_delete'] === 'set null'
        ));
    }
}
