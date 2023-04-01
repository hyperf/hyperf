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

use Countable;
use Hyperf\Collection\Collection;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use IteratorAggregate;
use JsonSerializable;
use RuntimeException;

class Paginator extends AbstractPaginator implements Arrayable, Countable, IteratorAggregate, JsonSerializable, Jsonable
{
    /**
     * Determine if there are more items in the data source.
     */
    protected bool $hasMore;

    /**
     * Create a new paginator instance.
     *
     * @param array $options (path, query, fragment, pageName)
     * @param mixed $items
     */
    public function __construct($items, int $perPage, ?int $currentPage = null, array $options = [])
    {
        $this->options = $options;

        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }

        $this->perPage = $perPage;
        $this->currentPage = $this->setCurrentPage($currentPage);
        $this->path = $this->path !== '/' ? rtrim($this->path, '/') : $this->path;

        $this->setItems($items);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Get the URL for the next page.
     */
    public function nextPageUrl(): ?string
    {
        if ($this->hasMorePages()) {
            return $this->url($this->currentPage() + 1);
        }
        return null;
    }

    /**
     * Render the paginator using the given view.
     */
    public function render(?string $view = null, array $data = []): string
    {
        if ($view) {
            throw new RuntimeException('WIP.');
        }
        return json_encode($data, 0);
    }

    /**
     * Manually indicate that the paginator does have more pages.
     */
    public function hasMorePagesWhen(bool $hasMore = true): self
    {
        $this->hasMore = $hasMore;

        return $this;
    }

    /**
     * Determine if there are more items in the data source.
     */
    public function hasMorePages(): bool
    {
        return $this->hasMore;
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return [
            'current_page' => $this->currentPage(),
            'data' => $this->items->toArray(),
            'first_page_url' => $this->url(1),
            'from' => $this->firstItem(),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path,
            'per_page' => $this->perPage(),
            'prev_page_url' => $this->previousPageUrl(),
            'to' => $this->lastItem(),
        ];
    }

    /**
     * Convert the object into something JSON serializable.
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Get the current page for the request.
     */
    protected function setCurrentPage(?int $currentPage): int
    {
        $currentPage = $currentPage ?: static::resolveCurrentPage();

        return $this->isValidPageNumber($currentPage) ? $currentPage : 1;
    }

    /**
     * Set the items for the paginator.
     * @param mixed $items
     */
    protected function setItems($items): void
    {
        $this->items = $items instanceof Collection ? $items : Collection::make($items);

        $this->hasMore = $this->items->count() > $this->perPage;

        $this->items = $this->items->slice(0, $this->perPage);
    }
}
