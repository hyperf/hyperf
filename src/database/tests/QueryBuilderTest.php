<?php

namespace HyperfTest\Database;


use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Grammars\Grammar;
use Hyperf\Database\Query\Processors\Processor;
use PHPUnit\Framework\TestCase;

/**
 * @property ConnectionInterface $connection
 * @property Builder $queryBuilder
 */
class QueryBuilderTest extends TestCase
{

    protected function setUp()
    {
        $this->connection = $this->prophesize(ConnectionInterface::class);
        $this->queryBuilder = new Builder($this->connection->reveal(), new Grammar(), new Processor());
    }


    public function testQueryBuilder()
    {
        $this->assertInstanceOf(Builder::class, $this->queryBuilder);
    }

    public function testSelect()
    {
        $this->queryBuilder->select(['*']);
        $this->assertSame('select *', $this->queryBuilder->toSql());
        $this->queryBuilder->select(['id', 'name']);
        $this->assertSame('select "id", "name"', $this->queryBuilder->toSql());
    }

}