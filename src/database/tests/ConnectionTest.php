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

use Hyperf\Database\Connection;
use Hyperf\Database\Query\Expression;
use Hyperf\Database\Query\Grammars\MySqlGrammar;
use Mockery;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ConnectionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function getConnection(): Connection
    {
        $connection = new Connection(Mockery::mock(PDO::class));
        return $connection->setQueryGrammar(new MySqlGrammar());
    }

    public function testConnectionTable()
    {
        $connection = $this->getConnection();

        $sql = $connection->table('user')->toSql();

        $this->assertSame('select * from `user`', $sql);

        $sql = $connection->table('user as u')->toSql();

        $this->assertSame('select * from `user` as `u`', $sql);

        $sql = $connection->table(new Expression('(select 1 as id) a'))->toSql();

        $this->assertSame('select * from (select 1 as id) a', $sql);
    }
}
