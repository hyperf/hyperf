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
use Countable;
use Hyperf\Collection\Collection;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use Hyperf\Paginator\Contract\CursorPaginator as CursorPaginatorContract;
use IteratorAggregate;
use JsonSerializable;

class CursorPaginator extends AbstractCursorPaginator implements ArrayAccess, Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable, CursorPaginatorContract
{
    /**
     * Indicates whether there are more items in the data source.
     *
     * @return bool
     */
    protected bool $hasMore;

    /**
     * Create a new paginator instance.
     */
    public function __construct(mixed $items, int $perPage, ?Cursor $cursor = null, array $options = [])
    {
        $this->options = $options;

        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }

        $this->perPage = (int) $perPage;
        $this->cursor = $cursor;
        $this->path = $this->path !== '/' ? rtrim($this->path, '/') : $this->path;

        $this->setItems($items);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Determine if there are more items in the data source.
     */
    public function hasMorePages(): bool
    {
        return (is_null($this->cursor) && $this->hasMore)
            || (! is_null($this->cursor) && $this->cursor->pointsToNextItems() && $this->hasMore)
            || (! is_null($this->cursor) && $this->cursor->pointsToPreviousItems());
    }

    /**
     * Determine if there are enough items to split into multiple pages.
     */
    public function hasPages(): bool
    {
        return ! $this->onFirstPage() || $this->hasMorePages();
    }

    /**
     * Determine if the paginator is on the first page.
     */
    public function onFirstPage(): bool
    {
        return is_null($this->cursor) || ($this->cursor->pointsToPreviousItems() && ! $this->hasMore);
    }

    /**
     * Determine if the paginator is on the last page.
     */
    public function onLastPage(): bool
    {
        return ! $this->hasMorePages();
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return [
            'data' => $this->items->toArray(),
            'path' => $this->path(),
            'per_page' => $this->perPage(),
            'next_cursor' => $this->nextCursor()?->encode(),
            'next_page_url' => $this->nextPageUrl(),
            'prev_cursor' => $this->previousCursor()?->encode(),
            'prev_page_url' => $this->previousPageUrl(),
        ];
    }

    /**
     * Convert the object into something JSON serializable.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR | $options);
    }

    /**
     * Set the items for the paginator.
     * @param mixed $items
     */
    protected function setItems($items)
    {
        $this->items = $items instanceof Collection ? $items : Collection::make($items);

        $this->hasMore = $this->items->count() > $this->perPage;

        $this->items = $this->items->slice(0, $this->perPage);

        if (! is_null($this->cursor) && $this->cursor->pointsToPreviousItems()) {
            $this->items = $this->items->reverse()->values();
        }
    }
}
