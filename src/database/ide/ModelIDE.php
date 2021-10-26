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
namespace Hyperf\Database\Model;

class ModelIDE
{
    /**
     * @var Builder
     */
    protected static $instance;

    /**
     * Create and return an un-saved model instance.
     *
     * @param array $attributes
     * @return Model|static
     */
    public static function make($attributes = [])
    {
        return static::$instance->make($attributes);
    }

    /**
     * Register a new global scope.
     *
     * @param string $identifier
     * @param \Closure|Scope $scope
     *
     * @return Builder
     */
    public static function withGlobalScope($identifier, $scope)
    {
        return static::$instance->withGlobalScope($identifier, $scope);
    }

    /**
     * Remove a registered global scope.
     *
     * @param Scope|string $scope
     * @return Builder
     */
    public static function withoutGlobalScope($scope)
    {
        return static::$instance->withoutGlobalScope($scope);
    }

    /**
     * Remove all or passed registered global scopes.
     *
     * @param null|array $scopes
     * @return Builder
     */
    public static function withoutGlobalScopes($scopes = null)
    {
        return static::$instance->withoutGlobalScopes($scopes);
    }

    /**
     * Get an array of global scopes that were removed from the query.
     *
     * @return array
     */
    public static function removedScopes()
    {
        return static::$instance->removedScopes();
    }

    /**
     * Add a where clause on the primary key to the query.
     *
     * @param mixed $id
     * @return Builder
     */
    public static function whereKey($id)
    {
        return static::$instance->whereKey($id);
    }

    /**
     * Add a where clause on the primary key to the query.
     *
     * @param mixed $id
     * @return Builder
     */
    public static function whereKeyNot($id)
    {
        return static::$instance->whereKeyNot($id);
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param array|\Closure|string $column
     * @param mixed $operator
     * @param mixed $value
     * @param string $boolean
     * @return Builder
     */
    public static function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        return static::$instance->where($column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where" clause to the query.
     *
     * @param array|\Closure|string $column
     * @param mixed $operator
     * @param mixed $value
     * @return Builder
     */
    public static function orWhere($column, $operator = null, $value = null)
    {
        return static::$instance->orWhere($column, $operator, $value);
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     *
     * @param string $column
     * @return Builder
     */
    public static function latest($column = null)
    {
        return static::$instance->latest($column);
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     *
     * @param string $column
     * @return Builder
     */
    public static function oldest($column = null)
    {
        return static::$instance->oldest($column);
    }

    /**
     * Create a collection of models from plain arrays.
     *
     * @param array $items
     * @return Collection
     */
    public static function hydrate($items)
    {
        return static::$instance->hydrate($items);
    }

    /**
     * Create a collection of models from a raw query.
     *
     * @param string $query
     * @param array $bindings
     * @return Collection
     */
    public static function fromQuery($query, $bindings = [])
    {
        return static::$instance->fromQuery($query, $bindings);
    }

    /**
     * Find a model by its primary key.
     *
     * @param mixed $id
     * @param array $columns
     * @return null|Collection|Model|static|static[]
     */
    public static function find($id, $columns = [])
    {
        return static::$instance->find($id, $columns);
    }

    /**
     * Find multiple models by their primary keys.
     *
     * @param array|\Hyperf\Utils\Contracts\Arrayable $ids
     * @param array $columns
     * @return Collection
     */
    public static function findMany($ids, $columns = [])
    {
        return static::$instance->findMany($ids, $columns);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param mixed $id
     * @param array $columns
     * @throws ModelNotFoundException
     * @return Collection|Model|static|static[]
     */
    public static function findOrFail($id, $columns = [])
    {
        return static::$instance->findOrFail($id, $columns);
    }

    /**
     * Find a model by its primary key or return fresh model instance.
     *
     * @param mixed $id
     * @param array $columns
     * @return Model|static
     */
    public static function findOrNew($id, $columns = [])
    {
        return static::$instance->findOrNew($id, $columns);
    }

    /**
     * Get the first record matching the attributes or instantiate it.
     *
     * @param array $attributes
     * @param array $values
     * @return Model|static
     */
    public static function firstOrNew($attributes = [], $values = [])
    {
        return static::$instance->firstOrNew($attributes, $values);
    }

    /**
     * Get the first record matching the attributes or create it.
     *
     * @param array $attributes
     * @param array $values
     * @return Model|static
     */
    public static function firstOrCreate($attributes, $values = [])
    {
        return static::$instance->firstOrCreate($attributes, $values);
    }

    /**
     * Create or update a record matching the attributes, and fill it with values.
     *
     * @param array $attributes
     * @param array $values
     * @return Model|static
     */
    public static function updateOrCreate($attributes, $values = [])
    {
        return static::$instance->updateOrCreate($attributes, $values);
    }

    /**
     * Execute the query and get the first result or throw an exception.
     *
     * @param array $columns
     * @throws ModelNotFoundException
     * @return Model|static
     */
    public static function firstOrFail($columns = [])
    {
        return static::$instance->firstOrFail($columns);
    }

    /**
     * Execute the query and get the first result or call a callback.
     *
     * @param array|\Closure $columns
     * @param null|\Closure $callback
     * @return mixed|Model|static
     */
    public static function firstOr($columns = [], $callback = null)
    {
        return static::$instance->firstOr($columns, $callback);
    }

    /**
     * Get a single column's value from the first result of a query.
     *
     * @param string $column
     * @return mixed
     */
    public static function value($column)
    {
        return static::$instance->value($column);
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param array|string $columns
     * @return Collection|static[]
     */
    public static function get($columns = [])
    {
        return static::$instance->get($columns);
    }

    /**
     * Get the hydrated models without eager loading.
     *
     * @param array|string $columns
     * @return Model[]|static[]
     */
    public static function getModels($columns = [])
    {
        return static::$instance->getModels($columns);
    }

    /**
     * Eager load the relationships for the models.
     *
     * @param array $models
     * @return array
     */
    public static function eagerLoadRelations($models)
    {
        return static::$instance->eagerLoadRelations($models);
    }

    /**
     * Get a lazy collection for the given query.
     *
     * @return \Generator
     */
    public static function cursor()
    {
        return static::$instance->cursor();
    }

    /**
     * Get an array with the values of a given column.
     *
     * @param string $column
     * @param null|string $key
     * @return \Hyperf\Utils\Collection
     */
    public static function pluck($column, $key = null)
    {
        return static::$instance->pluck($column, $key);
    }

    /**
     * Paginate the given query.
     *
     * @param null|int $perPage
     * @param array $columns
     * @param string $pageName
     * @param null|int $page
     * @throws \InvalidArgumentException
     * @return \Hyperf\Contract\LengthAwarePaginatorInterface
     */
    public static function paginate($perPage = null, $columns = [], $pageName = 'page', $page = null)
    {
        return static::$instance->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param null|int $perPage
     * @param array $columns
     * @param string $pageName
     * @param null|int $page
     * @return \Hyperf\Contract\PaginatorInterface
     */
    public static function simplePaginate($perPage = null, $columns = [], $pageName = 'page', $page = null)
    {
        return static::$instance->simplePaginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Save a new model and return the instance.
     *
     * @param array $attributes
     * @return $this|Model
     */
    public static function create($attributes = [])
    {
        return static::$instance->create($attributes);
    }

    /**
     * Save a new model and return the instance. Allow mass-assignment.
     *
     * @param array $attributes
     * @return $this|Model
     */
    public static function forceCreate($attributes)
    {
        return static::$instance->forceCreate($attributes);
    }

    /**
     * Register a replacement for the default delete function.
     *
     * @param \Closure $callback
     */
    public static function onDelete($callback)
    {
        static::$instance->onDelete($callback);
    }

    /**
     * Call the given local model scopes.
     *
     * @param array|string $scopes
     * @return mixed|static
     */
    public static function scopes($scopes)
    {
        return static::$instance->scopes($scopes);
    }

    /**
     * Apply the scopes to the Eloquent builder instance and return it.
     *
     * @return Builder
     */
    public static function applyScopes()
    {
        return static::$instance->applyScopes();
    }

    /**
     * Prevent the specified relations from being eager loaded.
     *
     * @param mixed $relations
     * @return Builder
     */
    public static function without($relations)
    {
        return static::$instance->without($relations);
    }

    /**
     * Create a new instance of the model being queried.
     *
     * @param array $attributes
     * @return Model|static
     */
    public static function newModelInstance($attributes = [])
    {
        return static::$instance->newModelInstance($attributes);
    }

    /**
     * Apply query-time casts to the model instance.
     *
     * @param array $casts
     * @return Builder
     */
    public static function withCasts($casts)
    {
        return static::$instance->withCasts($casts);
    }

    /**
     * Get the underlying query builder instance.
     *
     * @return \Hyperf\Database\Query\Builder
     */
    public static function getQuery()
    {
        return static::$instance->getQuery();
    }

    /**
     * Set the underlying query builder instance.
     *
     * @param \Hyperf\Database\Query\Builder $query
     * @return Builder
     */
    public static function setQuery($query)
    {
        return static::$instance->setQuery($query);
    }

    /**
     * Get a base query builder instance.
     *
     * @return \Hyperf\Database\Query\Builder
     */
    public static function toBase()
    {
        return static::$instance->toBase();
    }

    /**
     * Get the relationships being eagerly loaded.
     *
     * @return array
     */
    public static function getEagerLoads()
    {
        return static::$instance->getEagerLoads();
    }

    /**
     * Set the relationships being eagerly loaded.
     *
     * @param array $eagerLoad
     * @return Builder
     */
    public static function setEagerLoads($eagerLoad)
    {
        return static::$instance->setEagerLoads($eagerLoad);
    }

    /**
     * Get the model instance being queried.
     *
     * @return Model|static
     */
    public static function getModel()
    {
        return static::$instance->getModel();
    }

    /**
     * Set a model instance for the model being queried.
     *
     * @param Model $model
     * @return Builder
     */
    public static function setModel($model)
    {
        return static::$instance->setModel($model);
    }

    /**
     * Get the given macro by name.
     *
     * @param string $name
     * @return \Closure
     */
    public static function getMacro($name)
    {
        return static::$instance->getMacro($name);
    }

    /**
     * Checks if a macro is registered.
     *
     * @param string $name
     * @return bool
     */
    public static function hasMacro($name)
    {
        return static::$instance::hasMacro($name);
    }

    /**
     * Chunk the results of the query.
     *
     * @param int $count
     * @param callable $callback
     * @return bool
     */
    public static function chunk($count, $callback)
    {
        return static::$instance->chunk($count, $callback);
    }

    /**
     * Execute a callback over each item while chunking.
     *
     * @param callable $callback
     * @param int $count
     * @return bool
     */
    public static function each($callback, $count = 1000)
    {
        return static::$instance->each($callback, $count);
    }

    /**
     * Chunk the results of a query by comparing IDs.
     *
     * @param int $count
     * @param callable $callback
     * @param null|string $column
     * @param null|string $alias
     * @return bool
     */
    public static function chunkById($count, $callback, $column = null, $alias = null)
    {
        return static::$instance->chunkById($count, $callback, $column, $alias);
    }

    /**
     * Execute the query and get the first result.
     *
     * @param array|string $columns
     * @return null|Model|object|static
     */
    public static function first($columns = [])
    {
        return static::$instance->first($columns);
    }

    /**
     * Apply the callback's query changes if the given "value" is true.
     *
     * @param mixed $value
     * @param callable $callback
     * @param null|callable $default
     * @return $this|mixed
     */
    public static function when($value, $callback, $default = null)
    {
        return static::$instance->when($value, $callback, $default);
    }

    /**
     * Pass the query to a given callback.
     *
     * @param callable $callback
     * @return \Hyperf\Database\Query\Builder
     */
    public static function tap($callback)
    {
        return static::$instance->tap($callback);
    }

    /**
     * Apply the callback's query changes if the given "value" is false.
     *
     * @param mixed $value
     * @param callable $callback
     * @param null|callable $default
     * @return $this|mixed
     */
    public static function unless($value, $callback, $default = null)
    {
        return static::$instance->unless($value, $callback, $default);
    }

    /**
     * Add a relationship count / exists condition to the query.
     *
     * @param \Hyperf\Database\Model\Relations\Relation|string $relation
     * @param string $operator
     * @param int $count
     * @param string $boolean
     * @param null|\Closure $callback
     * @throws \RuntimeException
     * @return Builder|static
     */
    public static function has($relation, $operator = '>=', $count = 1, $boolean = 'and', $callback = null)
    {
        return static::$instance->has($relation, $operator, $count, $boolean, $callback);
    }

    /**
     * Add a relationship count / exists condition to the query with an "or".
     *
     * @param string $relation
     * @param string $operator
     * @param int $count
     * @return Builder|static
     */
    public static function orHas($relation, $operator = '>=', $count = 1)
    {
        return static::$instance->orHas($relation, $operator, $count);
    }

    /**
     * Add a relationship count / exists condition to the query.
     *
     * @param string $relation
     * @param string $boolean
     * @param null|\Closure $callback
     * @return Builder|static
     */
    public static function doesntHave($relation, $boolean = 'and', $callback = null)
    {
        return static::$instance->doesntHave($relation, $boolean, $callback);
    }

    /**
     * Add a relationship count / exists condition to the query with an "or".
     *
     * @param string $relation
     * @return Builder|static
     */
    public static function orDoesntHave($relation)
    {
        return static::$instance->orDoesntHave($relation);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses.
     *
     * @param string $relation
     * @param null|\Closure $callback
     * @param string $operator
     * @param int $count
     * @return Builder|static
     */
    public static function whereHas($relation, $callback = null, $operator = '>=', $count = 1)
    {
        return static::$instance->whereHas($relation, $callback, $operator, $count);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param string $relation
     * @param null|\Closure $callback
     * @param string $operator
     * @param int $count
     * @return Builder|static
     */
    public static function orWhereHas($relation, $callback = null, $operator = '>=', $count = 1)
    {
        return static::$instance->orWhereHas($relation, $callback, $operator, $count);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses.
     *
     * @param string $relation
     * @param null|\Closure $callback
     * @return Builder|static
     */
    public static function whereDoesntHave($relation, $callback = null)
    {
        return static::$instance->whereDoesntHave($relation, $callback);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param string $relation
     * @param null|\Closure $callback
     * @return Builder|static
     */
    public static function orWhereDoesntHave($relation, $callback = null)
    {
        return static::$instance->orWhereDoesntHave($relation, $callback);
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query.
     *
     * @param string $relation
     * @param array|string $types
     * @param string $operator
     * @param int $count
     * @param string $boolean
     * @param null|\Closure $callback
     * @return Builder|static
     */
    public static function hasMorph($relation, $types, $operator = '>=', $count = 1, $boolean = 'and', $callback = null)
    {
        return static::$instance->hasMorph($relation, $types, $operator, $count, $boolean, $callback);
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query.
     *
     * @param string $relation
     * @param array|string $types
     * @param string $boolean
     * @param null|\Closure $callback
     * @return Builder|static
     */
    public static function doesntHaveMorph($relation, $types, $boolean = 'and', $callback = null)
    {
        return static::$instance->doesntHaveMorph($relation, $types, $boolean, $callback);
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses.
     *
     * @param string $relation
     * @param array|string $types
     * @param null|\Closure $callback
     * @param string $operator
     * @param int $count
     * @return Builder|static
     */
    public static function whereHasMorph($relation, $types, $callback = null, $operator = '>=', $count = 1)
    {
        return static::$instance->whereHasMorph($relation, $types, $callback, $operator, $count);
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param string $relation
     * @param array|string $types
     * @param null|\Closure $callback
     * @param string $operator
     * @param int $count
     * @return Builder|static
     */
    public static function orWhereHasMorph($relation, $types, $callback = null, $operator = '>=', $count = 1)
    {
        return static::$instance->orWhereHasMorph($relation, $types, $callback, $operator, $count);
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses.
     *
     * @param string $relation
     * @param array|string $types
     * @param null|\Closure $callback
     * @return Builder|static
     */
    public static function whereDoesntHaveMorph($relation, $types, $callback = null)
    {
        return static::$instance->whereDoesntHaveMorph($relation, $types, $callback);
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param string $relation
     * @param array|string $types
     * @param null|\Closure $callback
     * @return Builder|static
     */
    public static function orWhereDoesntHaveMorph($relation, $types, $callback = null)
    {
        return static::$instance->orWhereDoesntHaveMorph($relation, $types, $callback);
    }

    /**
     * Add subselect queries to count the relations.
     *
     * @param mixed $relations
     * @return Builder
     */
    public static function withCount($relations)
    {
        return static::$instance->withCount($relations);
    }

    /**
     * Merge the where constraints from another query to the current query.
     *
     * @param Builder $from
     * @return Builder|static
     */
    public static function mergeConstraintsFrom($from)
    {
        return static::$instance->mergeConstraintsFrom($from);
    }

    /**
     * Set the columns to be selected.
     *
     * @param array|mixed $columns
     * @return \Hyperf\Database\Query\Builder
     */
    public static function select($columns = [])
    {
        return static::$instance->select($columns);
    }

    /**
     * Add a subselect expression to the query.
     *
     * @param \Closure|\Hyperf\Database\Query\Builder|string $query
     * @param string $as
     * @throws \InvalidArgumentException
     * @return \Hyperf\Database\Query\Builder
     */
    public static function selectSub($query, $as)
    {
        return static::$instance->selectSub($query, $as);
    }

    /**
     * Add a new "raw" select expression to the query.
     *
     * @param string $expression
     * @param array $bindings
     * @return \Hyperf\Database\Query\Builder
     */
    public static function selectRaw($expression, $bindings = [])
    {
        return static::$instance->selectRaw($expression, $bindings);
    }

    /**
     * Makes "from" fetch from a subquery.
     *
     * @param \Closure|\Hyperf\Database\Query\Builder|string $query
     * @param string $as
     * @throws \InvalidArgumentException
     * @return \Hyperf\Database\Query\Builder
     */
    public static function fromSub($query, $as)
    {
        return static::$instance->fromSub($query, $as);
    }

    /**
     * Add a raw from clause to the query.
     *
     * @param string $expression
     * @param mixed $bindings
     * @return \Hyperf\Database\Query\Builder
     */
    public static function fromRaw($expression, $bindings = [])
    {
        return static::$instance->fromRaw($expression, $bindings);
    }

    /**
     * Add a new select column to the query.
     *
     * @param array|mixed $column
     * @return \Hyperf\Database\Query\Builder
     */
    public static function addSelect($column)
    {
        return static::$instance->addSelect($column);
    }

    /**
     * Force the query to only return distinct results.
     *
     * @return \Hyperf\Database\Query\Builder
     */
    public static function distinct()
    {
        return static::$instance->distinct();
    }

    /**
     * Set the table which the query is targeting.
     *
     * @param \Closure|\Hyperf\Database\Query\Builder|string $table
     * @return \Hyperf\Database\Query\Builder
     */
    public static function from($table)
    {
        return static::$instance->from($table);
    }

    /**
     * Add a join clause to the query.
     *
     * @param string $table
     * @param \Closure|string $first
     * @param null|string $operator
     * @param null|string $second
     * @param string $type
     * @param bool $where
     * @return \Hyperf\Database\Query\Builder
     */
    public static function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        return static::$instance->join($table, $first, $operator, $second, $type, $where);
    }

    /**
     * Add a "join where" clause to the query.
     *
     * @param string $table
     * @param \Closure|string $first
     * @param string $operator
     * @param string $second
     * @param string $type
     * @return \Hyperf\Database\Query\Builder
     */
    public static function joinWhere($table, $first, $operator, $second, $type = 'inner')
    {
        return static::$instance->joinWhere($table, $first, $operator, $second, $type);
    }

    /**
     * Add a subquery join clause to the query.
     *
     * @param \Closure|\Hyperf\Database\Query\Builder|string $query
     * @param string $as
     * @param \Closure|string $first
     * @param null|string $operator
     * @param null|string $second
     * @param string $type
     * @param bool $where
     * @throws \InvalidArgumentException
     * @return \Hyperf\Database\Query\Builder
     */
    public static function joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        return static::$instance->joinSub($query, $as, $first, $operator, $second, $type, $where);
    }

    /**
     * Add a left join to the query.
     *
     * @param string $table
     * @param \Closure|string $first
     * @param null|string $operator
     * @param null|string $second
     * @return \Hyperf\Database\Query\Builder
     */
    public static function leftJoin($table, $first, $operator = null, $second = null)
    {
        return static::$instance->leftJoin($table, $first, $operator, $second);
    }

    /**
     * Add a "join where" clause to the query.
     *
     * @param string $table
     * @param \Closure|string $first
     * @param string $operator
     * @param string $second
     * @return \Hyperf\Database\Query\Builder
     */
    public static function leftJoinWhere($table, $first, $operator, $second)
    {
        return static::$instance->leftJoinWhere($table, $first, $operator, $second);
    }

    /**
     * Add a subquery left join to the query.
     *
     * @param \Closure|\Hyperf\Database\Query\Builder|string $query
     * @param string $as
     * @param \Closure|string $first
     * @param null|string $operator
     * @param null|string $second
     * @return \Hyperf\Database\Query\Builder
     */
    public static function leftJoinSub($query, $as, $first, $operator = null, $second = null)
    {
        return static::$instance->leftJoinSub($query, $as, $first, $operator, $second);
    }

    /**
     * Add a right join to the query.
     *
     * @param string $table
     * @param \Closure|string $first
     * @param null|string $operator
     * @param null|string $second
     * @return \Hyperf\Database\Query\Builder
     */
    public static function rightJoin($table, $first, $operator = null, $second = null)
    {
        return static::$instance->rightJoin($table, $first, $operator, $second);
    }

    /**
     * Add a "right join where" clause to the query.
     *
     * @param string $table
     * @param \Closure|string $first
     * @param string $operator
     * @param string $second
     * @return \Hyperf\Database\Query\Builder
     */
    public static function rightJoinWhere($table, $first, $operator, $second)
    {
        return static::$instance->rightJoinWhere($table, $first, $operator, $second);
    }

    /**
     * Add a subquery right join to the query.
     *
     * @param \Closure|\Hyperf\Database\Query\Builder|string $query
     * @param string $as
     * @param \Closure|string $first
     * @param null|string $operator
     * @param null|string $second
     * @return \Hyperf\Database\Query\Builder
     */
    public static function rightJoinSub($query, $as, $first, $operator = null, $second = null)
    {
        return static::$instance->rightJoinSub($query, $as, $first, $operator, $second);
    }

    /**
     * Add a "cross join" clause to the query.
     *
     * @param string $table
     * @param null|\Closure|string $first
     * @param null|string $operator
     * @param null|string $second
     * @return \Hyperf\Database\Query\Builder
     */
    public static function crossJoin($table, $first = null, $operator = null, $second = null)
    {
        return static::$instance->crossJoin($table, $first, $operator, $second);
    }

    /**
     * Merge an array of where clauses and bindings.
     *
     * @param array $wheres
     * @param array $bindings
     */
    public static function mergeWheres($wheres, $bindings)
    {
        static::$instance->mergeWheres($wheres, $bindings);
    }

    /**
     * Prepare the value and operator for a where clause.
     *
     * @param string $value
     * @param string $operator
     * @param bool $useDefault
     * @throws \InvalidArgumentException
     * @return array
     */
    public static function prepareValueAndOperator($value, $operator, $useDefault = false)
    {
        return static::$instance->prepareValueAndOperator($value, $operator, $useDefault);
    }

    /**
     * Add a "where" clause comparing two columns to the query.
     *
     * @param array|string $first
     * @param null|string $operator
     * @param null|string $second
     * @param null|string $boolean
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereColumn($first, $operator = null, $second = null, $boolean = 'and')
    {
        return static::$instance->whereColumn($first, $operator, $second, $boolean);
    }

    /**
     * Add an "or where" clause comparing two columns to the query.
     *
     * @param array|string $first
     * @param null|string $operator
     * @param null|string $second
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereColumn($first, $operator = null, $second = null)
    {
        return static::$instance->orWhereColumn($first, $operator, $second);
    }

    /**
     * Add a raw where clause to the query.
     *
     * @param string $sql
     * @param mixed $bindings
     * @param string $boolean
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereRaw($sql, $bindings = [], $boolean = 'and')
    {
        return static::$instance->whereRaw($sql, $bindings, $boolean);
    }

    /**
     * Add a raw or where clause to the query.
     *
     * @param string $sql
     * @param mixed $bindings
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereRaw($sql, $bindings = [])
    {
        return static::$instance->orWhereRaw($sql, $bindings);
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param string $column
     * @param mixed $values
     * @param string $boolean
     * @param bool $not
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        return static::$instance->whereIn($column, $values, $boolean, $not);
    }

    /**
     * Add an "or where in" clause to the query.
     *
     * @param string $column
     * @param mixed $values
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereIn($column, $values)
    {
        return static::$instance->orWhereIn($column, $values);
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param string $column
     * @param mixed $values
     * @param string $boolean
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereNotIn($column, $values, $boolean = 'and')
    {
        return static::$instance->whereNotIn($column, $values, $boolean);
    }

    /**
     * Add an "or where not in" clause to the query.
     *
     * @param string $column
     * @param mixed $values
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereNotIn($column, $values)
    {
        return static::$instance->orWhereNotIn($column, $values);
    }

    /**
     * Add a "where in raw" clause for integer values to the query.
     *
     * @param string $column
     * @param array|\Hyperf\Utils\Contracts\Arrayable $values
     * @param string $boolean
     * @param bool $not
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereIntegerInRaw($column, $values, $boolean = 'and', $not = false)
    {
        return static::$instance->whereIntegerInRaw($column, $values, $boolean, $not);
    }

    /**
     * Add a "where not in raw" clause for integer values to the query.
     *
     * @param string $column
     * @param array|\Hyperf\Utils\Contracts\Arrayable $values
     * @param string $boolean
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereIntegerNotInRaw($column, $values, $boolean = 'and')
    {
        return static::$instance->whereIntegerNotInRaw($column, $values, $boolean);
    }

    /**
     * Add a "where null" clause to the query.
     *
     * @param array|string $columns
     * @param string $boolean
     * @param bool $not
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereNull($columns, $boolean = 'and', $not = false)
    {
        return static::$instance->whereNull($columns, $boolean, $not);
    }

    /**
     * Add an "or where null" clause to the query.
     *
     * @param string $column
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereNull($column)
    {
        return static::$instance->orWhereNull($column);
    }

    /**
     * Add a "where not null" clause to the query.
     *
     * @param array|string $columns
     * @param string $boolean
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereNotNull($columns, $boolean = 'and')
    {
        return static::$instance->whereNotNull($columns, $boolean);
    }

    /**
     * Add a where between statement to the query.
     *
     * @param string $column
     * @param array $values
     * @param string $boolean
     * @param bool $not
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereBetween($column, $values, $boolean = 'and', $not = false)
    {
        return static::$instance->whereBetween($column, $values, $boolean, $not);
    }

    /**
     * Add an or where between statement to the query.
     *
     * @param string $column
     * @param array $values
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereBetween($column, $values)
    {
        return static::$instance->orWhereBetween($column, $values);
    }

    /**
     * Add a where not between statement to the query.
     *
     * @param string $column
     * @param array $values
     * @param string $boolean
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereNotBetween($column, $values, $boolean = 'and')
    {
        return static::$instance->whereNotBetween($column, $values, $boolean);
    }

    /**
     * Add an or where not between statement to the query.
     *
     * @param string $column
     * @param array $values
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereNotBetween($column, $values)
    {
        return static::$instance->orWhereNotBetween($column, $values);
    }

    /**
     * Add an "or where not null" clause to the query.
     *
     * @param string $column
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereNotNull($column)
    {
        return static::$instance->orWhereNotNull($column);
    }

    /**
     * Add a "where date" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param null|\DateTimeInterface|string $value
     * @param string $boolean
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereDate($column, $operator, $value = null, $boolean = 'and')
    {
        return static::$instance->whereDate($column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where date" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param null|\DateTimeInterface|string $value
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereDate($column, $operator, $value = null)
    {
        return static::$instance->orWhereDate($column, $operator, $value);
    }

    /**
     * Add a "where time" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param null|\DateTimeInterface|string $value
     * @param string $boolean
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereTime($column, $operator, $value = null, $boolean = 'and')
    {
        return static::$instance->whereTime($column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where time" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param null|\DateTimeInterface|string $value
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereTime($column, $operator, $value = null)
    {
        return static::$instance->orWhereTime($column, $operator, $value);
    }

    /**
     * Add a "where day" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param null|\DateTimeInterface|string $value
     * @param string $boolean
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereDay($column, $operator, $value = null, $boolean = 'and')
    {
        return static::$instance->whereDay($column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where day" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param null|\DateTimeInterface|string $value
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereDay($column, $operator, $value = null)
    {
        return static::$instance->orWhereDay($column, $operator, $value);
    }

    /**
     * Add a "where month" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param null|\DateTimeInterface|string $value
     * @param string $boolean
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereMonth($column, $operator, $value = null, $boolean = 'and')
    {
        return static::$instance->whereMonth($column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where month" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param null|\DateTimeInterface|string $value
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereMonth($column, $operator, $value = null)
    {
        return static::$instance->orWhereMonth($column, $operator, $value);
    }

    /**
     * Add a "where year" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param null|\DateTimeInterface|int|string $value
     * @param string $boolean
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereYear($column, $operator, $value = null, $boolean = 'and')
    {
        return static::$instance->whereYear($column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where year" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param null|\DateTimeInterface|int|string $value
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereYear($column, $operator, $value = null)
    {
        return static::$instance->orWhereYear($column, $operator, $value);
    }

    /**
     * Add a nested where statement to the query.
     *
     * @param \Closure $callback
     * @param string $boolean
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereNested($callback, $boolean = 'and')
    {
        return static::$instance->whereNested($callback, $boolean);
    }

    /**
     * Create a new query instance for nested where condition.
     *
     * @return \Hyperf\Database\Query\Builder
     */
    public static function forNestedWhere()
    {
        return static::$instance->forNestedWhere();
    }

    /**
     * Add another query builder as a nested where to the query builder.
     *
     * @param $this $query
     * @param string $boolean
     * @param mixed $query
     * @return \Hyperf\Database\Query\Builder
     */
    public static function addNestedWhereQuery($query, $boolean = 'and')
    {
        return static::$instance->addNestedWhereQuery($query, $boolean);
    }

    /**
     * Add an exists clause to the query.
     *
     * @param \Closure $callback
     * @param string $boolean
     * @param bool $not
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereExists($callback, $boolean = 'and', $not = false)
    {
        return static::$instance->whereExists($callback, $boolean, $not);
    }

    /**
     * Add an or exists clause to the query.
     *
     * @param \Closure $callback
     * @param bool $not
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereExists($callback, $not = false)
    {
        return static::$instance->orWhereExists($callback, $not);
    }

    /**
     * Add a where not exists clause to the query.
     *
     * @param \Closure $callback
     * @param string $boolean
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereNotExists($callback, $boolean = 'and')
    {
        return static::$instance->whereNotExists($callback, $boolean);
    }

    /**
     * Add a where not exists clause to the query.
     *
     * @param \Closure $callback
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereNotExists($callback)
    {
        return static::$instance->orWhereNotExists($callback);
    }

    /**
     * Add an exists clause to the query.
     *
     * @param \Hyperf\Database\Query\Builder $query
     * @param string $boolean
     * @param bool $not
     * @return \Hyperf\Database\Query\Builder
     */
    public static function addWhereExistsQuery($query, $boolean = 'and', $not = false)
    {
        return static::$instance->addWhereExistsQuery($query, $boolean, $not);
    }

    /**
     * Adds a where condition using row values.
     *
     * @param array $columns
     * @param string $operator
     * @param array $values
     * @param string $boolean
     * @throws \InvalidArgumentException
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereRowValues($columns, $operator, $values, $boolean = 'and')
    {
        return static::$instance->whereRowValues($columns, $operator, $values, $boolean);
    }

    /**
     * Adds a or where condition using row values.
     *
     * @param array $columns
     * @param string $operator
     * @param array $values
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereRowValues($columns, $operator, $values)
    {
        return static::$instance->orWhereRowValues($columns, $operator, $values);
    }

    /**
     * Add a "where JSON contains" clause to the query.
     *
     * @param string $column
     * @param mixed $value
     * @param string $boolean
     * @param bool $not
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereJsonContains($column, $value, $boolean = 'and', $not = false)
    {
        return static::$instance->whereJsonContains($column, $value, $boolean, $not);
    }

    /**
     * Add a "or where JSON contains" clause to the query.
     *
     * @param string $column
     * @param mixed $value
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereJsonContains($column, $value)
    {
        return static::$instance->orWhereJsonContains($column, $value);
    }

    /**
     * Add a "where JSON not contains" clause to the query.
     *
     * @param string $column
     * @param mixed $value
     * @param string $boolean
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereJsonDoesntContain($column, $value, $boolean = 'and')
    {
        return static::$instance->whereJsonDoesntContain($column, $value, $boolean);
    }

    /**
     * Add a "or where JSON not contains" clause to the query.
     *
     * @param string $column
     * @param mixed $value
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereJsonDoesntContain($column, $value)
    {
        return static::$instance->orWhereJsonDoesntContain($column, $value);
    }

    /**
     * Add a "where JSON length" clause to the query.
     *
     * @param string $column
     * @param mixed $operator
     * @param mixed $value
     * @param string $boolean
     * @return \Hyperf\Database\Query\Builder
     */
    public static function whereJsonLength($column, $operator, $value = null, $boolean = 'and')
    {
        return static::$instance->whereJsonLength($column, $operator, $value, $boolean);
    }

    /**
     * Add a "or where JSON length" clause to the query.
     *
     * @param string $column
     * @param mixed $operator
     * @param mixed $value
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orWhereJsonLength($column, $operator, $value = null)
    {
        return static::$instance->orWhereJsonLength($column, $operator, $value);
    }

    /**
     * Handles dynamic "where" clauses to the query.
     *
     * @param string $method
     * @param array $parameters
     * @return \Hyperf\Database\Query\Builder
     */
    public static function dynamicWhere($method, $parameters)
    {
        return static::$instance->dynamicWhere($method, $parameters);
    }

    /**
     * Add a "group by" clause to the query.
     *
     * @param array|string $groups
     * @return \Hyperf\Database\Query\Builder
     */
    public static function groupBy(...$groups)
    {
        return static::$instance->groupBy(...$groups);
    }

    /**
     * Add a "having" clause to the query.
     *
     * @param string $column
     * @param null|string $operator
     * @param null|string $value
     * @param string $boolean
     * @return \Hyperf\Database\Query\Builder
     */
    public static function having($column, $operator = null, $value = null, $boolean = 'and')
    {
        return static::$instance->having($column, $operator, $value, $boolean);
    }

    /**
     * Add a "or having" clause to the query.
     *
     * @param string $column
     * @param null|string $operator
     * @param null|string $value
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orHaving($column, $operator = null, $value = null)
    {
        return static::$instance->orHaving($column, $operator, $value);
    }

    /**
     * Add a "having between " clause to the query.
     *
     * @param string $column
     * @param array $values
     * @param string $boolean
     * @param bool $not
     * @return \Hyperf\Database\Query\Builder
     */
    public static function havingBetween($column, $values, $boolean = 'and', $not = false)
    {
        return static::$instance->havingBetween($column, $values, $boolean, $not);
    }

    /**
     * Add a raw having clause to the query.
     *
     * @param string $sql
     * @param array $bindings
     * @param string $boolean
     * @return \Hyperf\Database\Query\Builder
     */
    public static function havingRaw($sql, $bindings = [], $boolean = 'and')
    {
        return static::$instance->havingRaw($sql, $bindings, $boolean);
    }

    /**
     * Add a raw or having clause to the query.
     *
     * @param string $sql
     * @param array $bindings
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orHavingRaw($sql, $bindings = [])
    {
        return static::$instance->orHavingRaw($sql, $bindings);
    }

    /**
     * Add an "order by" clause to the query.
     *
     * @param \Closure|\Hyperf\Database\Query\Builder|string $column
     * @param string $direction
     * @throws \InvalidArgumentException
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orderBy($column, $direction = 'asc')
    {
        return static::$instance->orderBy($column, $direction);
    }

    /**
     * Add a descending "order by" clause to the query.
     *
     * @param string $column
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orderByDesc($column)
    {
        return static::$instance->orderByDesc($column);
    }

    /**
     * Put the query's results in random order.
     *
     * @param string $seed
     * @return \Hyperf\Database\Query\Builder
     */
    public static function inRandomOrder($seed = '')
    {
        return static::$instance->inRandomOrder($seed);
    }

    /**
     * Add a raw "order by" clause to the query.
     *
     * @param string $sql
     * @param array $bindings
     * @return \Hyperf\Database\Query\Builder
     */
    public static function orderByRaw($sql, $bindings = [])
    {
        return static::$instance->orderByRaw($sql, $bindings);
    }

    /**
     * Alias to set the "offset" value of the query.
     *
     * @param int $value
     * @return \Hyperf\Database\Query\Builder
     */
    public static function skip($value)
    {
        return static::$instance->skip($value);
    }

    /**
     * Set the "offset" value of the query.
     *
     * @param int $value
     * @return \Hyperf\Database\Query\Builder
     */
    public static function offset($value)
    {
        return static::$instance->offset($value);
    }

    /**
     * Alias to set the "limit" value of the query.
     *
     * @param int $value
     * @return \Hyperf\Database\Query\Builder
     */
    public static function take($value)
    {
        return static::$instance->take($value);
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param int $value
     * @return \Hyperf\Database\Query\Builder
     */
    public static function limit($value)
    {
        return static::$instance->limit($value);
    }

    /**
     * Set the limit and offset for a given page.
     *
     * @param int $page
     * @param int $perPage
     * @return \Hyperf\Database\Query\Builder
     */
    public static function forPage($page, $perPage = 15)
    {
        return static::$instance->forPage($page, $perPage);
    }

    /**
     * Constrain the query to the previous "page" of results before a given ID.
     *
     * @param int $perPage
     * @param null|int $lastId
     * @param string $column
     * @return \Hyperf\Database\Query\Builder
     */
    public static function forPageBeforeId($perPage = 15, $lastId = 0, $column = 'id')
    {
        return static::$instance->forPageBeforeId($perPage, $lastId, $column);
    }

    /**
     * Constrain the query to the next "page" of results after a given ID.
     *
     * @param int $perPage
     * @param null|int $lastId
     * @param string $column
     * @return \Hyperf\Database\Query\Builder
     */
    public static function forPageAfterId($perPage = 15, $lastId = 0, $column = 'id')
    {
        return static::$instance->forPageAfterId($perPage, $lastId, $column);
    }

    /**
     * Add a union statement to the query.
     *
     * @param \Closure|\Hyperf\Database\Query\Builder $query
     * @param bool $all
     *
     * @return \Hyperf\Database\Query\Builder
     */
    public static function union($query, $all = false)
    {
        return static::$instance->union($query, $all);
    }

    /**
     * Add a union all statement to the query.
     *
     * @param \Closure|\Hyperf\Database\Query\Builder $query
     *
     * @return \Hyperf\Database\Query\Builder
     */
    public static function unionAll($query)
    {
        return static::$instance->unionAll($query);
    }

    /**
     * Lock the selected rows in the table.
     *
     * @param bool|string $value
     * @return \Hyperf\Database\Query\Builder
     */
    public static function lock($value = true)
    {
        return static::$instance->lock($value);
    }

    /**
     * Lock the selected rows in the table for updating.
     *
     * @return \Hyperf\Database\Query\Builder
     */
    public static function lockForUpdate()
    {
        return static::$instance->lockForUpdate();
    }

    /**
     * Share lock the selected rows in the table.
     *
     * @return \Hyperf\Database\Query\Builder
     */
    public static function sharedLock()
    {
        return static::$instance->sharedLock();
    }

    /**
     * Get the SQL representation of the query.
     *
     * @return string
     */
    public static function toSql()
    {
        return static::$instance->toSql();
    }

    /**
     * Get the count of the total records for the paginator.
     *
     * @param array $columns
     * @return int
     */
    public static function getCountForPagination($columns = [])
    {
        return static::$instance->getCountForPagination($columns);
    }

    /**
     * Concatenate values of a given column as a string.
     *
     * @param string $column
     * @param string $glue
     * @return string
     */
    public static function implode($column, $glue = '')
    {
        return static::$instance->implode($column, $glue);
    }

    /**
     * Determine if any rows exist for the current query.
     *
     * @return bool
     */
    public static function exists()
    {
        return static::$instance->exists();
    }

    /**
     * Determine if no rows exist for the current query.
     *
     * @return bool
     */
    public static function doesntExist()
    {
        return static::$instance->doesntExist();
    }

    /**
     * Retrieve the "count" result of the query.
     *
     * @param string $columns
     * @return int
     */
    public static function count($columns = '*')
    {
        return static::$instance->count($columns);
    }

    /**
     * Retrieve the minimum value of a given column.
     *
     * @param string $column
     * @return mixed
     */
    public static function min($column)
    {
        return static::$instance->min($column);
    }

    /**
     * Retrieve the maximum value of a given column.
     *
     * @param string $column
     * @return mixed
     */
    public static function max($column)
    {
        return static::$instance->max($column);
    }

    /**
     * Retrieve the sum of the values of a given column.
     *
     * @param string $column
     * @return mixed
     */
    public static function sum($column)
    {
        return static::$instance->sum($column);
    }

    /**
     * Retrieve the average of the values of a given column.
     *
     * @param string $column
     * @return mixed
     */
    public static function avg($column)
    {
        return static::$instance->avg($column);
    }

    /**
     * Alias for the "avg" method.
     *
     * @param string $column
     * @return mixed
     */
    public static function average($column)
    {
        return static::$instance->average($column);
    }

    /**
     * Execute an aggregate function on the database.
     *
     * @param string $function
     * @param array $columns
     * @return mixed
     */
    public static function aggregate($function, $columns = [])
    {
        return static::$instance->aggregate($function, $columns);
    }

    /**
     * Execute a numeric aggregate function on the database.
     *
     * @param string $function
     * @param array $columns
     * @return float|int
     */
    public static function numericAggregate($function, $columns = [])
    {
        return static::$instance->numericAggregate($function, $columns);
    }

    /**
     * Insert a new record into the database.
     *
     * @param array $values
     * @return bool
     */
    public static function insert($values)
    {
        return static::$instance->insert($values);
    }

    /**
     * Insert a new record into the database while ignoring errors.
     *
     * @param array $values
     * @return int
     */
    public static function insertOrIgnore($values)
    {
        return static::$instance->insertOrIgnore($values);
    }

    /**
     * Insert a new record and get the value of the primary key.
     *
     * @param array $values
     * @param null|string $sequence
     * @return int
     */
    public static function insertGetId($values, $sequence = null)
    {
        return static::$instance->insertGetId($values, $sequence);
    }

    /**
     * Insert new records into the table using a subquery.
     *
     * @param array $columns
     * @param \Closure|\Hyperf\Database\Query\Builder|string $query
     * @return int
     */
    public static function insertUsing($columns, $query)
    {
        return static::$instance->insertUsing($columns, $query);
    }

    /**
     * Insert or update a record matching the attributes, and fill it with values.
     *
     * @param array $attributes
     * @param array $values
     * @return bool
     */
    public static function updateOrInsert($attributes, $values = [])
    {
        return static::$instance->updateOrInsert($attributes, $values);
    }

    /**
     * Run a truncate statement on the table.
     */
    public static function truncate()
    {
        static::$instance->truncate();
    }

    /**
     * Create a raw database expression.
     *
     * @param mixed $value
     * @return \Hyperf\Database\Query\Expression
     */
    public static function raw($value)
    {
        return static::$instance->raw($value);
    }

    /**
     * Get the current query value bindings in a flattened array.
     *
     * @return array
     */
    public static function getBindings()
    {
        return static::$instance->getBindings();
    }

    /**
     * Get the raw array of bindings.
     *
     * @return array
     */
    public static function getRawBindings()
    {
        return static::$instance->getRawBindings();
    }

    /**
     * Set the bindings on the query builder.
     *
     * @param array $bindings
     * @param string $type
     * @throws \InvalidArgumentException
     * @return \Hyperf\Database\Query\Builder
     */
    public static function setBindings($bindings, $type = 'where')
    {
        return static::$instance->setBindings($bindings, $type);
    }

    /**
     * Add a binding to the query.
     *
     * @param mixed $value
     * @param string $type
     * @throws \InvalidArgumentException
     * @return \Hyperf\Database\Query\Builder
     */
    public static function addBinding($value, $type = 'where')
    {
        return static::$instance->addBinding($value, $type);
    }

    /**
     * Merge an array of bindings into our bindings.
     *
     * @param \Hyperf\Database\Query\Builder $query
     * @return \Hyperf\Database\Query\Builder
     */
    public static function mergeBindings($query)
    {
        return static::$instance->mergeBindings($query);
    }

    /**
     * Get the database query processor instance.
     *
     * @return \Hyperf\Database\Query\Processors\Processor
     */
    public static function getProcessor()
    {
        return static::$instance->getProcessor();
    }

    /**
     * Get the query grammar instance.
     *
     * @return \Hyperf\Database\Query\Grammars\Grammar
     */
    public static function getGrammar()
    {
        return static::$instance->getGrammar();
    }

    /**
     * Use the write pdo for query.
     *
     * @return \Hyperf\Database\Query\Builder
     */
    public static function useWritePdo()
    {
        return static::$instance->useWritePdo();
    }

    /**
     * Clone the query without the given properties.
     *
     * @param array $properties
     *
     * @return \Hyperf\Database\Query\Builder
     */
    public static function cloneWithout($properties)
    {
        return static::$instance->cloneWithout($properties);
    }

    /**
     * Clone the query without the given bindings.
     *
     * @param array $except
     *
     * @return \Hyperf\Database\Query\Builder
     */
    public static function cloneWithoutBindings($except)
    {
        return static::$instance->cloneWithoutBindings($except);
    }

    /**
     * Register a custom macro.
     *
     * @param string $name
     * @param callable|object $macro
     */
    public static function macro($name, $macro)
    {
        \Hyperf\Database\Query\Builder::macro($name, $macro);
    }

    /**
     * Mix another object into the class.
     *
     * @param object $mixin
     * @throws \ReflectionException
     */
    public static function mixin($mixin)
    {
        \Hyperf\Database\Query\Builder::mixin($mixin);
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param string $method
     * @param array $parameters
     * @throws \BadMethodCallException
     * @return mixed
     */
    public static function macroCall($method, $parameters)
    {
        return static::$instance->macroCall($method, $parameters);
    }
}
