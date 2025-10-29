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

namespace Hyperf\Database\Model\Concerns;

use Closure;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\MorphTo;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\Database\Query\Builder as QueryBuilder;
use Hyperf\Database\Query\Expression;
use Hyperf\Stringable\Str;
use Hyperf\Stringable\StrCache;
use RuntimeException;

trait QueriesRelationships
{
    /**
     * Add a relationship count / exists condition to the query.
     *
     * @param Relation|string $relation
     * @param string $operator
     * @param int $count
     * @param string $boolean
     * @return Builder|static
     */
    public function has($relation, $operator = '>=', $count = 1, $boolean = 'and', ?Closure $callback = null)
    {
        if (is_string($relation)) {
            if (str_contains($relation, '.')) {
                return $this->hasNested($relation, $operator, $count, $boolean, $callback);
            }

            $relation = $this->getRelationWithoutConstraints($relation);
        }

        if ($relation instanceof MorphTo) {
            throw new RuntimeException('Please use whereHasMorph() for MorphTo relationships.');
        }

        // If we only need to check for the existence of the relation, then we can optimize
        // the subquery to only run a "where exists" clause instead of this full "count"
        // clause. This will make these queries run much faster compared with a count.
        $method = $this->canUseExistsForExistenceCheck($operator, $count)
            ? 'getRelationExistenceQuery'
            : 'getRelationExistenceCountQuery';

        $hasQuery = $relation->{$method}(
            $relation->getRelated()->newQueryWithoutRelationships(),
            $this
        );

        // Next we will call any given callback as an "anonymous" scope so they can get the
        // proper logical grouping of the where clauses if needed by this Model query
        // builder. Then, we will be ready to finalize and return this query instance.
        if ($callback) {
            $hasQuery->callScope($callback);
        }

        return $this->addHasWhere(
            $hasQuery,
            $relation,
            $operator,
            $count,
            $boolean
        );
    }

    /**
     * Add a relationship count / exists condition to the query with an "or".
     *
     * @param string $relation
     * @param string $operator
     * @param int $count
     * @return Builder|static
     */
    public function orHas($relation, $operator = '>=', $count = 1)
    {
        return $this->has($relation, $operator, $count, 'or');
    }

    /**
     * Add a relationship count / exists condition to the query.
     *
     * @param string $relation
     * @param string $boolean
     * @return Builder|static
     */
    public function doesntHave($relation, $boolean = 'and', ?Closure $callback = null)
    {
        return $this->has($relation, '<', 1, $boolean, $callback);
    }

    /**
     * Add a relationship count / exists condition to the query with an "or".
     *
     * @param string $relation
     * @return Builder|static
     */
    public function orDoesntHave($relation)
    {
        return $this->doesntHave($relation, 'or');
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses.
     *
     * @param string $relation
     * @param string $operator
     * @param int $count
     * @return Builder|static
     */
    public function whereHas($relation, ?Closure $callback = null, $operator = '>=', $count = 1)
    {
        return $this->has($relation, $operator, $count, 'and', $callback);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses.
     *
     * Also load the relationship with same condition.
     */
    public function withWhereHas(string $relation, ?Closure $callback = null, string $operator = '>=', int $count = 1): Builder|static
    {
        return $this->whereHas(Str::before($relation, ':'), $callback, $operator, $count)
            ->with($callback ? [$relation => fn ($query) => $callback($query)] : $relation);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param string $relation
     * @param string $operator
     * @param int $count
     * @return Builder|static
     */
    public function orWhereHas($relation, ?Closure $callback = null, $operator = '>=', $count = 1)
    {
        return $this->has($relation, $operator, $count, 'or', $callback);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses.
     *
     * @param string $relation
     * @return Builder|static
     */
    public function whereDoesntHave($relation, ?Closure $callback = null)
    {
        return $this->doesntHave($relation, 'and', $callback);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param string $relation
     * @return Builder|static
     */
    public function orWhereDoesntHave($relation, ?Closure $callback = null)
    {
        return $this->doesntHave($relation, 'or', $callback);
    }

    /**
     * Add subselect queries to count the relations.
     *
     * @param mixed $relations
     * @return $this
     */
    public function withCount($relations): static
    {
        return $this->withAggregate(is_array($relations) ? $relations : func_get_args(), '*', 'count');
    }

    /**
     * Add subselect queries to include the max of the relation's column.
     *
     * @param array|string $relation
     * @return $this
     */
    public function withMax($relation, string $column): static
    {
        return $this->withAggregate($relation, $column, 'max');
    }

    /**
     * Add subselect queries to include the min of the relation's column.
     *
     * @param array|string $relation
     * @return $this
     */
    public function withMin($relation, string $column): static
    {
        return $this->withAggregate($relation, $column, 'min');
    }

    /**
     * Add subselect queries to include the sum of the relation's column.
     *
     * @param array|string $relation
     * @return $this
     */
    public function withSum($relation, string $column): static
    {
        return $this->withAggregate($relation, $column, 'sum');
    }

    /**
     * Add subselect queries to include the average of the relation's column.
     *
     * @param array|string $relation
     * @return $this
     */
    public function withAvg($relation, string $column): static
    {
        return $this->withAggregate($relation, $column, 'avg');
    }

    /**
     * Add subselect queries to include an aggregate value for a relationship.
     *
     * @param mixed $relations
     * @param string $function
     */
    public function withAggregate($relations, string $column, $function = null): static
    {
        if (empty($relations)) {
            return $this;
        }

        if (is_null($this->query->columns)) {
            $this->query->select([$this->query->from . '.*']);
        }

        $relations = is_array($relations) ? $relations : [$relations];

        foreach ($this->parseWithRelations($relations) as $name => $constraints) {
            // First we will determine if the name has been aliased using an "as" clause on the name
            // and if it has we will extract the actual relationship name and the desired name of
            // the resulting column. This allows multiple aggregates on the same relationships.
            $segments = explode(' ', $name);

            unset($alias);

            if (count($segments) === 3 && Str::lower($segments[1]) === 'as') {
                [$name, $alias] = [$segments[0], $segments[2]];
            }

            $relation = $this->getRelationWithoutConstraints($name);

            if ($function) {
                $hashedColumn = $this->getQuery()->from === $relation->getQuery()->getQuery()->from
                    ? "{$relation->getRelationCountHash(false)}.{$column}"
                    : $column;

                $expression = sprintf('%s(%s)', $function, $this->getQuery()->getGrammar()->wrap(
                    $column === '*' ? $column : $relation->getRelated()->qualifyColumn($hashedColumn)
                ));
            } else {
                $expression = $column;
            }

            // Here, we will grab the relationship sub-query and prepare to add it to the main query
            // as a sub-select. First, we'll get the "has" query and use that to get the relation
            // sub-query. We'll format this relationship name and append this column if needed.
            $query = $relation->getRelationExistenceQuery(
                $relation->getRelated()->newQuery(),
                $this,
                new Expression($expression)
            )->setBindings([], 'select');

            $query->callScope($constraints);

            $query = $query->mergeConstraintsFrom($relation->getQuery())->toBase();

            // If the query contains certain elements like orderings / more than one column selected
            // then we will remove those elements from the query so that it will execute properly
            // when given to the database. Otherwise, we may receive SQL errors or poor syntax.
            $query->orders = null;
            $query->setBindings([], 'order');

            if (count($query->columns) > 1) {
                $query->columns = [$query->columns[0]];
                $query->bindings['select'] = [];
            }

            // Finally, we will make the proper column alias to the query and run this sub-select on
            // the query builder. Then, we will return the builder instance back to the developer
            // for further constraint chaining that needs to take place on the query as needed.
            $alias = $alias ?? StrCache::snake(
                preg_replace('/[^[:alnum:][:space:]_]/u', '', "{$name} {$function} {$column}")
            );

            $this->selectSub(
                $function ? $query : $query->limit(1),
                $alias
            );
        }

        return $this;
    }

    /**
     * Merge the where constraints from another query to the current query.
     *
     * @return Builder|static
     */
    public function mergeConstraintsFrom(Builder $from)
    {
        $whereBindings = $from->getQuery()->getRawBindings()['where'] ?? [];

        // Here we have some other query that we want to merge the where constraints from. We will
        // copy over any where constraints on the query as well as remove any global scopes the
        // query might have removed. Then we will return ourselves with the finished merging.
        return $this->withoutGlobalScopes(
            $from->removedScopes()
        )->mergeWheres(
            $from->getQuery()->wheres,
            $whereBindings
        );
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses.
     *
     * @param string $relation
     * @param array|string $types
     * @param string $operator
     * @param int $count
     * @return $this
     */
    public function whereHasMorph($relation, $types, ?Closure $callback = null, $operator = '>=', $count = 1)
    {
        return $this->hasMorph($relation, $types, $operator, $count, 'and', $callback);
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param array|string $types
     * @return $this
     */
    public function orWhereHasMorph(string $relation, $types, ?Closure $callback = null, string $operator = '>=', int $count = 1)
    {
        return $this->hasMorph($relation, $types, $operator, $count, 'or', $callback);
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses.
     *
     * @param array|string $types
     * @return $this
     */
    public function whereDoesntHaveMorph(string $relation, $types, ?Closure $callback = null)
    {
        return $this->doesntHaveMorph($relation, $types, 'and', $callback);
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param array|string $types
     * @return $this
     */
    public function orWhereDoesntHaveMorph(string $relation, $types, ?Closure $callback = null)
    {
        return $this->doesntHaveMorph($relation, $types, 'or', $callback);
    }

    /**
     * Add a basic where clause to a relationship query.
     */
    public function whereRelation(string $relation, array|Closure|Expression|string $column, mixed $operator = null, mixed $value = null): Builder|static
    {
        return $this->whereHas($relation, function ($query) use ($column, $operator, $value) {
            if ($column instanceof Closure) {
                $column($query);
            } else {
                $query->where($column, $operator, $value);
            }
        });
    }

    /**
     * Add an "or where" clause to a relationship query.
     */
    public function orWhereRelation(string $relation, array|Closure|Expression|string $column, mixed $operator = null, mixed $value = null): Builder|static
    {
        return $this->orWhereHas($relation, function ($query) use ($column, $operator, $value) {
            if ($column instanceof Closure) {
                $column($query);
            } else {
                $query->where($column, $operator, $value);
            }
        });
    }

    /**
     * Add a polymorphic relationship condition to the query with a where clause.
     */
    public function whereMorphRelation(MorphTo|string $relation, array|string $types, array|Closure|Expression|string $column, mixed $operator = null, mixed $value = null): Builder|static
    {
        return $this->whereHasMorph($relation, $types, function ($query) use ($column, $operator, $value) {
            $query->where($column, $operator, $value);
        });
    }

    /**
     * Add a polymorphic relationship condition to the query with an "or where" clause.
     */
    public function orWhereMorphRelation(MorphTo|string $relation, array|string $types, array|Closure|Expression|string $column, mixed $operator = null, mixed $value = null): Builder|static
    {
        return $this->orWhereHasMorph($relation, $types, function ($query) use ($column, $operator, $value) {
            $query->where($column, $operator, $value);
        });
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query.
     *
     * @param string $relation
     * @param array|string $types
     * @param string $operator
     * @param int $count
     * @param string $boolean
     * @return $this
     */
    public function hasMorph($relation, $types, $operator = '>=', $count = 1, $boolean = 'and', ?Closure $callback = null)
    {
        $relation = $this->getRelationWithoutConstraints($relation);

        $types = (array) $types;

        if ($types === ['*']) {
            $types = $this->model->newModelQuery()->distinct()->pluck($relation->getMorphType())->filter()->all();

            foreach ($types as &$type) {
                $type = Relation::getMorphedModel($type) ?? $type;
            }
        }

        return $this->where(function ($query) use ($relation, $callback, $operator, $count, $types) {
            foreach ($types as $type) {
                $query->orWhere(function ($query) use ($relation, $callback, $operator, $count, $type) {
                    $belongsTo = $this->getBelongsToRelation($relation, $type);

                    if ($callback) {
                        $callback = function ($query) use ($callback, $type) {
                            return $callback($query, $type);
                        };
                    }

                    $query->where($relation->getMorphType(), '=', (new $type())->getMorphClass())
                        ->whereHas($belongsTo, $callback, $operator, $count);
                });
            }
        }, null, null, $boolean);
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with an "or".
     */
    public function orHasMorph(MorphTo|string $relation, array|string $types, string $operator = '>=', int $count = 1): Builder|static
    {
        return $this->hasMorph($relation, $types, $operator, $count, 'or');
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query.
     *
     * @param array|string $types
     * @param string $boolean
     * @return $this
     */
    public function doesntHaveMorph(string $relation, $types, $boolean = 'and', ?Closure $callback = null)
    {
        return $this->hasMorph($relation, $types, '<', 1, $boolean, $callback);
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with an "or".
     */
    public function orDoesntHaveMorph(MorphTo|string $relation, array|string $types): Builder|static
    {
        return $this->doesntHaveMorph($relation, $types, 'or');
    }

    /**
     * Add nested relationship count / exists conditions to the query.
     *
     * Sets up recursive call to whereHas until we finish the nested relation.
     *
     * @param string $relations
     * @param string $operator
     * @param int $count
     * @param string $boolean
     * @param null|Closure $callback
     * @return Builder|static
     */
    protected function hasNested($relations, $operator = '>=', $count = 1, $boolean = 'and', $callback = null)
    {
        $relations = explode('.', $relations);

        $doesntHave = $operator === '<' && $count === 1;

        if ($doesntHave) {
            $operator = '>=';
            $count = 1;
        }

        $closure = function ($q) use (&$closure, &$relations, $operator, $count, $callback) {
            // In order to nest "has", we need to add count relation constraints on the
            // callback Closure. We'll do this by simply passing the Closure its own
            // reference to itself so it calls itself recursively on each segment.
            count($relations) > 1
                ? $q->whereHas(array_shift($relations), $closure)
                : $q->has(array_shift($relations), $operator, $count, 'and', $callback);
        };

        return $this->has(array_shift($relations), $doesntHave ? '<' : '>=', 1, $boolean, $closure);
    }

    /**
     * Add the "has" condition where clause to the query.
     *
     * @param string $operator
     * @param int $count
     * @param string $boolean
     * @return Builder|static
     */
    protected function addHasWhere(Builder $hasQuery, Relation $relation, $operator, $count, $boolean)
    {
        $hasQuery->mergeConstraintsFrom($relation->getQuery());

        return $this->canUseExistsForExistenceCheck($operator, $count)
            ? $this->addWhereExistsQuery($hasQuery->toBase(), $boolean, $operator === '<' && $count === 1)
            : $this->addWhereCountQuery($hasQuery->toBase(), $operator, $count, $boolean);
    }

    /**
     * Add a sub-query count clause to this query.
     *
     * @param string $operator
     * @param int $count
     * @param string $boolean
     * @return $this
     */
    protected function addWhereCountQuery(QueryBuilder $query, $operator = '>=', $count = 1, $boolean = 'and')
    {
        $this->query->addBinding($query->getBindings(), 'where');

        return $this->where(
            new Expression('(' . $query->toSql() . ')'),
            $operator,
            is_numeric($count) ? new Expression($count) : $count,
            $boolean
        );
    }

    /**
     * Get the "has relation" base query instance.
     *
     * @param string $relation
     * @return Relation
     */
    protected function getRelationWithoutConstraints($relation)
    {
        return Relation::noConstraints(function () use ($relation) {
            return $this->getModel()->{$relation}();
        });
    }

    /**
     * Check if we can run an "exists" query to optimize performance.
     *
     * @param string $operator
     * @param int $count
     * @return bool
     */
    protected function canUseExistsForExistenceCheck($operator, $count)
    {
        return ($operator === '>=' || $operator === '<') && $count === 1;
    }

    /**
     * Get the BelongsTo relationship for a single polymorphic type.
     *
     * @param string $type
     * @return mixed
     */
    protected function getBelongsToRelation(MorphTo $relation, $type)
    {
        $belongsTo = Relation::noConstraints(function () use ($relation, $type) {
            return $this->model->belongsTo(
                $type,
                $relation->getForeignKeyName(),
                $relation->getOwnerKeyName()
            );
        });

        $belongsTo->getQuery()->mergeConstraintsFrom($relation->getQuery());

        return $belongsTo;
    }
}
