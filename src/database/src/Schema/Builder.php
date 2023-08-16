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
namespace Hyperf\Database\Schema;

use Closure;
use Hyperf\Database\Connection;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Schema\Grammars\Grammar as SchemaGrammar;
use LogicException;

use function Hyperf\Tappable\tap;

class Builder
{
    /**
     * The default string length for migrations.
     *
     * @var int
     */
    public static $defaultStringLength = 255;

    /**
     * The database connection instance.
     *
     * @var Connection
     */
    protected ConnectionInterface $connection;

    /**
     * The schema grammar instance.
     */
    protected SchemaGrammar $grammar;

    /**
     * The Blueprint resolver callback.
     */
    protected ?Closure $resolver = null;

    /**
     * Create a new database Schema manager.
     *
     * @param Connection $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
        $this->grammar = $connection->getSchemaGrammar();
    }

    /**
     * Set the default string length for migrations.
     *
     * @param int $length
     */
    public static function defaultStringLength($length)
    {
        static::$defaultStringLength = $length;
    }

    /**
     * Determine if the given table exists.
     *
     * @param string $table
     */
    public function hasTable($table): bool
    {
        $table = $this->connection->getTablePrefix() . $table;

        return count($this->connection->selectFromWriteConnection(
            $this->grammar->compileTableExists(),
            [$table]
        )) > 0;
    }

    /**
     * Determine if the given table has a given column.
     *
     * @param string $table
     * @param string $column
     */
    public function hasColumn($table, $column): bool
    {
        return in_array(
            strtolower($column),
            array_map('strtolower', $this->getColumnListing($table))
        );
    }

    /**
     * Determine if the given table has given columns.
     *
     * @param string $table
     */
    public function hasColumns($table, array $columns): bool
    {
        $tableColumns = array_map('strtolower', $this->getColumnListing($table));

        foreach ($columns as $column) {
            if (! in_array(strtolower($column), $tableColumns)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the data type for the given column name.
     *
     * @param string $table
     * @param string $column
     * @return string
     */
    public function getColumnType($table, $column)
    {
        $table = $this->connection->getTablePrefix() . $table;

        return $this->connection->getDoctrineColumn($table, $column)->getType()->getName();
    }

    /**
     * Get the column listing for a given table.
     *
     * @param string $table
     */
    public function getColumnListing($table): array
    {
        $results = $this->connection->selectFromWriteConnection($this->grammar->compileColumnListing(
            $this->connection->getTablePrefix() . $table
        ));

        return $this->connection->getPostProcessor()->processColumnListing($results);
    }

    /**
     * Get the columns.
     */
    public function getColumns(): array
    {
        $results = $this->connection->selectFromWriteConnection(
            $this->grammar->compileColumns(),
            [
                $this->connection->getDatabaseName(),
            ]
        );
        return $this->connection->getPostProcessor()->processColumns($results);
    }

    /**
     * Modify a table on the schema.
     *
     * @param string $table
     */
    public function table($table, Closure $callback): void
    {
        $this->build($this->createBlueprint($table, $callback));
    }

    /**
     * Create a new table on the schema.
     *
     * @param string $table
     */
    public function create($table, Closure $callback): void
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($callback) {
            $blueprint->create();

            $callback($blueprint);
        }));
    }

    /**
     * Drop a table from the schema.
     *
     * @param string $table
     */
    public function drop($table): void
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) {
            $blueprint->drop();
        }));
    }

    /**
     * Drop a table from the schema if it exists.
     *
     * @param string $table
     */
    public function dropIfExists($table): void
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) {
            $blueprint->dropIfExists();
        }));
    }

    /**
     * Drop all tables from the database.
     *
     * @throws LogicException
     */
    public function dropAllTables(): void
    {
        throw new LogicException('This database driver does not support dropping all tables.');
    }

    /**
     * Drop all views from the database.
     *
     * @throws LogicException
     */
    public function dropAllViews(): void
    {
        throw new LogicException('This database driver does not support dropping all views.');
    }

    /**
     * Rename a table on the schema.
     *
     * @param string $from
     * @param string $to
     */
    public function rename($from, $to): void
    {
        $this->build(tap($this->createBlueprint($from), function ($blueprint) use ($to) {
            $blueprint->rename($to);
        }));
    }

    /**
     * Enable foreign key constraints.
     */
    public function enableForeignKeyConstraints(): bool
    {
        return $this->connection->statement(
            $this->grammar->compileEnableForeignKeyConstraints()
        );
    }

    /**
     * Disable foreign key constraints.
     */
    public function disableForeignKeyConstraints(): bool
    {
        return $this->connection->statement(
            $this->grammar->compileDisableForeignKeyConstraints()
        );
    }

    /**
     * Get the database connection instance.
     *
     * @return \Hyperf\Database\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Set the database connection instance.
     *
     * @return $this
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Set the Schema Blueprint resolver callback.
     */
    public function blueprintResolver(Closure $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Execute the blueprint to build / modify the table.
     *
     * @param \Hyperf\Database\Schema\Blueprint $blueprint
     */
    protected function build(Blueprint $blueprint)
    {
        $blueprint->build($this->connection, $this->grammar);
    }

    /**
     * Create a new command set with a Closure.
     *
     * @param string $table
     * @return \Hyperf\Database\Schema\Blueprint
     */
    protected function createBlueprint($table, Closure $callback = null)
    {
        $prefix = $this->connection->getConfig('prefix_indexes')
            ? $this->connection->getConfig('prefix')
            : '';

        if (isset($this->resolver)) {
            return call_user_func($this->resolver, $table, $callback, $prefix);
        }

        return new Blueprint($table, $callback, $prefix);
    }
}
