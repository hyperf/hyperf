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

namespace HyperfTest\Database\SQLite;

use BadMethodCallException;
use Hyperf\Database\Exception\QueryException;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\SQLite\Connectors\SQLiteConnector;
use Hyperf\Database\SQLite\Schema\SQLiteBuilder;
use Hyperf\Database\SQLite\SQLiteConnection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DatabaseSQLiteSchemaForeignKeysTest extends TestCase
{
    /**
     * Regression test for https://github.com/hyperf/hyperf/issues/7341.
     *
     * Foreign keys defined on a table creation statement must not produce
     * empty SQL statements, otherwise `PDO::prepare()` fails with
     * "Argument #1 ($query) cannot be empty".
     */
    public function testCreateTableWithForeignKeyDoesNotProduceEmptyStatements()
    {
        $connection = $this->getConnection();
        $grammar = $connection->getSchemaGrammar();

        $blueprint = new Blueprint('tasks');
        $blueprint->create();
        $blueprint->uuid('id')->primary();
        $blueprint->uuid('workflow_id');
        $blueprint->string('status')->index();
        $blueprint->timestamps();
        $blueprint->dateTime('completed_at')->nullable();
        $blueprint->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');

        $statements = $blueprint->toSql($connection, $grammar);

        $this->assertNotEmpty($statements);
        foreach ($statements as $statement) {
            $this->assertNotSame('', $statement);
        }
    }

    public function testCreateTableWithForeignKey()
    {
        $schema = $this->getSchemaBuilder();

        $schema->create('workflows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('status')->index();
            $table->timestamps();
            $table->dateTime('completed_at')->nullable();
        });

        $schema->create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workflow_id');
            $table->string('status')->index();
            $table->timestamps();
            $table->dateTime('completed_at')->nullable();

            $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
        });

        $foreignKeys = $schema->getForeignKeys('tasks');

        $this->assertCount(1, $foreignKeys);
        $this->assertSame(['workflow_id'], $foreignKeys[0]['columns']);
        $this->assertSame('workflows', $foreignKeys[0]['foreign_table']);
        $this->assertSame(['id'], $foreignKeys[0]['foreign_columns']);
        $this->assertSame('cascade', $foreignKeys[0]['on_delete']);
    }

    public function testForeignKeyConstraintsAreEnforced()
    {
        $schema = $this->getSchemaBuilder();

        $schema->create('workflows', function (Blueprint $table) {
            $table->uuid('id')->primary();
        });

        $schema->create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workflow_id');

            $table->foreign('workflow_id')->references('id')->on('workflows');
        });

        $this->expectException(QueryException::class);

        $schema->getConnection()->table('tasks')->insert([
            'id' => 'e5aa4f38-03b9-4f0f-8bd2-3a1e5c6f4f01',
            'workflow_id' => '5f0a6e1e-1d07-4d8d-b1ea-d5a5c921ea6f',
        ]);
    }

    public function testDroppingForeignKeysThrowsOnSQLite()
    {
        $schema = $this->getSchemaBuilder();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('SQLite doesn\'t support dropping foreign keys (you would need to re-create the table).');

        $schema->table('tasks', function (Blueprint $table) {
            $table->dropForeign(['workflow_id']);
        });
    }

    public function testMultipleDropColumnCallsThrowOnSQLite()
    {
        $schema = $this->getSchemaBuilder();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('SQLite doesn\'t support multiple calls to dropColumn / renameColumn in a single modification.');

        $schema->table('tasks', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('completed_at');
        });
    }

    protected function getConnection(): SQLiteConnection
    {
        $pdo = (new SQLiteConnector())
            ->connect([
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]);

        return new SQLiteConnection($pdo, ':memory:', '', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
    }

    protected function getSchemaBuilder(): SQLiteBuilder
    {
        return $this->getConnection()->getSchemaBuilder();
    }
}
