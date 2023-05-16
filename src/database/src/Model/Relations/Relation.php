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

use Closure;
use Hyperf\Collection\Arr;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Query\Expression;
use Hyperf\Macroable\Macroable;
use Hyperf\Support\Traits\ForwardsCalls;

use function Hyperf\Collection\collect;
use function Hyperf\Collection\last;

/**
 * @mixin \Hyperf\Database\Model\Builder
 */
abstract class Relation
{
    use ForwardsCalls, Macroable {
        __call as macroCall;
    }

    /**
     * An array to map class names to their morph names in database.
     *
     * @var array
     */
    public static $morphMap = [];

    /**
     * The Model query builder instance.
     *
     * @var \Hyperf\Database\Model\Builder
     */
    protected $query;

    /**
     * The parent model instance.
     *
     * @var \Hyperf\Database\Model\Model
     */
    protected $parent;

    /**
     * The related model instance.
     *
     * @var \Hyperf\Database\Model\Model
     */
    protected $related;

    /**
     * Create a new relation instance.
     */
    public function __construct(Builder $query, Model $parent)
    {
        $this->query = $query;
        $this->parent = $parent;
        $this->related = $query->getModel();

        $this->addConstraints();
    }

    /**
     * Handle dynamic method calls to the relationship.
     *
     * @param string $method
     * @param array $parameters
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        $result = $this->forwardCallTo($this->query, $method, $parameters);

        if ($result === $this->query) {
            return $this;
        }

        return $result;
    }

    /**
     * Force a clone of the underlying query builder when cloning.
     */
    public function __clone()
    {
        $this->query = clone $this->query;
    }

    /**
     * Run a callback with constraints disabled on the relation.
     */
    public static function noConstraints(Closure $callback)
    {
        $previous = Constraint::isConstraint();

        Constraint::setConstraint(false);

        // When resetting the relation where clause, we want to shift the first element
        // off of the bindings, leaving only the constraints that the developers put
        // as "extra" on the relationships, and not original relation constraints.
        try {
            return call_user_func($callback);
        } finally {
            Constraint::setConstraint($previous);
        }
    }

    /**
     * Set the base constraints on the relation query.
     */
    abstract public function addConstraints();

    /**
     * Set the constraints for an eager load of the relation.
     */
    abstract public function addEagerConstraints(array $models);

    /**
     * Initialize the relation on a set of models.
     *
     * @param string $relation
     * @return array
     */
    abstract public function initRelation(array $models, $relation);

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param string $relation
     * @return array
     */
    abstract public function match(array $models, Collection $results, $relation);

    /**
     * Get the results of the relationship.
     */
    abstract public function getResults();

    /**
     * Get the relationship for eager loading.
     *
     * @return \Hyperf\Database\Model\Collection
     */
    public function getEager()
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
        return $this->query->get($columns);
    }

    /**
     * Touch all of the related models for the relationship.
     */
    public function touch()
    {
        $model = $this->getRelated();

        if (! $model::isIgnoringTouch()) {
            $this->rawUpdate([
                $model->getUpdatedAtColumn() => $model->freshTimestampString(),
            ]);
        }
    }

    /**
     * Run a raw update against the base query.
     *
     * @return int
     */
    public function rawUpdate(array $attributes = [])
    {
        return $this->query->withoutGlobalScopes()->update($attributes);
    }

    /**
     * Add the constraints for a relationship count query.
     *
     * @return \Hyperf\Database\Model\Builder
     */
    public function getRelationExistenceCountQuery(Builder $query, Builder $parentQuery)
    {
        return $this->getRelationExistenceQuery(
            $query,
            $parentQuery,
            new Expression('count(*)')
        )->setBindings([], 'select');
    }

    /**
     * Add the constraints for an internal relationship existence query.
     *
     * Essentially, these queries compare on column names like whereColumn.
     *
     * @param array|mixed $columns
     * @return \Hyperf\Database\Model\Builder
     */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        return $query->select($columns)->whereColumn(
            $this->getQualifiedParentKeyName(),
            '=',
            $this->getExistenceCompareKey()
        );
    }

    /**
     * Get the underlying query for the relation.
     *
     * @return \Hyperf\Database\Model\Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get the base query builder driving the Model builder.
     *
     * @return \Hyperf\Database\Query\Builder
     */
    public function getBaseQuery()
    {
        return $this->query->getQuery();
    }

    /**
     * Get the parent model of the relation.
     *
     * @return \Hyperf\Database\Model\Model
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get the fully qualified parent key name.
     *
     * @return string
     */
    public function getQualifiedParentKeyName()
    {
        return $this->parent->getQualifiedKeyName();
    }

    /**
     * Get the related model of the relation.
     *
     * @return \Hyperf\Database\Model\Model
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * Get the name of the "created at" column.
     *
     * @return string
     */
    public function createdAt()
    {
        return $this->parent->getCreatedAtColumn();
    }

    /**
     * Get the name of the "updated at" column.
     *
     * @return string
     */
    public function updatedAt()
    {
        return $this->parent->getUpdatedAtColumn();
    }

    /**
     * Get the name of the related model's "updated at" column.
     *
     * @return string
     */
    public function relatedUpdatedAt()
    {
        return $this->related->getUpdatedAtColumn();
    }

    /**
     * Set or get the morph map for polymorphic relations.
     *
     * @param bool $merge
     * @return array
     */
    public static function morphMap(array $map = null, $merge = true)
    {
        $map = static::buildMorphMapFromModels($map);

        if (is_array($map)) {
            static::$morphMap = $merge && static::$morphMap
                            ? $map + static::$morphMap : $map;
        }

        return static::$morphMap;
    }

    /**
     * Get the model associated with a custom polymorphic type.
     *
     * @param string $alias
     * @return null|string
     */
    public static function getMorphedModel($alias)
    {
        return self::$morphMap[$alias] ?? null;
    }

    /**
     * Get a relationship join table hash.
     *
     * For safety, The relationship ensures this method is only used in the same coroutine.
     *
     * @return string
     */
    public function getRelationCountHash(bool $incrementJoinCount = true)
    {
        return 'hyperf_reserved_' . ($incrementJoinCount ? static::$selfJoinCount++ : static::$selfJoinCount);
    }

    /**
     * Get all of the primary keys for an array of models.
     *
     * @param string $key
     * @return array
     */
    protected function getKeys(array $models, $key = null)
    {
        return collect($models)->map(function ($value) use ($key) {
            return $key ? $value->getAttribute($key) : $value->getKey();
        })->values()->unique(null, true)->sort()->all();
    }

    /**
     * Get the name of the "where in" method for eager loading.
     *
     * @param string $key
     * @return string
     */
    protected function whereInMethod(Model $model, $key)
    {
        return $model->getKeyName() === last(explode('.', $key))
                    && $model->getIncrementing()
                    && in_array($model->getKeyType(), ['int', 'integer'])
                        ? 'whereIntegerInRaw'
                        : 'whereIn';
    }

    /**
     * Builds a table-keyed array from model class names.
     *
     * @param null|string[] $models
     * @return null|array
     */
    protected static function buildMorphMapFromModels(array $models = null)
    {
        if (is_null($models) || Arr::isAssoc($models)) {
            return $models;
        }

        return array_combine(array_map(function ($model) {
            return (new $model())->getTable();
        }, $models), $models);
    }
}
