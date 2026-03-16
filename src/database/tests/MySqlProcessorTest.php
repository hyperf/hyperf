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
use Hyperf\Database\Query\Processors\MySqlProcessor;
use Hyperf\Database\Schema\Column;
use Hyperf\Database\Schema\Grammars\MySqlGrammar;
use Hyperf\Database\Schema\MySqlBuilder;
use HyperfTest\Database\Stubs\ContainerStub;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class MySqlProcessorTest extends TestCase
{
    public function testProcessColumnListing()
    {
        $processor = new MySqlProcessor();
        $listing = [['column_name' => 'id'], ['column_name' => 'name'], ['column_name' => 'email']];
        $expected = ['id', 'name', 'email'];
        $this->assertEquals($expected, $processor->processColumnListing($listing));

        // convert listing to objects to simulate PDO::FETCH_CLASS
        foreach ($listing as &$row) {
            $row = (object) $row;
        }

        $this->assertEquals($expected, $processor->processColumnListing($listing));
    }

    public function testProcessColumns()
    {
        $container = ContainerStub::getContainer();

        /** @var Connection $connection */
        $connection = $container->get(ConnectionResolverInterface::class)->connection();
        $connection->setSchemaGrammar(new MySqlGrammar());
        $builder = new MySqlBuilder($connection);

        $columns = $builder->getColumns();

        $this->assertTrue(is_array($columns));
        $this->assertTrue(count($columns) > 0);
        $this->assertInstanceOf(Column::class, $columns[0]);
    }
}
