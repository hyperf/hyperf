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
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Query\Processors\PostgresProcessor;
use Hyperf\Database\Schema\Column;
use Hyperf\Database\Schema\Grammars\PostgresGrammar;
use Hyperf\Database\Schema\PostgresBuilder;
use HyperfTest\Database\Stubs\ContainerStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class PostgreSqlProcessorTest extends TestCase
{
    public function testProcessColumnListing()
    {
        $processor = new PostgresProcessor();
        $listing = [['column_name' => 'id'], ['column_name' => 'name'], ['column_name' => 'email']];
        $expected = ['id', 'name', 'email'];
        $this->assertEquals($expected, $processor->processColumnListing($listing));

        // convert listing to objects to simulate PDO::FETCH_CLASS
        foreach ($listing as &$row) {
            $row = (object) $row;
        }

        $this->assertEquals($expected, $processor->processColumnListing($listing));
    }
}
