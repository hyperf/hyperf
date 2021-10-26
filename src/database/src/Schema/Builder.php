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
use LogicException;

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
     * @var \Hyperf\Database\Connection
     */
    protected $connection;

    /**
     * The schema grammar instance.
     *
     * @var \Hyperf\Database\Schema\Grammars\MySqlGrammar
     */
    protected $grammar;

    /**
     * The Blueprint resolver callback.
     *
     * @var \Closure
     */
    protected $resolver;

    /**
     * Create a new database Schema manager.
     *
     * @param \Hyperf\Database\Connection $connection
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
     * @return bool
     */
    public function hasTable($table)
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
     * @return bool
     */
    public function hasColumn($table, $column)
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
     * @return bool
     */
    public function hasColumns($table, array $columns)
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
     * @return array
     */
    public function getColumnListing($table)
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
    public function table($table, Closure $callback)
    {
        $this->build($this->createBlueprint($table, $callback));
    }

    /**
     * Create a new table on the schema.
     *
     * @param string $table
     */
    public function create($table, Closure $callback)
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
    public function drop($table)
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
    public function dropIfExists($table)
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) {
            $blueprint->dropIfExists();
        }));
    }

    /**
     * Drop all tables from the database.
     *
     * @throws \LogicException
     */
    public function dropAllTables()
    {
        throw new LogicException('This database driver does not support dropping all tables.');
    }

    /**
     * Drop all views from the database.
     *
     * @throws \LogicException
     */
    public function dropAllViews()
    {
        throw new LogicException('This database driver does not support dropping all views.');
    }

    /**
     * Rename a table on the schema.
     *
     * @param string $from
     * @param string $to
     */
    public function rename($from, $to)
    {
        $this->build(tap($this->createBlueprint($from), function ($blueprint) use ($to) {
            $blueprint->rename($to);
        }));
    }

    /**
     * Enable foreign key constraints.
     *
     * @return bool
     */
    public function enableForeignKeyConstraints()
    {
        return $this->connection->statement(
            $this->grammar->compileEnableForeignKeyConstraints()
        );
    }

    /**
     * Disable foreign key constraints.
     *
     * @return bool
     */
    public function disableForeignKeyConstraints()
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
