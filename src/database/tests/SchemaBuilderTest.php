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
use Hyperf\Database\Model\Register;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;
use HyperfTest\Database\Stubs\ContainerStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class SchemaBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        $container = ContainerStub::getContainer();
        $db = new Db($container);
        $container->allows('get')->with(Db::class)->andReturns($db);
        $connectionResolverInterface = $container->get(ConnectionResolverInterface::class);
        Register::setConnectionResolver($connectionResolverInterface);
    }

    public function testGetTables(): void
    {
        Schema::create('foo', static function (Blueprint $table) {
            $table->comment('This is a comment');
            $table->increments('id');
        });

        Schema::create('bar', static function (Blueprint $table) {
            $table->string('name');
        });

        Schema::create('baz', static function (Blueprint $table) {
            $table->integer('votes');
        });

        $tables = Schema::getTables();
        $this->assertEmpty(array_diff(['foo', 'bar', 'baz'], array_column($tables, 'name')));
        $this->assertNotEmpty(array_filter($tables, static function ($table) {
            return $table['name'] === 'foo' && $table['comment'] === 'This is a comment';
        }));
        Schema::drop('foo');
        Schema::drop('bar');
        Schema::drop('baz');
    }

    public function testViews(): void
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
        Schema::dropAllViews();
        $this->assertFalse(Schema::hasView('demo1'));
        $this->assertEmpty(array_filter(Schema::getViews(), function ($table) {
            return $table['name'] === 'demo1';
        }));
        Schema::drop('view_1');
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
}
