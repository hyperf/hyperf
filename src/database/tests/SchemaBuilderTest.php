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

    protected function tearDown(): void
    {
        Schema::drop('foo');
        Schema::drop('bar');
        Schema::drop('baz');
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
    }
}
