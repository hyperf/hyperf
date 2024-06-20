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

namespace Hyperf\Paginator;

use ArrayAccess;
use Closure;
use Exception;
use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\Database\Model\Collection as ModelCollection;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\Pivot;
use Hyperf\Resource\Json\JsonResource;
use Hyperf\Stringable\Str;
use Hyperf\Support\Traits\ForwardsCalls;
use Hyperf\Tappable\Tappable;
use stdClass;
use Stringable;
use Traversable;

use function Hyperf\Collection\collect;

abstract class AbstractCursorPaginator implements Stringable
{
    use ForwardsCalls;
    use Tappable;

    /**
     * All of the items being paginated.
     */
    protected Collection|ModelCollection $items;

    /**
     * The number of items to be shown per page.
     */
    protected int $perPage;

    /**
     * The base path to assign to all URLs.
     */
    protected string $path = '/';

    /**
     * The query parameters to add to all URLs.
     */
    protected array $query = [];

    /**
     * The URL fragment to add to all URLs.
     */
    protected ?string $fragment = null;

    /**
     * The cursor string variable used to store the page.
     */
    protected string $cursorName = 'cursor';

    /**
     * The current cursor.
     */
    protected ?Cursor $cursor = null;

    /**
     * The paginator parameters for the cursor.
     */
    protected array $parameters = [];

    /**
     * The paginator options.
     */
    protected array $options = [];

    /**
     * Indicates whether there are more items in the data source.
     *
     * @return bool
     */
    protected bool $hasMore;

    /**
     * The current cursor resolver callback.
     */
    protected static ?Closure $currentCursorResolver = null;

    /**
     * Make dynamic calls into the collection.
     * @param mixed $method
     * @param mixed $parameters
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->getCollection(), $method, $parameters);
    }

    /**
     * Get the URL for a given cursor.
     */
    public function url(?Cursor $cursor): string
    {
        // If we have any extra query string key / value pairs that need to be added
        // onto the URL, we will put them in query string form and then attach it
        // to the URL. This allows for extra information like sortings storage.
        $parameters = is_null($cursor) ? [] : [$this->cursorName => $cursor->encode()];

        if (count($this->query) > 0) {
            $parameters = array_merge($this->query, $parameters);
        }

        return $this->path()
            . (str_contains($this->path(), '?') ? '&' : '?')
            . Arr::query($parameters)
            . $this->buildFragment();
    }

    /**
     * Get the URL for the previous page.
     */
    public function previousPageUrl(): ?string
    {
        if (is_null($previousCursor = $this->previousCursor())) {
            return null;
        }

        return $this->url($previousCursor);
    }

    /**
     * The URL for the next page, or null.
     */
    public function nextPageUrl(): ?string
    {
        if (is_null($nextCursor = $this->nextCursor())) {
            return null;
        }

        return $this->url($nextCursor);
    }

    /**
     * Get the "cursor" that points to the previous set of items.
     */
    public function previousCursor(): ?Cursor
    {
        if (is_null($this->cursor)
            || ($this->cursor->pointsToPreviousItems() && ! $this->hasMore)) {
            return null;
        }

        if ($this->items->isEmpty()) {
            return null;
        }

        return $this->getCursorForItem($this->items->first(), false);
    }

    /**
     * Get the "cursor" that points to the next set of items.
     */
    public function nextCursor(): ?Cursor
    {
        if ((is_null($this->cursor) && ! $this->hasMore)
            || (! is_null($this->cursor) && $this->cursor->pointsToNextItems() && ! $this->hasMore)) {
            return null;
        }

        if ($this->items->isEmpty()) {
            return null;
        }

        return $this->getCursorForItem($this->items->last());
    }

    /**
     * Get a cursor instance for the given item.
     */
    public function getCursorForItem(array|ArrayAccess|stdClass $item, bool $isNext = true): Cursor
    {
        return new Cursor($this->getParametersForItem($item), $isNext);
    }

    /**
     * Get the cursor parameters for a given object.
     *
     * @param ArrayAccess|stdClass $item
     * @return array
     *
     * @throws Exception
     */
    public function getParametersForItem($item)
    {
        return collect($this->parameters)
            ->filter()
            ->flip()
            ->map(function ($_, $parameterName) use ($item) {
                if ($item instanceof JsonResource) {
                    $item = $item->resource;
                }

                if ($item instanceof Model
                    && ! is_null($parameter = $this->getPivotParameterForItem($item, $parameterName))) {
                    return $parameter;
                }
                if ($item instanceof ArrayAccess || is_array($item)) {
                    return $this->ensureParameterIsPrimitive(
                        $item[$parameterName] ?? $item[Str::afterLast($parameterName, '.')]
                    );
                }
                if (is_object($item)) {
                    return $this->ensureParameterIsPrimitive(
                        $item->{$parameterName} ?? $item->{Str::afterLast($parameterName, '.')}
                    );
                }

                throw new Exception('Only arrays and objects are supported when cursor paginating items.');
            })->toArray();
    }

    /**
     * Get / set the URL fragment to be appended to URLs.
     */
    public function fragment(?string $fragment = null): null|static|string
    {
        if (is_null($fragment)) {
            return $this->fragment;
        }

        $this->fragment = $fragment;

        return $this;
    }

    /**
     * Add a set of query string values to the paginator.
     */
    public function appends(null|array|string $key, ?string $value = null): static
    {
        if (is_null($key)) {
            return $this;
        }

        if (is_array($key)) {
            return $this->appendArray($key);
        }

        return $this->addQuery($key, $value);
    }

    /**
     * Add all current query string values to the paginator.
     */
    public function withQueryString(): static
    {
        if ($query = Paginator::resolveQueryString()) {
            return $this->appends($query);
        }

        return $this;
    }

    /**
     * Load a set of relationships onto the mixed relationship collection.
     */
    public function loadMorph(string $relation, array $relations): static
    {
        $collection = $this->getCollection();
        if ($collection instanceof ModelCollection) {
            $collection->loadMorph($relation, $relations);
        }
        return $this;
    }

    /**
     * Load a set of relationship counts onto the mixed relationship collection.
     */
    public function loadMorphCount(string $relation, array $relations): static
    {
        $collection = $this->getCollection();
        if ($collection instanceof ModelCollection) {
            $collection->loadMorphCount($relation, $relations);
        }

        return $this;
    }

    /**
     * Get the slice of items being paginated.
     */
    public function items(): array
    {
        return $this->items->all();
    }

    /**
     * Transform each item in the slice of items using a callback.
     */
    public function through(callable $callback): static
    {
        $this->items->transform($callback);

        return $this;
    }

    /**
     * Get the number of items shown per page.
     */
    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * Get the current cursor being paginated.
     */
    public function cursor(): ?Cursor
    {
        return $this->cursor;
    }

    /**
     * Get the query string variable used to store the cursor.
     */
    public function getCursorName(): string
    {
        return $this->cursorName;
    }

    /**
     * Set the query string variable used to store the cursor.
     */
    public function setCursorName(string $name): static
    {
        $this->cursorName = $name;

        return $this;
    }

    /**
     * Set the base path to assign to all URLs.
     */
    public function withPath(string $path): static
    {
        return $this->setPath($path);
    }

    /**
     * Set the base path to assign to all URLs.
     */
    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the base path for paginator generated URLs.
     */
    public function path(): ?string
    {
        return $this->path;
    }

    /**
     * Resolve the current cursor or return the default value.
     * @param null|mixed $default
     */
    public static function resolveCurrentCursor(string $cursorName = 'cursor', $default = null): ?Cursor
    {
        if (isset(static::$currentCursorResolver)) {
            return call_user_func(static::$currentCursorResolver, $cursorName);
        }

        return $default;
    }

    /**
     * Set the current cursor resolver callback.
     */
    public static function currentCursorResolver(Closure $resolver): void
    {
        static::$currentCursorResolver = $resolver;
    }

    /**
     * Get an iterator for the items.
     */
    public function getIterator(): Traversable
    {
        return $this->items->getIterator();
    }

    /**
     * Determine if the list of items is empty.
     */
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * Determine if the list of items is not empty.
     */
    public function isNotEmpty(): bool
    {
        return $this->items->isNotEmpty();
    }

    /**
     * Get the number of items for the current page.
     */
    public function count(): int
    {
        return $this->items->count();
    }

    /**
     * Get the paginator's underlying collection.
     */
    public function getCollection(): Collection|ModelCollection
    {
        return $this->items;
    }

    /**
     * Set the paginator's underlying collection.
     */
    public function setCollection(Collection $collection): static
    {
        $this->items = $collection;

        return $this;
    }

    /**
     * Get the paginator options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Determine if the given item exists.
     */
    public function offsetExists(mixed $key): bool
    {
        return $this->items->has($key);
    }

    /**
     * Get the item at the given offset.
     */
    public function offsetGet(mixed $key): mixed
    {
        return $this->items->get($key);
    }

    /**
     * Set the item at the given offset.
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->items->put($key, $value);
    }

    /**
     * Unset the item at the given key.
     */
    public function offsetUnset(mixed $key): void
    {
        $this->items->forget($key);
    }

    /**
     * Get the cursor parameter value from a pivot model if applicable.
     */
    protected function getPivotParameterForItem(ArrayAccess|stdClass $item, string $parameterName): ?string
    {
        $table = Str::beforeLast($parameterName, '.');
        if (is_object($item) && method_exists($item, 'getRelations')) {
            foreach ($item->getRelations() as $relation) {
                if ($relation instanceof Pivot && $relation->getTable() === $table) {
                    return $this->ensureParameterIsPrimitive(
                        $relation->getAttribute(Str::afterLast($parameterName, '.'))
                    );
                }
            }
        }
        return null;
    }

    /**
     * Ensure the parameter is a primitive type.
     *
     * This can resolve issues that arise the developer uses a value object for an attribute.
     */
    protected function ensureParameterIsPrimitive(mixed $parameter): mixed
    {
        return is_object($parameter) && method_exists($parameter, '__toString')
            ? (string) $parameter
            : $parameter;
    }

    /**
     * Add an array of query string values.
     */
    protected function appendArray(array $keys): static
    {
        foreach ($keys as $key => $value) {
            $this->addQuery($key, $value);
        }

        return $this;
    }

    /**
     * Add a query string value to the paginator.
     */
    protected function addQuery(string $key, string $value): static
    {
        if ($key !== $this->cursorName) {
            $this->query[$key] = $value;
        }

        return $this;
    }

    /**
     * Build the full fragment portion of a URL.
     */
    protected function buildFragment(): string
    {
        return $this->fragment ? '#' . $this->fragment : '';
    }
}
