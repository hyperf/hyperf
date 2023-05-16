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
namespace HyperfTest\Database\PgSQL\DBAL;

use Hyperf\Database\PgSQL\DBAL\Connection;
use Hyperf\Database\PgSQL\DBAL\Result;
use Hyperf\Database\PgSQL\DBAL\Statement;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\PostgreSQL;

/**
 * @internal
 * @coversNothing
 */
class ConnectionTest extends TestCase
{
    protected Connection $connection;

    public function setUp(): void
    {
        if (SWOOLE_MAJOR_VERSION < 5) {
            $this->markTestSkipped('PostgreSql requires Swoole version >= 5.0.0');
        }

        $pgsql = new PostgreSQL();
        $connected = $pgsql->connect('host=127.0.0.1 port=5432 dbname=postgres user=postgres password=postgres');
        if (! $connected) {
            $this->fail('Failed to connect to PostgreSQL server.');
        }

        $this->connection = new Connection($pgsql);

        $pgsql->query('CREATE TABLE IF NOT EXISTS connection_tests_for_hyperf( id SERIAL constraint id primary key, val1 varchar(255) not null, val2 varchar(255) not null);');
    }

    public function tearDown(): void
    {
        $this->connection->exec('DROP TABLE IF EXISTS connection_tests_for_hyperf');
    }

    public function testExec()
    {
        $sql = sprintf('insert into "connection_tests_for_hyperf" ("val1", "val2") values (\'%s\', \'%s\');', uniqid(), uniqid());
        $this->assertGreaterThan(0, $this->connection->exec($sql));
    }

    public function testPrepare()
    {
        $stmt = $this->connection->prepare('select * from "connection_tests_for_hyperf" where "val1" = $1');
        $this->assertInstanceOf(Statement::class, $stmt);
    }

    public function testQuery()
    {
        $this->insertOneRow();
        $result = $this->connection->query('select * from "connection_tests_for_hyperf"');
        $this->assertInstanceOf(Result::class, $result);
        $this->assertGreaterThan(0, $result->rowCount());
    }

    public function testLastInsertId()
    {
        $this->insertOneRow();
        $one = $this->connection->lastInsertId();
        $this->assertIsNumeric($one);
        $this->assertGreaterThan(0, $one);

        $this->insertOneRow();
        $two = $this->connection->lastInsertId();
        $this->assertIsNumeric($two);
        $this->assertGreaterThan($one, $two);

        $this->assertEquals($two, $this->connection->lastInsertId('connection_tests_for_hyperf_id_seq'));
    }

    public function testTransactions()
    {
        $count = $this->getRowCount();

        $this->connection->beginTransaction();
        $this->insertOneRow();
        $this->connection->rollBack();

        $this->assertEquals($count, $this->getRowCount());

        $this->connection->beginTransaction();
        $this->insertOneRow();
        $this->connection->commit();

        $this->assertEquals($count + 1, $this->getRowCount());
    }

    public function testQuote()
    {
        $this->assertSame("'hyperf'", $this->connection->quote('hyperf'));
    }

    public function testGetServerVersion()
    {
        $this->assertMatchesRegularExpression('/^(?P<major>\d+)(?:\.(?P<minor>\d+)(?:\.(?P<patch>\d+))?)?/', $this->connection->getServerVersion());
    }

    public function testGetNativeConnection()
    {
        $this->assertInstanceOf(PostgreSQL::class, $this->connection->getNativeConnection());
    }

    public function testResult()
    {
        $insertResult = $this->connection
            ->prepare('insert into "connection_tests_for_hyperf" ("val1", "val2") values ($1, $2)')
            ->execute([$val1 = uniqid(), $val2 = uniqid()]);

        $this->assertEquals(1, $insertResult->rowCount());

        $result = $this->connection
            ->prepare('select val1, val2, id from "connection_tests_for_hyperf" where "val1" = $1')
            ->execute([$val1]);

        $row = $result->fetchNumeric();
        $id = $row[2];

        $this->assertSame([$val1, $val2, $id], $row);
        $this->assertSame(['val1' => $val1, 'val2' => $val2, 'id' => $id], $result->fetchAssociative());
        $this->assertSame($val1, $result->fetchOne());
        $this->assertSame([[$val1, $val2, $id]], $result->fetchAllNumeric());
        $this->assertSame([['val1' => $val1, 'val2' => $val2, 'id' => $id]], $result->fetchAllAssociative());
        $this->assertSame([$val1], $result->fetchFirstColumn());
        $this->assertEquals(1, $result->rowCount());
        $this->assertEquals(3, $result->columnCount());
    }

    private function insertOneRow()
    {
        $stmt = $this->connection->prepare('insert into "connection_tests_for_hyperf" ("val1", "val2") values ($1, $2)');
        $stmt->execute([uniqid(), uniqid()]);
    }

    private function getRowCount()
    {
        $result = $this->connection->query('select count(*) from "connection_tests_for_hyperf"');
        return $result->fetchOne();
    }
}
