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
namespace Hyperf\Database\PgSQL\Query\Grammars;

use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Grammars\Grammar;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;

class PostgresGrammar extends Grammar
{
    /**
     * All of the available clause operators.
     *
     * @var string[]
     */
    protected array $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=',
        'like', 'not like', 'between', 'ilike', 'not ilike',
        '~', '&', '|', '#', '<<', '>>', '<<=', '>>=',
        '&&', '@>', '<@', '?', '?|', '?&', '||', '-', '@?', '@@', '#-',
        'is distinct from', 'is not distinct from',
    ];

    /**
     * Compile an insert ignore statement into SQL.
     */
    public function compileInsertOrIgnore(Builder $query, array $values): string
    {
        return $this->compileInsert($query, $values) . ' on conflict do nothing';
    }

    /**
     * Compile an insert and get ID statement into SQL.
     */
    public function compileInsertGetId(Builder $query, array $values, string $sequence): string
    {
        return $this->compileInsert($query, $values) . ' returning ' . $this->wrap($sequence ?: 'id');
    }

    /**
     * Compile an update statement into SQL.
     */
    public function compileUpdate(Builder $query, array $values): string
    {
        $table = $this->wrapTable($query->from);

        // Each one of the columns in the update statements needs to be wrapped in the
        // keyword identifiers, also a place-holder needs to be created for each of
        // the values in the list of bindings so we can make the sets statements.
        $columns = $this->compileUpdateColumns($query, $values);

        $from = $this->compileUpdateFrom($query);

        $where = $this->compileUpdateWheres($query);

        return trim("update {$table} set {$columns}{$from} {$where}");
    }

    /**
     * Compile an "upsert" statement into SQL.
     */
    public function compileUpsert(Builder $query, array $values, array $uniqueBy, array $update): string
    {
        $sql = $this->compileInsert($query, $values);

        $sql .= ' on conflict (' . $this->columnize($uniqueBy) . ') do update set ';

        $columns = collect($update)->map(function ($value, $key) {
            return is_numeric($key)
                ? $this->wrap($value) . ' = ' . $this->wrapValue('excluded') . '.' . $this->wrap($value)
                : $this->wrap($key) . ' = ' . $this->parameter($value);
        })->implode(', ');

        return $sql . $columns;
    }

    /**
     * Prepare the bindings for an update statement.
     */
    public function prepareBindingsForUpdateFrom(array $bindings, array $values): array
    {
        $values = collect($values)->map(function ($value, $column) {
            return is_array($value) || ($this->isJsonSelector($column) && ! $this->isExpression($value))
                ? json_encode($value)
                : $value;
        })->all();

        $bindingsWithoutWhere = Arr::except($bindings, ['select', 'where']);

        return array_values(
            array_merge($values, $bindings['where'], Arr::flatten($bindingsWithoutWhere))
        );
    }

    /**
     * Prepare the bindings for an update statement.
     */
    public function prepareBindingsForUpdate(array $bindings, array $values): array
    {
        $values = collect($values)->map(function ($value, $column) {
            return is_array($value) || ($this->isJsonSelector($column) && ! $this->isExpression($value))
                ? json_encode($value)
                : $value;
        })->all();

        $cleanBindings = Arr::except($bindings, 'select');

        return array_values(
            array_merge($values, Arr::flatten($cleanBindings))
        );
    }

    /**
     * Compile a delete statement into SQL.
     */
    public function compileDelete(Builder $query): string
    {
        if (isset($query->joins) || isset($query->limit)) {
            return $this->compileDeleteWithJoinsOrLimit($query);
        }

        return parent::compileDelete($query);
    }

    /**
     * Compile a truncate table statement into SQL.
     */
    public function compileTruncate(Builder $query): array
    {
        return ['truncate ' . $this->wrapTable($query->from) . ' restart identity cascade' => []];
    }

    /**
     * Compile an update from statement into SQL.
     */
    protected function compileUpdateFrom(Builder $query): string
    {
        if (! isset($query->joins)) {
            return '';
        }

        // When using Postgres, updates with joins list the joined tables in the from
        // clause, which is different than other systems like MySQL. Here, we will
        // compile out the tables that are joined and add them to a from clause.
        $froms = collect($query->joins)->map(function ($join) {
            return $this->wrapTable($join->table);
        })->all();

        if (count($froms) > 0) {
            return ' from ' . implode(', ', $froms);
        }
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function whereBasic(Builder $query, array $where): string
    {
        if (Str::contains(strtolower($where['operator']), 'like')) {
            return sprintf(
                '%s::text %s %s',
                $this->wrap($where['column']),
                $where['operator'],
                $this->parameter($where['value'])
            );
        }

        return parent::whereBasic($query, $where);
    }

    /**
     * Compile a "where date" clause.
     */
    protected function whereDate(Builder $query, array $where): string
    {
        $value = $this->parameter($where['value']);

        return $this->wrap($where['column']) . '::date ' . $where['operator'] . ' ' . $value;
    }

    /**
     * Compile a "where time" clause.
     */
    protected function whereTime(Builder $query, array $where): string
    {
        $value = $this->parameter($where['value']);

        return $this->wrap($where['column']) . '::time ' . $where['operator'] . ' ' . $value;
    }

    /**
     * Compile a date based where clause.
     */
    protected function dateBasedWhere(string $type, Builder $query, array $where): string
    {
        $value = $this->parameter($where['value']);

        return 'extract(' . $type . ' from ' . $this->wrap($where['column']) . ') ' . $where['operator'] . ' ' . $value;
    }

    /**
     * Compile the "select *" portion of the query.
     */
    protected function compileColumns(Builder $query, array $columns): ?string
    {
        // If the query is actually performing an aggregating select, we will let that
        // compiler handle the building of the select clauses, as it will need some
        // more syntax that is best handled by that function to keep things neat.
        if (! is_null($query->aggregate)) {
            return null;
        }

        if (is_array($query->distinct)) {
            $select = 'select distinct on (' . $this->columnize($query->distinct) . ') ';
        } elseif ($query->distinct) {
            $select = 'select distinct ';
        } else {
            $select = 'select ';
        }

        return $select . $this->columnize($columns);
    }

    /**
     * Compile a "JSON contains" statement into SQL.
     */
    protected function compileJsonContains(string $column, string $value): string
    {
        $column = str_replace('->>', '->', $this->wrap($column));

        return '(' . $column . ')::jsonb @> ' . $value;
    }

    /**
     * Compile a "JSON length" statement into SQL.
     */
    protected function compileJsonLength(string $column, string $operator, string $value): string
    {
        $column = str_replace('->>', '->', $this->wrap($column));

        return 'json_array_length((' . $column . ')::json) ' . $operator . ' ' . $value;
    }

    /**
     * Compile the lock into SQL.
     */
    protected function compileLock(Builder $query, bool|string $value): string
    {
        if (! is_string($value)) {
            return $value ? 'for update' : 'for share';
        }

        return $value;
    }

    /**
     * Compile the columns for an update statement.
     */
    protected function compileUpdateColumns(Builder $query, array $values): string
    {
        return collect($values)->map(function ($value, $key) {
            $column = last(explode('.', $key));

            if ($this->isJsonSelector($key)) {
                return $this->compileJsonUpdateColumn($column, $value);
            }

            return $this->wrap($column) . ' = ' . $this->parameter($value);
        })->implode(', ');
    }

    /**
     * Prepares a JSON column being updated using the JSONB_SET function.
     */
    protected function compileJsonUpdateColumn(string $key, mixed $value): string
    {
        $segments = explode('->', $key);

        $field = $this->wrap(array_shift($segments));

        $path = '\'{"' . implode('","', $segments) . '"}\'';

        return "{$field} = jsonb_set({$field}::jsonb, {$path}, {$this->parameter($value)})";
    }

    /**
     * Compile the additional where clauses for updates with joins.
     */
    protected function compileUpdateWheres(Builder $query): string
    {
        $baseWheres = $this->compileWheres($query);

        if (! isset($query->joins)) {
            return $baseWheres;
        }

        // Once we compile the join constraints, we will either use them as the where
        // clause or append them to the existing base where clauses. If we need to
        // strip the leading boolean we will do so when using as the only where.
        $joinWheres = $this->compileUpdateJoinWheres($query);

        if (trim($baseWheres) == '') {
            return 'where ' . $this->removeLeadingBoolean($joinWheres);
        }

        return $baseWheres . ' ' . $joinWheres;
    }

    /**
     * Compile the "join" clause where clauses for an update.
     */
    protected function compileUpdateJoinWheres(Builder $query): string
    {
        $joinWheres = [];

        // Here we will just loop through all of the join constraints and compile them
        // all out then implode them. This should give us "where" like syntax after
        // everything has been built and then we will join it to the real wheres.
        foreach ($query->joins as $join) {
            foreach ($join->wheres as $where) {
                $method = "where{$where['type']}";

                $joinWheres[] = $where['boolean'] . ' ' . $this->{$method}($query, $where);
            }
        }

        return implode(' ', $joinWheres);
    }

    /**
     * Compile an update statement with joins or limit into SQL.
     */
    protected function compileUpdateWithJoinsOrLimit(Builder $query, array $values): string
    {
        $table = $this->wrapTable($query->from);

        $columns = $this->compileUpdateColumns($query, $values);

        $alias = last(preg_split('/\s+as\s+/i', $query->from));

        $selectSql = $this->compileSelect($query->select($alias . '.ctid'));

        return "update {$table} set {$columns} where {$this->wrap('ctid')} in ({$selectSql})";
    }

    /**
     * Compile a delete statement with joins or limit into SQL.
     */
    protected function compileDeleteWithJoinsOrLimit(Builder $query): string
    {
        $table = $this->wrapTable($query->from);

        $alias = last(preg_split('/\s+as\s+/i', $query->from));

        $selectSql = $this->compileSelect($query->select($alias . '.ctid'));

        return "delete from {$table} where {$this->wrap('ctid')} in ({$selectSql})";
    }

    /**
     * Wrap the given JSON selector.
     */
    protected function wrapJsonSelector(string $value): string
    {
        $path = explode('->', $value);

        $field = $this->wrapSegments(explode('.', array_shift($path)));

        $wrappedPath = $this->wrapJsonPathAttributes($path);

        $attribute = array_pop($wrappedPath);

        if (! empty($wrappedPath)) {
            return $field . '->' . implode('->', $wrappedPath) . '->>' . $attribute;
        }

        return $field . '->>' . $attribute;
    }

    /**
     * Wrap the given JSON selector for boolean values.
     */
    protected function wrapJsonBooleanSelector(string $value): string
    {
        $selector = str_replace(
            '->>',
            '->',
            $this->wrapJsonSelector($value)
        );

        return '(' . $selector . ')::jsonb';
    }

    /**
     * Wrap the given JSON boolean value.
     */
    protected function wrapJsonBooleanValue(string $value): string
    {
        return "'" . $value . "'::jsonb";
    }

    /**
     * Wrap the attributes of the give JSON path.
     */
    protected function wrapJsonPathAttributes(array $path): array
    {
        return array_map(function ($attribute) {
            return filter_var($attribute, FILTER_VALIDATE_INT) !== false
                ? $attribute
                : "'{$attribute}'";
        }, $path);
    }
}
