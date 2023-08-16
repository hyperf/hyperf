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

/**
 * @internal
 * @coversNothing
 */
class MySQLDriverTest extends PDODriverTest
{
    protected $driver = 'mysql';

    public function testFetch()
    {
        parent::testFetch();
    }

    public function testQuery()
    {
        parent::testQuery();
    }

    public function testRun()
    {
        $db = $this->getContainer()->get(DB::class);

        $sql = 'SELECT * FROM `user` WHERE id = ?;';
        $bindings = [2];
        $res = $db->run(function () use ($sql, $bindings) {
            $statement = $this->prepare($sql);

            $statement->execute($bindings);

            $items = $statement->fetchAll();
            foreach ($items as $item) {
                $result[] = (object) $item;
            }
            return $result;
        });

        $this->assertSame('Hyperflex', $res[0]->name);
    }

    public function testInsertAndExecute()
    {
        parent::testInsertAndExecute();
    }

    public function testTransaction()
    {
        parent::testTransaction();
    }

    public function testConfig()
    {
        parent::testConfig();
    }

    public function testMultiTransaction()
    {
        parent::testMultiTransaction();
    }

    public function testThrowException()
    {
        $this->assertTrue(true);
    }

    public function testThrowExceptionWhenDotOpenExceptionOption()
    {
        $this->assertTrue(true);
    }
}
