<?php

namespace Hyperf\DbConnection\Query\Grammars;

use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Grammars\PostgresGrammar as QueryPostgresGrammar;

class PostgresGrammar extends QueryPostgresGrammar
{
    protected function whereBasic(Builder $query, $where)
    {
        $value = $this->parameter($where['value'], $query);

        return $this->wrap($where['column']) . ' ' . $where['operator'] . ' ' . $value;
    }

    public function parameterize(array $values, ?Builder $query = null)
    {
        return implode(', ', array_map(function ($item) use ($query) {
            return $this->parameter($item, $query);
        }, $values));
    }

    public function parameter($value, ?Builder $query = null)
    {
        return $this->isExpression($value) ? $this->getValue($value) : '$'. $query->prepareNum++;
    }

    public function compileInsert(Builder $query, array $values)
    {
        // Essentially we will force every insert to be treated as a batch insert which
        // simply makes creating the SQL easier for us since we can utilize the same
        // basic routine regardless of an amount of records given to us to insert.
        $table = $this->wrapTable($query->from);

        if (! is_array(reset($values))) {
            $values = [$values];
        }

        $columns = $this->columnize(array_keys(reset($values)));

        // We need to build a list of parameter place-holders of values that are bound
        // to the query. Each insert should have the exact same amount of parameter
        // bindings so we will loop through the record and parameterize them all.
        $parameters = collect($values)->map(function ($record) use ($query) {
            return '(' . $this->parameterize($record, $query) . ')';
        })->implode(', ');

        return "insert into {$table} ({$columns}) values {$parameters}";
    }

    /**
     * Compile an update statement into SQL.
     *
     * @param array $values
     * @return string
     */
    public function compileUpdate(Builder $query, $values)
    {
        $table = $this->wrapTable($query->from);

        // Each one of the columns in the update statements needs to be wrapped in the
        // keyword identifiers, also a place-holder needs to be created for each of
        // the values in the list of bindings so we can make the sets statements.
        $columns = collect($values)->map(function ($value, $key) use ($query) {
            $key = strpos($key, '.') ? explode('.', $key)[1] : $key;
            return $key . ' = ' . $this->parameter($value, $query);
        })->implode(', ');

        // If the query has any "join" clauses, we will setup the joins on the builder
        // and compile them so we can attach them to this update, as update queries
        // can get join statements to attach to other tables when they're needed.
        $joins = '';

        if (isset($query->joins)) {
            $joins = ' ' . $this->compileJoins($query, $query->joins);
        }

        // Of course, update queries may also be constrained by where clauses so we'll
        // need to compile the where clauses and attach it to the query so only the
        // intended records are updated by the SQL statements we generate to run.
        $wheres = $this->compileWheres($query);

        return trim("update {$table}{$joins} set {$columns} {$wheres}");
    }

    /**
     * Wrap a value in keyword identifiers.
     *
     * @param \Hyperf\Database\Query\Expression|string $value
     * @param bool $prefixAlias
     * @return string
     */
    public function wrap($value, $prefixAlias = false)
    {
        if ($this->isExpression($value)) {
            return $this->getValue($value);
        }

        // If the value being wrapped has a column alias we will need to separate out
        // the pieces so we can wrap each of the segments of the expression on its
        // own, and then join these both back together using the "as" connector.
        if (stripos($value, ' as ') !== false) {
            return $this->wrapAliasedValue($value, $prefixAlias);
        }

        // If the given value is a JSON selector we will wrap it differently than a
        // traditional value. We will need to split this path and wrap each part
        // wrapped, etc. Otherwise, we will simply wrap the value as a string.
        if ($this->isJsonSelector($value)) {
            return $this->wrapJsonSelector($value);
        }

        var_dump('this is value=======');
        var_dump($value);

        return $this->wrapSegments(explode('.', $value));
    }
}
