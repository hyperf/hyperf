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

namespace Hyperf\Database\Query\Grammars;

use Hyperf\Collection\Arr;
use Hyperf\Database\Grammar as BaseGrammar;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Expression;
use Hyperf\Database\Query\JoinClause;
use Hyperf\Database\Query\JoinLateralClause;
use Hyperf\Stringable\Str;
use RuntimeException;

use function Hyperf\Collection\collect;
use function Hyperf\Collection\head;
use function Hyperf\Collection\last;

class Grammar extends BaseGrammar
{
    /**
     * The grammar specific operators.
     */
    protected array $operators = [];

    /**
     * The components that make up a select clause.
     */
    protected array $selectComponents = [
        'aggregate',
        'columns',
        'from',
        'joins',
        'wheres',
        'groups',
        'havings',
        'orders',
        'limit',
        'offset',
        'unions',
        'lock',
    ];

    /**
     * Compile a select query into SQL.
     */
    public function compileSelect(Builder $query): string
    {
        if ($query->unions && $query->aggregate) {
            return $this->compileUnionAggregate($query);
        }

        // If the query does not have any columns set, we'll set the columns to the
        // * character to just get all of the columns from the database. Then we
        // can build the query and concatenate all the pieces together as one.
        $original = $query->columns;

        if (is_null($query->columns)) {
            $query->columns = ['*'];
        }

        // To compile the query, we'll spin through each component of the query and
        // see if that component exists. If it does we'll just call the compiler
        // function for the component which is responsible for making the SQL.
        $sql = trim(
            $this->concatenate(
                $this->compileComponents($query)
            )
        );

        $query->columns = $original;

        return $sql;
    }

    /**
     * Prepare the binding for a "JSON contains" statement.
     *
     * @param mixed $binding
     * @return string
     */
    public function prepareBindingForJsonContains($binding)
    {
        return json_encode($binding);
    }

    /**
     * Compile the random statement into SQL.
     *
     * @param string $seed
     */
    public function compileRandom($seed): string
    {
        return 'RANDOM()';
    }

    /**
     * Compile an insert ignore statement into SQL.
     */
    public function compileInsertOrIgnore(Builder $query, array $values)
    {
        throw new RuntimeException('This database engine does not support insert or ignore.');
    }

    /**
     * Compile an exists statement into SQL.
     */
    public function compileExists(Builder $query): string
    {
        $select = $this->compileSelect($query);

        return "select exists({$select}) as {$this->wrap('exists')}";
    }

    /**
     * Compile an insert statement into SQL.
     */
    public function compileInsert(Builder $query, array $values): string
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
        $parameters = collect($values)->map(function ($record) {
            return '(' . $this->parameterize($record) . ')';
        })->implode(', ');

        return "insert into {$table} ({$columns}) values {$parameters}";
    }

    /**
     * Compile an insert and get ID statement into SQL.
     *
     * @param array $values
     * @param string $sequence
     */
    public function compileInsertGetId(Builder $query, $values, $sequence): string
    {
        return $this->compileInsert($query, $values);
    }

    /**
     * Compile an insert statement using a subquery into SQL.
     */
    public function compileInsertUsing(Builder $query, array $columns, string $sql): string
    {
        $table = $this->wrapTable($query->from);
        if (empty($columns) || $columns === ['*']) {
            return "insert into {$table} {$sql}";
        }

        return "insert into {$table} ({$this->columnize($columns)}) {$sql}";
    }

    /**
     * Compile an insert ignore statement using a subquery into SQL.
     */
    public function compileInsertOrIgnoreUsing(Builder $query, array $columns, string $sql): string
    {
        throw new RuntimeException('This database engine does not support inserting while ignoring errors.');
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
     * Compile an "upsert" statement into SQL.
     *
     * @throws RuntimeException
     */
    public function compileUpsert(Builder $query, array $values, array $uniqueBy, array $update): string
    {
        throw new RuntimeException('This database engine does not support upserts.');
    }

    /**
     * Prepare the bindings for an update statement.
     */
    public function prepareBindingsForUpdate(array $bindings, array $values): array
    {
        $cleanBindings = Arr::except($bindings, ['join', 'select']);

        return array_values(
            array_merge($bindings['join'], $values, Arr::flatten($cleanBindings))
        );
    }

    /**
     * Compile a delete statement into SQL.
     */
    public function compileDelete(Builder $query): string
    {
        $wheres = is_array($query->wheres) ? $this->compileWheres($query) : '';

        return trim("delete from {$this->wrapTable($query->from)} {$wheres}");
    }

    /**
     * Prepare the bindings for a delete statement.
     */
    public function prepareBindingsForDelete(array $bindings): array
    {
        return Arr::flatten($bindings);
    }

    /**
     * Compile a truncate table statement into SQL.
     */
    public function compileTruncate(Builder $query): array
    {
        return ['truncate ' . $this->wrapTable($query->from) => []];
    }

    /**
     * Determine if the grammar supports savepoints.
     */
    public function supportsSavepoints(): bool
    {
        return true;
    }

    /**
     * Compile the SQL statement to define a savepoint.
     *
     * @param string $name
     */
    public function compileSavepoint($name): string
    {
        return 'SAVEPOINT ' . $name;
    }

    /**
     * Compile the SQL statement to execute a savepoint rollback.
     *
     * @param string $name
     */
    public function compileSavepointRollBack($name): string
    {
        return 'ROLLBACK TO SAVEPOINT ' . $name;
    }

    /**
     * Wrap a value in keyword identifiers.
     *
     * @param bool $prefixAlias
     * @return string
     */
    public function wrap(Expression|string $value, $prefixAlias = false)
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

        return $this->wrapSegments(explode('.', $value));
    }

    /**
     * Get the grammar specific operators.
     */
    public function getOperators(): array
    {
        return $this->operators;
    }

    /**
     * Substitute the given bindings into the given raw SQL query.
     *
     * @param string $sql
     * @param array $bindings
     * @return string
     */
    public function substituteBindingsIntoRawSql($sql, $bindings)
    {
        $query = '';

        $isStringLiteral = false;

        for ($i = 0; $i < strlen($sql); ++$i) {
            $char = $sql[$i];
            $nextChar = $sql[$i + 1] ?? null;

            // Single quotes can be escaped as '' according to the SQL standard while
            // MySQL uses \'. Postgres has operators like ?| that must get encoded
            // in PHP like ??|. We should skip over the escaped characters here.
            if (in_array($char . $nextChar, ["\\'", "''", '??'])) {
                $query .= $char . $nextChar;
                ++$i;
            } elseif ($char === "'") { // Starting / leaving string literal...
                $query .= $char;
                $isStringLiteral = ! $isStringLiteral;
            } elseif ($char === '?' && ! $isStringLiteral) { // Substitutable binding...
                $query .= array_shift($bindings) ?? '?';
            } else { // Normal character...
                $query .= $char;
            }
        }

        return $query;
    }

    /**
     * Compile a "lateral join" clause.
     *
     * @throws RuntimeException
     */
    public function compileJoinLateral(JoinLateralClause $join, string $expression): string
    {
        throw new RuntimeException('This database engine does not support lateral joins.');
    }

    /**
     * Compile the columns for an update statement.
     */
    protected function compileUpdateColumns(Builder $query, array $values): string
    {
        return collect($values)->map(function ($value, $key) {
            return $this->wrap($key) . ' = ' . $this->parameter($value);
        })->implode(', ');
    }

    /**
     * Compile a "where JSON overlaps" clause.
     */
    protected function whereJsonOverlaps(Builder $query, array $where): string
    {
        $not = $where['not'] ? 'not ' : '';

        return $not . $this->compileJsonOverlaps(
            $where['column'],
            $this->parameter($where['value'])
        );
    }

    /**
     * Compile a "JSON overlaps" statement into SQL.
     */
    protected function compileJsonOverlaps(string $column, string $value): string
    {
        throw new RuntimeException('This database engine does not support JSON overlaps operations.');
    }

    /**
     * Compile the components necessary for a select clause.
     */
    protected function compileComponents(Builder $query): array
    {
        $sql = [];

        foreach ($this->selectComponents as $component) {
            // To compile the query, we'll spin through each component of the query and
            // see if that component exists. If it does we'll just call the compiler
            // function for the component which is responsible for making the SQL.
            if (isset($query->{$component}) && ! is_null($query->{$component})) {
                $method = 'compile' . ucfirst($component);

                $sql[$component] = $this->{$method}($query, $query->{$component});
            }
        }

        return $sql;
    }

    /**
     * Compile an aggregated select clause.
     *
     * @param array $aggregate
     */
    protected function compileAggregate(Builder $query, $aggregate): string
    {
        $column = $this->columnize($aggregate['columns']);

        // If the query has a "distinct" constraint and we're not asking for all columns
        // we need to prepend "distinct" onto the column name so that the query takes
        // it into account when it performs the aggregating operations on the data.
        if ($query->distinct && $column !== '*') {
            $column = 'distinct ' . $column;
        }

        return 'select ' . $aggregate['function'] . '(' . $column . ') as aggregate';
    }

    /**
     * Compile the "select *" portion of the query.
     *
     * @param array $columns
     */
    protected function compileColumns(Builder $query, $columns): ?string
    {
        // If the query is actually performing an aggregating select, we will let that
        // compiler handle the building of the select clauses, as it will need some
        // more syntax that is best handled by that function to keep things neat.
        if (! is_null($query->aggregate)) {
            return null;
        }

        $select = $query->distinct ? 'select distinct ' : 'select ';

        return $select . $this->columnize($columns);
    }

    /**
     * Compile the "from" portion of the query.
     *
     * @param string $table
     */
    protected function compileFrom(Builder $query, $table): string
    {
        return 'from ' . $this->wrapTable($table);
    }

    /**
     * Compile the "join" portions of the query.
     *
     * @param array $joins
     */
    protected function compileJoins(Builder $query, $joins): string
    {
        return collect($joins)->map(function ($join) use ($query) {
            $table = $this->wrapTable($join->table);

            $nestedJoins = is_null($join->joins) ? '' : ' ' . $this->compileJoins($query, $join->joins);

            $tableAndNestedJoins = is_null($join->joins) ? $table : '(' . $table . $nestedJoins . ')';

            if ($join instanceof JoinLateralClause) {
                return $this->compileJoinLateral($join, $tableAndNestedJoins);
            }

            return trim("{$join->type} join {$tableAndNestedJoins} {$this->compileWheres($join)}");
        })->implode(' ');
    }

    /**
     * Compile the "where" portions of the query.
     */
    protected function compileWheres(Builder $query): string
    {
        // Each type of where clauses has its own compiler function which is responsible
        // for actually creating the where clauses SQL. This helps keep the code nice
        // and maintainable since each clause has a very small method that it uses.
        if (is_null($query->wheres)) {
            return '';
        }

        // If we actually have some where clauses, we will strip off the first boolean
        // operator, which is added by the query builders for convenience so we can
        // avoid checking for the first clauses in each of the compilers methods.
        if (count($sql = $this->compileWheresToArray($query)) > 0) {
            return $this->concatenateWhereClauses($query, $sql);
        }

        return '';
    }

    /**
     * Get an array of all the where clauses for the query.
     *
     * @param Builder $query
     */
    protected function compileWheresToArray($query): array
    {
        return collect($query->wheres)->map(function ($where) use ($query) {
            return $where['boolean'] . ' ' . $this->{"where{$where['type']}"}($query, $where);
        })->all();
    }

    /**
     * Format the where clause statements into one string.
     *
     * @param Builder $query
     * @param array $sql
     */
    protected function concatenateWhereClauses($query, $sql): string
    {
        $conjunction = $query instanceof JoinClause ? 'on' : 'where';

        return $conjunction . ' ' . $this->removeLeadingBoolean(implode(' ', $sql));
    }

    /**
     * Compile a raw where clause.
     *
     * @param array $where
     * @return string
     */
    protected function whereRaw(Builder $query, $where)
    {
        return $where['sql'];
    }

    /**
     * Compile a basic where clause.
     *
     * @param array $where
     */
    protected function whereBasic(Builder $query, $where): string
    {
        $value = $this->parameter($where['value']);

        return $this->wrap($where['column']) . ' ' . $where['operator'] . ' ' . $value;
    }

    /**
     * Compile a "where JSON boolean" clause.
     *
     * @param array $where
     * @return string
     */
    protected function whereJsonBoolean(Builder $query, $where)
    {
        $column = $this->wrapJsonBooleanSelector($where['column']);

        $value = $this->wrapJsonBooleanValue(
            $this->parameter($where['value'])
        );

        return $column . ' ' . $where['operator'] . ' ' . $value;
    }

    /**
     * Wrap the given JSON selector for boolean values.
     *
     * @param string $value
     * @return string
     */
    protected function wrapJsonBooleanSelector($value)
    {
        return $this->wrapJsonSelector($value);
    }

    /**
     * Wrap the given JSON boolean value.
     * @param mixed $value
     */
    protected function wrapJsonBooleanValue($value)
    {
        return $value;
    }

    /**
     * Compile a "where in" clause.
     *
     * @param array $where
     */
    protected function whereIn(Builder $query, $where): string
    {
        if (! empty($where['values'])) {
            return $this->wrap($where['column']) . ' in (' . $this->parameterize($where['values']) . ')';
        }

        return '0 = 1';
    }

    /**
     * Compile a "where not in" clause.
     *
     * @param array $where
     */
    protected function whereNotIn(Builder $query, $where): string
    {
        if (! empty($where['values'])) {
            return $this->wrap($where['column']) . ' not in (' . $this->parameterize($where['values']) . ')';
        }

        return '1 = 1';
    }

    /**
     * Compile a "where not in raw" clause.
     *
     * For safety, whereIntegerInRaw ensures this method is only used with integer values.
     *
     * @param array $where
     */
    protected function whereNotInRaw(Builder $query, $where): string
    {
        if (! empty($where['values'])) {
            return $this->wrap($where['column']) . ' not in (' . implode(', ', $where['values']) . ')';
        }

        return '1 = 1';
    }

    /**
     * Compile a where in sub-select clause.
     *
     * @param array $where
     */
    protected function whereInSub(Builder $query, $where): string
    {
        return $this->wrap($where['column']) . ' in (' . $this->compileSelect($where['query']) . ')';
    }

    /**
     * Compile a where not in sub-select clause.
     *
     * @param array $where
     */
    protected function whereNotInSub(Builder $query, $where): string
    {
        return $this->wrap($where['column']) . ' not in (' . $this->compileSelect($where['query']) . ')';
    }

    /**
     * Compile a "where in raw" clause.
     *
     * For safety, whereIntegerInRaw ensures this method is only used with integer values.
     *
     * @param array $where
     */
    protected function whereInRaw(Builder $query, $where): string
    {
        if (! empty($where['values'])) {
            return $this->wrap($where['column']) . ' in (' . implode(', ', $where['values']) . ')';
        }

        return '0 = 1';
    }

    /**
     * Compile a "where null" clause.
     *
     * @param array $where
     */
    protected function whereNull(Builder $query, $where): string
    {
        return $this->wrap($where['column']) . ' is null';
    }

    /**
     * Compile a "where not null" clause.
     *
     * @param array $where
     */
    protected function whereNotNull(Builder $query, $where): string
    {
        return $this->wrap($where['column']) . ' is not null';
    }

    /**
     * Compile a "between" where clause.
     *
     * @param array $where
     */
    protected function whereBetween(Builder $query, $where): string
    {
        $between = $where['not'] ? 'not between' : 'between';

        $min = $this->parameter(reset($where['values']));

        $max = $this->parameter(end($where['values']));

        return $this->wrap($where['column']) . ' ' . $between . ' ' . $min . ' and ' . $max;
    }

    /**
     * Compile a "where date" clause.
     *
     * @param array $where
     */
    protected function whereDate(Builder $query, $where): string
    {
        return $this->dateBasedWhere('date', $query, $where);
    }

    /**
     * Compile a "where time" clause.
     *
     * @param array $where
     */
    protected function whereTime(Builder $query, $where): string
    {
        return $this->dateBasedWhere('time', $query, $where);
    }

    /**
     * Compile a "where day" clause.
     *
     * @param array $where
     */
    protected function whereDay(Builder $query, $where): string
    {
        return $this->dateBasedWhere('day', $query, $where);
    }

    /**
     * Compile a "where month" clause.
     *
     * @param array $where
     */
    protected function whereMonth(Builder $query, $where): string
    {
        return $this->dateBasedWhere('month', $query, $where);
    }

    /**
     * Compile a "where year" clause.
     *
     * @param array $where
     */
    protected function whereYear(Builder $query, $where): string
    {
        return $this->dateBasedWhere('year', $query, $where);
    }

    /**
     * Compile a date based where clause.
     *
     * @param string $type
     * @param array $where
     */
    protected function dateBasedWhere($type, Builder $query, $where): string
    {
        $value = $this->parameter($where['value']);

        return $type . '(' . $this->wrap($where['column']) . ') ' . $where['operator'] . ' ' . $value;
    }

    /**
     * Compile a where clause comparing two columns..
     *
     * @param array $where
     */
    protected function whereColumn(Builder $query, $where): string
    {
        return $this->wrap($where['first']) . ' ' . $where['operator'] . ' ' . $this->wrap($where['second']);
    }

    /**
     * Compile a nested where clause.
     *
     * @param array $where
     */
    protected function whereNested(Builder $query, $where): string
    {
        // Here we will calculate what portion of the string we need to remove. If this
        // is a join clause query, we need to remove the "on" portion of the SQL and
        // if it is a normal query we need to take the leading "where" of queries.
        $offset = $query instanceof JoinClause ? 3 : 6;

        return '(' . substr($this->compileWheres($where['query']), $offset) . ')';
    }

    /**
     * Compile a where condition with a sub-select.
     *
     * @param array $where
     */
    protected function whereSub(Builder $query, $where): string
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']) . ' ' . $where['operator'] . " ({$select})";
    }

    /**
     * Compile a where exists clause.
     *
     * @param array $where
     */
    protected function whereExists(Builder $query, $where): string
    {
        return 'exists (' . $this->compileSelect($where['query']) . ')';
    }

    /**
     * Compile a where exists clause.
     *
     * @param array $where
     */
    protected function whereNotExists(Builder $query, $where): string
    {
        return 'not exists (' . $this->compileSelect($where['query']) . ')';
    }

    /**
     * Compile a where row values condition.
     *
     * @param array $where
     */
    protected function whereRowValues(Builder $query, $where): string
    {
        $columns = $this->columnize($where['columns']);

        $values = $this->parameterize($where['values']);

        return '(' . $columns . ') ' . $where['operator'] . ' (' . $values . ')';
    }

    /**
     * Compile a "where JSON contains" clause.
     *
     * @param array $where
     */
    protected function whereJsonContains(Builder $query, $where): string
    {
        $not = $where['not'] ? 'not ' : '';

        return $not . $this->compileJsonContains(
            $where['column'],
            $this->parameter($where['value'])
        );
    }

    /**
     * Compile a "JSON contains" statement into SQL.
     *
     * @param string $column
     * @param string $value
     * @throws RuntimeException
     */
    protected function compileJsonContains($column, $value): string
    {
        throw new RuntimeException('This database engine does not support JSON contains operations.');
    }

    /**
     * Compile a "where JSON length" clause.
     *
     * @param array $where
     */
    protected function whereJsonLength(Builder $query, $where): string
    {
        return $this->compileJsonLength(
            $where['column'],
            $where['operator'],
            $this->parameter($where['value'])
        );
    }

    /**
     * Compile a "where fulltext" clause.
     */
    protected function whereFullText(Builder $query, array $where): string
    {
        throw new RuntimeException('This database engine does not support fulltext search operations.');
    }

    /**
     * Compile a "JSON length" statement into SQL.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @throws RuntimeException
     */
    protected function compileJsonLength($column, $operator, $value): string
    {
        throw new RuntimeException('This database engine does not support JSON length operations.');
    }

    /**
     * Compile the "group by" portions of the query.
     *
     * @param array $groups
     */
    protected function compileGroups(Builder $query, $groups): string
    {
        return 'group by ' . $this->columnize($groups);
    }

    /**
     * Compile the "having" portions of the query.
     *
     * @param array $havings
     */
    protected function compileHavings(Builder $query, $havings): string
    {
        $sql = implode(' ', array_map([$this, 'compileHaving'], $havings));

        return 'having ' . $this->removeLeadingBoolean($sql);
    }

    /**
     * Compile a single having clause.
     */
    protected function compileHaving(array $having): string
    {
        // If the having clause is "raw", we can just return the clause straight away
        // without doing any more processing on it. Otherwise, we will compile the
        // clause into SQL based on the components that make it up from builder.
        if ($having['type'] === 'Raw') {
            return $having['boolean'] . ' ' . $having['sql'];
        }
        if ($having['type'] === 'between') {
            return $this->compileHavingBetween($having);
        }

        return $this->compileBasicHaving($having);
    }

    /**
     * Compile a basic having clause.
     *
     * @param array $having
     */
    protected function compileBasicHaving($having): string
    {
        $column = $this->wrap($having['column']);

        $parameter = $this->parameter($having['value']);

        return $having['boolean'] . ' ' . $column . ' ' . $having['operator'] . ' ' . $parameter;
    }

    /**
     * Compile a "between" having clause.
     *
     * @param array $having
     */
    protected function compileHavingBetween($having): string
    {
        $between = $having['not'] ? 'not between' : 'between';

        $column = $this->wrap($having['column']);

        $min = $this->parameter(head($having['values']));

        $max = $this->parameter(last($having['values']));

        return $having['boolean'] . ' ' . $column . ' ' . $between . ' ' . $min . ' and ' . $max;
    }

    /**
     * Compile the "order by" portions of the query.
     *
     * @param array $orders
     */
    protected function compileOrders(Builder $query, $orders): string
    {
        if (! empty($orders)) {
            return 'order by ' . implode(', ', $this->compileOrdersToArray($query, $orders));
        }

        return '';
    }

    /**
     * Compile the query orders to an array.
     *
     * @param array $orders
     */
    protected function compileOrdersToArray(Builder $query, $orders): array
    {
        return array_map(function ($order) {
            return $order['sql'] ?? $this->wrap($order['column']) . ' ' . $order['direction'];
        }, $orders);
    }

    /**
     * Compile the "limit" portions of the query.
     *
     * @param int $limit
     */
    protected function compileLimit(Builder $query, $limit): string
    {
        return 'limit ' . (int) $limit;
    }

    /**
     * Compile the "offset" portions of the query.
     *
     * @param int $offset
     */
    protected function compileOffset(Builder $query, $offset): string
    {
        return 'offset ' . (int) $offset;
    }

    /**
     * Compile the "union" queries attached to the main query.
     */
    protected function compileUnions(Builder $query): string
    {
        $sql = '';

        foreach ($query->unions as $union) {
            $sql .= $this->compileUnion($union);
        }

        if (! empty($query->unionOrders)) {
            $sql .= ' ' . $this->compileOrders($query, $query->unionOrders);
        }

        if (isset($query->unionLimit)) {
            $sql .= ' ' . $this->compileLimit($query, $query->unionLimit);
        }

        if (isset($query->unionOffset)) {
            $sql .= ' ' . $this->compileOffset($query, $query->unionOffset);
        }

        return ltrim($sql);
    }

    /**
     * Compile a single union statement.
     */
    protected function compileUnion(array $union): string
    {
        $conjunction = $union['all'] ? ' union all ' : ' union ';

        return $conjunction . $union['query']->toSql();
    }

    /**
     * Compile a union aggregate query into SQL.
     */
    protected function compileUnionAggregate(Builder $query): string
    {
        $sql = $this->compileAggregate($query, $query->aggregate);

        $query->aggregate = null;

        return $sql . ' from (' . $this->compileSelect($query) . ') as ' . $this->wrapTable('temp_table');
    }

    /**
     * Compile the lock into SQL.
     *
     * @param bool|string $value
     */
    protected function compileLock(Builder $query, $value): string
    {
        return is_string($value) ? $value : '';
    }

    /**
     * Wrap the given JSON selector.
     *
     * @param string $value
     */
    protected function wrapJsonSelector($value): string
    {
        throw new RuntimeException('This database engine does not support JSON operations.');
    }

    /**
     * Split the given JSON selector into the field and the optional path and wrap them separately.
     *
     * @param string $column
     */
    protected function wrapJsonFieldAndPath($column): array
    {
        $parts = explode('->', $column, 2);

        $field = $this->wrap($parts[0]);

        $path = count($parts) > 1 ? ', ' . $this->wrapJsonPath($parts[1], '->') : '';

        return [$field, $path];
    }

    /**
     * Wrap the given JSON path.
     *
     * @param string $value
     * @param string $delimiter
     */
    protected function wrapJsonPath($value, $delimiter = '->'): string
    {
        return '\'$."' . str_replace($delimiter, '"."', $value) . '"\'';
    }

    /**
     * Determine if the given string is a JSON selector.
     *
     * @param string $value
     */
    protected function isJsonSelector($value): bool
    {
        return Str::contains($value, '->');
    }

    /**
     * Concatenate an array of segments, removing empties.
     *
     * @param array $segments
     */
    protected function concatenate($segments): string
    {
        return implode(' ', array_filter($segments, function ($value) {
            return (string) $value !== '';
        }));
    }

    /**
     * Remove the leading boolean from a statement.
     *
     * @param string $value
     * @return string
     */
    protected function removeLeadingBoolean($value)
    {
        return preg_replace('/and |or /i', '', $value, 1);
    }
}
