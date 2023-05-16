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
namespace Hyperf\Database\Model\Relations;

use Generator;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\ModelNotFoundException;
use Hyperf\Database\Model\SoftDeletes;

use function Hyperf\Support\class_uses_recursive;

class HasManyThrough extends Relation
{
    /**
     * The "through" parent model instance.
     *
     * @var \Hyperf\Database\Model\Model
     */
    protected $throughParent;

    /**
     * The far parent model instance.
     *
     * @var \Hyperf\Database\Model\Model
     */
    protected $farParent;

    /**
     * The near key on the relationship.
     *
     * @var string
     */
    protected $firstKey;

    /**
     * The far key on the relationship.
     *
     * @var string
     */
    protected $secondKey;

    /**
     * The local key on the relationship.
     *
     * @var string
     */
    protected $localKey;

    /**
     * The local key on the intermediary model.
     *
     * @var string
     */
    protected $secondLocalKey;

    /**
     * The count of self joins.
     *
     * @var int
     */
    protected static $selfJoinCount = 0;

    /**
     * Create a new has many through relationship instance.
     *
     * @param string $firstKey
     * @param string $secondKey
     * @param string $localKey
     * @param string $secondLocalKey
     */
    public function __construct(Builder $query, Model $farParent, Model $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey)
    {
        $this->localKey = $localKey;
        $this->firstKey = $firstKey;
        $this->secondKey = $secondKey;
        $this->farParent = $farParent;
        $this->throughParent = $throughParent;
        $this->secondLocalKey = $secondLocalKey;

        parent::__construct($query, $throughParent);
    }

    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints()
    {
        $localValue = $this->farParent[$this->localKey];

        $this->performJoin();

        if (Constraint::isConstraint()) {
            $this->query->where($this->getQualifiedFirstKeyName(), '=', $localValue);
        }
    }

    /**
     * Get the fully qualified parent key name.
     *
     * @return string
     */
    public function getQualifiedParentKeyName()
    {
        return $this->parent->qualifyColumn($this->secondLocalKey);
    }

    /**
     * Determine whether "through" parent of the relation uses Soft Deletes.
     *
     * @return bool
     */
    public function throughParentSoftDeletes()
    {
        return in_array(SoftDeletes::class, class_uses_recursive($this->throughParent));
    }

    /**
     * Set the constraints for an eager load of the relation.
     */
    public function addEagerConstraints(array $models)
    {
        $whereIn = $this->whereInMethod($this->farParent, $this->localKey);

        $this->query->{$whereIn}(
            $this->getQualifiedFirstKeyName(),
            $this->getKeys($models, $this->localKey)
        );
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param string $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param string $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        // Once we have the dictionary we can simply spin through the parent models to
        // link them up with their children using the keyed dictionary to make the
        // matching very convenient and easy work. Then we'll just return them.
        foreach ($models as $model) {
            if (isset($dictionary[$key = $model->getAttribute($this->localKey)])) {
                $model->setRelation(
                    $relation,
                    $this->related->newCollection($dictionary[$key])
                );
            }
        }

        return $models;
    }

    /**
     * Get the first related model record matching the attributes or instantiate it.
     *
     * @return \Hyperf\Database\Model\Model
     */
    public function firstOrNew(array $attributes)
    {
        if (is_null($instance = $this->where($attributes)->first())) {
            $instance = $this->related->newInstance($attributes);
        }

        return $instance;
    }

    /**
     * Create or update a related record matching the attributes, and fill it with values.
     *
     * @return \Hyperf\Database\Model\Model
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        $instance = $this->firstOrNew($attributes);

        $instance->fill($values)->save();

        return $instance;
    }

    /**
     * Execute the query and get the first related model.
     *
     * @param array $columns
     */
    public function first($columns = ['*'])
    {
        $results = $this->take(1)->get($columns);

        return count($results) > 0 ? $results->first() : null;
    }

    /**
     * Execute the query and get the first result or throw an exception.
     *
     * @param array $columns
     * @return \Hyperf\Database\Model\Model|static
     * @throws \Hyperf\Database\Model\ModelNotFoundException
     */
    public function firstOrFail($columns = ['*'])
    {
        if (! is_null($model = $this->first($columns))) {
            return $model;
        }

        throw (new ModelNotFoundException())->setModel(get_class($this->related));
    }

    /**
     * Find a related model by its primary key.
     *
     * @param array $columns
     * @param mixed $id
     * @return null|\Hyperf\Database\Model\Collection|\Hyperf\Database\Model\Model
     */
    public function find($id, $columns = ['*'])
    {
        if (is_array($id)) {
            return $this->findMany($id, $columns);
        }

        return $this->where(
            $this->getRelated()->getQualifiedKeyName(),
            '=',
            $id
        )->first($columns);
    }

    /**
     * Find multiple related models by their primary keys.
     *
     * @param array $columns
     * @param mixed $ids
     * @return \Hyperf\Database\Model\Collection
     */
    public function findMany($ids, $columns = ['*'])
    {
        if (empty($ids)) {
            return $this->getRelated()->newCollection();
        }

        return $this->whereIn(
            $this->getRelated()->getQualifiedKeyName(),
            $ids
        )->get($columns);
    }

    /**
     * Find a related model by its primary key or throw an exception.
     *
     * @param array $columns
     * @param mixed $id
     * @return \Hyperf\Database\Model\Collection|\Hyperf\Database\Model\Model
     * @throws \Hyperf\Database\Model\ModelNotFoundException
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

        throw (new ModelNotFoundException())->setModel(get_class($this->related), $id);
    }

    /**
     * Get the results of the relationship.
     */
    public function getResults()
    {
        return $this->get();
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param array $columns
     * @return \Hyperf\Database\Model\Collection
     */
    public function get($columns = ['*'])
    {
        $builder = $this->prepareQueryBuilder($columns);

        $models = $builder->getModels();

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded. This will solve the
        // n + 1 query problem for the developer and also increase performance.
        if (count($models) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $this->related->newCollection($models);
    }

    /**
     * Get a paginator for the "select" statement.
     */
    public function paginate(int $perPage = null, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginatorInterface
    {
        $this->query->addSelect($this->shouldSelect($columns));

        return $this->query->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param int $perPage
     * @param array $columns
     * @param string $pageName
     * @param null|int $page
     * @return \Hyperf\Contract\PaginatorInterface
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->query->addSelect($this->shouldSelect($columns));

        return $this->query->simplePaginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Chunk the results of the query.
     *
     * @param int $count
     * @return bool
     */
    public function chunk($count, callable $callback)
    {
        return $this->prepareQueryBuilder()->chunk($count, $callback);
    }

    /**
     * Get a generator for the given query.
     *
     * @return Generator
     */
    public function cursor()
    {
        return $this->prepareQueryBuilder()->cursor();
    }

    /**
     * Execute a callback over each item while chunking.
     *
     * @param int $count
     * @return bool
     */
    public function each(callable $callback, $count = 1000)
    {
        return $this->chunk($count, function ($results) use ($callback) {
            foreach ($results as $key => $value) {
                if ($callback($value, $key) === false) {
                    return false;
                }
            }
        });
    }

    /**
     * Add the constraints for a relationship query.
     *
     * @param array|mixed $columns
     * @return \Hyperf\Database\Model\Builder
     */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        if ($parentQuery->getQuery()->from === $query->getQuery()->from) {
            return $this->getRelationExistenceQueryForSelfRelation($query, $parentQuery, $columns);
        }

        if ($parentQuery->getQuery()->from === $this->throughParent->getTable()) {
            return $this->getRelationExistenceQueryForThroughSelfRelation($query, $parentQuery, $columns);
        }

        $this->performJoin($query);

        return $query->select($columns)->whereColumn(
            $this->getQualifiedLocalKeyName(),
            '=',
            $this->getQualifiedFirstKeyName()
        );
    }

    /**
     * Add the constraints for a relationship query on the same table.
     *
     * @param array|mixed $columns
     * @return \Hyperf\Database\Model\Builder
     */
    public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query->from($query->getModel()->getTable() . ' as ' . $hash = $this->getRelationCountHash());

        $query->join($this->throughParent->getTable(), $this->getQualifiedParentKeyName(), '=', $hash . '.' . $this->secondKey);

        if ($this->throughParentSoftDeletes()) {
            $query->whereNull($this->throughParent->getQualifiedDeletedAtColumn());
        }

        $query->getModel()->setTable($hash);

        return $query->select($columns)->whereColumn(
            $parentQuery->getQuery()->from . '.' . $this->localKey,
            '=',
            $this->getQualifiedFirstKeyName()
        );
    }

    /**
     * Add the constraints for a relationship query on the same table as the through parent.
     *
     * @param array|mixed $columns
     * @return \Hyperf\Database\Model\Builder
     */
    public function getRelationExistenceQueryForThroughSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $table = $this->throughParent->getTable() . ' as ' . $hash = $this->getRelationCountHash();

        $query->join($table, $hash . '.' . $this->secondLocalKey, '=', $this->getQualifiedFarKeyName());

        if ($this->throughParentSoftDeletes()) {
            $query->whereNull($hash . '.' . $this->throughParent->getDeletedAtColumn());
        }

        return $query->select($columns)->whereColumn(
            $parentQuery->getQuery()->from . '.' . $this->localKey,
            '=',
            $hash . '.' . $this->firstKey
        );
    }

    /**
     * Get the qualified foreign key on the related model.
     *
     * @return string
     */
    public function getQualifiedFarKeyName()
    {
        return $this->getQualifiedForeignKeyName();
    }

    /**
     * Get the foreign key on the "through" model.
     *
     * @return string
     */
    public function getFirstKeyName()
    {
        return $this->firstKey;
    }

    /**
     * Get the qualified foreign key on the "through" model.
     *
     * @return string
     */
    public function getQualifiedFirstKeyName()
    {
        return $this->throughParent->qualifyColumn($this->firstKey);
    }

    /**
     * Get the foreign key on the related model.
     *
     * @return string
     */
    public function getForeignKeyName()
    {
        return $this->secondKey;
    }

    /**
     * Get the qualified foreign key on the related model.
     *
     * @return string
     */
    public function getQualifiedForeignKeyName()
    {
        return $this->related->qualifyColumn($this->secondKey);
    }

    /**
     * Get the local key on the far parent model.
     *
     * @return string
     */
    public function getLocalKeyName()
    {
        return $this->localKey;
    }

    /**
     * Get the qualified local key on the far parent model.
     *
     * @return string
     */
    public function getQualifiedLocalKeyName()
    {
        return $this->farParent->qualifyColumn($this->localKey);
    }

    /**
     * Get the local key on the intermediary model.
     *
     * @return string
     */
    public function getSecondLocalKeyName()
    {
        return $this->secondLocalKey;
    }

    /**
     * Set the join clause on the query.
     */
    protected function performJoin(Builder $query = null)
    {
        $query = $query ?: $this->query;

        $farKey = $this->getQualifiedFarKeyName();

        $query->join($this->throughParent->getTable(), $this->getQualifiedParentKeyName(), '=', $farKey);

        if ($this->throughParentSoftDeletes()) {
            $query->whereNull($this->throughParent->getQualifiedDeletedAtColumn());
        }
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @return array
     */
    protected function buildDictionary(Collection $results)
    {
        $dictionary = [];

        // First we will create a dictionary of models keyed by the foreign key of the
        // relationship as this will allow us to quickly access all of the related
        // models without having to do nested looping which will be quite slow.
        foreach ($results as $result) {
            $dictionary[$result->laravel_through_key][] = $result;
        }

        return $dictionary;
    }

    /**
     * Set the select clause for the relation query.
     *
     * @return array
     */
    protected function shouldSelect(array $columns = ['*'])
    {
        if ($columns == ['*']) {
            $columns = [$this->related->getTable() . '.*'];
        }

        return array_merge($columns, [$this->getQualifiedFirstKeyName() . ' as laravel_through_key']);
    }

    /**
     * Prepare the query builder for query execution.
     *
     * @param array $columns
     * @return \Hyperf\Database\Model\Builder
     */
    protected function prepareQueryBuilder($columns = ['*'])
    {
        $builder = $this->query->applyScopes();

        return $builder->addSelect(
            $this->shouldSelect($builder->getQuery()->columns ? [] : $columns)
        );
    }
}
