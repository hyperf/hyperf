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
namespace Hyperf\Database\Sqlsrv\Query\Grammars;

use Hyperf\Collection\Arr;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Grammars\Grammar;
use Hyperf\Stringable\Str;

class SqlServerGrammar extends Grammar
{
    /**
     * All of the available clause operators.
     *
     * @var string[]
     */
    protected array $operators = [
        '=', '<', '>', '<=', '>=', '!<', '!>', '<>', '!=',
        'like', 'not like', 'ilike',
        '&', '&=', '|', '|=', '^', '^=',
    ];

    /**
     * The components that make up a select clause.
     *
     * @var string[]
     */
    protected array $selectComponents = [
        'aggregate',
        'columns',
        'from',
        'indexHint',
        'joins',
        'wheres',
        'groups',
        'havings',
        'orders',
        'offset',
        'limit',
        'lock',
    ];

    /**
     * Compile a select query into SQL.
     */
    public function compileSelect(Builder $query): string
    {
        // An order by clause is required for SQL Server offset to function...
        if ($query->offset && empty($query->orders)) {
            $query->orders[] = ['sql' => '(SELECT 0)'];
        }

        return parent::compileSelect($query);
    }

    /**
     * Prepare the binding for a "JSON contains" statement.
     *
     * @param mixed $binding
     */
    public function prepareBindingForJsonContains($binding): string
    {
        return is_bool($binding) ? json_encode($binding) : $binding;
    }

    /**
     * Compile a "JSON value cast" statement into SQL.
     */
    public function compileJsonValueCast(string $value): string
    {
        return 'json_query(' . $value . ')';
    }

    /**
     * Compile the random statement into SQL.
     *
     * @param int|string $seed
     */
    public function compileRandom($seed): string
    {
        return 'NEWID()';
    }

    /**
     * Compile an exists statement into SQL.
     */
    public function compileExists(Builder $query): string
    {
        $existsQuery = clone $query;

        $existsQuery->columns = [];

        return $this->compileSelect($existsQuery->selectRaw('1 [exists]')->limit(1));
    }

    /**
     * Compile an "upsert" statement into SQL.
     */
    public function compileUpsert(Builder $query, array $values, array $uniqueBy, array $update): string
    {
        $columns = $this->columnize(array_keys(reset($values)));

        $sql = 'merge ' . $this->wrapTable($query->from) . ' ';

        $parameters = collect($values)->map(function ($record) {
            return '(' . $this->parameterize($record) . ')';
        })->implode(', ');

        $sql .= 'using (values ' . $parameters . ') ' . $this->wrapTable('laravel_source') . ' (' . $columns . ') ';

        $on = collect($uniqueBy)->map(function ($column) use ($query) {
            return $this->wrap('laravel_source.' . $column) . ' = ' . $this->wrap($query->from . '.' . $column);
        })->implode(' and ');

        $sql .= 'on ' . $on . ' ';

        if ($update) {
            $update = collect($update)->map(function ($value, $key) {
                return is_numeric($key)
                    ? $this->wrap($value) . ' = ' . $this->wrap('laravel_source.' . $value)
                    : $this->wrap($key) . ' = ' . $this->parameter($value);
            })->implode(', ');

            $sql .= 'when matched then update set ' . $update . ' ';
        }

        $sql .= 'when not matched then insert (' . $columns . ') values (' . $columns . ');';

        return $sql;
    }

    /**
     * Prepare the bindings for an update statement.
     */
    public function prepareBindingsForUpdate(array $bindings, array $values): array
    {
        $cleanBindings = Arr::except($bindings, 'select');

        return array_values(
            array_merge($values, Arr::flatten($cleanBindings))
        );
    }

    /**
     * Compile the SQL statement to define a savepoint.
     *
     * @param string $name
     */
    public function compileSavepoint($name): string
    {
        return 'SAVE TRANSACTION ' . $name;
    }

    /**
     * Compile the SQL statement to execute a savepoint rollback.
     *
     * @param string $name
     */
    public function compileSavepointRollBack($name): string
    {
        return 'ROLLBACK TRANSACTION ' . $name;
    }

    /**
     * Get the format for database stored dates.
     */
    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:s.v';
    }

    /**
     * Wrap a table in keyword identifiers.
     *
     * @param \Hyperf\Database\Query\Expression|string $table
     */
    public function wrapTable($table): string
    {
        if (! $this->isExpression($table)) {
            return $this->wrapTableValuedFunction(parent::wrapTable($table));
        }

        return $this->getValue($table);
    }

    /**
     * Compile the "select *" portion of the query.
     *
     * @param array $columns
     */
    protected function compileColumns(Builder $query, $columns): ?string
    {
        if (! is_null($query->aggregate)) {
            return null;
        }

        $select = $query->distinct ? 'select distinct ' : 'select ';

        // If there is a limit on the query, but not an offset, we will add the top
        // clause to the query, which serves as a "limit" type clause within the
        // SQL Server system similar to the limit keywords available in MySQL.
        if (is_numeric($query->limit) && $query->limit > 0 && $query->offset <= 0) {
            $select .= 'top ' . ((int) $query->limit) . ' ';
        }

        return $select . $this->columnize($columns);
    }

    /**
     * Compile the "from" portion of the query.
     *
     * @param string $table
     */
    protected function compileFrom(Builder $query, $table): string
    {
        $from = parent::compileFrom($query, $table);

        if (is_string($query->lock)) {
            return $from . ' ' . $query->lock;
        }

        if (! is_null($query->lock)) {
            return $from . ' with(rowlock,' . ($query->lock ? 'updlock,' : '') . 'holdlock)';
        }

        return $from;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $where
     */
    protected function whereBitwise(Builder $query, $where): string
    {
        $value = $this->parameter($where['value']);

        $operator = str_replace('?', '??', $where['operator']);

        return '(' . $this->wrap($where['column']) . ' ' . $operator . ' ' . $value . ') != 0';
    }

    /**
     * Compile a "where date" clause.
     *
     * @param array $where
     */
    protected function whereDate(Builder $query, $where): string
    {
        $value = $this->parameter($where['value']);

        return 'cast(' . $this->wrap($where['column']) . ' as date) ' . $where['operator'] . ' ' . $value;
    }

    /**
     * Compile a "where time" clause.
     *
     * @param array $where
     */
    protected function whereTime(Builder $query, $where): string
    {
        $value = $this->parameter($where['value']);

        return 'cast(' . $this->wrap($where['column']) . ' as time) ' . $where['operator'] . ' ' . $value;
    }

    /**
     * Compile a "JSON contains" statement into SQL.
     *
     * @param string $column
     * @param string $value
     */
    protected function compileJsonContains($column, $value): string
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($column);

        return $value . ' in (select [value] from openjson(' . $field . $path . '))';
    }

    /**
     * Compile a "JSON contains key" statement into SQL.
     */
    protected function compileJsonContainsKey(string $column): string
    {
        $segments = explode('->', $column);

        $lastSegment = array_pop($segments);

        if (preg_match('/\[([0-9]+)\]$/', $lastSegment, $matches)) {
            $segments[] = Str::beforeLast($lastSegment, $matches[0]);

            $key = $matches[1];
        } else {
            $key = "'" . str_replace("'", "''", $lastSegment) . "'";
        }

        [$field, $path] = $this->wrapJsonFieldAndPath(implode('->', $segments));

        return $key . ' in (select [key] from openjson(' . $field . $path . '))';
    }

    /**
     * Compile a "JSON length" statement into SQL.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     */
    protected function compileJsonLength($column, $operator, $value): string
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($column);

        return '(select count(*) from openjson(' . $field . $path . ')) ' . $operator . ' ' . $value;
    }

    /**
     * Compile a single having clause.
     */
    protected function compileHaving(array $having): string
    {
        if ($having['type'] === 'Bitwise') {
            return $this->compileHavingBitwise($having);
        }

        return parent::compileHaving($having);
    }

    /**
     * Compile a having clause involving a bitwise operator.
     */
    protected function compileHavingBitwise(array $having): string
    {
        $column = $this->wrap($having['column']);

        $parameter = $this->parameter($having['value']);

        return '(' . $column . ' ' . $having['operator'] . ' ' . $parameter . ') != 0';
    }

    /**
     * Compile a delete statement without joins into SQL.
     */
    protected function compileDeleteWithoutJoins(Builder $query, string $table, string $where): string
    {
        $sql = parent::compileDeleteWithoutJoins($query, $table, $where);

        return ! is_null($query->limit) && $query->limit > 0 && $query->offset <= 0
            ? Str::replaceFirst('delete', 'delete top (' . $query->limit . ')', $sql)
            : $sql;
    }

    /**
     * Compile the "limit" portions of the query.
     *
     * @param int $limit
     */
    protected function compileLimit(Builder $query, $limit): string
    {
        $limit = (int) $limit;

        if ($limit && $query->offset > 0) {
            return "fetch next {$limit} rows only";
        }

        return '';
    }

    /**
     * Compile a row number clause.
     */
    protected function compileRowNumber(string $partition, string $orders): string
    {
        if (empty($orders)) {
            $orders = 'order by (select 0)';
        }

        return parent::compileRowNumber($partition, $orders);
    }

    /**
     * Compile the "offset" portions of the query.
     *
     * @param int $offset
     */
    protected function compileOffset(Builder $query, $offset): string
    {
        $offset = (int) $offset;

        if ($offset) {
            return "offset {$offset} rows";
        }

        return '';
    }

    /**
     * Compile the lock into SQL.
     *
     * @param bool|string $value
     */
    protected function compileLock(Builder $query, $value): string
    {
        return '';
    }

    /**
     * Wrap a union subquery in parentheses.
     */
    protected function wrapUnion(string $sql): string
    {
        return 'select * from (' . $sql . ') as ' . $this->wrapTable('temp_table');
    }

    /**
     * Compile an update statement with joins into SQL.
     */
    protected function compileUpdateWithJoins(Builder $query, string $table, string $columns, string $where): string
    {
        $alias = last(explode(' as ', $table));

        $joins = $this->compileJoins($query, $query->joins);

        return "update {$alias} set {$columns} from {$table} {$joins} {$where}";
    }

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param string $value
     */
    protected function wrapValue($value): string
    {
        return $value === '*' ? $value : '[' . str_replace(']', ']]', $value) . ']';
    }

    /**
     * Wrap the given JSON selector.
     *
     * @param string $value
     */
    protected function wrapJsonSelector($value): string
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($value);

        return 'json_value(' . $field . $path . ')';
    }

    /**
     * Wrap the given JSON boolean value.
     *
     * @param string $value
     */
    protected function wrapJsonBooleanValue($value): string
    {
        return "'" . $value . "'";
    }

    /**
     * Wrap a table in keyword identifiers.
     */
    protected function wrapTableValuedFunction(string $table): string
    {
        if (preg_match('/^(.+?)(\(.*?\))]$/', $table, $matches) === 1) {
            $table = $matches[1] . ']' . $matches[2];
        }

        return $table;
    }
}
