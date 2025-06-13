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

namespace Hyperf\Database\Schema\Grammars;

use Doctrine\DBAL\Schema\AbstractSchemaManager as SchemaManager;
use Doctrine\DBAL\Schema\TableDiff;
use Hyperf\Database\Connection;
use Hyperf\Database\Grammar as BaseGrammar;
use Hyperf\Database\Query\Expression;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Fluent;
use RuntimeException;

use function Hyperf\Tappable\tap;

abstract class Grammar extends BaseGrammar
{
    /**
     * If this Grammar supports schema changes wrapped in a transaction.
     */
    protected bool $transactions = false;

    /**
     * The commands to be executed outside of create or alter command.
     */
    protected array $fluentCommands = [];

    /**
     * Compile a rename column command.
     */
    public function compileRenameColumn(Blueprint $blueprint, Fluent $command, Connection $connection): array
    {
        return RenameColumn::compile($this, $blueprint, $command, $connection);
    }

    /**
     * Compile a change column command into a series of SQL statements.
     *
     * @throws RuntimeException
     */
    public function compileChange(Blueprint $blueprint, Fluent $command, Connection $connection): array
    {
        return ChangeColumn::compile($this, $blueprint, $command, $connection);
    }

    /**
     * Compile a fulltext index key command.
     *
     * @throws RuntimeException
     */
    public function compileFullText(Blueprint $blueprint, Fluent $command): string
    {
        throw new RuntimeException('This database driver does not support fulltext index creation.');
    }

    /**
     * Compile a drop fulltext index command.
     */
    public function compileDropFullText(Blueprint $blueprint, Fluent $command): string
    {
        throw new RuntimeException('This database driver does not support fulltext index creation.');
    }

    /**
     * Compile a foreign key command.
     */
    public function compileForeign(Blueprint $blueprint, Fluent $command): string
    {
        // We need to prepare several of the elements of the foreign key definition
        // before we can create the SQL, such as wrapping the tables and convert
        // an array of columns to comma-delimited strings for the SQL queries.
        $sql = sprintf(
            'alter table %s add constraint %s ',
            $this->wrapTable($blueprint),
            $this->wrap($command->index)
        );

        // Once we have the initial portion of the SQL statement we will add on the
        // key name, table name, and referenced columns. These will complete the
        // main portion of the SQL statement and this SQL will almost be done.
        $sql .= sprintf(
            'foreign key (%s) references %s (%s)',
            $this->columnize($command->columns),
            $this->wrapTable($command->on),
            $this->columnize((array) $command->references)
        );

        // Once we have the basic foreign key creation statement constructed we can
        // build out the syntax for what should happen on an update or delete of
        // the affected columns, which will get something like "cascade", etc.
        if (! is_null($command->onDelete)) {
            $sql .= " on delete {$command->onDelete}";
        }

        if (! is_null($command->onUpdate)) {
            $sql .= " on update {$command->onUpdate}";
        }

        return $sql;
    }

    /**
     * Add a prefix to an array of values.
     *
     * @param string $prefix
     */
    public function prefixArray($prefix, array $values): array
    {
        return array_map(function ($value) use ($prefix) {
            return $prefix . ' ' . $value;
        }, $values);
    }

    /**
     * Wrap a table in keyword identifiers.
     *
     * @param mixed $table
     * @return string
     */
    public function wrapTable($table)
    {
        return parent::wrapTable(
            $table instanceof Blueprint ? $table->getTable() : $table
        );
    }

    /**
     * Wrap a value in keyword identifiers.
     *
     * @param Expression|string $value
     * @param bool $prefixAlias
     * @return string
     */
    public function wrap(Expression|Fluent|string $value, $prefixAlias = false)
    {
        return parent::wrap(
            $value instanceof Fluent ? $value->name : $value,
            $prefixAlias
        );
    }

    /**
     * Create an empty Doctrine DBAL TableDiff from the Blueprint.
     *
     * @return TableDiff
     */
    public function getDoctrineTableDiff(Blueprint $blueprint, SchemaManager $schema)
    {
        $table = $this->getTablePrefix() . $blueprint->getTable();

        return tap(new TableDiff($table), function ($tableDiff) use ($schema, $table) {
            $tableDiff->fromTable = $schema->listTableDetails($table);
        });
    }

    /**
     * Get the fluent commands for the grammar.
     */
    public function getFluentCommands(): array
    {
        return $this->fluentCommands;
    }

    /**
     * Check if this Grammar supports schema changes wrapped in a transaction.
     */
    public function supportsSchemaTransactions(): bool
    {
        return $this->transactions;
    }

    /**
     * Compile the blueprint's column definitions.
     *
     * @return array
     */
    protected function getColumns(Blueprint $blueprint)
    {
        $columns = [];

        foreach ($blueprint->getAddedColumns() as $column) {
            // Each of the column types have their own compiler functions which are tasked
            // with turning the column definition into its SQL format for this platform
            // used by the connection. The column's modifiers are compiled and added.
            $sql = $this->wrap($column) . ' ' . $this->getType($column);

            $columns[] = $this->addModifiers($sql, $blueprint, $column);
        }

        return $columns;
    }

    /**
     * Get the SQL for the column data type.
     *
     * @return string
     */
    protected function getType(Fluent $column)
    {
        return $this->{'type' . ucfirst($column->type)}($column);
    }

    /**
     * Add the column modifiers to the definition.
     *
     * @param string $sql
     * @return string
     */
    protected function addModifiers($sql, Blueprint $blueprint, Fluent $column)
    {
        foreach ($this->modifiers as $modifier) {
            if (method_exists($this, $method = "modify{$modifier}")) {
                $sql .= $this->{$method}($blueprint, $column);
            }
        }

        return $sql;
    }

    /**
     * Get the primary key command if it exists on the blueprint.
     *
     * @param string $name
     * @return null|Fluent
     */
    protected function getCommandByName(Blueprint $blueprint, $name)
    {
        $commands = $this->getCommandsByName($blueprint, $name);

        if (count($commands) > 0) {
            return reset($commands);
        }
    }

    /**
     * Get all of the commands with a given name.
     *
     * @param string $name
     * @return array
     */
    protected function getCommandsByName(Blueprint $blueprint, $name)
    {
        return array_filter($blueprint->getCommands(), function ($value) use ($name) {
            return $value->name == $name;
        });
    }

    /**
     * Format a value so that it can be used in "default" clauses.
     *
     * @param mixed $value
     * @return string
     */
    protected function getDefaultValue($value)
    {
        if ($value instanceof Expression) {
            return $value;
        }

        return is_bool($value)
            ? "'" . (int) $value . "'"
            : "'" . (string) $value . "'";
    }
}
