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
}
