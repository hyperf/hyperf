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

namespace Hyperf\Database\SQLite\Schema\Grammars;

use Hyperf\Collection\Arr;
use Hyperf\Database\Connection;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Grammars\Grammar;
use Hyperf\Stringable\Str;
use Hyperf\Support\Fluent;
use RuntimeException;

use function Hyperf\Collection\collect;

class SQLiteGrammar extends Grammar
{
    /**
     * The possible column modifiers.
     *
     * @var string[]
     */
    protected array $modifiers = ['Increment', 'Nullable', 'Default', 'VirtualAs', 'StoredAs'];

    /**
     * The columns available as serials.
     *
     * @var string[]
     */
    protected array $serials = ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'];

    /**
     * Compile the query to determine if a table exists.
     */
    public function compileTableExists(): string
    {
        return "select * from sqlite_master where type = 'table' and name = ?";
    }

    /**
     * Compile the query to determine if the dbstat table is available.
     */
    public function compileDbstatExists(): string
    {
        return "select exists (select 1 from pragma_compile_options where compile_options = 'ENABLE_DBSTAT_VTAB') as enabled";
    }

    /**
     * Compile the query to determine the tables.
     */
    public function compileTables(bool $withSize = false): string
    {
        return $withSize
            ? 'select m.tbl_name as name, sum(s.pgsize) as size from sqlite_master as m '
            . 'join dbstat as s on s.name = m.name '
            . "where m.type in ('table', 'index') and m.tbl_name not like 'sqlite_%' "
            . 'group by m.tbl_name '
            . 'order by m.tbl_name'
            : "select name from sqlite_master where type = 'table' and name not like 'sqlite_%' order by name";
    }

    /**
     * Compile the query to determine the views.
     */
    public function compileViews(): string
    {
        return "select name, sql as definition from sqlite_master where type = 'view' order by name";
    }

    /**
     * Compile the SQL needed to retrieve all table names.
     */
    public function compileGetAllTables(): string
    {
        return 'select type, name from sqlite_master where type = \'table\' and name not like \'sqlite_%\'';
    }

    /**
     * Compile the SQL needed to retrieve all view names.
     */
    public function compileGetAllViews(): string
    {
        return 'select type, name from sqlite_master where type = \'view\'';
    }

    /**
     * Compile the query to determine the list of columns.
     */
    public function compileColumnListing(string $table): string
    {
        return 'pragma table_info(' . $this->wrap(str_replace('.', '__', $table)) . ')';
    }

    /**
     * Compile the query to determine the columns.
     */
    public function compileColumns(string $table): string
    {
        return sprintf(
            'select %s as "table_name", name as "column_name", type, not "notnull" as "nullable", dflt_value as "default", pk as "primary", cid '
            . 'from pragma_table_info(%s) order by cid asc',
            $tableName = $this->wrap(str_replace('.', '__', $table)),
            $tableName
        );
    }

    /**
     * Compile the query to determine the indexes.
     */
    public function compileIndexes(string $table): string
    {
        return sprintf(
            'select "primary" as name, group_concat(col) as columns, 1 as "unique", 1 as "primary" '
            . 'from (select name as col from pragma_table_info(%s) where pk > 0 order by pk, cid) group by name '
            . 'union select name, group_concat(col) as columns, "unique", origin = "pk" as "primary" '
            . 'from (select il.*, ii.name as col from pragma_index_list(%s) il, pragma_index_info(il.name) ii order by il.seq, ii.seqno) '
            . 'group by name, "unique", "primary"',
            $table = $this->wrap(str_replace('.', '__', $table)),
            $table
        );
    }

    /**
     * Compile the query to determine the foreign keys.
     */
    public function compileForeignKeys(string $table): string
    {
        return sprintf(
            'select group_concat("from") as columns, "table" as foreign_table, '
            . 'group_concat("to") as foreign_columns, on_update, on_delete '
            . 'from (select * from pragma_foreign_key_list(%s) order by id desc, seq) '
            . 'group by id, "table", on_update, on_delete',
            $this->wrap(str_replace('.', '__', $table))
        );
    }

    /**
     * Compile a create table command.
     */
    public function compileCreate(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf(
            '%s table %s (%s%s%s)',
            $blueprint->temporary ? 'create temporary' : 'create',
            $this->wrapTable($blueprint),
            implode(', ', $this->getColumns($blueprint)),
            (string) $this->addForeignKeys($blueprint),
            (string) $this->addPrimaryKeys($blueprint)
        );
    }

    /**
     * Compile alter table commands for adding columns.
     */
    public function compileAdd(Blueprint $blueprint, Fluent $command): array
    {
        $columns = $this->prefixArray('add column', $this->getColumns($blueprint));

        return collect($columns)->reject(function ($column) {
            return preg_match('/as \(.*\) stored/', $column) > 0;
        })->map(function ($column) use ($blueprint) {
            return 'alter table ' . $this->wrapTable($blueprint) . ' ' . $column;
        })->all();
    }

    /**
     * Compile a unique key command.
     */
    public function compileUnique(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf(
            'create unique index %s on %s (%s)',
            $this->wrap($command->index),
            $this->wrapTable($blueprint),
            $this->columnize($command->columns)
        );
    }

    /**
     * Compile a plain index key command.
     */
    public function compileIndex(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf(
            'create index %s on %s (%s)',
            $this->wrap($command->index),
            $this->wrapTable($blueprint),
            $this->columnize($command->columns)
        );
    }

    /**
     * Compile a spatial index key command.
     *
     * @throws RuntimeException
     */
    public function compileSpatialIndex(Blueprint $blueprint, Fluent $command): void
    {
        throw new RuntimeException('The database driver in use does not support spatial indexes.');
    }

    /**
     * Compile a foreign key command.
     */
    public function compileForeign(Blueprint $blueprint, Fluent $command): string
    {
        // Handled on table creation...
        return '';
    }

    /**
     * Compile a drop table command.
     */
    public function compileDrop(Blueprint $blueprint, Fluent $command): string
    {
        return 'drop table ' . $this->wrapTable($blueprint);
    }

    /**
     * Compile a drop table (if exists) command.
     */
    public function compileDropIfExists(Blueprint $blueprint, Fluent $command): string
    {
        return 'drop table if exists ' . $this->wrapTable($blueprint);
    }

    /**
     * Compile the SQL needed to drop all tables.
     */
    public function compileDropAllTables(): string
    {
        return "delete from sqlite_master where type in ('table', 'index', 'trigger')";
    }

    /**
     * Compile the SQL needed to drop all views.
     */
    public function compileDropAllViews(): string
    {
        return "delete from sqlite_master where type in ('view')";
    }

    /**
     * Compile the SQL needed to rebuild the database.
     */
    public function compileRebuild(): string
    {
        return 'vacuum';
    }

    /**
     * Compile a drop column command.
     */
    public function compileDropColumn(Blueprint $blueprint, Fluent $command): array
    {
        $table = $this->wrapTable($blueprint);

        $columns = $this->prefixArray('drop column', $this->wrapArray($command->columns));

        return collect($columns)
            ->map(
                fn ($column) => 'alter table ' . $table . ' ' . $column
            )->all();
    }

    /**
     * Compile a drop unique key command.
     */
    public function compileDropUnique(Blueprint $blueprint, Fluent $command): string
    {
        $index = $this->wrap($command->index);

        return "drop index {$index}";
    }

    /**
     * Compile a drop index command.
     */
    public function compileDropIndex(Blueprint $blueprint, Fluent $command): string
    {
        $index = $this->wrap($command->index);

        return "drop index {$index}";
    }

    /**
     * Compile a drop spatial index command.
     *
     * @throws RuntimeException
     */
    public function compileDropSpatialIndex(Blueprint $blueprint, Fluent $command): void
    {
        throw new RuntimeException('The database driver in use does not support spatial indexes.');
    }

    /**
     * Compile a rename table command.
     */
    public function compileRename(Blueprint $blueprint, Fluent $command): string
    {
        $from = $this->wrapTable($blueprint);

        return "alter table {$from} rename to " . $this->wrapTable($command->to);
    }

    /**
     * Compile a rename index command.
     *
     * @throws RuntimeException
     */
    public function compileRenameIndex(Blueprint $blueprint, Fluent $command, Connection $connection): array
    {
        $indexes = $this->getIndexColumns($connection, $blueprint->getTable());
        if (! $index = Arr::get($indexes, $command->from)) {
            throw new RuntimeException("Index [{$command->from}] does not exist.");
        }

        $compileMethod = $index['unique'] ? 'compileUnique' : 'compileIndex';

        return [
            $this->compileDropIndex($blueprint, new Fluent(['index' => $command->from])),
            $this->{$compileMethod}(
                $blueprint,
                new Fluent(['index' => $command->to, 'columns' => $index['columns']])
            )];
    }

    /**
     * Compile the command to enable foreign key constraints.
     */
    public function compileEnableForeignKeyConstraints(): string
    {
        return 'PRAGMA foreign_keys = ON;';
    }

    /**
     * Compile the command to disable foreign key constraints.
     */
    public function compileDisableForeignKeyConstraints(): string
    {
        return 'PRAGMA foreign_keys = OFF;';
    }

    /**
     * Compile the SQL needed to enable a writable schema.
     */
    public function compileEnableWriteableSchema(): string
    {
        return 'PRAGMA writable_schema = 1;';
    }

    /**
     * Compile the SQL needed to disable a writable schema.
     */
    public function compileDisableWriteableSchema(): string
    {
        return 'PRAGMA writable_schema = 0;';
    }

    /**
     * Create the column definition for a spatial Geometry type.
     */
    public function typeGeometry(Fluent $column): string
    {
        return 'geometry';
    }

    /**
     * Create the column definition for a spatial Point type.
     */
    public function typePoint(Fluent $column): string
    {
        return 'point';
    }

    /**
     * Create the column definition for a spatial LineString type.
     */
    public function typeLineString(Fluent $column): string
    {
        return 'linestring';
    }

    /**
     * Create the column definition for a spatial Polygon type.
     */
    public function typePolygon(Fluent $column): string
    {
        return 'polygon';
    }

    /**
     * Create the column definition for a spatial GeometryCollection type.
     */
    public function typeGeometryCollection(Fluent $column): string
    {
        return 'geometrycollection';
    }

    /**
     * Create the column definition for a spatial MultiPoint type.
     */
    public function typeMultiPoint(Fluent $column): string
    {
        return 'multipoint';
    }

    /**
     * Create the column definition for a spatial MultiLineString type.
     */
    public function typeMultiLineString(Fluent $column): string
    {
        return 'multilinestring';
    }

    /**
     * Create the column definition for a spatial MultiPolygon type.
     */
    public function typeMultiPolygon(Fluent $column): string
    {
        return 'multipolygon';
    }

    protected function getIndexColumns(Connection $connection, ?string $tableName = null): array
    {
        $sql = <<<'SQL'
            SELECT t.name AS table_name,
                   i.*
              FROM sqlite_master t
              JOIN pragma_index_list(t.name) i
SQL;

        $conditions = [
            "t.type = 'table'",
            "t.name NOT IN ('geometry_columns', 'spatial_ref_sys', 'sqlite_sequence')",
        ];

        if ($tableName !== null) {
            $tableName = str_replace('.', '__', $tableName);
            $conditions[] = "t.name = '{$tableName}'";
        }

        $sql .= ' WHERE ' . implode(' AND ', $conditions) . ' ORDER BY t.name, i.seq';

        return $this->getTableIndexesList(
            $connection,
            $connection->select($sql),
            $tableName
        );
    }

    /**
     * @see http://ezcomponents.org/docs/api/trunk/DatabaseSchema/ezcDbSchemaPgsqlReader.html
     */
    protected function getTableIndexesList(Connection $connection, array $tableIndexes, ?string $tableName = null): array
    {
        $indexBuffer = [];

        // fetch primary
        $indexArray = $connection->select("SELECT * FROM PRAGMA_TABLE_INFO ('{$tableName}')");

        usort(
            $indexArray,
            static function ($a, $b): int {
                if ($a->pk === $b->pk) {
                    return $a->cid - $b->cid;
                }

                return $a->pk - $b->pk;
            },
        );

        foreach ($indexArray as $indexColumnRow) {
            if ($indexColumnRow->pk === 0 || $indexColumnRow->pk === '0') {
                continue;
            }

            $indexBuffer[] = [
                'key_name' => 'primary',
                'primary' => true,
                'non_unique' => false,
                'column_name' => $indexColumnRow->name,
                'where' => null,
                'flags' => null,
                'length' => null,
            ];
        }

        // fetch regular indexes
        foreach ($tableIndexes as $tableIndex) {
            // Ignore indexes with reserved names, e.g. autoindexes
            if (str_starts_with($tableIndex->name, 'sqlite_')) {
                continue;
            }

            $keyName = $tableIndex->name;
            $idx = [];
            $idx['key_name'] = $keyName;
            $idx['primary'] = false;
            $idx['non_unique'] = ! $tableIndex->unique;

            $indexArray = $connection->select("SELECT * FROM PRAGMA_INDEX_INFO ('{$keyName}')");

            foreach ($indexArray as $indexColumnRow) {
                $idx['column_name'] = $indexColumnRow->name;
                $indexBuffer[] = $idx;
            }
        }

        $result = [];
        foreach ($indexBuffer as $tableIndex) {
            $indexName = $keyName = $tableIndex['key_name'];
            if ($tableIndex['primary']) {
                $keyName = 'primary';
            }

            $keyName = strtolower($keyName);

            if (! isset($result[$keyName])) {
                $options = [
                    'lengths' => [],
                ];

                if (isset($tableIndex['where'])) {
                    $options['where'] = $tableIndex['where'];
                }

                $result[$keyName] = [
                    'name' => $indexName,
                    'columns' => [],
                    'unique' => ! $tableIndex['non_unique'],
                    'primary' => $tableIndex['primary'],
                    'flags' => $tableIndex['flags'] ?? [],
                    'options' => $options,
                ];
            }

            $result[$keyName]['columns'][] = $tableIndex['column_name'];
            $result[$keyName]['options']['lengths'][] = $tableIndex['length'] ?? null;
        }

        return $result;
    }

    /**
     * Get the foreign key syntax for a table creation statement.
     */
    protected function addForeignKeys(Blueprint $blueprint): ?string
    {
        $foreigns = $this->getCommandsByName($blueprint, 'foreign');

        return collect($foreigns)->reduce(function ($sql, $foreign) {
            // Once we have all the foreign key commands for the table creation statement
            // we'll loop through each of them and add them to the create table SQL we
            // are building, since SQLite needs foreign keys on the tables creation.
            $sql .= $this->getForeignKey($foreign);

            if (! is_null($foreign->onDelete)) {
                $sql .= " on delete {$foreign->onDelete}";
            }

            // If this foreign key specifies the action to be taken on update we will add
            // that to the statement here. We'll append it to this SQL and then return
            // the SQL so we can keep adding any other foreign constraints onto this.
            if (! is_null($foreign->onUpdate)) {
                $sql .= " on update {$foreign->onUpdate}";
            }

            return $sql;
        }, '');
    }

    /**
     * Get the SQL for the foreign key.
     *
     * @param Fluent $foreign
     */
    protected function getForeignKey($foreign): string
    {
        // We need to columnize the columns that the foreign key is being defined for
        // so that it is a properly formatted list. Once we have done this, we can
        // return the foreign key SQL declaration to the calling method for use.
        return sprintf(
            ', foreign key(%s) references %s(%s)',
            $this->columnize($foreign->columns),
            $this->wrapTable($foreign->on),
            $this->columnize((array) $foreign->references)
        );
    }

    /**
     * Get the primary key syntax for a table creation statement.
     */
    protected function addPrimaryKeys(Blueprint $blueprint): ?string
    {
        if (! is_null($primary = $this->getCommandByName($blueprint, 'primary'))) {
            return ", primary key ({$this->columnize($primary->columns)})";
        }

        return null;
    }

    /**
     * Create the column definition for a char type.
     */
    protected function typeChar(Fluent $column): string
    {
        return 'varchar';
    }

    /**
     * Create the column definition for a string type.
     */
    protected function typeString(Fluent $column): string
    {
        return 'varchar';
    }

    /**
     * Create the column definition for a tiny text type.
     */
    protected function typeTinyText(Fluent $column): string
    {
        return 'text';
    }

    /**
     * Create the column definition for a text type.
     */
    protected function typeText(Fluent $column): string
    {
        return 'text';
    }

    /**
     * Create the column definition for a medium text type.
     */
    protected function typeMediumText(Fluent $column): string
    {
        return 'text';
    }

    /**
     * Create the column definition for a long text type.
     */
    protected function typeLongText(Fluent $column): string
    {
        return 'text';
    }

    /**
     * Create the column definition for an integer type.
     */
    protected function typeInteger(Fluent $column): string
    {
        return 'integer';
    }

    /**
     * Create the column definition for a big integer type.
     */
    protected function typeBigInteger(Fluent $column): string
    {
        return 'integer';
    }

    /**
     * Create the column definition for a medium integer type.
     */
    protected function typeMediumInteger(Fluent $column): string
    {
        return 'integer';
    }

    /**
     * Create the column definition for a tiny integer type.
     */
    protected function typeTinyInteger(Fluent $column): string
    {
        return 'integer';
    }

    /**
     * Create the column definition for a small integer type.
     */
    protected function typeSmallInteger(Fluent $column): string
    {
        return 'integer';
    }

    /**
     * Create the column definition for a float type.
     */
    protected function typeFloat(Fluent $column): string
    {
        return 'float';
    }

    /**
     * Create the column definition for a double type.
     */
    protected function typeDouble(Fluent $column): string
    {
        return 'float';
    }

    /**
     * Create the column definition for a decimal type.
     */
    protected function typeDecimal(Fluent $column): string
    {
        return 'numeric';
    }

    /**
     * Create the column definition for a boolean type.
     */
    protected function typeBoolean(Fluent $column): string
    {
        return 'tinyint(1)';
    }

    /**
     * Create the column definition for an enumeration type.
     */
    protected function typeEnum(Fluent $column): string
    {
        return sprintf(
            'varchar check ("%s" in (%s))',
            $column->name,
            $this->quoteString($column->allowed)
        );
    }

    /**
     * Create the column definition for a json type.
     */
    protected function typeJson(Fluent $column): string
    {
        return 'text';
    }

    /**
     * Create the column definition for a jsonb type.
     */
    protected function typeJsonb(Fluent $column): string
    {
        return 'text';
    }

    /**
     * Create the column definition for a date type.
     */
    protected function typeDate(Fluent $column): string
    {
        return 'date';
    }

    /**
     * Create the column definition for a date-time type.
     */
    protected function typeDateTime(Fluent $column): string
    {
        return $this->typeTimestamp($column);
    }

    /**
     * Create the column definition for a date-time (with time zone) type.
     *
     * Note: "SQLite does not have a storage class set aside for storing dates and/or times."
     *
     * @see https://www.sqlite.org/datatype3.html
     */
    protected function typeDateTimeTz(Fluent $column): string
    {
        return $this->typeDateTime($column);
    }

    /**
     * Create the column definition for a time type.
     */
    protected function typeTime(Fluent $column): string
    {
        return 'time';
    }

    /**
     * Create the column definition for a time (with time zone) type.
     */
    protected function typeTimeTz(Fluent $column): string
    {
        return $this->typeTime($column);
    }

    /**
     * Create the column definition for a timestamp type.
     */
    protected function typeTimestamp(Fluent $column): string
    {
        return $column->useCurrent ? 'datetime default CURRENT_TIMESTAMP' : 'datetime';
    }

    /**
     * Create the column definition for a timestamp (with time zone) type.
     */
    protected function typeTimestampTz(Fluent $column): string
    {
        return $this->typeTimestamp($column);
    }

    /**
     * Create the column definition for a year type.
     */
    protected function typeYear(Fluent $column): string
    {
        return $this->typeInteger($column);
    }

    /**
     * Create the column definition for a binary type.
     */
    protected function typeBinary(Fluent $column): string
    {
        return 'blob';
    }

    /**
     * Create the column definition for a uuid type.
     */
    protected function typeUuid(Fluent $column): string
    {
        return 'varchar';
    }

    /**
     * Create the column definition for an IP address type.
     */
    protected function typeIpAddress(Fluent $column): string
    {
        return 'varchar';
    }

    /**
     * Create the column definition for a MAC address type.
     */
    protected function typeMacAddress(Fluent $column): string
    {
        return 'varchar';
    }

    /**
     * Create the column definition for a generated, computed column type.
     *
     * @throws RuntimeException
     */
    protected function typeComputed(Fluent $column): void
    {
        throw new RuntimeException('This database driver requires a type, see the virtualAs / storedAs modifiers.');
    }

    /**
     * Get the SQL for a generated virtual column modifier.
     */
    protected function modifyVirtualAs(Blueprint $blueprint, Fluent $column): ?string
    {
        if (! is_null($virtualAs = $column->virtualAsJson)) {
            if ($this->isJsonSelector($virtualAs)) {
                $virtualAs = $this->wrapJsonSelector($virtualAs);
            }

            return " as ({$virtualAs})";
        }

        if (! is_null($column->virtualAs)) {
            return " as ({$column->virtualAs})";
        }

        return null;
    }

    /**
     * Get the SQL for a generated stored column modifier.
     */
    protected function modifyStoredAs(Blueprint $blueprint, Fluent $column): ?string
    {
        if (! is_null($storedAs = $column->storedAsJson)) {
            if ($this->isJsonSelector($storedAs)) {
                $storedAs = $this->wrapJsonSelector($storedAs);
            }

            return " as ({$storedAs}) stored";
        }

        if (! is_null($column->storedAs)) {
            return " as ({$column->storedAs}) stored";
        }

        return null;
    }

    /**
     * Determine if the given string is a JSON selector.
     */
    protected function isJsonSelector(string $value): bool
    {
        return str_contains($value, '->');
    }

    /**
     * Wrap the given JSON selector.
     */
    protected function wrapJsonSelector(string $value): string
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($value);

        return 'json_extract(' . $field . $path . ')';
    }

    /**
     * Split the given JSON selector into the field and the optional path and wrap them separately.
     */
    protected function wrapJsonFieldAndPath(string $column): array
    {
        $parts = explode('->', $column, 2);

        $field = $this->wrap($parts[0]);

        $path = count($parts) > 1 ? ', ' . $this->wrapJsonPath($parts[1], '->') : '';

        return [$field, $path];
    }

    /**
     * Wrap the given JSON path.
     */
    protected function wrapJsonPath(string $value, string $delimiter = '->'): string
    {
        $value = preg_replace("/([\\\\]+)?\\'/", "''", $value);

        $jsonPath = collect(explode($delimiter, $value))
            ->map(fn ($segment) => $this->wrapJsonPathSegment($segment))
            ->implode('.');

        return "'$" . (str_starts_with($jsonPath, '[') ? '' : '.') . $jsonPath . "'";
    }

    /**
     * Wrap the given JSON path segment.
     */
    protected function wrapJsonPathSegment(string $segment): string
    {
        if (preg_match('/(\[[^\]]+\])+$/', $segment, $parts)) {
            $key = Str::beforeLast($segment, $parts[0]);

            if (! empty($key)) {
                return '"' . $key . '"' . $parts[0];
            }

            return $parts[0];
        }

        return '"' . $segment . '"';
    }

    /**
     * Get the SQL for a nullable column modifier.
     */
    protected function modifyNullable(Blueprint $blueprint, Fluent $column): ?string
    {
        if (is_null($column->virtualAs) && is_null($column->storedAs)) {
            return $column->nullable ? '' : ' not null';
        }

        if ($column->nullable === false) {
            return ' not null';
        }

        return null;
    }

    /**
     * Get the SQL for a default column modifier.
     */
    protected function modifyDefault(Blueprint $blueprint, Fluent $column): ?string
    {
        if (! is_null($column->default) && is_null($column->virtualAs) && is_null($column->storedAs)) {
            return ' default ' . $this->getDefaultValue($column->default);
        }

        return null;
    }

    /**
     * Get the SQL for an auto-increment column modifier.
     */
    protected function modifyIncrement(Blueprint $blueprint, Fluent $column): ?string
    {
        if (in_array($column->type, $this->serials) && $column->autoIncrement) {
            return ' primary key autoincrement';
        }

        return null;
    }
}
