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

namespace HyperfTest\DB\Cases;

use Hyperf\DB\DB;
use Hyperf\DB\Exception\QueryException;
use Hyperf\DB\Pool\PoolFactory;
use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class MySQLDriverTest extends AbstractTestCase
{
    public function testFetch()
    {
        $db = $this->getContainer()->get(DB::class);

        $res = $db->fetch('SELECT * FROM `user` WHERE id = ?;', [2]);

        $this->assertSame('Hyperflex', $res['name']);
    }

    public function testQuery()
    {
        $db = $this->getContainer()->get(DB::class);

        $res = $db->query('SELECT * FROM `user` WHERE id = ?;', [2]);

        $this->assertSame('Hyperflex', $res[0]['name']);
    }

    public function testRun()
    {
        $db = $this->getContainer()->get(DB::class);

        $sql = 'SELECT * FROM `user` WHERE id = ?;';
        $bindings = [2];
        $mode = PDO::FETCH_OBJ;
        $res = $db->run(function (PDO $pdo) use ($sql, $bindings, $mode) {
            $statement = $pdo->prepare($sql);

            $this->bindValues($statement, $bindings);

            $statement->execute();

            return $statement->fetchAll($mode);
        });

        $this->assertSame('Hyperflex', $res[0]->name);
    }

    public function testInsertAndExecute()
    {
        $db = $this->getContainer()->get(DB::class);

        $id = $db->insert('INSERT INTO `user` (`name`, `gender`) VALUES (?,?);', [$name = uniqid(), $gender = rand(0, 2)]);
        $this->assertTrue($id > 0);

        $res = $db->fetch('SELECT * FROM `user` WHERE id = ?;', [$id]);
        $this->assertSame($name, $res['name']);
        $this->assertSame($gender, $res['gender']);

        $res = $db->execute('UPDATE `user` SET `name` = ? WHERE id = ?', [$name = uniqid(), $id]);
        $this->assertTrue($res > 0);
        $res = $db->fetch('SELECT * FROM `user` WHERE id = ?;', [$id]);
        $this->assertSame($name, $res['name']);
    }

    public function testTransaction()
    {
        $db = $this->getContainer()->get(DB::class);
        $db->beginTransaction();
        $id = $db->insert('INSERT INTO `user` (`name`, `gender`) VALUES (?,?);', [$name = uniqid(), $gender = rand(0, 2)]);
        $this->assertTrue($id > 0);
        $db->commit();

        $res = $db->fetch('SELECT * FROM `user` WHERE id = ?;', [$id]);
        $this->assertSame($name, $res['name']);
        $this->assertSame($gender, $res['gender']);

        $db->beginTransaction();
        $id = $db->insert('INSERT INTO `user` (`name`, `gender`) VALUES (?,?);', [$name = uniqid(), $gender = rand(0, 2)]);
        $this->assertTrue($id > 0);
        $db->rollBack();

        $res = $db->fetch('SELECT * FROM `user` WHERE id = ?;', [$id]);
        $this->assertNull($res);
    }

    public function testConfig()
    {
        $factory = $this->getContainer()->get(PoolFactory::class);
        $pool = $factory->getPool('default');

        $this->assertSame('hyperf', $pool->getConfig()['database']);
        $this->assertSame([], $pool->getConfig()['options']);

        $connection = $pool->get();
        $this->assertSame(6, count($connection->getConfig()['pool']));
        $this->assertSame(20, $connection->getConfig()['pool']['max_connections']);
    }

    public function testMultiTransaction()
    {
        $db = $this->getContainer()->get(DB::class);
        $db->beginTransaction();
        $id = $db->insert('INSERT INTO `user` (`name`, `gender`) VALUES (?,?);', [$name = 'trans' . uniqid(), $gender = rand(0, 2)]);
        $this->assertTrue($id > 0);
        $db->beginTransaction();
        $id2 = $db->insert('INSERT INTO `user` (`name`, `gender`) VALUES (?,?);', ['rollback' . uniqid(), rand(0, 2)]);
        $this->assertTrue($id2 > 0);
        $db->rollBack();
        $db->commit();

        $res = $db->fetch('SELECT * FROM `user` WHERE id = ?;', [$id2]);
        $this->assertNull($res);
        $res = $db->fetch('SELECT * FROM `user` WHERE id = ?;', [$id]);
        $this->assertNotNull($res);
    }

    public function testStaticCall()
    {
        $this->getContainer();
        $res = DB::fetch('SELECT * FROM `user` WHERE id = ?;', [1]);

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

    public function testThrowException()
    {
        $this->getContainer();

        $this->expectException(PDOException::class);
        $this->expectExceptionMessageMatches('/doesn\'t exist/');

        $res = DB::fetch('SELECT * FROM `user1` WHERE id = ?;', [1]);

        $this->assertSame('Hyperf', $res['name']);
    }

    public function testThrowExceptionWhenDotOpenExceptionOption()
    {
        $this->getContainer([PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT, PDO::ATTR_EMULATE_PREPARES => true]);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches('/PDO execute failed/');

        $res = DB::fetch('SELECT * FROM `user1` WHERE id = ?;', [1]);

        $this->assertSame('Hyperf', $res['name']);
    }
}
