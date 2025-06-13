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

namespace Hyperf\Database\Query;

use BackedEnum;
use BadMethodCallException;
use Closure;
use DateTimeInterface;
use Generator;
use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Contract\PaginatorInterface;
use Hyperf\Database\Concerns\BuildsQueries;
use Hyperf\Database\Concerns\ExplainsQueries;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Exception\InvalidBindingException;
use Hyperf\Database\Model\Builder as ModelBuilder;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\Database\Query\Grammars\Grammar;
use Hyperf\Database\Query\Processors\Processor;
use Hyperf\Macroable\Macroable;
use Hyperf\Paginator\Contract\CursorPaginator as CursorPaginatorContract;
use Hyperf\Paginator\Cursor;
use Hyperf\Paginator\Paginator;
use Hyperf\Stringable\Str;
use Hyperf\Stringable\StrCache;
use Hyperf\Support\Traits\ForwardsCalls;
use InvalidArgumentException;
use RuntimeException;

use function Hyperf\Collection\collect;
use function Hyperf\Collection\last;
use function Hyperf\Tappable\tap;

class Builder
{
    use BuildsQueries, ExplainsQueries, ForwardsCalls, Macroable {
        __call as macroCall;
    }

    /**
     * The database connection instance.
     *
     * @var ConnectionInterface
     */
    public $connection;

    /**
     * The database query grammar instance.
     *
     * @var Grammar
     */
    public $grammar;

    /**
     * The database query post processor instance.
     *
     * @var Processor
     */
    public $processor;

    /**
     * The current query value bindings.
     *
     * @var array
     */
    public $bindings
        = [
            'select' => [],
            'from' => [],
            'join' => [],
            'where' => [],
            'having' => [],
            'order' => [],
            'union' => [],
        ];

    /**
     * An aggregate function and column to be run.
     *
     * @var array
     */
    public $aggregate;

    /**
     * The columns that should be returned.
     *
     * @var array
     */
    public $columns;

    /**
     * Indicates if the query returns distinct results.
     *
     * @var bool
     */
    public $distinct = false;

    /**
     * The table which the query is targeting.
     *
     * @var string
     */
    public $from;

    /**
     * The index hint for the query.
     * @var IndexHint
     */
    public $indexHint;

    /**
     * The table joins for the query.
     *
     * @var array
     */
    public $joins;

    /**
     * The where constraints for the query.
     *
     * @var array
     */
    public $wheres = [];

    /**
     * The groupings for the query.
     *
     * @var array
     */
    public $groups;

    /**
     * The having constraints for the query.
     *
     * @var array
     */
    public $havings;

    /**
     * The orderings for the query.
     *
     * @var array
     */
    public $orders;

    /**
     * The maximum number of records to return.
     *
     * @var int
     */
    public $limit;

    /**
     * The number of records to skip.
     *
     * @var int
     */
    public $offset;

    /**
     * The query union statements.
     *
     * @var array
     */
    public $unions;

    /**
     * The maximum number of union records to return.
     *
     * @var int
     */
    public $unionLimit;

    /**
     * The number of union records to skip.
     *
     * @var int
     */
    public $unionOffset;

    /**
     * The orderings for the union query.
     *
     * @var array
     */
    public $unionOrders;

    /**
     * Indicates whether row locking is being used.
     *
     * @var bool|string
     */
    public $lock;

    /**
     * All of the available clause operators.
     *
     * @var array
     */
    public $operators
        = [
            '=',
            '<',
            '>',
            '<=',
            '>=',
            '<>',
            '!=',
            '<=>',
            'like',
            'like binary',
            'not like',
            'ilike',
            '&',
            '|',
            '^',
            '<<',
            '>>',
            'rlike',
            'regexp',
            'not regexp',
            '~',
            '~*',
            '!~',
            '!~*',
            'similar to',
            'not similar to',
            'not ilike',
            '~~*',
            '!~~*',
        ];

    /**
     * Whether use write pdo for select.
     *
     * @var bool
     */
    public $useWritePdo = false;

    /**
     * Create a new query builder instance.
     */
    public function __construct(
        ConnectionInterface $connection,
        ?Grammar $grammar = null,
        ?Processor $processor = null
    ) {
        $this->connection = $connection;
        $this->grammar = $grammar ?: $connection->getQueryGrammar();
        $this->processor = $processor ?: $connection->getPostProcessor();
    }

    /**
     * Handle dynamic method calls into the method.
     *
     * @param string $method
     * @param array $parameters
     * @throws BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }
        if (Str::startsWith($method, 'where')) {
            return $this->dynamicWhere($method, $parameters);
        }

        static::throwBadMethodCallException($method);
    }

    /**
     * Set the columns to be selected.
     *
     * @param array|mixed $columns
     * @return $this
     */
    public function select($columns = ['*'])
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();

        return $this;
    }

    /**
     * Add a subselect expression to the query.
     *
     * @param Builder|Closure|string $query
     * @param string $as
     * @return Builder|static
     * @throws InvalidArgumentException
     */
    public function selectSub($query, $as)
    {
        [$query, $bindings] = $this->createSub($query);

        return $this->selectRaw('(' . $query . ') as ' . $this->grammar->wrap($as), $bindings);
    }

    /**
     * Add a new "raw" select expression to the query.
     *
     * @param string $expression
     * @return Builder|static
     */
    public function selectRaw($expression, array $bindings = [])
    {
        $this->addSelect(new Expression($expression));

        if ($bindings) {
            $this->addBinding($bindings, 'select');
        }

        return $this;
    }

    /**
     * Makes "from" fetch from a subquery.
     *
     * @param Builder|Closure|string $query
     * @param string $as
     * @return Builder|static
     * @throws InvalidArgumentException
     */
    public function fromSub($query, $as)
    {
        [$query, $bindings] = $this->createSub($query);

        return $this->fromRaw('(' . $query . ') as ' . $this->grammar->wrapTable($as), $bindings);
    }

    /**
     * Add a raw from clause to the query.
     *
     * @param string $expression
     * @param mixed $bindings
     * @return Builder|static
     */
    public function fromRaw($expression, $bindings = [])
    {
        $this->from = new Expression($expression);

        $this->addBinding($bindings, 'from');

        return $this;
    }

    /**
     * Add a new select column to the query.
     *
     * @param array|mixed $column
     * @return $this
     */
    public function addSelect($column)
    {
        $columns = is_array($column) ? $column : func_get_args();

        foreach ($columns as $as => $column) {
            if (is_string($as) && $this->isQueryable($column)) {
                if (is_null($this->columns)) {
                    $this->select($this->from . '.*');
                }

                $this->selectSub($column, $as);
            } else {
                if (is_array($this->columns) && in_array($column, $this->columns, true)) {
                    continue;
                }

                $this->columns[] = $column;
            }
        }

        return $this;
    }

    /**
     * Force the query to only return distinct results.
     *
     * @return $this
     */
    public function distinct()
    {
        $this->distinct = true;

        return $this;
    }

    /**
     * Set the table which the query is targeting.
     *
     * @param string $table
     * @return $this
     */
    public function from($table)
    {
        $this->from = $table;

        return $this;
    }

    /**
     * Set the force indexes which the query should be used.
     * @deprecated It will be removed in v3.1, please use `forceIndex` instead
     */
    public function forceIndexes(array $forceIndexes): static
    {
        $values = [];
        foreach ($forceIndexes as $forceIndex) {
            $values[] = '`' . str_replace('`', '``', $forceIndex) . '`';
        }

        $this->indexHint = new IndexHint('force', implode(',', $values));

        return $this;
    }

    /**
     * Add an index hint to suggest a query index.
     */
    public function useIndex(string $index): static
    {
        $this->indexHint = new IndexHint('hint', $index);

        return $this;
    }

    /**
     * Add an index hint to force a query index.
     */
    public function forceIndex(string $index): static
    {
        $this->indexHint = new IndexHint('force', $index);

        return $this;
    }

    /**
     * Add an index hint to ignore a query index.
     *
     * @return $this
     */
    public function ignoreIndex(string $index): static
    {
        $this->indexHint = new IndexHint('ignore', $index);

        return $this;
    }

    /**
     * Add a join clause to the query.
     *
     * @param string $table
     * @param Closure|string $first
     * @param null|string $operator
     * @param null|string $second
     * @param string $type
     * @param bool $where
     * @return $this
     */
    public function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        $join = new JoinClause($this, $type, $table);

        // If the first "column" of the join is really a Closure instance the developer
        // is trying to build a join with a complex "on" clause containing more than
        // one condition, so we'll add the join and call a Closure with the query.
        if ($first instanceof Closure) {
            call_user_func($first, $join);

            $this->joins[] = $join;

            $this->addBinding($join->getBindings(), 'join');
        }

        // If the column is simply a string, we can assume the join simply has a basic
        // "on" clause with a single condition. So we will just build the join with
        // this simple join clauses attached to it. There is not a join callback.
        else {
            $method = $where ? 'where' : 'on';

            $this->joins[] = $join->{$method}($first, $operator, $second);

            $this->addBinding($join->getBindings(), 'join');
        }

        return $this;
    }

    /**
     * Add a "join where" clause to the query.
     *
     * @param string $table
     * @param Closure|string $first
     * @param string $operator
     * @param string $second
     * @param string $type
     * @return Builder|static
     */
    public function joinWhere($table, $first, $operator, $second, $type = 'inner')
    {
        return $this->join($table, $first, $operator, $second, $type, true);
    }

    /**
     * Add a subquery join clause to the query.
     *
     * @param Builder|Closure|string $query
     * @param string $as
     * @param Closure|string $first
     * @param null|string $operator
     * @param null|string $second
     * @param string $type
     * @param bool $where
     * @return Builder|static
     * @throws InvalidArgumentException
     */
    public function joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        [$query, $bindings] = $this->createSub($query);

        $expression = '(' . $query . ') as ' . $this->grammar->wrapTable($as);

        $this->addBinding($bindings, 'join');

        return $this->join(new Expression($expression), $first, $operator, $second, $type, $where);
    }

    /**
     * Add a lateral join clause to the query.
     */
    public function joinLateral(Builder|Closure|ModelBuilder|string $query, string $as, string $type = 'inner'): static
    {
        [$query, $bindings] = $this->createSub($query);

        $expression = '(' . $query . ') as ' . $this->grammar->wrapTable($as);

        $this->addBinding($bindings, 'join');

        $this->joins[] = $this->newJoinLateralClause($this, $type, new Expression($expression));

        return $this;
    }

    /**
     * Add a lateral left join to the query.
     */
    public function leftJoinLateral(Builder|Closure|ModelBuilder|string $query, string $as): static
    {
        return $this->joinLateral($query, $as, 'left');
    }

    /**
     * Add a left join to the query.
     *
     * @param string $table
     * @param Closure|string $first
     * @param null|string $operator
     * @param null|string $second
     * @return Builder|static
     */
    public function leftJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'left');
    }

    /**
     * Add a "join where" clause to the query.
     *
     * @param string $table
     * @param Closure|string $first
     * @param string $operator
     * @param string $second
     * @return Builder|static
     */
    public function leftJoinWhere($table, $first, $operator, $second)
    {
        return $this->joinWhere($table, $first, $operator, $second, 'left');
    }

    /**
     * Add a subquery left join to the query.
     *
     * @param Builder|Closure|string $query
     * @param string $as
     * @param Closure|string $first
     * @param null|string $operator
     * @param null|string $second
     * @return Builder|static
     */
    public function leftJoinSub($query, $as, $first, $operator = null, $second = null)
    {
        return $this->joinSub($query, $as, $first, $operator, $second, 'left');
    }

    /**
     * Add a right join to the query.
     *
     * @param string $table
     * @param Closure|string $first
     * @param null|string $operator
     * @param null|string $second
     * @return Builder|static
     */
    public function rightJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'right');
    }

    /**
     * Add a "right join where" clause to the query.
     *
     * @param string $table
     * @param Closure|string $first
     * @param string $operator
     * @param string $second
     * @return Builder|static
     */
    public function rightJoinWhere($table, $first, $operator, $second)
    {
        return $this->joinWhere($table, $first, $operator, $second, 'right');
    }

    /**
     * Add a subquery right join to the query.
     *
     * @param Builder|Closure|string $query
     * @param string $as
     * @param Closure|string $first
     * @param null|string $operator
     * @param null|string $second
     * @return Builder|static
     */
    public function rightJoinSub($query, $as, $first, $operator = null, $second = null)
    {
        return $this->joinSub($query, $as, $first, $operator, $second, 'right');
    }

    /**
     * Add a "cross join" clause to the query.
     *
     * @param string $table
     * @param null|Closure|string $first
     * @param null|string $operator
     * @param null|string $second
     * @return Builder|static
     */
    public function crossJoin($table, $first = null, $operator = null, $second = null)
    {
        if ($first) {
            return $this->join($table, $first, $operator, $second, 'cross');
        }

        $this->joins[] = new JoinClause($this, 'cross', $table);

        return $this;
    }

    /**
     * Merge an array of where clauses and bindings.
     *
     * @param array $wheres
     * @param array $bindings
     */
    public function mergeWheres($wheres, $bindings)
    {
        $this->wheres = array_merge($this->wheres, (array) $wheres);

        $this->bindings['where'] = array_values(array_merge($this->bindings['where'], (array) $bindings));
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param array|Closure|string $column
     * @param string $boolean
     * @param null|mixed $operator
     * @param null|mixed $value
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
        if (is_array($column)) {
            return $this->addArrayOfWheres($column, $boolean);
        }

        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);

        // If the columns is actually a Closure instance, we will assume the developer
        // wants to begin a nested where statement which is wrapped in parenthesis.
        // We'll add that Closure to the query then return back out immediately.
        if ($column instanceof Closure) {
            return $this->whereNested($column, $boolean);
        }

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        // If the value is a Closure, it means the developer is performing an entire
        // sub-select within the query and we will need to compile the sub-select
        // within the where clause to get the appropriate query record results.
        if ($value instanceof Closure) {
            return $this->whereSub($column, $operator, $value, $boolean);
        }

        // If the value is "null", we will just assume the developer wants to add a
        // where null clause to the query. So, we will allow a short-cut here to
        // that method for convenience so the developer doesn't have to check.
        if (is_null($value)) {
            return $this->whereNull($column, $boolean, $operator !== '=');
        }

        $type = 'Basic';

        // If the column is making a JSON reference we'll check to see if the value
        // is a boolean. If it is, we'll add the raw boolean string as an actual
        // value to the query to ensure this is properly handled by the query.
        if (Str::contains((string) $column, '->') && is_bool($value)) {
            $value = new Expression($value ? 'true' : 'false');

            if (is_string($column)) {
                $type = 'JsonBoolean';
            }
        }

        // Now that we are working with just a simple query we can put the elements
        // in our array and add the query binding to our array of bindings that
        // will be bound to each SQL statements when it is finally executed.
        $this->wheres[] = compact('type', 'column', 'operator', 'value', 'boolean');

        if (! $value instanceof Expression) {
            $this->addBinding($this->assertBinding($value, $column), 'where');
        }

        return $this;
    }

    /**
     * Prepare the value and operator for a where clause.
     *
     * @param string $value
     * @param string $operator
     * @param bool $useDefault
     * @return array
     * @throws InvalidArgumentException
     */
    public function prepareValueAndOperator($value, $operator, $useDefault = false)
    {
        if ($useDefault) {
            return [$operator, '='];
        }
        if ($this->invalidOperatorAndValue($operator, $value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        return [$value, $operator];
    }

    /**
     * Add an "or where" clause to the query.
     *
     * @param array|Closure|string $column
     * @param null|mixed $operator
     * @param null|mixed $value
     * @return Builder|static
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);

        return $this->where($column, $operator, $value, 'or');
    }

    /**
     * Add a "where" clause comparing two columns to the query.
     *
     * @param array|string $first
     * @param null|string $operator
     * @param null|string $second
     * @param null|string $boolean
     * @return Builder|static
     */
    public function whereColumn($first, $operator = null, $second = null, $boolean = 'and')
    {
        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
        if (is_array($first)) {
            return $this->addArrayOfWheres($first, $boolean, 'whereColumn');
        }

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            [$second, $operator] = [$operator, '='];
        }

        // Finally, we will add this where clause into this array of clauses that we
        // are building for the query. All of them will be compiled via a grammar
        // once the query is about to be executed and run against the database.
        $type = 'Column';

        $this->wheres[] = compact('type', 'first', 'operator', 'second', 'boolean');

        return $this;
    }

    /**
     * Add an "or where" clause comparing two columns to the query.
     *
     * @param array|string $first
     * @param null|string $operator
     * @param null|string $second
     * @return Builder|static
     */
    public function orWhereColumn($first, $operator = null, $second = null)
    {
        return $this->whereColumn($first, $operator, $second, 'or');
    }

    /**
     * Add a raw where clause to the query.
     *
     * @param string $sql
     * @param string $boolean
     * @param mixed $bindings
     * @return $this
     */
    public function whereRaw($sql, $bindings = [], $boolean = 'and')
    {
        $this->wheres[] = ['type' => 'raw', 'sql' => $sql, 'boolean' => $boolean];

        $this->addBinding((array) $bindings, 'where');

        return $this;
    }

    /**
     * Add a raw or where clause to the query.
     *
     * @param string $sql
     * @param mixed $bindings
     * @return Builder|static
     */
    public function orWhereRaw($sql, $bindings = [])
    {
        return $this->whereRaw($sql, $bindings, 'or');
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param string $column
     * @param string $boolean
     * @param bool $not
     * @param mixed $values
     * @return $this
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotIn' : 'In';

        // If the value is a query builder instance we will assume the developer wants to
        // look for any values that exists within this given query. So we will add the
        // query accordingly so that this query is properly executed when it is run.
        if ($values instanceof self || $values instanceof ModelBuilder || $values instanceof Closure) {
            [$query, $bindings] = $this->createSub($values);

            $values = [new Expression($query)];

            $this->addBinding($bindings, 'where');
        }

        // Next, if the value is Arrayable we need to cast it to its raw array form so we
        // have the underlying array value instead of an Arrayable object which is not
        // able to be added as a binding, etc. We will then add to the wheres array.
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        $this->wheres[] = compact('type', 'column', 'values', 'boolean');

        // Finally we'll add a binding for each values unless that value is an expression
        // in which case we will just skip over it since it will be the query as a raw
        // string and not as a parameterized place-holder to be replaced by the PDO.
        $this->addBinding($this->cleanBindings($values), 'where');

        return $this;
    }

    /**
     * Add an "or where in" clause to the query.
     *
     * @param string $column
     * @param mixed $values
     * @return Builder|static
     */
    public function orWhereIn($column, $values)
    {
        return $this->whereIn($column, $values, 'or');
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param string $column
     * @param string $boolean
     * @param mixed $values
     * @return Builder|static
     */
    public function whereNotIn($column, $values, $boolean = 'and')
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Add an "or where not in" clause to the query.
     *
     * @param string $column
     * @param mixed $values
     * @return Builder|static
     */
    public function orWhereNotIn($column, $values)
    {
        return $this->whereNotIn($column, $values, 'or');
    }

    /**
     * Add a "where in raw" clause for integer values to the query.
     *
     * @param string $column
     * @param array|Arrayable $values
     * @param string $boolean
     * @param bool $not
     * @return $this
     */
    public function whereIntegerInRaw($column, $values, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotInRaw' : 'InRaw';

        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        foreach ($values as &$value) {
            $value = (int) $value;
        }

        $this->wheres[] = compact('type', 'column', 'values', 'boolean');

        return $this;
    }

    /**
     * Add a "where not in raw" clause for integer values to the query.
     *
     * @param string $column
     * @param array|Arrayable $values
     * @param string $boolean
     * @return $this
     */
    public function whereIntegerNotInRaw($column, $values, $boolean = 'and')
    {
        return $this->whereIntegerInRaw($column, $values, $boolean, true);
    }

    /**
     * Add a "where null" clause to the query.
     *
     * @param array|string $columns
     * @param string $boolean
     * @param bool $not
     * @return $this
     */
    public function whereNull($columns, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotNull' : 'Null';

        foreach (Arr::wrap($columns) as $column) {
            $this->wheres[] = compact('type', 'column', 'boolean');
        }

        return $this;
    }

    /**
     * Add an "or where null" clause to the query.
     *
     * @param string $column
     * @return Builder|static
     */
    public function orWhereNull($column)
    {
        return $this->whereNull($column, 'or');
    }

    /**
     * Add a "where not null" clause to the query.
     *
     * @param string $column
     * @param string $boolean
     * @return Builder|static
     */
    public function whereNotNull($column, $boolean = 'and')
    {
        return $this->whereNull($column, $boolean, true);
    }

    /**
     * Add a where between statement to the query.
     *
     * @param string $column
     * @param string $boolean
     * @param bool $not
     * @return $this
     */
    public function whereBetween($column, array $values, $boolean = 'and', $not = false)
    {
        $type = 'between';

        $this->wheres[] = compact('type', 'column', 'values', 'boolean', 'not');

        $this->addBinding($this->cleanBindings($this->assertBinding($values, $column, 2)), 'where');

        return $this;
    }

    /**
     * Add an or where between statement to the query.
     *
     * @param string $column
     * @return Builder|static
     */
    public function orWhereBetween($column, array $values)
    {
        return $this->whereBetween($column, $values, 'or');
    }

    /**
     * Add a where not between statement to the query.
     *
     * @param string $column
     * @param string $boolean
     * @return Builder|static
     */
    public function whereNotBetween($column, array $values, $boolean = 'and')
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }

    /**
     * Add an or where not between statement to the query.
     *
     * @param string $column
     * @return Builder|static
     */
    public function orWhereNotBetween($column, array $values)
    {
        return $this->whereNotBetween($column, $values, 'or');
    }

    /**
     * Add an "or where not null" clause to the query.
     *
     * @param string $column
     * @return Builder|static
     */
    public function orWhereNotNull($column)
    {
        return $this->whereNotNull($column, 'or');
    }

    /**
     * Add a "where date" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param DateTimeInterface|string $value
     * @param string $boolean
     * @return Builder|static
     */
    public function whereDate($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d');
        }

        return $this->addDateBasedWhere('Date', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where date" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param DateTimeInterface|string $value
     * @return Builder|static
     */
    public function orWhereDate($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);

        return $this->whereDate($column, $operator, $value, 'or');
    }

    /**
     * Add a "where time" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param DateTimeInterface|string $value
     * @param string $boolean
     * @return Builder|static
     */
    public function whereTime($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('H:i:s');
        }

        return $this->addDateBasedWhere('Time', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where time" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param DateTimeInterface|string $value
     * @return Builder|static
     */
    public function orWhereTime($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);

        return $this->whereTime($column, $operator, $value, 'or');
    }

    /**
     * Add a "where day" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param DateTimeInterface|string $value
     * @param string $boolean
     * @return Builder|static
     */
    public function whereDay($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('d');
        }

        return $this->addDateBasedWhere('Day', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where day" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param DateTimeInterface|string $value
     * @return Builder|static
     */
    public function orWhereDay($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);

        return $this->addDateBasedWhere('Day', $column, $operator, $value, 'or');
    }

    /**
     * Add a "where month" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param DateTimeInterface|string $value
     * @param string $boolean
     * @return Builder|static
     */
    public function whereMonth($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('m');
        }

        return $this->addDateBasedWhere('Month', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where month" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param DateTimeInterface|string $value
     * @return Builder|static
     */
    public function orWhereMonth($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);

        return $this->addDateBasedWhere('Month', $column, $operator, $value, 'or');
    }

    /**
     * Add a "where year" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param DateTimeInterface|int|string $value
     * @param string $boolean
     * @return Builder|static
     */
    public function whereYear($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y');
        }

        return $this->addDateBasedWhere('Year', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where year" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param DateTimeInterface|int|string $value
     * @return Builder|static
     */
    public function orWhereYear($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);

        return $this->addDateBasedWhere('Year', $column, $operator, $value, 'or');
    }

    /**
     * Add a nested where statement to the query.
     *
     * @param string $boolean
     * @return Builder|static
     */
    public function whereNested(Closure $callback, $boolean = 'and')
    {
        call_user_func($callback, $query = $this->forNestedWhere());

        return $this->addNestedWhereQuery($query, $boolean);
    }

    /**
     * Create a new query instance for nested where condition.
     *
     * @return Builder
     */
    public function forNestedWhere()
    {
        return $this->newQuery()->from($this->from);
    }

    /**
     * Add another query builder as a nested where to the query builder.
     *
     * @param Builder|static $query
     * @param string $boolean
     * @return $this
     */
    public function addNestedWhereQuery($query, $boolean = 'and')
    {
        if (count($query->wheres)) {
            $type = 'Nested';

            $this->wheres[] = compact('type', 'query', 'boolean');

            $this->addBinding($query->getRawBindings()['where'], 'where');
        }

        return $this;
    }

    /**
     * Add an exists clause to the query.
     *
     * @param string $boolean
     * @param bool $not
     * @return $this
     */
    public function whereExists(Closure $callback, $boolean = 'and', $not = false)
    {
        $query = $this->forSubQuery();

        // Similar to the sub-select clause, we will create a new query instance so
        // the developer may cleanly specify the entire exists query and we will
        // compile the whole thing in the grammar and insert it into the SQL.
        call_user_func($callback, $query);

        return $this->addWhereExistsQuery($query, $boolean, $not);
    }

    /**
     * Add an or exists clause to the query.
     *
     * @param bool $not
     * @return Builder|static
     */
    public function orWhereExists(Closure $callback, $not = false)
    {
        return $this->whereExists($callback, 'or', $not);
    }

    /**
     * Add a where not exists clause to the query.
     *
     * @param string $boolean
     * @return Builder|static
     */
    public function whereNotExists(Closure $callback, $boolean = 'and')
    {
        return $this->whereExists($callback, $boolean, true);
    }

    /**
     * Add a where not exists clause to the query.
     *
     * @return Builder|static
     */
    public function orWhereNotExists(Closure $callback)
    {
        return $this->orWhereExists($callback, true);
    }

    /**
     * Add an exists clause to the query.
     *
     * @param string $boolean
     * @param bool $not
     * @return $this
     */
    public function addWhereExistsQuery(self $query, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotExists' : 'Exists';

        $this->wheres[] = compact('type', 'query', 'boolean');

        $this->addBinding($query->getBindings(), 'where');

        return $this;
    }

    /**
     * Adds a where condition using row values.
     *
     * @param array $columns
     * @param string $operator
     * @param array $values
     * @param string $boolean
     * @return $this
     */
    public function whereRowValues($columns, $operator, $values, $boolean = 'and')
    {
        if (count($columns) !== count($values)) {
            throw new InvalidArgumentException('The number of columns must match the number of values');
        }

        $type = 'RowValues';

        $this->wheres[] = compact('type', 'columns', 'operator', 'values', 'boolean');

        $this->addBinding($this->cleanBindings($values));

        return $this;
    }

    /**
     * Adds a or where condition using row values.
     *
     * @param array $columns
     * @param string $operator
     * @param array $values
     * @return $this
     */
    public function orWhereRowValues($columns, $operator, $values)
    {
        return $this->whereRowValues($columns, $operator, $values, 'or');
    }

    /**
     * Add a "where JSON contains" clause to the query.
     *
     * @param string $column
     * @param string $boolean
     * @param bool $not
     * @param mixed $value
     * @return $this
     */
    public function whereJsonContains($column, $value, $boolean = 'and', $not = false)
    {
        $type = 'JsonContains';

        $this->wheres[] = compact('type', 'column', 'value', 'boolean', 'not');

        if (! $value instanceof Expression) {
            $this->addBinding($this->grammar->prepareBindingForJsonContains($value));
        }

        return $this;
    }

    /**
     * Add a "or where JSON contains" clause to the query.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function orWhereJsonContains($column, $value)
    {
        return $this->whereJsonContains($column, $value, 'or');
    }

    /**
     * Add a "where JSON not contains" clause to the query.
     *
     * @param string $column
     * @param string $boolean
     * @param mixed $value
     * @return $this
     */
    public function whereJsonDoesntContain($column, $value, $boolean = 'and')
    {
        return $this->whereJsonContains($column, $value, $boolean, true);
    }

    /**
     * Add a "or where JSON not contains" clause to the query.
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function orWhereJsonDoesntContain($column, $value)
    {
        return $this->whereJsonDoesntContain($column, $value, 'or');
    }

    /**
     * Add a "where JSON overlaps" clause to the query.
     */
    public function whereJsonOverlaps(string $column, mixed $value, string $boolean = 'and', bool $not = false): static
    {
        $type = 'JsonOverlaps';

        $this->wheres[] = compact('type', 'column', 'value', 'boolean', 'not');

        if (! $value instanceof Expression) {
            $this->addBinding($this->grammar->prepareBindingForJsonContains($value));
        }

        return $this;
    }

    /**
     * Add an "or where JSON overlaps" clause to the query.
     */
    public function orWhereJsonOverlaps(string $column, mixed $value): static
    {
        return $this->whereJsonOverlaps($column, $value, 'or');
    }

    /**
     * Add a "where JSON not overlap" clause to the query.
     */
    public function whereJsonDoesntOverlap(string $column, mixed $value, string $boolean = 'and'): static
    {
        return $this->whereJsonOverlaps($column, $value, $boolean, true);
    }

    /**
     * Add an "or where JSON not overlap" clause to the query.
     */
    public function orWhereJsonDoesntOverlap(string $column, mixed $value): static
    {
        return $this->whereJsonDoesntOverlap($column, $value, 'or');
    }

    /**
     * Add an "where Bit Functions and Operators" clause to the query.
     */
    public function whereBit(string $key, mixed $operator = 'and', mixed $value = null, string $boolean = 'and', bool $not = false): static
    {
        $type = $not ? '!=' : '=';
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);
        $operator = match ($operator) {
            '|', 'or' => '|',
            '^', 'xor' => '^',
            default => '&'
        };
        return $this->whereRaw(sprintf('%s %s ? %s ?', $key, $operator, $type), [$value, $value], $boolean);
    }

    /**
     * Add an "where Bit Not Functions and Operators" clause to the query.
     */
    public function whereBitNot(string $key, mixed $operator = 'and', mixed $value = null, string $boolean = 'and'): static
    {
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);
        return $this->whereBit($key, $operator, $value, $boolean, true);
    }

    /**
     * Add an "or where Bit Functions and Operators" clause to the query.
     */
    public function orWhereBit(string $key, mixed $operator = 'and', mixed $value = null, bool $not = false): static
    {
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);
        return $this->whereBit($key, $operator, $value, 'or', $not);
    }

    /**
     * Add an "or where Bit Not Functions and Operators" clause to the query.
     */
    public function orWhereBitNot(string $key, mixed $operator = 'and', mixed $value = null): static
    {
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);
        return $this->orWhereBit($key, $operator, $value, true);
    }

    /**
     * Add an "where Bit Or Functions and Operators" clause to the query.
     */
    public function whereBitOr(string $key, mixed $value = null, bool $not = false): static
    {
        return $this->whereBit($key, 'or', $value, 'and', $not);
    }

    /**
     * Add an "where Bit Or Not Functions and Operators" clause to the query.
     */
    public function whereBitOrNot(string $key, mixed $value = null): static
    {
        return $this->orWhereBit($key, 'or', $value, true);
    }

    /**
     * Add an "or where Bit Or Functions and Operators" clause to the query.
     */
    public function orWhereBitOr(string $key, mixed $value = null, bool $not = false): static
    {
        return $this->orWhereBit($key, 'or', $value, $not);
    }

    /**
     * Add an "or where Bit Or Functions and Operators" clause to the query.
     */
    public function orWhereBitOrNot(string $key, mixed $value = null): static
    {
        return $this->orWhereBitOr($key, $value, true);
    }

    /**
     * Add an "where Bit Xor Functions and Operators" clause to the query.
     */
    public function whereBitXor(string $key, mixed $value = null, bool $not = false): static
    {
        return $this->whereBit($key, 'xor', $value, 'and', $not);
    }

    /**
     * Add an "where Bit Xor Not Functions and Operators" clause to the query.
     */
    public function whereBitXorNot(string $key, mixed $value = null): static
    {
        return $this->whereBitXor($key, $value, true);
    }

    /**
     * Add an "or where Bit Xor Functions and Operators" clause to the query.
     */
    public function orWhereBitXor(string $key, mixed $value = null, bool $not = false): static
    {
        return $this->orWhereBit($key, 'xor', $value, $not);
    }

    /**
     * Add an "or where Bit Xor Not Functions and Operators" clause to the query.
     */
    public function orWhereBitXorNot(string $key, mixed $value = null): static
    {
        return $this->orWhereBitXor($key, $value, true);
    }

    /**
     * Add a "where JSON length" clause to the query.
     *
     * @param string $column
     * @param string $boolean
     * @param null|mixed $value
     * @param mixed $operator
     * @return $this
     */
    public function whereJsonLength($column, $operator, $value = null, $boolean = 'and')
    {
        $type = 'JsonLength';

        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);

        $this->wheres[] = compact('type', 'column', 'operator', 'value', 'boolean');

        if (! $value instanceof Expression) {
            $this->addBinding((int) $this->assertBinding($value, $column));
        }

        return $this;
    }

    /**
     * Add a "or where JSON length" clause to the query.
     *
     * @param string $column
     * @param null|mixed $value
     * @param mixed $operator
     * @return $this
     */
    public function orWhereJsonLength($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);

        return $this->whereJsonLength($column, $operator, $value, 'or');
    }

    /**
     * Handles dynamic "where" clauses to the query.
     *
     * @param string $method
     * @param array $parameters
     * @return $this
     */
    public function dynamicWhere($method, $parameters)
    {
        $finder = substr($method, 5);

        $segments = preg_split('/(And|Or)(?=[A-Z])/', $finder, -1, PREG_SPLIT_DELIM_CAPTURE);

        // The connector variable will determine which connector will be used for the
        // query condition. We will change it as we come across new boolean values
        // in the dynamic method strings, which could contain a number of these.
        $connector = 'and';

        $index = 0;

        foreach ($segments as $segment) {
            // If the segment is not a boolean connector, we can assume it is a column's name
            // and we will add it to the query as a new constraint as a where clause, then
            // we can keep iterating through the dynamic method string's segments again.
            if ($segment !== 'And' && $segment !== 'Or') {
                $this->addDynamic($segment, $connector, $parameters, $index);

                ++$index;
            }

            // Otherwise, we will store the connector so we know how the next where clause we
            // find in the query should be connected to the previous ones, meaning we will
            // have the proper boolean connector to connect the next where clause found.
            else {
                $connector = $segment;
            }
        }
        return $this;
    }

    /**
     * Add a "where fulltext" clause to the query.
     *
     * @param string|string[] $columns
     * @return $this
     */
    public function whereFullText(array|string $columns, string $value, array $options = [], string $boolean = 'and'): static
    {
        $type = 'FullText';

        $columns = (array) $columns;

        $this->wheres[] = compact('type', 'columns', 'value', 'options', 'boolean');

        $this->addBinding($value);

        return $this;
    }

    /**
     * Add a "or where fulltext" clause to the query.
     *
     * @param string|string[] $columns
     * @return $this
     */
    public function orWhereFullText(array|string $columns, string $value, array $options = []): static
    {
        return $this->whereFullText($columns, $value, $options, 'or');
    }

    /**
     * Add a "where" clause to the query for multiple columns with "and" conditions between them.
     *
     * @param string[] $columns
     */
    public function whereAll(array $columns, mixed $operator = null, mixed $value = null, string $boolean = 'and'): static
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value,
            $operator,
            func_num_args() === 2
        );

        $this->whereNested(function ($query) use ($columns, $operator, $value) {
            foreach ($columns as $column) {
                $query->where($column, $operator, $value, 'and');
            }
        }, $boolean);

        return $this;
    }

    /**
     * Add an "or where" clause to the query for multiple columns with "and" conditions between them.
     *
     * @param string[] $columns
     */
    public function orWhereAll(array $columns, mixed $operator = null, mixed $value = null): static
    {
        return $this->whereAll($columns, $operator, $value, 'or');
    }

    /**
     * Add an "where" clause to the query for multiple columns with "or" conditions between them.
     *
     * @param string[] $columns
     */
    public function whereAny(array $columns, mixed $operator = null, mixed $value = null, string $boolean = 'and'): static
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value,
            $operator,
            func_num_args() === 2
        );

        $this->whereNested(function ($query) use ($columns, $operator, $value) {
            foreach ($columns as $column) {
                $query->where($column, $operator, $value, 'or');
            }
        }, $boolean);

        return $this;
    }

    /**
     * Add an "or where" clause to the query for multiple columns with "or" conditions between them.
     *
     * @param string[] $columns
     */
    public function orWhereAny(array $columns, mixed $operator = null, mixed $value = null): static
    {
        return $this->whereAny($columns, $operator, $value, 'or');
    }

    /**
     * Add a "where not" clause to the query for multiple columns where none of the conditions should be true.
     * @param Expression[]|string[] $columns
     */
    public function whereNone(array $columns, mixed $operator = null, mixed $value = null, string $boolean = 'and'): static
    {
        return $this->whereAny($columns, $operator, $value, $boolean . ' not');
    }

    /**
     * Add an "or where not" clause to the query for multiple columns where none of the conditions should be true.
     * @param Expression[]|string[] $columns
     */
    public function orWhereNone(array $columns, mixed $operator = null, mixed $value = null): static
    {
        return $this->whereNone($columns, $operator, $value, 'or');
    }

    /**
     * Add a "group by" clause to the query.
     *
     * @param array|Expression|string ...$groups
     * @return $this
     */
    public function groupBy(...$groups): static
    {
        foreach ($groups as $group) {
            $this->groups = array_merge((array) $this->groups, Arr::wrap($group));
        }

        return $this;
    }

    /**
     * Add a "having" clause to the query.
     *
     * @param string $column
     * @param null|string $operator
     * @param null|string $value
     * @param string $boolean
     * @return $this
     */
    public function having($column, $operator = null, $value = null, $boolean = 'and')
    {
        $type = 'Basic';

        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        $this->havings[] = compact('type', 'column', 'operator', 'value', 'boolean');

        if (! $value instanceof Expression) {
            $this->addBinding($this->assertBinding($value, $column), 'having');
        }

        return $this;
    }

    /**
     * Add a "or having" clause to the query.
     *
     * @param string $column
     * @param null|string $operator
     * @param null|string $value
     * @return Builder|static
     */
    public function orHaving($column, $operator = null, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);

        return $this->having($column, $operator, $value, 'or');
    }

    /**
     * Add a "having between " clause to the query.
     *
     * @param string $column
     * @param string $boolean
     * @param bool $not
     * @return Builder|static
     */
    public function havingBetween($column, array $values, $boolean = 'and', $not = false)
    {
        $type = 'between';

        $this->havings[] = compact('type', 'column', 'values', 'boolean', 'not');

        $this->addBinding($this->cleanBindings($this->assertBinding($values, $column, 2)), 'having');

        return $this;
    }

    /**
     * Add a raw having clause to the query.
     *
     * @param string $sql
     * @param string $boolean
     * @return $this
     */
    public function havingRaw($sql, array $bindings = [], $boolean = 'and')
    {
        $type = 'Raw';

        $this->havings[] = compact('type', 'sql', 'boolean');

        $this->addBinding($bindings, 'having');

        return $this;
    }

    /**
     * Add a raw or having clause to the query.
     *
     * @param string $sql
     * @return Builder|static
     */
    public function orHavingRaw($sql, array $bindings = [])
    {
        return $this->havingRaw($sql, $bindings, 'or');
    }

    /**
     * Add an "order by" clause to the query.
     *
     * @param Closure|Expression|ModelBuilder|static|string $column
     * @return $this
     */
    public function orderBy(mixed $column, string $direction = 'asc'): static
    {
        if ($this->isQueryable($column)) {
            [$query, $bindings] = $this->createSub($column);

            $column = new Expression('(' . $query . ')');

            $this->addBinding($bindings, $this->unions ? 'unionOrder' : 'order');
        }

        $this->{$this->unions ? 'unionOrders' : 'orders'}[] = [
            'column' => $column,
            'direction' => strtolower($direction) === 'asc' ? 'asc' : 'desc',
        ];

        return $this;
    }

    /**
     * Add a descending "order by" clause to the query.
     *
     * @param string $column
     * @return $this
     */
    public function orderByDesc($column)
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     *
     * @param string $column
     * @return Builder|static
     */
    public function latest($column = 'created_at')
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     *
     * @param string $column
     * @return Builder|static
     */
    public function oldest($column = 'created_at')
    {
        return $this->orderBy($column, 'asc');
    }

    /**
     * Put the query's results in random order.
     *
     * @param string $seed
     * @return $this
     */
    public function inRandomOrder($seed = '')
    {
        return $this->orderByRaw($this->grammar->compileRandom($seed));
    }

    /**
     * Add a raw "order by" clause to the query.
     *
     * @param string $sql
     * @param array $bindings
     * @return $this
     */
    public function orderByRaw($sql, $bindings = [])
    {
        $type = 'Raw';

        $this->{$this->unions ? 'unionOrders' : 'orders'}[] = compact('type', 'sql');

        $this->addBinding($bindings, 'order');

        return $this;
    }

    /**
     * Alias to set the "offset" value of the query.
     *
     * @param int $value
     * @return Builder|static
     */
    public function skip($value)
    {
        return $this->offset($value);
    }

    /**
     * Set the "offset" value of the query.
     *
     * @param int $value
     * @return $this
     */
    public function offset($value)
    {
        $property = $this->unions ? 'unionOffset' : 'offset';

        $this->{$property} = max(0, $value);

        return $this;
    }

    /**
     * Alias to set the "limit" value of the query.
     *
     * @param int $value
     * @return Builder|static
     */
    public function take($value)
    {
        return $this->limit($value);
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param int $value
     * @return $this
     */
    public function limit($value)
    {
        $property = $this->unions ? 'unionLimit' : 'limit';

        if ($value >= 0) {
            $this->{$property} = $value;
        }

        return $this;
    }

    /**
     * Set the limit and offset for a given page.
     *
     * @param int $page
     * @param int $perPage
     * @return Builder|static
     */
    public function forPage($page, $perPage = 15)
    {
        return $this->skip(($page - 1) * $perPage)->take($perPage);
    }

    /**
     * Constrain the query to the previous "page" of results before a given ID.
     *
     * @param int $perPage
     * @param null|int $lastId
     * @param string $column
     * @return $this
     */
    public function forPageBeforeId($perPage = 15, $lastId = 0, $column = 'id')
    {
        $this->orders = $this->removeExistingOrdersFor($column);

        if (! is_null($lastId)) {
            $this->where($column, '<', $lastId);
        }

        return $this->orderBy($column, 'desc')->limit($perPage);
    }

    /**
     * Constrain the query to the next "page" of results after a given ID.
     *
     * @param int $perPage
     * @param null|int $lastId
     * @param string $column
     * @return $this
     */
    public function forPageAfterId($perPage = 15, $lastId = 0, $column = 'id')
    {
        $this->orders = $this->removeExistingOrdersFor($column);

        if (! is_null($lastId)) {
            $this->where($column, '>', $lastId);
        }

        return $this->orderBy($column, 'asc')->limit($perPage);
    }

    /**
     * Remove all existing orders and optionally add a new order.
     *
     * @param Closure|Expression|ModelBuilder|static|string $column
     */
    public function reorder(mixed $column = null, string $direction = 'asc'): static
    {
        $this->orders = null;
        $this->unionOrders = null;
        $this->bindings['order'] = [];
        $this->bindings['unionOrder'] = [];

        if ($column) {
            return $this->orderBy($column, $direction);
        }

        return $this;
    }

    /**
     * Add a union statement to the query.
     *
     * @param Builder|Closure $query
     * @param bool $all
     * @return Builder|static
     */
    public function union($query, $all = false)
    {
        if ($query instanceof Closure) {
            call_user_func($query, $query = $this->newQuery());
        }

        $this->unions[] = compact('query', 'all');

        $this->addBinding($query->getBindings(), 'union');

        return $this;
    }

    /**
     * Add a union all statement to the query.
     *
     * @param Builder|Closure $query
     * @return Builder|static
     */
    public function unionAll($query)
    {
        return $this->union($query, true);
    }

    /**
     * Lock the selected rows in the table.
     *
     * @param bool|string $value
     * @return $this
     */
    public function lock($value = true)
    {
        $this->lock = $value;

        if (! is_null($this->lock)) {
            $this->useWritePdo();
        }

        return $this;
    }

    /**
     * Lock the selected rows in the table for updating.
     *
     * @return Builder
     */
    public function lockForUpdate()
    {
        return $this->lock(true);
    }

    /**
     * Share lock the selected rows in the table.
     *
     * @return Builder
     */
    public function sharedLock()
    {
        return $this->lock(false);
    }

    /**
     * Get the SQL representation of the query.
     *
     * @return string
     */
    public function toSql()
    {
        return $this->grammar->compileSelect($this);
    }

    /**
     * Get the raw SQL representation of the query with embedded bindings.
     *
     * @return string
     */
    public function toRawSql()
    {
        $bindings = array_map(fn ($value) => $this->connection->escape($value), $this->connection->prepareBindings($this->getBindings()));

        return $this->grammar->substituteBindingsIntoRawSql(
            $this->toSql(),
            $bindings
        );
    }

    /**
     * Execute a query for a single record by ID.
     *
     * @param mixed $id
     * @param array $columns
     * @return mixed|static
     */
    public function find($id, $columns = ['*'])
    {
        return $this->where('id', '=', $id)->first($columns);
    }

    /**
     * Get a single column's value from the first result of a query.
     *
     * @param string $column
     */
    public function value($column)
    {
        $result = (array) $this->first([$column]);

        return count($result) > 0 ? reset($result) : null;
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param array|string $columns
     */
    public function get($columns = ['*']): Collection
    {
        return collect($this->onceWithColumns(Arr::wrap($columns), function () {
            return $this->processor->processSelect($this, $this->runSelect());
        }));
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param int $perPage
     * @param array $columns
     * @param string $pageName
     * @param null|int $page
     */
    public function simplePaginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null): PaginatorInterface
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $this->skip(($page - 1) * $perPage)->take($perPage + 1);

        return $this->simplePaginator($this->get($columns), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * @param int $perPage
     * @param string[] $columns
     * @param string $pageName
     * @param null|int $page
     */
    public function paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null): LengthAwarePaginatorInterface
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $total = $this->getCountForPagination();
        $results = $total ? $this->forPage($page, $perPage)->get($columns) : collect();

        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Get the count of the total records for the paginator.
     *
     * @param array $columns
     * @return int
     */
    public function getCountForPagination($columns = ['*'])
    {
        $results = $this->runPaginationCountQuery($columns);

        // Once we have run the pagination count query, we will get the resulting count and
        // take into account what type of query it was. When there is a group by we will
        // just return the count of the entire results set since that will be correct.
        if (! isset($results[0])) {
            return 0;
        }
        if (is_object($results[0])) {
            return (int) $results[0]->aggregate;
        }

        return (int) array_change_key_case((array) $results[0])['aggregate'];
    }

    /**
     * Get a generator for the given query.
     *
     * @return Generator
     */
    public function cursor()
    {
        if (is_null($this->columns)) {
            $this->columns = ['*'];
        }

        return $this->connection->cursor($this->toSql(), $this->getBindings(), ! $this->useWritePdo);
    }

    /**
     * Chunk the results of a query by comparing numeric IDs.
     *
     * @param int $count
     * @param string $column
     * @param null|string $alias
     * @return bool
     */
    public function chunkById($count, callable $callback, $column = 'id', $alias = null)
    {
        $alias = $alias ?: $column;

        $lastId = null;

        do {
            $clone = clone $this;

            // We'll execute the query for the given page and get the results. If there are
            // no results we can just break and return from here. When there are results
            // we will call the callback with the current chunk of these results here.
            $results = $clone->forPageAfterId($count, $lastId, $column)->get();

            $countResults = $results->count();

            if ($countResults == 0) {
                break;
            }

            // On each chunk result set, we will pass them to the callback and then let the
            // developer take care of everything within the callback, which allows us to
            // keep the memory low for spinning through large result sets for working.
            if ($callback($results) === false) {
                return false;
            }

            $lastResult = $results->last();
            $lastId = is_array($lastResult) ? $lastResult[$alias] : $lastResult->{$alias};

            if ($lastId === null) {
                throw new RuntimeException("The chunkById operation was aborted because the [{$alias}] column is not present in the query result.");
            }

            unset($results);
        } while ($countResults == $count);

        return true;
    }

    /**
     * Get an array with the values of a given column.
     *
     * @param string $column
     * @param null|string $key
     * @return Collection
     */
    public function pluck($column, $key = null)
    {
        // First, we will need to select the results of the query accounting for the
        // given columns / key. Once we have the results, we will be able to take
        // the results and get the exact data that was requested for the query.
        $queryResult = $this->onceWithColumns(is_null($key) ? [$column] : [$column, $key], function () {
            return $this->processor->processSelect($this, $this->runSelect());
        });

        if (empty($queryResult)) {
            return collect();
        }

        // If the columns are qualified with a table or have an alias, we cannot use
        // those directly in the "pluck" operations since the results from the DB
        // are only keyed by the column itself. We'll strip the table out here.
        $column = $this->stripTableForPluck($column);

        $key = $this->stripTableForPluck($key);

        return is_array($queryResult[0]) ? $this->pluckFromArrayColumn($queryResult, $column, $key) : $this->pluckFromObjectColumn($queryResult, $column, $key);
    }

    /**
     * Concatenate values of a given column as a string.
     *
     * @param string $column
     * @param string $glue
     * @return string
     */
    public function implode($column, $glue = '')
    {
        return $this->pluck($column)->implode($glue);
    }

    /**
     * Determine if any rows exist for the current query.
     *
     * @return bool
     */
    public function exists()
    {
        $results = $this->connection->select($this->grammar->compileExists($this), $this->getBindings(), ! $this->useWritePdo);

        // If the results has rows, we will get the row and see if the exists column is a
        // boolean true. If there is no results for this query we will return false as
        // there are no rows for this query at all and we can return that info here.
        if (isset($results[0])) {
            $results = (array) $results[0];

            return (bool) $results['exists'];
        }

        return false;
    }

    /**
     * Determine if no rows exist for the current query.
     *
     * @return bool
     */
    public function doesntExist()
    {
        return ! $this->exists();
    }

    /**
     * Execute the given callback if no rows exist for the current query.
     */
    public function existsOr(Closure $callback): mixed
    {
        return $this->exists() ? true : $callback();
    }

    /**
     * Execute the given callback if rows exist for the current query.
     */
    public function doesntExistOr(Closure $callback): mixed
    {
        return $this->doesntExist() ? true : $callback();
    }

    /**
     * Retrieve the "count" result of the query.
     *
     * @param string $columns
     * @return int
     */
    public function count($columns = '*')
    {
        return (int) $this->aggregate(__FUNCTION__, Arr::wrap($columns));
    }

    /**
     * Retrieve the minimum value of a given column.
     *
     * @param string $column
     */
    public function min($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Retrieve the maximum value of a given column.
     *
     * @param string $column
     */
    public function max($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Retrieve the sum of the values of a given column.
     *
     * @param string $column
     */
    public function sum($column)
    {
        $result = $this->aggregate(__FUNCTION__, [$column]);

        return $result ?: 0;
    }

    /**
     * Retrieve the average of the values of a given column.
     *
     * @param string $column
     */
    public function avg($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Alias for the "avg" method.
     *
     * @param string $column
     */
    public function average($column)
    {
        return $this->avg($column);
    }

    /**
     * Execute an aggregate function on the database.
     *
     * @param string $function
     * @param array $columns
     */
    public function aggregate($function, $columns = ['*'])
    {
        $results = $this->cloneWithout($this->unions ? [] : ['columns'])
            ->cloneWithoutBindings($this->unions ? [] : ['select'])
            ->setAggregate($function, $columns)
            ->get($columns);

        if (! $results->isEmpty()) {
            return array_change_key_case((array) $results[0])['aggregate'];
        }
    }

    /**
     * Execute a numeric aggregate function on the database.
     *
     * @param string $function
     * @param array $columns
     * @return float|int
     */
    public function numericAggregate($function, $columns = ['*'])
    {
        $result = $this->aggregate($function, $columns);

        // If there is no result, we can obviously just return 0 here. Next, we will check
        // if the result is an integer or float. If it is already one of these two data
        // types we can just return the result as-is, otherwise we will convert this.
        if (! $result) {
            return 0;
        }

        if (is_int($result) || is_float($result)) {
            return $result;
        }

        // If the result doesn't contain a decimal place, we will assume it is an int then
        // cast it to one. When it does we will cast it to a float since it needs to be
        // cast to the expected data type for the developers out of pure convenience.
        return strpos((string) $result, '.') === false ? (int) $result : (float) $result;
    }

    /**
     * Insert a new record into the database.
     *
     * @return bool
     */
    public function insert(array $values)
    {
        // Since every insert gets treated like a batch insert, we will make sure the
        // bindings are structured in a way that is convenient when building these
        // inserts statements by verifying these elements are actually an array.
        if (empty($values)) {
            return true;
        }

        if (! is_array(reset($values))) {
            $values = [$values];
        }

        // Here, we will sort the insert keys for every record so that each insert is
        // in the same order for the record. We need to make sure this is the case
        // so there are not any errors or problems when inserting these records.
        else {
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = $value;
            }
        }

        // Finally, we will run this query against the database connection and return
        // the results. We will need to also flatten these bindings before running
        // the query so they are all in one huge, flattened array for execution.
        return $this->connection->insert($this->grammar->compileInsert($this, $values), $this->cleanBindings(Arr::flatten($values, 1)));
    }

    /**
     * Insert a new record and get the value of the primary key.
     *
     * @param null|string $sequence
     * @return int
     */
    public function insertGetId(array $values, $sequence = null)
    {
        $sql = $this->grammar->compileInsertGetId($this, $values, $sequence);

        $values = $this->cleanBindings($values);

        return $this->processor->processInsertGetId($this, $sql, $values, $sequence);
    }

    /**
     * Insert new records into the table using a subquery.
     *
     * @param Builder|Closure|string $query
     * @return bool
     */
    public function insertUsing(array $columns, $query)
    {
        [$sql, $bindings] = $this->createSub($query);

        return $this->connection->insert($this->grammar->compileInsertUsing($this, $columns, $sql), $this->cleanBindings($bindings));
    }

    /**
     * Insert new records into the table using a subquery while ignoring errors.
     */
    public function insertOrIgnoreUsing(array $columns, array|Builder|Closure|ModelBuilder|string $query): int
    {
        [$sql, $bindings] = $this->createSub($query);

        return $this->connection->affectingStatement(
            $this->grammar->compileInsertOrIgnoreUsing($this, $columns, $sql),
            $this->cleanBindings($bindings)
        );
    }

    /**
     * Insert ignore a new record into the database.
     */
    public function insertOrIgnore(array $values): int
    {
        if (empty($values)) {
            return 0;
        }
        if (! is_array(reset($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
                ksort($value);
                $values[$key] = $value;
            }
        }
        return $this->connection->affectingStatement(
            $this->grammar->compileInsertOrIgnore($this, $values),
            $this->cleanBindings(Arr::flatten($values, 1))
        );
    }

    /**
     * Update a record in the database.
     *
     * @return int
     */
    public function update(array $values)
    {
        $sql = $this->grammar->compileUpdate($this, $values);

        return $this->connection->update($sql, $this->cleanBindings($this->grammar->prepareBindingsForUpdate($this->bindings, $values)));
    }

    /**
     * Insert or update a record matching the attributes, and fill it with values.
     *
     * @return bool
     */
    public function updateOrInsert(array $attributes, array $values = [])
    {
        if (! $this->where($attributes)->exists()) {
            return $this->insert(array_merge($attributes, $values));
        }

        if (empty($values)) {
            return true;
        }

        return (bool) $this->take(1)->update($values);
    }

    /**
     * Insert new records or update the existing ones.
     *
     * @param array|string $uniqueBy
     * @param null|array $update
     * @return int
     */
    public function upsert(array $values, $uniqueBy, $update = null)
    {
        if (empty($values)) {
            return 0;
        }
        if ($update === []) {
            return (int) $this->insert($values);
        }

        if (! is_array(reset($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = $value;
            }
        }

        if (is_null($update)) {
            $update = array_keys(reset($values));
        }

        $bindings = $this->cleanBindings(array_merge(
            Arr::flatten($values, 1),
            collect($update)->reject(function ($value, $key) {
                return is_int($key);
            })->all()
        ));

        return $this->connection->affectingStatement(
            $this->grammar->compileUpsert($this, $values, (array) $uniqueBy, $update),
            $bindings
        );
    }

    /**
     * Increment a column's value by a given amount.
     *
     * @param string $column
     * @param float|int $amount
     * @return int
     */
    public function increment(Expression|string $column, mixed $amount = 1, array $extra = [])
    {
        if (! is_numeric($amount)) {
            throw new InvalidArgumentException('Non-numeric value passed to increment method.');
        }

        $wrapped = $this->grammar->wrap($column);

        $columns = array_merge([$column => $this->raw("{$wrapped} + {$amount}")], $extra);

        return $this->update($columns);
    }

    /**
     * Increment the given column's values by the given amounts.
     */
    public function incrementEach(array $columns, array $extra = []): int
    {
        foreach ($columns as $column => $amount) {
            if (! is_numeric($amount)) {
                throw new InvalidArgumentException("Non-numeric value passed as increment amount for column: '{$column}'.");
            }
            if (! is_string($column)) {
                throw new InvalidArgumentException('Non-associative array passed to incrementEach method.');
            }

            $columns[$column] = $this->raw("{$this->grammar->wrap($column)} + {$amount}");
        }

        return $this->update(array_merge($columns, $extra));
    }

    /**
     * Decrement a column's value by a given amount.
     *
     * @param string $column
     * @param float|int $amount
     * @return int
     */
    public function decrement(Expression|string $column, mixed $amount = 1, array $extra = [])
    {
        if (! is_numeric($amount)) {
            throw new InvalidArgumentException('Non-numeric value passed to decrement method.');
        }

        $wrapped = $this->grammar->wrap($column);

        $columns = array_merge([$column => $this->raw("{$wrapped} - {$amount}")], $extra);

        return $this->update($columns);
    }

    /**
     * Decrement the given column's values by the given amounts.
     */
    public function decrementEach(array $columns, array $extra = []): int
    {
        foreach ($columns as $column => $amount) {
            if (! is_numeric($amount)) {
                throw new InvalidArgumentException("Non-numeric value passed as decrement amount for column: '{$column}'.");
            }
            if (! is_string($column)) {
                throw new InvalidArgumentException('Non-associative array passed to decrementEach method.');
            }

            $columns[$column] = $this->raw("{$this->grammar->wrap($column)} - {$amount}");
        }

        return $this->update(array_merge($columns, $extra));
    }

    /**
     * Delete a record from the database.
     *
     * @param null|mixed $id
     * @return int
     */
    public function delete($id = null)
    {
        // If an ID is passed to the method, we will set the where clause to check the
        // ID to let developers to simply and quickly remove a single row from this
        // database without manually specifying the "where" clauses on the query.
        if (! is_null($id)) {
            $this->where($this->from . '.id', '=', $id);
        }

        return $this->connection->delete($this->grammar->compileDelete($this), $this->cleanBindings($this->grammar->prepareBindingsForDelete($this->bindings)));
    }

    /**
     * Run a truncate statement on the table.
     */
    public function truncate()
    {
        foreach ($this->grammar->compileTruncate($this) as $sql => $bindings) {
            $this->connection->statement($sql, $bindings);
        }
    }

    /**
     * Get a paginator only supporting simple next and previous links.
     *
     * This is more efficient on larger data-sets, etc.
     */
    public function cursorPaginate(?int $perPage = 15, array|string $columns = ['*'], string $cursorName = 'cursor', null|Cursor|string $cursor = null): CursorPaginatorContract
    {
        return $this->paginateUsingCursor($perPage, $columns, $cursorName, $cursor);
    }

    /**
     * Get a new instance of the query builder.
     *
     * @return Builder
     */
    public function newQuery()
    {
        return new static($this->connection, $this->grammar, $this->processor);
    }

    /**
     * Get all of the query builder's columns in a text-only array with all expressions evaluated.
     */
    public function getColumns(): array
    {
        if (! is_null($this->columns)) {
            return array_map(function ($column) {
                return $column instanceof Expression ? $this->grammar->getValue($column) : $column;
            }, $this->columns);
        }
        return [];
    }

    /**
     * Create a raw database expression.
     *
     * @param mixed $value
     * @return Expression
     */
    public function raw($value)
    {
        return $this->connection->raw($value);
    }

    /**
     * Get the current query value bindings in a flattened array.
     *
     * @return array
     */
    public function getBindings()
    {
        return Arr::flatten($this->bindings);
    }

    /**
     * Get the raw array of bindings.
     *
     * @return array
     */
    public function getRawBindings()
    {
        return $this->bindings;
    }

    /**
     * Set the bindings on the query builder.
     *
     * @param string $type
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setBindings(array $bindings, $type = 'where')
    {
        if (! array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }

        $this->bindings[$type] = $bindings;

        return $this;
    }

    /**
     * Add a binding to the query.
     *
     * @param string $type
     * @param mixed $value
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addBinding($value, $type = 'where')
    {
        if (! array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }

        if (is_array($value)) {
            $this->bindings[$type] = array_values(array_merge($this->bindings[$type], $this->castBindings($value)));
        } else {
            $this->bindings[$type][] = $this->castBinding($value);
        }

        return $this;
    }

    /**
     * Cast the given binding value.
     */
    public function castBinding(mixed $value)
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return $value;
    }

    /**
     * Cast the given binding value.
     */
    public function castBindings(array $values)
    {
        $result = [];
        foreach ($values as $value) {
            $result[] = $this->castBinding($value);
        }

        return $result;
    }

    /**
     * Merge an array of bindings into our bindings.
     *
     * @return $this
     */
    public function mergeBindings(self $query)
    {
        $this->bindings = array_merge_recursive($this->bindings, $query->bindings);

        return $this;
    }

    /**
     * Get the database connection instance.
     *
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get the database query processor instance.
     *
     * @return Processor
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * Get the query grammar instance.
     *
     * @return Grammar
     */
    public function getGrammar()
    {
        return $this->grammar;
    }

    /**
     * Use the write pdo for query.
     *
     * @return $this
     */
    public function useWritePdo()
    {
        $this->useWritePdo = true;

        return $this;
    }

    /**
     * Clone the query.
     *
     * @return static
     */
    public function clone()
    {
        return clone $this;
    }

    /**
     * Clone the query without the given properties.
     *
     * @return static
     */
    public function cloneWithout(array $properties)
    {
        return tap(clone $this, function ($clone) use ($properties) {
            foreach ($properties as $property) {
                $clone->{$property} = null;
            }
        });
    }

    /**
     * Clone the query without the given bindings.
     *
     * @return static
     */
    public function cloneWithoutBindings(array $except)
    {
        return tap(clone $this, function ($clone) use ($except) {
            foreach ($except as $type) {
                $clone->bindings[$type] = [];
            }
        });
    }

    /**
     * Get the default key name of the table.
     */
    protected function defaultKeyName(): string
    {
        return 'id';
    }

    /**
     * Ensure the proper order by required for cursor pagination.
     */
    protected function ensureOrderForCursorPagination(bool $shouldReverse = false): Collection
    {
        if (empty($this->orders) && empty($this->unionOrders)) {
            $this->enforceOrderBy();
        }

        $reverseDirection = function ($order) {
            if (! isset($order['direction'])) {
                return $order;
            }

            $order['direction'] = $order['direction'] === 'asc' ? 'desc' : 'asc';

            return $order;
        };

        if ($shouldReverse) {
            $this->orders = collect($this->orders)->map($reverseDirection)->toArray();
            $this->unionOrders = collect($this->unionOrders)->map($reverseDirection)->toArray();
        }

        $orders = ! empty($this->unionOrders) ? $this->unionOrders : $this->orders;

        return collect($orders)
            ->filter(fn ($order) => Arr::has($order, 'direction'))
            ->values();
    }

    /**
     * Get the query builder instances that are used in the union of the query.
     */
    protected function getUnionBuilders(): Collection
    {
        return isset($this->unions)
            ? collect($this->unions)->pluck('query')
            : collect();
    }

    /**
     * Get a new join lateral clause.
     *
     * @param string $table
     */
    protected function newJoinLateralClause(self $parentQuery, string $type, Expression|string $table): JoinLateralClause
    {
        return new JoinLateralClause($parentQuery, $type, $table);
    }

    /**
     * Determine if the value is a query builder instance or a Closure.
     *
     * @param mixed $value
     * @return bool
     */
    protected function isQueryable($value)
    {
        return $value instanceof static
            || $value instanceof ModelBuilder
            || $value instanceof Relation
            || $value instanceof Closure;
    }

    /**
     * Creates a subquery and parse it.
     *
     * @param Builder|Closure|string $query
     * @return array
     */
    protected function createSub($query)
    {
        // If the given query is a Closure, we will execute it while passing in a new
        // query instance to the Closure. This will give the developer a chance to
        // format and work with the query before we cast it to a raw SQL string.
        if ($query instanceof Closure) {
            $callback = $query;

            $callback($query = $this->forSubQuery());
        }

        return $this->parseSub($query);
    }

    /**
     * Parse the subquery into SQL and bindings.
     *
     * @param mixed $query
     * @return array
     */
    protected function parseSub($query)
    {
        if ($query instanceof self || $query instanceof ModelBuilder) {
            return [$query->toSql(), $query->getBindings()];
        }
        if (is_string($query)) {
            return [$query, []];
        }
        throw new InvalidArgumentException();
    }

    /**
     * Add an array of where clauses to the query.
     *
     * @param array $column
     * @param string $boolean
     * @param string $method
     * @return $this
     */
    protected function addArrayOfWheres($column, $boolean, $method = 'where')
    {
        return $this->whereNested(function ($query) use ($column, $method, $boolean) {
            foreach ($column as $key => $value) {
                if (is_numeric($key) && is_array($value)) {
                    $query->{$method}(...array_values($value));
                } else {
                    $query->{$method}($key, '=', $value, $boolean);
                }
            }
        }, $boolean);
    }

    /**
     * Determine if the given operator and value combination is legal.
     * Prevents using Null values with invalid operators.
     *
     * @param string $operator
     * @param mixed $value
     * @return bool
     */
    protected function invalidOperatorAndValue($operator, $value)
    {
        return is_null($value) && in_array($operator, $this->operators) && ! in_array($operator, ['=', '<>', '!=']);
    }

    /**
     * Determine if the given operator is supported.
     *
     * @param string $operator
     */
    protected function invalidOperator($operator): bool
    {
        if (! is_string($operator)) {
            return true;
        }

        return ! in_array(strtolower($operator), $this->operators, true) && ! in_array(strtolower($operator), $this->grammar->getOperators(), true);
    }

    /**
     * Add a where in with a sub-select to the query.
     *
     * @param string $column
     * @param string $boolean
     * @param bool $not
     * @return $this
     */
    protected function whereInSub($column, Closure $callback, $boolean, $not)
    {
        $type = $not ? 'NotInSub' : 'InSub';

        // To create the exists sub-select, we will actually create a query and call the
        // provided callback with the query so the developer may set any of the query
        // conditions they want for the in clause, then we'll put it in this array.
        call_user_func($callback, $query = $this->forSubQuery());

        $this->wheres[] = compact('type', 'column', 'query', 'boolean');

        $this->addBinding($query->getBindings(), 'where');

        return $this;
    }

    /**
     * Add an external sub-select to the query.
     *
     * @param string $column
     * @param Builder|static $query
     * @param string $boolean
     * @param bool $not
     * @return $this
     */
    protected function whereInExistingQuery($column, $query, $boolean, $not)
    {
        $type = $not ? 'NotInSub' : 'InSub';

        $this->wheres[] = compact('type', 'column', 'query', 'boolean');

        $this->addBinding($query->getBindings(), 'where');

        return $this;
    }

    /**
     * Add a date based (year, month, day, time) statement to the query.
     *
     * @param string $type
     * @param string $column
     * @param string $operator
     * @param string $boolean
     * @param mixed $value
     * @return $this
     */
    protected function addDateBasedWhere($type, $column, $operator, $value, $boolean = 'and')
    {
        $this->wheres[] = compact('column', 'type', 'boolean', 'operator', 'value');

        if (! $value instanceof Expression) {
            $this->addBinding($this->assertBinding($value, $column), 'where');
        }

        return $this;
    }

    /**
     * Add a full sub-select to the query.
     *
     * @param string $column
     * @param string $operator
     * @param string $boolean
     * @return $this
     */
    protected function whereSub($column, $operator, Closure $callback, $boolean)
    {
        $type = 'Sub';

        // Once we have the query instance we can simply execute it so it can add all
        // of the sub-select's conditions to itself, and then we can cache it off
        // in the array of where clauses for the "main" parent query instance.
        call_user_func($callback, $query = $this->forSubQuery());

        $this->wheres[] = compact('type', 'column', 'operator', 'query', 'boolean');

        $this->addBinding($query->getBindings(), 'where');

        return $this;
    }

    /**
     * Add a single dynamic where clause statement to the query.
     *
     * @param string $segment
     * @param string $connector
     * @param array $parameters
     * @param int $index
     */
    protected function addDynamic($segment, $connector, $parameters, $index)
    {
        // Once we have parsed out the columns and formatted the boolean operators we
        // are ready to add it to this query as a where clause just like any other
        // clause on the query. Then we'll increment the parameter index values.
        $bool = strtolower($connector);

        $this->where(StrCache::snake($segment), '=', $parameters[$index], $bool);
    }

    /**
     * Get an array with all orders with a given column removed.
     *
     * @param string $column
     * @return array
     */
    protected function removeExistingOrdersFor($column)
    {
        return Collection::make($this->orders)->reject(function ($order) use ($column) {
            return isset($order['column']) ? $order['column'] === $column : false;
        })->values()->all();
    }

    /**
     * Run the query as a "select" statement against the connection.
     *
     * @return array
     */
    protected function runSelect()
    {
        return $this->connection->select($this->toSql(), $this->getBindings(), ! $this->useWritePdo);
    }

    /**
     * Clone the existing query instance for usage in a pagination subquery.
     *
     * @return self
     */
    protected function cloneForPaginationCount()
    {
        return $this->cloneWithout(['orders', 'limit', 'offset'])
            ->cloneWithoutBindings(['order']);
    }

    /**
     * Run a pagination count query.
     *
     * @param array $columns
     * @return array
     */
    protected function runPaginationCountQuery($columns = ['*'])
    {
        if ($this->groups || $this->havings) {
            $clone = $this->cloneForPaginationCount();

            if (is_null($clone->columns) && ! empty($this->joins)) {
                $clone->select($this->from . '.*');
            }

            return $this->newQuery()
                ->from(new Expression('(' . $clone->toSql() . ') as ' . $this->grammar->wrap('aggregate_table')))
                ->mergeBindings($clone)
                ->setAggregate('count', $this->withoutSelectAliases($columns))
                ->get()->all();
        }

        $without = $this->unions ? ['orders', 'limit', 'offset'] : ['columns', 'orders', 'limit', 'offset'];

        return $this->cloneWithout($without)
            ->cloneWithoutBindings($this->unions ? ['order'] : ['select', 'order'])
            ->setAggregate('count', $this->withoutSelectAliases($columns))
            ->get()
            ->all();
    }

    /**
     * Remove the column aliases since they will break count queries.
     *
     * @return array
     */
    protected function withoutSelectAliases(array $columns)
    {
        return array_map(function ($column) {
            return is_string($column) && ($aliasPosition = stripos($column, ' as ')) !== false ? substr($column, 0, $aliasPosition) : $column;
        }, $columns);
    }

    /**
     * Throw an exception if the query doesn't have an orderBy clause.
     *
     * @throws RuntimeException
     */
    protected function enforceOrderBy()
    {
        if (empty($this->orders) && empty($this->unionOrders)) {
            throw new RuntimeException('You must specify an orderBy clause when using this function.');
        }
    }

    /**
     * Strip off the table name or alias from a column identifier.
     *
     * @param string $column
     * @return null|string
     */
    protected function stripTableForPluck($column)
    {
        return is_null($column) ? $column : last(preg_split('~\.| ~', $column));
    }

    /**
     * Retrieve column values from rows represented as objects.
     *
     * @param array $queryResult
     * @param string $column
     * @param string $key
     * @return Collection
     */
    protected function pluckFromObjectColumn($queryResult, $column, $key)
    {
        $results = [];

        if (is_null($key)) {
            foreach ($queryResult as $row) {
                $results[] = $row->{$column};
            }
        } else {
            foreach ($queryResult as $row) {
                $results[$row->{$key}] = $row->{$column};
            }
        }

        return collect($results);
    }

    /**
     * Retrieve column values from rows represented as arrays.
     *
     * @param array $queryResult
     * @param string $column
     * @param string $key
     * @return Collection
     */
    protected function pluckFromArrayColumn($queryResult, $column, $key)
    {
        $results = [];

        if (is_null($key)) {
            foreach ($queryResult as $row) {
                $results[] = $row[$column];
            }
        } else {
            foreach ($queryResult as $row) {
                $results[$row[$key]] = $row[$column];
            }
        }

        return collect($results);
    }

    /**
     * Set the aggregate property without running the query.
     *
     * @param string $function
     * @param array $columns
     * @return $this
     */
    protected function setAggregate($function, $columns)
    {
        $this->aggregate = compact('function', 'columns');

        if (empty($this->groups)) {
            $this->orders = null;

            $this->bindings['order'] = [];
        }

        return $this;
    }

    /**
     * Execute the given callback while selecting the given columns.
     * After running the callback, the columns are reset to the original value.
     *
     * @param array $columns
     * @param callable $callback
     */
    protected function onceWithColumns($columns, $callback)
    {
        $original = $this->columns;

        if (is_null($original)) {
            $this->columns = $columns;
        }

        $result = $callback();

        $this->columns = $original;

        return $result;
    }

    /**
     * Create a new query instance for a sub-query.
     *
     * @return Builder
     */
    protected function forSubQuery()
    {
        return $this->newQuery();
    }

    /**
     * Remove all of the expressions from a list of bindings.
     */
    protected function cleanBindings(array $bindings): array
    {
        $result = [];
        foreach ($bindings as $binding) {
            if ($binding instanceof Expression) {
                continue;
            }

            $result[] = $this->castBinding($binding);
        }

        return $result;
    }

    /**
     * Create a new length-aware paginator instance.
     */
    protected function paginator(Collection $items, int $total, int $perPage, int $currentPage, array $options): LengthAwarePaginatorInterface
    {
        $container = ApplicationContext::getContainer();
        if (! method_exists($container, 'make')) {
            throw new RuntimeException('The DI container does not support make() method.');
        }
        return $container->make(LengthAwarePaginatorInterface::class, compact('items', 'total', 'perPage', 'currentPage', 'options'));
    }

    /**
     * Create a new simple paginator instance.
     */
    protected function simplePaginator(Collection $items, int $perPage, int $currentPage, array $options): PaginatorInterface
    {
        $container = ApplicationContext::getContainer();
        if (! method_exists($container, 'make')) {
            throw new RuntimeException('The DI container does not support make() method.');
        }
        return $container->make(PaginatorInterface::class, compact('items', 'perPage', 'currentPage', 'options'));
    }

    /**
     * Assert the value for bindings.
     *
     * @param mixed $value
     * @param string $column
     * @return mixed
     */
    protected function assertBinding($value, $column = '', int $limit = 0)
    {
        if ($limit === 0) {
            if (is_array($value)) {
                throw new InvalidBindingException(sprintf(
                    'The value of column %s is invalid.',
                    (string) $column
                ));
            }

            return $value;
        }

        if (count($value) !== $limit) {
            throw new InvalidBindingException(sprintf(
                'The value length of column %s is not equal with %d.',
                (string) $column,
                $limit
            ));
        }

        return $value;
    }
}
