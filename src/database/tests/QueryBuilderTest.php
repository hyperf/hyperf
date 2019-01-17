<?php

namespace HyperfTest\Database;


use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class QueryBuilderTest extends TestCase
{
    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }


    public function testQueryBuilder()
    {
    }

}