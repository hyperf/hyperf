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

use Hyperf\Database\PgSQL\PostgreSqlConnection;
use Mockery;
use PDO;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class PostgreSqlConnectionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testPrepareBindingsConvertsBooleanToStringWhenEmulatedPreparesEnabled()
    {
        $pdo = Mockery::mock(PDO::class);
        $connection = new PostgreSqlConnection(
            $pdo,
            'test_database',
            '',
            ['options' => [PDO::ATTR_EMULATE_PREPARES => true]]
        );

        $bindings = $connection->prepareBindings([true, false, 'string', 123]);

        $this->assertSame('true', $bindings[0]);
        $this->assertSame('false', $bindings[1]);
        $this->assertSame('string', $bindings[2]);
        $this->assertSame(123, $bindings[3]);
    }

    public function testPrepareBindingsConvertsBooleanToIntegerWhenEmulatedPreparesDisabled()
    {
        $pdo = Mockery::mock(PDO::class);
        $connection = new PostgreSqlConnection(
            $pdo,
            'test_database',
            '',
            ['options' => [PDO::ATTR_EMULATE_PREPARES => false]]
        );

        $bindings = $connection->prepareBindings([true, false]);

        $this->assertSame(1, $bindings[0]);
        $this->assertSame(0, $bindings[1]);
    }

    public function testEscapeBoolReturnsPostgresLiterals()
    {
        $pdo = Mockery::mock(PDO::class);
        $connection = new PostgreSqlConnection($pdo, 'test_database');

        $reflection = new ReflectionClass($connection);
        $method = $reflection->getMethod('escapeBool');

        $this->assertSame('true', $method->invoke($connection, true));
        $this->assertSame('false', $method->invoke($connection, false));
    }
}
