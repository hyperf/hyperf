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

use Hyperf\Database\Query\Processors\MySqlProcessor;

class MySqlBuilder extends Builder
{
    /**
     * Determine if the given table exists.
     *
     * @param string $table
     * @return bool
     */
    public function hasTable($table)
    {
        $table = $this->connection->getTablePrefix() . $table;

        return count($this->connection->select(
            $this->grammar->compileTableExists(),
            [$this->connection->getDatabaseName(), $table]
        )) > 0;
    }

    /**
     * Get the column listing for a given table.
     *
     * @param string $table
     * @return array
     */
    public function getColumnListing($table)
    {
        $table = $this->connection->getTablePrefix() . $table;

        $results = $this->connection->select(
            $this->grammar->compileColumnListing(),
            [$this->connection->getDatabaseName(), $table]
        );

        return $this->connection->getPostProcessor()->processColumnListing($results);
    }

    /**
     * Get the columns.
     */
    public function getColumns(): array
    {
        $results = $this->connection->select(
            $this->grammar->compileColumns(),
            [
                $this->connection->getDatabaseName(),
            ]
        );

        return $this->connection->getPostProcessor()->processColumns($results);
    }

    /**
     * Get the column type listing for a given table.
     *
     * @param string $table
     * @return array
     */
    public function getColumnTypeListing($table)
    {
        $table = $this->connection->getTablePrefix() . $table;

        $results = $this->connection->select(
            $this->grammar->compileColumnListing(),
            [$this->connection->getDatabaseName(), $table]
        );

        /** @var MySqlProcessor $processor */
        $processor = $this->connection->getPostProcessor();
        return $processor->processListing($results);
    }

    /**
     * Drop all tables from the database.
     */
    public function dropAllTables()
    {
        $tables = [];

        foreach ($this->getAllTables() as $row) {
            $row = (array) $row;

            $tables[] = reset($row);
        }

        if (empty($tables)) {
            return;
        }

        $this->disableForeignKeyConstraints();

        $this->connection->statement(
            $this->grammar->compileDropAllTables($tables)
        );

        $this->enableForeignKeyConstraints();
    }

    /**
     * Drop all views from the database.
     */
    public function dropAllViews()
    {
        $views = [];

        foreach ($this->getAllViews() as $row) {
            $row = (array) $row;

            $views[] = reset($row);
        }

        if (empty($views)) {
            return;
        }

        $this->connection->statement(
            $this->grammar->compileDropAllViews($views)
        );
    }

    /**
     * Get all of the table names for the database.
     *
     * @return array
     */
    public function getAllTables()
    {
        return $this->connection->select(
            $this->grammar->compileGetAllTables()
        );
    }

    /**
     * Get all of the view names for the database.
     *
     * @return array
     */
    protected function getAllViews()
    {
        return $this->connection->select(
            $this->grammar->compileGetAllViews()
        );
    }
}
