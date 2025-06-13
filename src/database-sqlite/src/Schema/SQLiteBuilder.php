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

namespace Hyperf\Database\SQLite\Schema;

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\Schema\Builder;
use Hyperf\Support\Filesystem\Filesystem;

class SQLiteBuilder extends Builder
{
    /**
     * Create a database in the schema.
     *
     * @param string $name
     */
    public function createDatabase($name): bool
    {
        return (bool) $this->getFilesystem()
            ->put($name, '');
    }

    /**
     * Drop a database from the schema if the database exists.
     *
     * @param string $name
     */
    public function dropDatabaseIfExists($name): bool
    {
        $file = $this->getFilesystem();

        return $file->exists($name)
            ? $file->delete($name)
            : true;
    }

    /**
     * Get the column type listing for a given table.
     *
     * @param string $table
     */
    public function getColumnTypeListing($table): array
    {
        $table = $this->connection->getTablePrefix() . $table;

        return $this->connection->getPostProcessor()->processColumnListing(
            $this->connection->select(
                $this->grammar->compileColumnListing($table)
            )
        );
    }

    /**
     * Get the indexes for a given table.
     */
    public function getIndexes(string $table): array
    {
        $table = $this->connection->getTablePrefix() . $table;

        return $this->connection->getPostProcessor()->processIndexes(
            $this->connection->selectFromWriteConnection($this->grammar->compileIndexes($table))
        );
    }

    /**
     * Get the names of the indexes for a given table.
     */
    public function getIndexListing(string $table): array
    {
        return array_column($this->getIndexes($table), 'name');
    }

    /**
     * Determine if the given table has a given index.
     *
     * @param array|string $index
     */
    public function hasIndex(string $table, $index, ?string $type = null): bool
    {
        $type = is_null($type) ? $type : strtolower($type);

        foreach ($this->getIndexes($table) as $value) {
            $typeMatches = is_null($type)
                || ($type === 'primary' && $value['primary'])
                || ($type === 'unique' && $value['unique'])
                || $type === $value['type'];

            if (($value['name'] === $index || $value['columns'] === $index) && $typeMatches) {
                return true;
            }
        }

        return false;
    }

    /**
     * Drop all tables from the database.
     */
    public function dropAllTables(): void
    {
        if ($this->connection->getDatabaseName() !== ':memory:') {
            $this->refreshDatabaseFile();
        }

        $this->connection->select($this->grammar->compileEnableWriteableSchema());

        $this->connection->select($this->grammar->compileDropAllTables());

        $this->connection->select($this->grammar->compileDisableWriteableSchema());

        $this->connection->select($this->grammar->compileRebuild());
    }

    /**
     * Drop all views from the database.
     */
    public function dropAllViews(): void
    {
        $this->connection->select($this->grammar->compileEnableWriteableSchema());

        $this->connection->select($this->grammar->compileDropAllViews());

        $this->connection->select($this->grammar->compileDisableWriteableSchema());

        $this->connection->select($this->grammar->compileRebuild());
    }

    /**
     * Empty the database file.
     */
    public function refreshDatabaseFile(): void
    {
        $this->getFilesystem()
            ->put($this->connection->getDatabaseName(), '');
    }

    protected function getFilesystem(): Filesystem
    {
        return ApplicationContext::getContainer()
            ->get(Filesystem::class);
    }
}
