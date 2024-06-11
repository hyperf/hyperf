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

namespace Hyperf\Scout;

use Closure;
use Hyperf\Collection\Collection as BaseCollection;
use Hyperf\Conditionable\Conditionable;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Macroable\Macroable;
use Hyperf\Paginator\AbstractPaginator;
use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Paginator\Paginator;

use function Hyperf\Tappable\tap;

class Builder
{
    use Macroable;
    use Conditionable;

    /**
     * Optional callback before model query execution.
     */
    public ?Closure $queryCallback = null;

    /**
     * The custom index specified for the search.
     */
    public ?string $index = null;

    /**
     * The "where" constraints added to the query.
     */
    public array $wheres = [];

    /**
     * The "limit" that should be applied to the search.
     */
    public int $limit = 0;

    /**
     * The "order" that should be applied to the search.
     */
    public array $orders = [];

    /**
     * Create a new search builder instance.
     */
    /**
     * @param Model&SearchableInterface $model
     * @param string $query the query expression
     * @param null|Closure $callback optional callback before search execution
     */
    public function __construct(public Model $model, public string $query, public ?Closure $callback = null, ?bool $softDelete = false)
    {
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
     */
    public function where(string $field, $value): static
    {
        $this->wheres[$field] = $value;
        return $this;
    }

    /**
     * Include soft deleted records in the results.
     */
    public function withTrashed(): static
    {
        unset($this->wheres['__soft_deleted']);
        return $this;
    }

    /**
     * Include only soft deleted records in the results.
     */
    public function onlyTrashed(): static
    {
        return tap($this->withTrashed(), function () {
            $this->wheres['__soft_deleted'] = 1;
        });
    }

    /**
     * Set the "limit" for the search query.
     */
    public function take(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Add an "order" for the search query.
     */
    public function orderBy(string $column, ?string $direction = 'asc'): static
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => strtolower($direction) == 'asc' ? 'asc' : 'desc',
        ];
        return $this;
    }

    /**
     * Pass the query to a given callback.
     */
    public function tap(Closure $callback): static
    {
        return $this->when(true, $callback);
    }

    /**
     * Set the callback that should have an opportunity to modify the database query.
     */
    public function query(Closure $callback): static
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
     * @return AbstractPaginator|LengthAwarePaginator
     */
    public function paginate(?int $perPage = null, ?string $pageName = 'page', ?int $page = null)
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
     * @return AbstractPaginator|LengthAwarePaginator
     */
    public function paginateRaw(?int $perPage = null, ?string $pageName = 'page', ?int $page = null)
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
