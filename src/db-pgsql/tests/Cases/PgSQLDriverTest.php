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
namespace HyperfTest\DB\PgSQL\Cases;

use Hyperf\DB\DB;
use Hyperf\DB\PgSQL\PgSQLPool;
use Hyperf\DB\Pool\PoolFactory;
use Swoole\Coroutine\PostgreSQL;

/**
 * @internal
 * @coversNothing
 */
class PgSQLDriverTest extends AbstractTestCase
{
    protected $driver = PgSQLPool::class;

    public function testFetch()
    {
        $db = $this->getContainer()->get(DB::class);

        $res = $db->fetch('SELECT * FROM USERS WHERE ID = $1;', [2]);

        $this->assertSame('Hyperflex', $res['name']);
    }

    public function testQuery()
    {
        $db = $this->getContainer()->get(DB::class);

        $res = $db->query('SELECT * FROM USERS WHERE id = $1;', [2]);

        $this->assertSame('Hyperflex', $res[0]['name']);
    }

    public function testRun()
    {
        $db = $this->getContainer()->get(DB::class);

        $sql = 'SELECT * FROM USERS WHERE ID = $1;';
        $bindings = [2];
        $res = $db->run(function (PostgreSQL $pdo) use ($sql, $bindings) {
            $statement = $this->prepare($sql);

            $resource = $pdo->execute($statement, $bindings);

            $result = [];
            for ($i = 0; $i < $pdo->numRows($resource); ++$i) {
                $result[] = $pdo->fetchObject($resource, $i);
            }

            return $result;
        });

        $this->assertSame('Hyperflex', $res[0]->name);
    }

    public function testInsertAndExecute()
    {
        $db = $this->getContainer()->get(DB::class);

        $id = $db->insert('INSERT INTO USERS (NAME, GENDER) VALUES ($1,$2) RETURNING ID;', [$name = uniqid(), $gender = rand(0, 2)]);
        $this->assertTrue($id > 0);

        $res = $db->fetch('SELECT * FROM USERS WHERE id = $1;', [$id]);
        $this->assertSame($name, $res['name']);
        $this->assertSame($gender, $res['gender']);

        $res = $db->execute('UPDATE USERS SET NAME = $1 WHERE ID = $2', [$name = uniqid(), $id]);
        $this->assertTrue($res > 0);
        $res = $db->fetch('SELECT * FROM USERS WHERE ID = $1;', [$id]);
        $this->assertSame($name, $res['name']);
    }

    public function testTransaction()
    {
        $db = $this->getContainer()->get(DB::class);
        $db->beginTransaction();
        $id = $db->insert('INSERT INTO USERS (NAME, GENDER) VALUES ($1,$2) RETURNING ID;', [$name = uniqid(), $gender = rand(0, 2)]);
        $this->assertTrue($id > 0);
        $db->commit();

        $res = $db->fetch('SELECT * FROM USERS WHERE id = $1;', [$id]);
        $this->assertSame($name, $res['name']);
        $this->assertSame($gender, $res['gender']);

        $db->beginTransaction();
        $id = $db->insert('INSERT INTO USERS (NAME, GENDER) VALUES ($1, $2) RETURNING ID;', [$name = uniqid(), $gender = rand(0, 2)]);
        $this->assertTrue($id > 0);
        $db->rollBack();

        $res = $db->fetch('SELECT * FROM USERS WHERE ID = $1;', [$id]);
        $this->assertNull($res);
    }

    public function testConfig()
    {
        $factory = $this->getContainer()->get(PoolFactory::class);
        $pool = $factory->getPool('default');

        $this->assertSame('postgres', $pool->getConfig()['database']);
        $this->assertSame([], $pool->getConfig()['options']);

        $connection = $pool->get();
        $this->assertSame(6, count($connection->getConfig()['pool']));
        $this->assertSame(20, $connection->getConfig()['pool']['max_connections']);
    }

    public function testMultiTransaction()
    {
        $db = $this->getContainer()->get(DB::class);
        $db->beginTransaction();
        $id = $db->insert('INSERT INTO USERS (NAME, GENDER) VALUES ($1, $2) RETURNING ID;', [$name = 'trans' . uniqid(), $gender = rand(0, 2)]);
        $this->assertTrue($id > 0);
        $db->beginTransaction();
        $id2 = $db->insert('INSERT INTO USERS (NAME, GENDER) VALUES ($1, $2) RETURNING ID;', ['rollback' . uniqid(), rand(0, 2)]);
        $this->assertTrue($id2 > 0);
        $db->rollBack();
        $db->commit();

        $res = $db->fetch('SELECT * FROM USERS WHERE ID = $1;', [$id2]);
        $this->assertNull($res);
        $res = $db->fetch('SELECT * FROM USERS WHERE ID = $1;', [$id]);
        $this->assertNotNull($res);
    }

    public function testStaticCall()
    {
        $this->getContainer();
        $res = DB::fetch('SELECT * FROM USERS WHERE ID = $1;', [1]);

        $this->assertSame('Hyperf', $res['name']);
    }

    public function testTransactionLevelWhenReconnect()
    {
        $container = $this->getContainer();
        $factory = $container->get(PoolFactory::class);
        $pool = $factory->getPool('default');
        $connection = $pool->get();
        $this->assertSame(0, $connection->transactionLevel());
        $connection->beginTransaction();
        $this->assertSame(1, $connection->transactionLevel());
        $connection->reconnect();
        $this->assertSame(0, $connection->transactionLevel());
    }
}
