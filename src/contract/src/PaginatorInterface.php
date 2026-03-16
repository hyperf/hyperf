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

namespace Hyperf\Contract;

interface PaginatorInterface
{
    /**
     * Get the URL for a given page.
     */
    public function url(int $page): string;

    /**
     * Add a set of query string values to the paginator.
     *
     * @param array|string $key
     * @return static
     */
    public function appends($key, ?string $value = null);

    /**
     * Get / set the URL fragment to be appended to URLs.
     *
     * @return static|string
     */
    public function fragment(?string $fragment = null);

    /**
     * The URL for the next page, or null.
     */
    public function nextPageUrl(): ?string;

    /**
     * Get the URL for the previous page, or null.
     */
    public function previousPageUrl(): ?string;

    /**
     * Get all of the items being paginated.
     */
    public function items(): array;

    /**
     * Get the "index" of the first item being paginated.
     */
    public function firstItem(): ?int;

    /**
     * Get the "index" of the last item being paginated.
     */
    public function lastItem(): ?int;

    /**
     * Determine how many items are being shown per page.
     */
    public function perPage(): int;

    /**
     * Determine the current page being paginated.
     */
    public function currentPage(): int;

    /**
     * Determine if there are enough items to split into multiple pages.
     */
    public function hasPages(): bool;

    /**
     * Determine if there is more items in the data store.
     */
    public function hasMorePages(): bool;

    /**
     * Determine if the list of items is empty or not.
     */
    public function isEmpty(): bool;

    /**
     * Determine if the list of items is not empty.
     */
    public function isNotEmpty(): bool;

    /**
     * Render the paginator using a given view.
     */
    public function render(?string $view = null, array $data = []): string;
}
