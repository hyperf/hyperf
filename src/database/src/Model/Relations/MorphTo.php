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

use BadMethodCallException;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;

use function Hyperf\Collection\collect;
use function Hyperf\Collection\head;

class MorphTo extends BelongsTo
{
    /**
     * The type of the polymorphic relation.
     *
     * @var string
     */
    protected $morphType;

    /**
     * The models whose relations are being eager loaded.
     *
     * @var Collection
     */
    protected $models;

    /**
     * All of the models keyed by ID.
     *
     * @var array
     */
    protected $dictionary = [];

    /**
     * A buffer of dynamic calls to query macros.
     *
     * @var array
     */
    protected $macroBuffer = [];

    /**
     * A map of relations to load for each individual morph type.
     *
     * @var array
     */
    protected $morphableEagerLoads = [];

    /**
     * Create a new morph to relationship instance.
     *
     * @param string $foreignKey
     * @param string $ownerKey
     * @param string $type
     * @param string $relation
     */
    public function __construct(Builder $query, Model $parent, $foreignKey, $ownerKey, $type, $relation)
    {
        $this->morphType = $type;

        parent::__construct($query, $parent, $foreignKey, $ownerKey, $relation);
    }

    /**
     * Handle dynamic method calls to the relationship.
     *
     * @param string $method
     * @param array $parameters
     */
    public function __call($method, $parameters)
    {
        try {
            $result = parent::__call($method, $parameters);

            if (in_array($method, ['select', 'selectRaw', 'selectSub', 'addSelect', 'withoutGlobalScopes'])) {
                $this->macroBuffer[] = compact('method', 'parameters');
            }

            return $result;
        }

        // If we tried to call a method that does not exist on the parent Builder instance,
        // we'll assume that we want to call a query macro (e.g. withTrashed) that only
        // exists on related models. We will just store the call and replay it later.
        catch (BadMethodCallException $e) {
            $this->macroBuffer[] = compact('method', 'parameters');

            return $this;
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     */
    public function addEagerConstraints(array $models)
    {
        $this->buildDictionary($this->models = Collection::make($models));
    }

    /**
     * Get the results of the relationship.
     */
    public function getResults()
    {
        return $this->ownerKey ? parent::getResults() : null;
    }

    /**
     * Get the results of the relationship.
     *
     * Called via eager load method of Model query builder.
     */
    public function getEager()
    {
        foreach (array_keys($this->dictionary) as $type) {
            $this->matchToMorphParents($type, $this->getResultsByType($type));
        }

        return $this->models;
    }

    /**
     * Create a new model instance by type.
     *
     * @param string $type
     * @return Model
     */
    public function createModelByType($type)
    {
        $class = Model::getActualClassNameForMorph($type);

        return new $class();
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param string $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        return $models;
    }

    /**
     * Associate the model instance to the given parent.
     *
     * @param Model $model
     * @return Model
     */
    public function associate($model)
    {
        $this->parent->setAttribute(
            $this->foreignKey,
            $model instanceof Model ? $model->getKey() : null
        );

        $this->parent->setAttribute(
            $this->morphType,
            $model instanceof Model ? $model->getMorphClass() : null
        );

        return $this->parent->setRelation($this->relationName, $model);
    }

    /**
     * Dissociate previously associated model from the given parent.
     *
     * @return Model
     */
    public function dissociate()
    {
        $this->parent->setAttribute($this->foreignKey, null);

        $this->parent->setAttribute($this->morphType, null);

        return $this->parent->setRelation($this->relationName, null);
    }

    /**
     * Touch all of the related models for the relationship.
     */
    public function touch()
    {
        if (! is_null($this->ownerKey)) {
            parent::touch();
        }
    }

    /**
     * Get the foreign key "type" name.
     *
     * @return string
     */
    public function getMorphType()
    {
        return $this->morphType;
    }

    /**
     * Get the dictionary used by the relationship.
     *
     * @return array
     */
    public function getDictionary()
    {
        return $this->dictionary;
    }

    /**
     * Specify which relations to load for a given morph type.
     *
     * @return $this
     */
    public function morphWith(array $with)
    {
        $this->morphableEagerLoads = array_merge(
            $this->morphableEagerLoads,
            $with
        );

        return $this;
    }

    /**
     * Build a dictionary with the models.
     */
    protected function buildDictionary(Collection $models)
    {
        foreach ($models as $model) {
            if ($model->{$this->morphType}) {
                $this->dictionary[$model->{$this->morphType}][$model->{$this->foreignKey}][] = $model;
            }
        }
    }

    /**
     * Get all of the relation results for a type.
     *
     * @param string $type
     * @return Collection
     */
    protected function getResultsByType($type)
    {
        $instance = $this->createModelByType($type);

        $ownerKey = $this->ownerKey ?? $instance->getKeyName();

        $query = $this->replayMacros($instance->newQuery())
            ->mergeConstraintsFrom($this->getQuery())
            ->with(array_merge(
                $this->getQuery()->getEagerLoads(),
                (array) ($this->morphableEagerLoads[get_class($instance)] ?? [])
            ));

        $whereIn = $this->whereInMethod($instance, $ownerKey);

        return $query->{$whereIn}(
            $instance->getTable() . '.' . $ownerKey,
            $this->gatherKeysByType($type)
        )->get();
    }

    /**
     * Gather all of the foreign keys for a given type.
     *
     * @param string $type
     * @return array
     */
    protected function gatherKeysByType($type)
    {
        return collect($this->dictionary[$type])->map(function ($models) {
            return head($models)->{$this->foreignKey};
        })->values()->unique()->all();
    }

    /**
     * Match the results for a given type to their parents.
     *
     * @param string $type
     */
    protected function matchToMorphParents($type, Collection $results)
    {
        foreach ($results as $result) {
            $ownerKey = ! is_null($this->ownerKey) ? $result->{$this->ownerKey} : $result->getKey();

            if (isset($this->dictionary[$type][$ownerKey])) {
                foreach ($this->dictionary[$type][$ownerKey] as $model) {
                    $model->setRelation($this->relationName, $result);
                }
            }
        }
    }

    /**
     * Replay stored macro calls on the actual related instance.
     *
     * @return Builder
     */
    protected function replayMacros(Builder $query)
    {
        foreach ($this->macroBuffer as $macro) {
            $query->{$macro['method']}(...$macro['parameters']);
        }

        return $query;
    }
}
