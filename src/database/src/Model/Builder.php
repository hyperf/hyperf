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

use BadMethodCallException;
use Closure;
use Generator;
use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Contract\PaginatorInterface;
use Hyperf\Database\Concerns\BuildsQueries;
use Hyperf\Database\Exception\UniqueConstraintViolationException;
use Hyperf\Database\Model\Collection as ModelCollection;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\Database\Query\Builder as QueryBuilder;
use Hyperf\Database\Query\Expression;
use Hyperf\Paginator\Contract\CursorPaginator;
use Hyperf\Paginator\Cursor;
use Hyperf\Paginator\Paginator;
use Hyperf\Stringable\Str;
use Hyperf\Support\Traits\ForwardsCalls;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

use function Hyperf\Collection\collect;
use function Hyperf\Tappable\tap;

/**
 * @template TModel of Model
 * @mixin \Hyperf\Database\Query\Builder
 */
class Builder
{
    use BuildsQueries;
    use ForwardsCalls;
    use Concerns\QueriesRelationships;

    /**
     * The base query builder instance.
     *
     * @var QueryBuilder
     */
    protected $query;

    /**
     * The model being queried.
     *
     * @var Model
     */
    protected $model;

    /**
     * The relationships that should be eager loaded.
     *
     * @var array
     */
    protected $eagerLoad = [];

    /**
     * All of the globally registered builder macros.
     *
     * @var array
     */
    protected static $macros = [];

    /**
     * All of the locally registered builder macros.
     *
     * @var array
     */
    protected $localMacros = [];

    /**
     * A replacement for the typical delete function.
     *
     * @var Closure
     */
    protected $onDelete;

    /**
     * The methods that should be returned from query builder.
     *
     * @var array
     */
    protected $passthru = [
        'insert', 'insertGetId', 'getBindings', 'toSql', 'toRawSql', 'insertOrIgnore',
        'exists', 'doesntExist', 'count', 'min', 'max', 'avg', 'average', 'sum', 'getConnection',
        'upsert', 'updateOrInsert', 'insertOrIgnoreUsing',
    ];

    /**
     * Applied global scopes.
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * Removed global scopes.
     *
     * @var array
     */
    protected $removedScopes = [];

    /**
     * Create a new Model query builder instance.
     */
    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }

    /**
     * Dynamically handle calls into the query instance.
     *
     * @param string $method
     * @param array $parameters
     */
    public function __call($method, $parameters)
    {
        if ($method === 'macro') {
            $this->localMacros[$parameters[0]] = $parameters[1];

            return;
        }

        if ($method === 'mixin') {
            return static::registerMixin($parameters[0], $parameters[1] ?? true);
        }

        if ($this->hasMacro($method)) {
            array_unshift($parameters, $this);

            return $this->localMacros[$method](...$parameters);
        }

        if (static::hasGlobalMacro($method)) {
            if (static::$macros[$method] instanceof Closure) {
                return call_user_func_array(static::$macros[$method]->bindTo($this, static::class), $parameters);
            }

            return call_user_func_array(static::$macros[$method], $parameters);
        }

        if (isset($this->model) && method_exists($this->model, $scope = 'scope' . ucfirst($method))) {
            return $this->callScope([$this->model, $scope], $parameters);
        }

        if (in_array($method, $this->passthru)) {
            return $this->toBase()->{$method}(...$parameters);
        }

        $this->query->{$method}(...$parameters);

        return $this;
    }

    /**
     * Dynamically handle calls into the query instance.
     *
     * @param string $method
     * @param array $parameters
     *
     * @throws BadMethodCallException
     */
    public static function __callStatic($method, $parameters)
    {
        if ($method === 'macro') {
            static::$macros[$parameters[0]] = $parameters[1];

            return;
        }

        if ($method === 'mixin') {
            return static::registerMixin($parameters[0], $parameters[1] ?? true);
        }

        if (! static::hasGlobalMacro($method)) {
            static::throwBadMethodCallException($method);
        }

        if (static::$macros[$method] instanceof Closure) {
            return call_user_func_array(Closure::bind(static::$macros[$method], null, static::class), $parameters);
        }

        return call_user_func_array(static::$macros[$method], $parameters);
    }

    /**
     * Force a clone of the underlying query builder when cloning.
     */
    public function __clone()
    {
        $this->query = clone $this->query;
    }

    /**
     * Clone the Model query builder.
     *
     * @return static
     */
    public function clone()
    {
        return clone $this;
    }

    /**
     * Create and return an un-saved model instance.
     *
     * @return Model
     */
    public function make(array $attributes = [])
    {
        return $this->newModelInstance($attributes);
    }

    /**
     * Register a new global scope.
     *
     * @param string $identifier
     * @param Closure|Scope $scope
     * @return $this
     */
    public function withGlobalScope($identifier, $scope)
    {
        $this->scopes[$identifier] = $scope;

        if (method_exists($scope, 'extend')) {
            $scope->extend($this);
        }

        return $this;
    }

    /**
     * Remove a registered global scope.
     *
     * @param Scope|string $scope
     * @return $this
     */
    public function withoutGlobalScope($scope)
    {
        if (! is_string($scope)) {
            $scope = get_class($scope);
        }

        unset($this->scopes[$scope]);

        $this->removedScopes[] = $scope;

        return $this;
    }

    /**
     * Remove all or passed registered global scopes.
     *
     * @return $this
     */
    public function withoutGlobalScopes(?array $scopes = null)
    {
        if (! is_array($scopes)) {
            $scopes = array_keys($this->scopes);
        }

        foreach ($scopes as $scope) {
            $this->withoutGlobalScope($scope);
        }

        return $this;
    }

    /**
     * Get an array of global scopes that were removed from the query.
     *
     * @return array
     */
    public function removedScopes()
    {
        return $this->removedScopes;
    }

    /**
     * Add a where clause on the primary key to the query.
     *
     * @param mixed $id
     * @return $this
     */
    public function whereKey($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $this->query->whereIn($this->model->getQualifiedKeyName(), $id);

            return $this;
        }

        return $this->where($this->model->getQualifiedKeyName(), '=', $id);
    }

    /**
     * Add a where clause on the primary key to the query.
     *
     * @param mixed $id
     * @return $this
     */
    public function whereKeyNot($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $this->query->whereNotIn($this->model->getQualifiedKeyName(), $id);

            return $this;
        }

        return $this->where($this->model->getQualifiedKeyName(), '!=', $id);
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
        if ($column instanceof Closure) {
            $column($query = $this->model->newModelQuery());

            $this->query->addNestedWhereQuery($query->getQuery(), $boolean);
        } else {
            $this->query->where(...func_get_args());
        }

        return $this;
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
        [$value, $operator] = $this->query->prepareValueAndOperator(
            $value,
            $operator,
            func_num_args() === 2
        );

        return $this->where($column, $operator, $value, 'or');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     *
     * @param string $column
     * @return $this
     */
    public function latest($column = null)
    {
        if (is_null($column)) {
            $column = $this->model->getCreatedAtColumn() ?? 'created_at';
        }

        $this->query->latest($column);

        return $this;
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     *
     * @param string $column
     * @return $this
     */
    public function oldest($column = null)
    {
        if (is_null($column)) {
            $column = $this->model->getCreatedAtColumn() ?? 'created_at';
        }

        $this->query->oldest($column);

        return $this;
    }

    /**
     * Create a collection of models from plain arrays.
     *
     * @return ModelCollection
     */
    public function hydrate(array $items)
    {
        $instance = $this->newModelInstance();

        return $instance->newCollection(array_map(function ($item) use ($instance) {
            return $instance->newFromBuilder($item);
        }, $items));
    }

    /**
     * Create a collection of models from a raw query.
     *
     * @param string $query
     * @param array $bindings
     * @return ModelCollection
     */
    public function fromQuery($query, $bindings = [])
    {
        return $this->hydrate(
            $this->query->getConnection()->select($query, $bindings)
        );
    }

    /**
     * Find a model by its primary key.
     *
     * @param array $columns
     * @param array|int|string $id
     * @return null|Model|ModelCollection|static|static[]
     */
    public function find($id, $columns = ['*'])
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return $this->findMany($id, $columns);
        }

        return $this->whereKey($id)->first($columns);
    }

    /**
     * Find multiple models by their primary keys.
     *
     * @param array|Arrayable $ids
     * @param array $columns
     * @return ModelCollection
     */
    public function findMany($ids, $columns = ['*'])
    {
        if (empty($ids)) {
            return $this->model->newCollection();
        }

        return $this->whereKey($ids)->get($columns);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param array $columns
     * @param mixed $id
     * @return Model|ModelCollection|static|static[]
     * @throws ModelNotFoundException
     */
    public function findOrFail($id, $columns = ['*'])
    {
        $result = $this->find($id, $columns);

        if (is_array($id)) {
            if (count($result) === count(array_unique($id))) {
                return $result;
            }
        } elseif (! is_null($result)) {
            return $result;
        }

        throw (new ModelNotFoundException())->setModel(
            get_class($this->model),
            $id
        );
    }

    /**
     * Find a model by its primary key or call a callback.
     *
     * @template TValue
     *
     * @param (Closure(): TValue)|list<string>|string $columns
     * @param null|(Closure(): TValue) $callback
     * @return (
     *     $id is (Arrayable<array-key, mixed>|array)
     *     ? Collection<int, TModel>
     *     : TModel|TValue
     * )
     */
    public function findOr(mixed $id, array|Closure|string $columns = ['*'], ?Closure $callback = null): mixed
    {
        if ($columns instanceof Closure) {
            $callback = $columns;

            $columns = ['*'];
        }

        if (! is_null($model = $this->find($id, $columns))) {
            return $model;
        }

        return $callback();
    }

    /**
     * Find a model by its primary key or return fresh model instance.
     *
     * @param array $columns
     * @param mixed $id
     * @return Model|static
     */
    public function findOrNew($id, $columns = ['*'])
    {
        if (! is_null($model = $this->find($id, $columns))) {
            return $model;
        }

        return $this->newModelInstance();
    }

    /**
     * Get the first record matching the attributes or instantiate it.
     *
     * @return Model|static
     */
    public function firstOrNew(array $attributes, array $values = [])
    {
        if (! is_null($instance = $this->where($attributes)->first())) {
            return $instance;
        }

        return $this->newModelInstance($attributes + $values);
    }

    /**
     * Get the first record matching the attributes. If the record is not found, create it.
     *
     * @return Model|static
     */
    public function firstOrCreate(array $attributes, array $values = [])
    {
        if (! is_null($instance = $this->where($attributes)->first())) {
            return $instance;
        }

        return $this->createOrFirst($attributes, $values);
    }

    /**
     * Attempt to create the record. If a unique constraint violation occurs, attempt to find the matching record.
     *
     * @return Model|static
     */
    public function createOrFirst(array $attributes = [], array $values = [])
    {
        try {
            return $this->create(array_merge($attributes, $values));
        } catch (UniqueConstraintViolationException $exception) {
            return $this->where($attributes)->first();
        }
    }

    /**
     * Create or update a record matching the attributes, and fill it with values.
     *
     * @return Model|static
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return tap($this->firstOrNew($attributes), function ($instance) use ($values) {
            $instance->fill($values)->save();
        });
    }

    /**
     * Execute the query and get the first result or throw an exception.
     *
     * @param array $columns
     * @return Model|static
     * @throws ModelNotFoundException
     */
    public function firstOrFail($columns = ['*'])
    {
        if (! is_null($model = $this->first($columns))) {
            return $model;
        }

        throw (new ModelNotFoundException())->setModel(get_class($this->model));
    }

    /**
     * Execute the query and get the first result or call a callback.
     *
     * @param array|Closure $columns
     * @return mixed|Model|static
     */
    public function firstOr($columns = ['*'], ?Closure $callback = null)
    {
        if ($columns instanceof Closure) {
            $callback = $columns;

            $columns = ['*'];
        }

        if (! is_null($model = $this->first($columns))) {
            return $model;
        }

        return call_user_func($callback);
    }

    /**
     * Get a single column's value from the first result of a query.
     *
     * @param string $column
     */
    public function value($column)
    {
        if ($result = $this->first([$column])) {
            return $result->{Str::afterLast($column, '.')};
        }
    }

    /**
     * Get a single column's value from the first result of the query or throw an exception.
     * @throws ModelNotFoundException<Model>
     */
    public function valueOrFail(Expression|string $column): mixed
    {
        $column = $column instanceof Expression ? $column->getValue() : $column;

        return $this->firstOrFail([$column])->{Str::afterLast($column, '.')};
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param array $columns
     * @return ModelCollection|static[]
     */
    public function get($columns = ['*'])
    {
        $builder = $this->applyScopes();

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded, which will solve the
        // n+1 query issue for the developers to avoid running a lot of queries.
        if (count($models = $builder->getModels($columns)) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $builder->getModel()->newCollection($models);
    }

    /**
     * Get the hydrated models without eager loading.
     *
     * @param array $columns
     * @return Model[]|static[]
     */
    public function getModels($columns = ['*'])
    {
        return $this->model->hydrate(
            $this->query->get($columns)->all()
        )->all();
    }

    /**
     * Eager load the relationships for the models.
     *
     * @return array
     */
    public function eagerLoadRelations(array $models)
    {
        foreach ($this->eagerLoad as $name => $constraints) {
            // For nested eager loads we'll skip loading them here and they will be set as an
            // eager load on the query to retrieve the relation so that they will be eager
            // loaded on that query, because that is where they get hydrated as models.
            if (strpos($name, '.') === false) {
                $models = $this->eagerLoadRelation($models, $name, $constraints);
            }
        }

        return $models;
    }

    /**
     * Get the relation instance for the given relation name.
     *
     * @param string $name
     * @return Relation
     */
    public function getRelation($name)
    {
        // We want to run a relationship query without any constrains so that we will
        // not have to remove these where clauses manually which gets really hacky
        // and error prone. We don't want constraints because we add eager ones.
        $relation = Relation::noConstraints(function () use ($name) {
            try {
                return $this->getModel()->newInstance()->{$name}();
            } catch (BadMethodCallException $e) {
                throw RelationNotFoundException::make($this->getModel(), $name);
            }
        });

        $nested = $this->relationsNestedUnder($name);

        // If there are nested relationships set on the query, we will put those onto
        // the query instances so that they can be handled after this relationship
        // is loaded. In this way they will all trickle down as they are loaded.
        if (count($nested) > 0) {
            $relation->getQuery()->with($nested);
        }

        return $relation;
    }

    /**
     * Get a generator for the given query.
     *
     * @return Generator
     */
    public function cursor()
    {
        foreach ($this->applyScopes()->query->cursor() as $record) {
            yield $this->model->newFromBuilder($record);
        }
    }

    /**
     * Chunk the results of a query by comparing numeric IDs.
     *
     * @param int $count
     * @param null|string $column
     * @param null|string $alias
     * @return bool
     */
    public function chunkById($count, callable $callback, $column = null, $alias = null)
    {
        $column = $column ?? $this->getModel()->getKeyName();

        $alias = $alias ?? $column;

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

            $lastId = $results->last()->{$alias};

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
        $results = $this->toBase()->pluck($column, $key);

        // If the model has a mutator for the requested column, we will spin through
        // the results and mutate the values so that the mutated version of these
        // columns are returned as you would expect from these Model models.
        if (! $this->model->hasGetMutator($column)
            && ! $this->model->hasCast($column)
            && ! in_array($column, $this->model->getDates())) {
            return $results;
        }

        return $results->map(function ($value) use ($column) {
            return $this->model->newFromBuilder([$column => $value])->{$column};
        });
    }

    /**
     * Paginate the given query.
     *
     * @throws InvalidArgumentException
     */
    public function paginate(?int $perPage = null, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginatorInterface
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $perPage = $perPage ?: $this->model->getPerPage();

        $results = ($total = $this->toBase()->getCountForPagination())
            ? $this->forPage($page, $perPage)->get($columns)
            : $this->model->newCollection();

        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param int $perPage
     * @param array $columns
     * @param string $pageName
     * @param null|int $page
     * @return PaginatorInterface
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $perPage = $perPage ?: $this->model->getPerPage();

        // Next we will set the limit and offset for this query so that when we get the
        // results we get the proper section of results. Then, we'll create the full
        // paginator instances for these results with the given page and per page.
        $this->skip(($page - 1) * $perPage)->take($perPage + 1);

        return $this->simplePaginator($this->get($columns), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Paginate the given query into a cursor paginator.
     */
    public function cursorPaginate(?int $perPage = null, array|string $columns = ['*'], string $cursorName = 'cursor', null|Cursor|string $cursor = null): CursorPaginator
    {
        $perPage = $perPage ?: $this->model->getPerPage();

        return $this->paginateUsingCursor($perPage, $columns, $cursorName, $cursor);
    }

    /**
     * Save a new model and return the instance.
     *
     * @return $this|Model
     */
    public function create(array $attributes = [])
    {
        return tap($this->newModelInstance($attributes), function ($instance) {
            $instance->save();
        });
    }

    /**
     * Save a new model and return the instance. Allow mass-assignment.
     *
     * @return $this|Model
     */
    public function forceCreate(array $attributes)
    {
        return $this->model->unguarded(function () use ($attributes) {
            return $this->newModelInstance()->create($attributes);
        });
    }

    /**
     * Update a record in the database.
     *
     * @return int
     */
    public function update(array $values)
    {
        return $this->toBase()->update($this->addUpdatedAtColumn($values));
    }

    /**
     * Update the column's update timestamp.
     */
    public function touch(?string $column = null): false|int
    {
        $time = $this->model->freshTimestamp();

        if ($column) {
            return $this->toBase()->update([$column => $time]);
        }

        $column = $this->model->getUpdatedAtColumn();

        if (! $this->model->usesTimestamps() || is_null($column)) {
            return false;
        }

        return $this->toBase()->update([$column => $time]);
    }

    /**
     * Increment a column's value by a given amount.
     *
     * @param string $column
     * @param float|int $amount
     * @return int
     */
    public function increment($column, $amount = 1, array $extra = [])
    {
        return $this->toBase()->increment(
            $column,
            $amount,
            $this->addUpdatedAtColumn($extra)
        );
    }

    /**
     * Decrement a column's value by a given amount.
     *
     * @param string $column
     * @param float|int $amount
     * @return int
     */
    public function decrement($column, $amount = 1, array $extra = [])
    {
        return $this->toBase()->decrement(
            $column,
            $amount,
            $this->addUpdatedAtColumn($extra)
        );
    }

    /**
     * Delete a record from the database.
     */
    public function delete()
    {
        if (isset($this->onDelete)) {
            return call_user_func($this->onDelete, $this);
        }

        return $this->toBase()->delete();
    }

    /**
     * Run the default delete function on the builder.
     *
     * Since we do not apply scopes here, the row will actually be deleted.
     */
    public function forceDelete()
    {
        return $this->query->delete();
    }

    /**
     * Register a replacement for the default delete function.
     */
    public function onDelete(Closure $callback)
    {
        $this->onDelete = $callback;
    }

    /**
     * Call the given local model scopes.
     */
    public function scopes(array $scopes)
    {
        $builder = $this;

        foreach ($scopes as $scope => $parameters) {
            // If the scope key is an integer, then the scope was passed as the value and
            // the parameter list is empty, so we will format the scope name and these
            // parameters here. Then, we'll be ready to call the scope on the model.
            if (is_int($scope)) {
                [$scope, $parameters] = [$parameters, []];
            }

            // Next we'll pass the scope callback to the callScope method which will take
            // care of grouping the "wheres" properly so the logical order doesn't get
            // messed up when adding scopes. Then we'll return back out the builder.
            $builder = $builder->callScope(
                [$this->model, 'scope' . ucfirst($scope)],
                (array) $parameters
            );
        }

        return $builder;
    }

    /**
     * Apply the scopes to the Model builder instance and return it.
     *
     * @return Builder|static
     */
    public function applyScopes()
    {
        if (! $this->scopes) {
            return $this;
        }

        $builder = clone $this;

        foreach ($this->scopes as $identifier => $scope) {
            if (! isset($builder->scopes[$identifier])) {
                continue;
            }

            $builder->callScope(function (Builder $builder) use ($scope) {
                // If the scope is a Closure we will just go ahead and call the scope with the
                // builder instance. The "callScope" method will properly group the clauses
                // that are added to this query so "where" clauses maintain proper logic.
                if ($scope instanceof Closure) {
                    $scope($builder);
                }

                // If the scope is a scope object, we will call the apply method on this scope
                // passing in the builder and the model instance. After we run all of these
                // scopes we will return back the builder instance to the outside caller.
                if ($scope instanceof Scope) {
                    $scope->apply($builder, $this->getModel());
                }
            });
        }

        return $builder;
    }

    /**
     * Set the relationships that should be eager loaded.
     *
     * @param mixed $relations
     * @return $this
     */
    public function with($relations)
    {
        $eagerLoad = $this->parseWithRelations(is_string($relations) ? func_get_args() : $relations);

        $this->eagerLoad = array_merge($this->eagerLoad, $eagerLoad);

        return $this;
    }

    /**
     * Prevent the specified relations from being eager loaded.
     *
     * @param mixed $relations
     * @return $this
     */
    public function without($relations)
    {
        $this->eagerLoad = array_diff_key($this->eagerLoad, array_flip(
            is_string($relations) ? func_get_args() : $relations
        ));

        return $this;
    }

    /**
     * Create a new instance of the model being queried.
     *
     * @param array $attributes
     * @return Model|static
     */
    public function newModelInstance($attributes = [])
    {
        return $this->model->newInstance($attributes)->setConnection(
            $this->query->getConnection()->getName()
        );
    }

    /**
     * Apply query-time casts to the model instance.
     */
    public function withCasts(array $casts): static
    {
        $this->model->mergeCasts($casts);

        return $this;
    }

    /**
     * Get the underlying query builder instance.
     *
     * @return QueryBuilder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set the underlying query builder instance.
     *
     * @param QueryBuilder $query
     * @return $this
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get a base query builder instance.
     *
     * @return QueryBuilder
     */
    public function toBase()
    {
        return $this->applyScopes()->getQuery();
    }

    /**
     * Get the relationships being eagerly loaded.
     *
     * @return array
     */
    public function getEagerLoads()
    {
        return $this->eagerLoad;
    }

    /**
     * Set the relationships being eagerly loaded.
     *
     * @return $this
     */
    public function setEagerLoads(array $eagerLoad)
    {
        $this->eagerLoad = $eagerLoad;

        return $this;
    }

    /**
     * Get the model instance being queried.
     *
     * @return Model|static
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set a model instance for the model being queried.
     *
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        $this->query->from($model->getTable());

        return $this;
    }

    /**
     * Qualify the given column name by the model's table.
     *
     * @param string $column
     * @return string
     */
    public function qualifyColumn($column)
    {
        return $this->model->qualifyColumn($column);
    }

    /**
     * Qualify the given columns with the model's table.
     */
    public function qualifyColumns(array $columns): array
    {
        return $this->model->qualifyColumns($columns);
    }

    /**
     * Get the given macro by name.
     *
     * @param string $name
     * @return Closure
     */
    public function getMacro($name)
    {
        return Arr::get($this->localMacros, $name);
    }

    /**
     * Checks if a macro is registered.
     *
     * @param string $name
     * @return bool
     */
    public function hasMacro($name)
    {
        return isset($this->localMacros[$name]);
    }

    /**
     * Get the given global macro by name.
     *
     * @param string $name
     * @return Closure
     */
    public static function getGlobalMacro($name)
    {
        return Arr::get(static::$macros, $name);
    }

    /**
     * Checks if a global macro is registered.
     *
     * @param string $name
     * @return bool
     */
    public static function hasGlobalMacro($name)
    {
        return isset(static::$macros[$name]);
    }

    /**
     * Get the default key name of the table.
     */
    protected function defaultKeyName(): string
    {
        return $this->getModel()->getKeyName();
    }

    /**
     * Ensure the proper order by required for cursor pagination.
     */
    protected function ensureOrderForCursorPagination(bool $shouldReverse = false): Collection
    {
        if (empty($this->query->orders) && empty($this->query->unionOrders)) {
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
            $this->query->orders = collect($this->query->orders)->map($reverseDirection)->toArray();
            $this->query->unionOrders = collect($this->query->unionOrders)->map($reverseDirection)->toArray();
        }

        $orders = ! empty($this->query->unionOrders) ? $this->query->unionOrders : $this->query->orders;

        return collect($orders)
            ->filter(fn ($order) => Arr::has($order, 'direction'))
            ->values();
    }

    /**
     * Get the Eloquent builder instances that are used in the union of the query.
     */
    protected function getUnionBuilders(): Collection
    {
        return isset($this->query->unions)
            ? collect($this->query->unions)->pluck('query')
            : collect();
    }

    /**
     * Register the given mixin with the builder.
     *
     * @param string $mixin
     * @param bool $replace
     */
    protected static function registerMixin($mixin, $replace)
    {
        $methods = (new ReflectionClass($mixin))->getMethods(
            ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
        );

        foreach ($methods as $method) {
            if ($replace || ! static::hasGlobalMacro($method->name)) {
                static::macro($method->name, $method->invoke($mixin));
            }
        }
    }

    /**
     * Eagerly load the relationship on a set of models.
     *
     * @param string $name
     * @return array
     */
    protected function eagerLoadRelation(array $models, $name, Closure $constraints)
    {
        // First we will "back up" the existing where conditions on the query so we can
        // add our eager constraints. Then we will merge the wheres that were on the
        // query back to it in order that any where conditions might be specified.
        $relation = $this->getRelation($name);

        $relation->addEagerConstraints($models);

        $constraints($relation);

        // Once we have the results, we just match those back up to their parent models
        // using the relationship instance. Then we just return the finished arrays
        // of models which have been eagerly hydrated and are readied for return.
        return $relation->match(
            $relation->initRelation($models, $name),
            $relation->getEager(),
            $name
        );
    }

    /**
     * Get the deeply nested relations for a given top-level relation.
     *
     * @param string $relation
     * @return array
     */
    protected function relationsNestedUnder($relation)
    {
        $nested = [];

        // We are basically looking for any relationships that are nested deeper than
        // the given top-level relationship. We will just check for any relations
        // that start with the given top relations and adds them to our arrays.
        foreach ($this->eagerLoad as $name => $constraints) {
            if ($this->isNestedUnder($relation, $name)) {
                $nested[substr($name, strlen($relation . '.'))] = $constraints;
            }
        }

        return $nested;
    }

    /**
     * Determine if the relationship is nested.
     *
     * @param string $relation
     * @param string $name
     * @return bool
     */
    protected function isNestedUnder($relation, $name)
    {
        return Str::contains($name, '.') && Str::startsWith($name, $relation . '.');
    }

    /**
     * Add a generic "order by" clause if the query doesn't already have one.
     */
    protected function enforceOrderBy()
    {
        if (empty($this->query->orders) && empty($this->query->unionOrders)) {
            $this->orderBy($this->model->getQualifiedKeyName(), 'asc');
        }
    }

    /**
     * Add the "updated at" column to an array of values.
     *
     * @return array
     */
    protected function addUpdatedAtColumn(array $values)
    {
        if (! $this->model->usesTimestamps()
            || is_null($this->model->getUpdatedAtColumn())) {
            return $values;
        }

        $column = $this->model->getUpdatedAtColumn();

        $values = array_merge(
            [$column => $this->model->freshTimestampString()],
            $values
        );

        $segments = preg_split('/\s+as\s+/i', $this->query->from);

        $qualifiedColumn = end($segments) . '.' . $column;

        $values[$qualifiedColumn] = $values[$column];

        unset($values[$column]);

        return $values;
    }

    /**
     * Apply the given scope on the current builder instance.
     *
     * @param array $parameters
     */
    protected function callScope(callable $scope, $parameters = [])
    {
        array_unshift($parameters, $this);

        $query = $this->getQuery();

        // We will keep track of how many wheres are on the query before running the
        // scope so that we can properly group the added scope constraints in the
        // query as their own isolated nested where statement and avoid issues.
        $originalWhereCount = is_null($query->wheres)
            ? 0 : count($query->wheres);

        $result = $scope(...array_values($parameters)) ?? $this;

        if (count((array) $query->wheres) > $originalWhereCount) {
            $this->addNewWheresWithinGroup($query, $originalWhereCount);
        }

        return $result;
    }

    /**
     * Nest where conditions by slicing them at the given where count.
     *
     * @param int $originalWhereCount
     */
    protected function addNewWheresWithinGroup(QueryBuilder $query, $originalWhereCount)
    {
        // Here, we totally remove all of the where clauses since we are going to
        // rebuild them as nested queries by slicing the groups of wheres into
        // their own sections. This is to prevent any confusing logic order.
        $allWheres = $query->wheres;

        $query->wheres = [];

        $this->groupWhereSliceForScope(
            $query,
            array_slice($allWheres, 0, $originalWhereCount)
        );

        $this->groupWhereSliceForScope(
            $query,
            array_slice($allWheres, $originalWhereCount)
        );
    }

    /**
     * Slice where conditions at the given offset and add them to the query as a nested condition.
     *
     * @param array $whereSlice
     */
    protected function groupWhereSliceForScope(QueryBuilder $query, $whereSlice)
    {
        $whereBooleans = collect($whereSlice)->pluck('boolean');

        // Here we'll check if the given subset of where clauses contains any "or"
        // booleans and in this case create a nested where expression. That way
        // we don't add any unnecessary nesting thus keeping the query clean.
        if ($whereBooleans->contains('or')) {
            $query->wheres[] = $this->createNestedWhere(
                $whereSlice,
                $whereBooleans->first()
            );
        } else {
            $query->wheres = array_merge($query->wheres, $whereSlice);
        }
    }

    /**
     * Create a where array with nested where conditions.
     *
     * @param array $whereSlice
     * @param string $boolean
     * @return array
     */
    protected function createNestedWhere($whereSlice, $boolean = 'and')
    {
        $whereGroup = $this->getQuery()->forNestedWhere();

        $whereGroup->wheres = $whereSlice;

        return ['type' => 'Nested', 'query' => $whereGroup, 'boolean' => $boolean];
    }

    /**
     * Parse a list of relations into individuals.
     *
     * @return array
     */
    protected function parseWithRelations(array $relations)
    {
        $results = [];

        foreach ($relations as $name => $constraints) {
            // If the "name" value is a numeric key, we can assume that no
            // constraints have been specified. We'll just put an empty
            // Closure there, so that we can treat them all the same.
            if (is_numeric($name)) {
                $name = $constraints;

                [$name, $constraints] = Str::contains($name, ':')
                    ? $this->createSelectWithConstraint($name)
                    : [$name, static function () {
                    }];
            }

            // We need to separate out any nested includes, which allows the developers
            // to load deep relationships using "dots" without stating each level of
            // the relationship with its own key in the array of eager-load names.
            $results = $this->addNestedWiths($name, $results);

            $results[$name] = $constraints;
        }

        return $results;
    }

    /**
     * Create a constraint to select the given columns for the relation.
     *
     * @param string $name
     * @return array
     */
    protected function createSelectWithConstraint($name)
    {
        return [explode(':', $name)[0], static function ($query) use ($name) {
            $query->select(explode(',', explode(':', $name)[1]));
        }];
    }

    /**
     * Parse the nested relationships in a relation.
     *
     * @param string $name
     * @param array $results
     * @return array
     */
    protected function addNestedWiths($name, $results)
    {
        $progress = [];

        // If the relation has already been set on the result array, we will not set it
        // again, since that would override any constraints that were already placed
        // on the relationships. We will only set the ones that are not specified.
        foreach (explode('.', $name) as $segment) {
            $progress[] = $segment;

            if (! isset($results[$last = implode('.', $progress)])) {
                $results[$last] = static function () {
                };
            }
        }

        return $results;
    }
}
