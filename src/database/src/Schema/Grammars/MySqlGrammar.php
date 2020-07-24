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

use Hyperf\Database\Connection;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Utils\Fluent;

class MySqlGrammar extends Grammar
{
    /**
     * The possible column modifiers.
     *
     * @var array
     */
    protected $modifiers = [
        'Unsigned', 'VirtualAs', 'StoredAs', 'Charset', 'Collate', 'Nullable',
        'Default', 'Increment', 'Comment', 'After', 'First', 'Srid',
    ];

    /**
     * The possible column serials.
     *
     * @var array
     */
    protected $serials = ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'];

    /**
     * Compile the query to determine the list of tables.
     *
     * @return string
     */
    public function compileTableExists()
    {
        return 'select * from information_schema.tables where table_schema = ? and table_name = ?';
    }

    /**
     * Compile the query to determine the list of columns.
     */
    public function compileColumnListing(): string
    {
        return 'select `column_key`, `column_name`, `data_type`, `column_comment`, `extra` from information_schema.columns where `table_schema` = ? and `table_name` = ? order by ORDINAL_POSITION';
    }

    /**
     * Compile the query to determine the list of columns.
     */
    public function compileColumns(): string
    {
        return 'select `table_schema`, `table_name`, `column_name`, `ordinal_position`, `column_default`, `is_nullable`, `data_type`, `column_comment` from information_schema.columns where `table_schema` = ? order by ORDINAL_POSITION';
    }

    /**
     * Compile a create table command.
     *
     * @return string
     */
    public function compileCreate(Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        $sql = $this->compileCreateTable(
            $blueprint,
            $command,
            $connection
        );

        // Once we have the primary SQL, we can add the encoding option to the SQL for
        // the table.  Then, we can check if a storage engine has been supplied for
        // the table. If so, we will add the engine declaration to the SQL query.
        $sql = $this->compileCreateEncoding(
            $sql,
            $connection,
            $blueprint
        );

        // Finally, we will append the engine configuration onto this SQL statement as
        // the final thing we do before returning this finished SQL. Once this gets
        // added the query will be ready to execute against the real connections.
        return $this->compileCreateEngine(
            $sql,
            $connection,
            $blueprint
        );
    }

    /**
     * Compile an add column command.
     *
     * @return string
     */
    public function compileAdd(Blueprint $blueprint, Fluent $command)
    {
        $columns = $this->prefixArray('add', $this->getColumns($blueprint));

        return 'alter table ' . $this->wrapTable($blueprint) . ' ' . implode(', ', $columns);
    }

    /**
     * Compile a primary key command.
     *
     * @return string
     */
    public function compilePrimary(Blueprint $blueprint, Fluent $command)
    {
        $command->name(null);

        return $this->compileKey($blueprint, $command, 'primary key');
    }

    /**
     * Compile a unique key command.
     *
     * @return string
     */
    public function compileUnique(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileKey($blueprint, $command, 'unique');
    }

    /**
     * Compile a plain index key command.
     *
     * @return string
     */
    public function compileIndex(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileKey($blueprint, $command, 'index');
    }

    /**
     * Compile a spatial index key command.
     *
     * @return string
     */
    public function compileSpatialIndex(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileKey($blueprint, $command, 'spatial index');
    }

    /**
     * Compile a drop table command.
     *
     * @return string
     */
    public function compileDrop(Blueprint $blueprint, Fluent $command)
    {
        return 'drop table ' . $this->wrapTable($blueprint);
    }

    /**
     * Compile a drop table (if exists) command.
     *
     * @return string
     */
    public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
    {
        return 'drop table if exists ' . $this->wrapTable($blueprint);
    }

    /**
     * Compile a drop column command.
     *
     * @return string
     */
    public function compileDropColumn(Blueprint $blueprint, Fluent $command)
    {
        $columns = $this->prefixArray('drop', $this->wrapArray($command->columns));

        return 'alter table ' . $this->wrapTable($blueprint) . ' ' . implode(', ', $columns);
    }

    /**
     * Compile a drop primary key command.
     *
     * @return string
     */
    public function compileDropPrimary(Blueprint $blueprint, Fluent $command)
    {
        return 'alter table ' . $this->wrapTable($blueprint) . ' drop primary key';
    }

    /**
     * Compile a drop unique key command.
     *
     * @return string
     */
    public function compileDropUnique(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap($command->index);

        return "alter table {$this->wrapTable($blueprint)} drop index {$index}";
    }

    /**
     * Compile a drop index command.
     *
     * @return string
     */
    public function compileDropIndex(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap($command->index);

        return "alter table {$this->wrapTable($blueprint)} drop index {$index}";
    }

    /**
     * Compile a drop spatial index command.
     *
     * @return string
     */
    public function compileDropSpatialIndex(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileDropIndex($blueprint, $command);
    }

    /**
     * Compile a drop foreign key command.
     *
     * @return string
     */
    public function compileDropForeign(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap($command->index);

        return "alter table {$this->wrapTable($blueprint)} drop foreign key {$index}";
    }

    /**
     * Compile a rename table command.
     *
     * @return string
     */
    public function compileRename(Blueprint $blueprint, Fluent $command)
    {
        $from = $this->wrapTable($blueprint);

        return "rename table {$from} to " . $this->wrapTable($command->to);
    }

    /**
     * Compile a rename index command.
     *
     * @return string
     */
    public function compileRenameIndex(Blueprint $blueprint, Fluent $command)
    {
        return sprintf(
            'alter table %s rename index %s to %s',
            $this->wrapTable($blueprint),
            $this->wrap($command->from),
            $this->wrap($command->to)
        );
    }

    /**
     * Compile the SQL needed to drop all tables.
     *
     * @param array $tables
     * @return string
     */
    public function compileDropAllTables($tables)
    {
        return 'drop table ' . implode(',', $this->wrapArray($tables));
    }

    /**
     * Compile the SQL needed to drop all views.
     *
     * @param array $views
     * @return string
     */
    public function compileDropAllViews($views)
    {
        return 'drop view ' . implode(',', $this->wrapArray($views));
    }

    /**
     * Compile the SQL needed to retrieve all table names.
     *
     * @return string
     */
    public function compileGetAllTables()
    {
        return 'SHOW FULL TABLES WHERE table_type = \'BASE TABLE\'';
    }

    /**
     * Compile the SQL needed to retrieve all view names.
     *
     * @return string
     */
    public function compileGetAllViews()
    {
        return 'SHOW FULL TABLES WHERE table_type = \'VIEW\'';
    }

    /**
     * Compile the command to enable foreign key constraints.
     *
     * @return string
     */
    public function compileEnableForeignKeyConstraints()
    {
        return 'SET FOREIGN_KEY_CHECKS=1;';
    }

    /**
     * Compile the command to disable foreign key constraints.
     *
     * @return string
     */
    public function compileDisableForeignKeyConstraints()
    {
        return 'SET FOREIGN_KEY_CHECKS=0;';
    }

    /**
     * Create the column definition for a spatial Geometry type.
     *
     * @return string
     */
    public function typeGeometry(Fluent $column)
    {
        return 'geometry';
    }

    /**
     * Create the column definition for a spatial Point type.
     *
     * @return string
     */
    public function typePoint(Fluent $column)
    {
        return 'point';
    }

    /**
     * Create the column definition for a spatial LineString type.
     *
     * @return string
     */
    public function typeLineString(Fluent $column)
    {
        return 'linestring';
    }

    /**
     * Create the column definition for a spatial Polygon type.
     *
     * @return string
     */
    public function typePolygon(Fluent $column)
    {
        return 'polygon';
    }

    /**
     * Create the column definition for a spatial GeometryCollection type.
     *
     * @return string
     */
    public function typeGeometryCollection(Fluent $column)
    {
        return 'geometrycollection';
    }

    /**
     * Create the column definition for a spatial MultiPoint type.
     *
     * @return string
     */
    public function typeMultiPoint(Fluent $column)
    {
        return 'multipoint';
    }

    /**
     * Create the column definition for a spatial MultiLineString type.
     *
     * @return string
     */
    public function typeMultiLineString(Fluent $column)
    {
        return 'multilinestring';
    }

    /**
     * Create the column definition for a spatial MultiPolygon type.
     *
     * @return string
     */
    public function typeMultiPolygon(Fluent $column)
    {
        return 'multipolygon';
    }

    /**
     * Create the main create table clause.
     *
     * @param \Hyperf\Database\Schema\Blueprint $blueprint
     * @param \Hyperf\Utils\Fluent $command
     * @param \Hyperf\Database\Connection $connection
     * @return string
     */
    protected function compileCreateTable($blueprint, $command, $connection)
    {
        return sprintf(
            '%s table %s (%s)',
            $blueprint->temporary ? 'create temporary' : 'create',
            $this->wrapTable($blueprint),
            implode(', ', $this->getColumns($blueprint))
        );
    }

    /**
     * Append the character set specifications to a command.
     *
     * @param string $sql
     * @return string
     */
    protected function compileCreateEncoding($sql, Connection $connection, Blueprint $blueprint)
    {
        // First we will set the character set if one has been set on either the create
        // blueprint itself or on the root configuration for the connection that the
        // table is being created on. We will add these to the create table query.
        if (isset($blueprint->charset)) {
            $sql .= ' default character set ' . $blueprint->charset;
        } elseif (! is_null($charset = $connection->getConfig('charset'))) {
            $sql .= ' default character set ' . $charset;
        }

        // Next we will add the collation to the create table statement if one has been
        // added to either this create table blueprint or the configuration for this
        // connection that the query is targeting. We'll add it to this SQL query.
        if (isset($blueprint->collation)) {
            $sql .= " collate '{$blueprint->collation}'";
        } elseif (! is_null($collation = $connection->getConfig('collation'))) {
            $sql .= " collate '{$collation}'";
        }

        return $sql;
    }

    /**
     * Append the engine specifications to a command.
     *
     * @param string $sql
     * @return string
     */
    protected function compileCreateEngine($sql, Connection $connection, Blueprint $blueprint)
    {
        if (isset($blueprint->engine)) {
            return $sql . ' engine = ' . $blueprint->engine;
        }
        if (! is_null($engine = $connection->getConfig('engine'))) {
            return $sql . ' engine = ' . $engine;
        }

        return $sql;
    }

    /**
     * Compile an index creation command.
     *
     * @param string $type
     * @return string
     */
    protected function compileKey(Blueprint $blueprint, Fluent $command, $type)
    {
        return sprintf(
            'alter table %s add %s %s%s(%s)',
            $this->wrapTable($blueprint),
            $type,
            $this->wrap($command->index),
            $command->algorithm ? ' using ' . $command->algorithm : '',
            $this->columnize($command->columns)
        );
    }

    /**
     * Create the column definition for a char type.
     *
     * @return string
     */
    protected function typeChar(Fluent $column)
    {
        return "char({$column->length})";
    }

    /**
     * Create the column definition for a string type.
     *
     * @return string
     */
    protected function typeString(Fluent $column)
    {
        return "varchar({$column->length})";
    }

    /**
     * Create the column definition for a text type.
     *
     * @return string
     */
    protected function typeText(Fluent $column)
    {
        return 'text';
    }

    /**
     * Create the column definition for a medium text type.
     *
     * @return string
     */
    protected function typeMediumText(Fluent $column)
    {
        return 'mediumtext';
    }

    /**
     * Create the column definition for a long text type.
     *
     * @return string
     */
    protected function typeLongText(Fluent $column)
    {
        return 'longtext';
    }

    /**
     * Create the column definition for a big integer type.
     *
     * @return string
     */
    protected function typeBigInteger(Fluent $column)
    {
        return 'bigint';
    }

    /**
     * Create the column definition for an integer type.
     *
     * @return string
     */
    protected function typeInteger(Fluent $column)
    {
        return 'int';
    }

    /**
     * Create the column definition for a medium integer type.
     *
     * @return string
     */
    protected function typeMediumInteger(Fluent $column)
    {
        return 'mediumint';
    }

    /**
     * Create the column definition for a tiny integer type.
     *
     * @return string
     */
    protected function typeTinyInteger(Fluent $column)
    {
        return 'tinyint';
    }

    /**
     * Create the column definition for a small integer type.
     *
     * @return string
     */
    protected function typeSmallInteger(Fluent $column)
    {
        return 'smallint';
    }

    /**
     * Create the column definition for a float type.
     *
     * @return string
     */
    protected function typeFloat(Fluent $column)
    {
        return $this->typeDouble($column);
    }

    /**
     * Create the column definition for a double type.
     *
     * @return string
     */
    protected function typeDouble(Fluent $column)
    {
        if ($column->total && $column->places) {
            return "double({$column->total}, {$column->places})";
        }

        return 'double';
    }

    /**
     * Create the column definition for a decimal type.
     *
     * @return string
     */
    protected function typeDecimal(Fluent $column)
    {
        return "decimal({$column->total}, {$column->places})";
    }

    /**
     * Create the column definition for a boolean type.
     *
     * @return string
     */
    protected function typeBoolean(Fluent $column)
    {
        return 'tinyint(1)';
    }

    /**
     * Create the column definition for an enumeration type.
     *
     * @return string
     */
    protected function typeEnum(Fluent $column)
    {
        return sprintf('enum(%s)', $this->quoteString($column->allowed));
    }

    /**
     * Create the column definition for a json type.
     *
     * @return string
     */
    protected function typeJson(Fluent $column)
    {
        return 'json';
    }

    /**
     * Create the column definition for a jsonb type.
     *
     * @return string
     */
    protected function typeJsonb(Fluent $column)
    {
        return 'json';
    }

    /**
     * Create the column definition for a date type.
     *
     * @return string
     */
    protected function typeDate(Fluent $column)
    {
        return 'date';
    }

    /**
     * Create the column definition for a date-time type.
     *
     * @return string
     */
    protected function typeDateTime(Fluent $column)
    {
        $columnType = $column->precision ? "datetime({$column->precision})" : 'datetime';

        return $column->useCurrent ? "{$columnType} default CURRENT_TIMESTAMP" : $columnType;
    }

    /**
     * Create the column definition for a date-time (with time zone) type.
     *
     * @return string
     */
    protected function typeDateTimeTz(Fluent $column)
    {
        return $this->typeDateTime($column);
    }

    /**
     * Create the column definition for a time type.
     *
     * @return string
     */
    protected function typeTime(Fluent $column)
    {
        return $column->precision ? "time({$column->precision})" : 'time';
    }

    /**
     * Create the column definition for a time (with time zone) type.
     *
     * @return string
     */
    protected function typeTimeTz(Fluent $column)
    {
        return $this->typeTime($column);
    }

    /**
     * Create the column definition for a timestamp type.
     *
     * @return string
     */
    protected function typeTimestamp(Fluent $column)
    {
        $columnType = $column->precision ? "timestamp({$column->precision})" : 'timestamp';

        return $column->useCurrent ? "{$columnType} default CURRENT_TIMESTAMP" : $columnType;
    }

    /**
     * Create the column definition for a timestamp (with time zone) type.
     *
     * @return string
     */
    protected function typeTimestampTz(Fluent $column)
    {
        return $this->typeTimestamp($column);
    }

    /**
     * Create the column definition for a year type.
     *
     * @return string
     */
    protected function typeYear(Fluent $column)
    {
        return 'year';
    }

    /**
     * Create the column definition for a binary type.
     *
     * @return string
     */
    protected function typeBinary(Fluent $column)
    {
        return 'blob';
    }

    /**
     * Create the column definition for a uuid type.
     *
     * @return string
     */
    protected function typeUuid(Fluent $column)
    {
        return 'char(36)';
    }

    /**
     * Create the column definition for an IP address type.
     *
     * @return string
     */
    protected function typeIpAddress(Fluent $column)
    {
        return 'varchar(45)';
    }

    /**
     * Create the column definition for a MAC address type.
     *
     * @return string
     */
    protected function typeMacAddress(Fluent $column)
    {
        return 'varchar(17)';
    }

    /**
     * Get the SQL for a generated virtual column modifier.
     *
     * @return null|string
     */
    protected function modifyVirtualAs(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->virtualAs)) {
            return " as ({$column->virtualAs})";
        }
    }

    /**
     * Get the SQL for a generated stored column modifier.
     *
     * @return null|string
     */
    protected function modifyStoredAs(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->storedAs)) {
            return " as ({$column->storedAs}) stored";
        }
    }

    /**
     * Get the SQL for an unsigned column modifier.
     *
     * @return null|string
     */
    protected function modifyUnsigned(Blueprint $blueprint, Fluent $column)
    {
        if ($column->unsigned) {
            return ' unsigned';
        }
    }

    /**
     * Get the SQL for a character set column modifier.
     *
     * @return null|string
     */
    protected function modifyCharset(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->charset)) {
            return ' character set ' . $column->charset;
        }
    }

    /**
     * Get the SQL for a collation column modifier.
     *
     * @return null|string
     */
    protected function modifyCollate(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->collation)) {
            return " collate '{$column->collation}'";
        }
    }

    /**
     * Get the SQL for a nullable column modifier.
     *
     * @return null|string
     */
    protected function modifyNullable(Blueprint $blueprint, Fluent $column)
    {
        if (is_null($column->virtualAs) && is_null($column->storedAs)) {
            return $column->nullable ? ' null' : ' not null';
        }
    }

    /**
     * Get the SQL for a default column modifier.
     *
     * @return null|string
     */
    protected function modifyDefault(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->default)) {
            return ' default ' . $this->getDefaultValue($column->default);
        }
    }

    /**
     * Get the SQL for an auto-increment column modifier.
     *
     * @return null|string
     */
    protected function modifyIncrement(Blueprint $blueprint, Fluent $column)
    {
        if (in_array($column->type, $this->serials) && $column->autoIncrement) {
            return ' auto_increment primary key';
        }
    }

    /**
     * Get the SQL for a "first" column modifier.
     *
     * @return null|string
     */
    protected function modifyFirst(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->first)) {
            return ' first';
        }
    }

    /**
     * Get the SQL for an "after" column modifier.
     *
     * @return null|string
     */
    protected function modifyAfter(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->after)) {
            return ' after ' . $this->wrap($column->after);
        }
    }

    /**
     * Get the SQL for a "comment" column modifier.
     *
     * @return null|string
     */
    protected function modifyComment(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->comment)) {
            return " comment '" . addslashes($column->comment) . "'";
        }
    }

    /**
     * Get the SQL for a SRID column modifier.
     *
     * @return null|string
     */
    protected function modifySrid(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->srid) && is_int($column->srid) && $column->srid > 0) {
            return ' srid ' . $column->srid;
        }
    }

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param string $value
     * @return string
     */
    protected function wrapValue($value)
    {
        if ($value !== '*') {
            return '`' . str_replace('`', '``', $value) . '`';
        }

        return $value;
    }
}
