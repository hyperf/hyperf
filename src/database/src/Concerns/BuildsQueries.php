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
namespace Hyperf\Database\Concerns;

use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Contract\PaginatorInterface;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Utils\ApplicationContext;

trait BuildsQueries
{
    /**
     * Chunk the results of the query.
     *
     * @param int $count
     * @return bool
     */
    public function chunk($count, callable $callback)
    {
        $this->enforceOrderBy();

        $page = 1;

        do {
            // We'll execute the query for the given page and get the results. If there are
            // no results we can just break and return from here. When there are results
            // we will call the callback with the current chunk of these results here.
            $results = $this->forPage($page, $count)->get();

            $countResults = $results->count();

            if ($countResults == 0) {
                break;
            }

            // On each chunk result set, we will pass them to the callback and then let the
            // developer take care of everything within the callback, which allows us to
            // keep the memory low for spinning through large result sets for working.
            if ($callback($results, $page) === false) {
                return false;
            }

            unset($results);

            ++$page;
        } while ($countResults == $count);

        return true;
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
     * Execute the query and get the first result.
     *
     * @param array $columns
     * @return null|Model|object|static
     */
    public function first($columns = ['*'])
    {
        return $this->take(1)->get($columns)->first();
    }

    /**
     * Apply the callback's query changes if the given "value" is true.
     *
     * @param callable $callback
     * @param callable $default
     * @param mixed $value
     * @return $this|mixed
     */
    public function when($value, $callback, $default = null)
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
     *
     * @param \Closure $callback
     * @return $this|mixed
     */
    public function tap($callback)
    {
        return $this->when(true, $callback);
    }

    /**
     * Apply the callback's query changes if the given "value" is false.
     *
     * @param callable $callback
     * @param callable $default
     * @param mixed $value
     * @return $this|mixed
     */
    public function unless($value, $callback, $default = null)
    {
        if (! $value) {
            return $callback($this, $value) ?: $this;
        }
        if ($default) {
            return $default($this, $value) ?: $this;
        }

        return $this;
    }

    /**
     * Create a new length-aware paginator instance.
     */
    protected function paginator(Collection $items, int $total, int $perPage, int $currentPage, array $options): LengthAwarePaginatorInterface
    {
        $container = ApplicationContext::getContainer();
        if (! method_exists($container, 'make')) {
            throw new \RuntimeException('The DI container does not support make() method.');
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
            throw new \RuntimeException('The DI container does not support make() method.');
        }
        return $container->make(PaginatorInterface::class, compact('items', 'perPage', 'currentPage', 'options'));
    }
}
