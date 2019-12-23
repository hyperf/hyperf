<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Scout;

use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Paginator\Paginator;
use Hyperf\Utils\Collection as BaseCollection;
use Hyperf\Utils\Traits\Macroable;

class Builder
{
    use Macroable;

    /**
     * The model instance.
     *
     * @var Model
     */
    public $model;

    /**
     * The query expression.
     *
     * @var string
     */
    public $query;

    /**
     * Optional callback before search execution.
     *
     * @var string
     */
    public $callback;

    /**
     * Optional callback before model query execution.
     *
     * @var null|\Closure
     */
    public $queryCallback;

    /**
     * The custom index specified for the search.
     *
     * @var string
     */
    public $index;

    /**
     * The "where" constraints added to the query.
     *
     * @var array
     */
    public $wheres = [];

    /**
     * The "limit" that should be applied to the search.
     *
     * @var int
     */
    public $limit;

    /**
     * The "order" that should be applied to the search.
     *
     * @var array
     */
    public $orders = [];

    /**
     * Create a new search builder instance.
     */
    public function __construct(Model $model, string $query, ?\Closure $callback = null, ?bool $softDelete = false)
    {
        $this->model = $model;
        $this->query = $query;
        $this->callback = $callback;
        if ($softDelete) {
            $this->wheres['__soft_deleted'] = 0;
        }
    }

    /**
     * Specify a custom index to perform this search on.
     */
    public function within(string $index): Builder
    {
        $this->index = $index;
        return $this;
    }

    /**
     * Add a constraint to the search query.
     *
     * @param mixed $value
     * @return $this
     */
    public function where(string $field, $value): Builder
    {
        $this->wheres[$field] = $value;
        return $this;
    }

    /**
     * Include soft deleted records in the results.
     *
     * @return $this
     */
    public function withTrashed(): Builder
    {
        unset($this->wheres['__soft_deleted']);
        return $this;
    }

    /**
     * Include only soft deleted records in the results.
     *
     * @return $this
     */
    public function onlyTrashed(): Builder
    {
        return tap($this->withTrashed(), function () {
            $this->wheres['__soft_deleted'] = 1;
        });
    }

    /**
     * Set the "limit" for the search query.
     *
     * @return $this
     */
    public function take(int $limit): Builder
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Add an "order" for the search query.
     */
    public function orderBy(string $column, ?string $direction = 'asc'): Builder
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => strtolower($direction) == 'asc' ? 'asc' : 'desc',
        ];
        return $this;
    }

    /**
     * Apply the callback's query changes if the given "value" is true.
     * @param mixed $value
     */
    public function when($value, callable $callback, ?callable $default = null): Builder
    {
        if ($value) {
            return $callback($this, $value) ?: $this;
        }
        if ($default) {
            return $default($this, $value) ?: $this;
        }
        return $this;
    }

    /**
     * Pass the query to a given callback.
     */
    public function tap(\Closure $callback): Builder
    {
        return $this->when(true, $callback);
    }

    /**
     * Set the callback that should have an opportunity to modify the database query.
     */
    public function query(\Closure $callback): Builder
    {
        $this->queryCallback = $callback;
        return $this;
    }

    /**
     * Get the raw results of the search.
     *
     * @return mixed
     */
    public function raw()
    {
        return $this->engine()->search($this);
    }

    /**
     * Get the keys of search results.
     */
    public function keys(): BaseCollection
    {
        return $this->engine()->keys($this);
    }

    /**
     * Get the first result from the search.
     */
    public function first(): Model
    {
        return $this->get()->first();
    }

    /**
     * Get the results of the search.
     */
    public function get(): Collection
    {
        return $this->engine()->get($this);
    }

    /**
     * Paginate the given query into a simple paginator.
     */
    public function paginate(?int $perPage = null, ?string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        $engine = $this->engine();
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $this->model->getPerPage();
        $results = $this->model->newCollection($engine->map(
            $this,
            $rawResults = $engine->paginate($this, $perPage, $page),
            $this->model
        )->all());
        $paginator = (new LengthAwarePaginator($results, $engine->getTotalCount($rawResults), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]));
        return $paginator->appends('query', $this->query);
    }

    /**
     * Paginate the given query into a simple paginator with raw data.
     */
    public function paginateRaw(?int $perPage = null, ?string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        $engine = $this->engine();
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $this->model->getPerPage();
        $results = $engine->paginate($this, $perPage, $page);
        $paginator = (new LengthAwarePaginator($results, $engine->getTotalCount($results), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]));
        return $paginator->appends('query', $this->query);
    }

    /**
     * Get the engine that should handle the query.
     */
    protected function engine()
    {
        return $this->model->searchableUsing();
    }
}
