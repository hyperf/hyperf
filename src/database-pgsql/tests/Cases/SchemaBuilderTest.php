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
    }
}
