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

namespace Hyperf\Paginator\Contract;

use Hyperf\Paginator\Cursor;

interface CursorPaginator
{
    /**
     * Get the URL for a given cursor.
     */
    public function url(?Cursor $cursor): string;

    /**
     * Add a set of query string values to the paginator.
     */
    public function appends(null|array|string $key, ?string $value = null): static;

    /**
     * Get / set the URL fragment to be appended to URLs.
     */
    public function fragment(?string $fragment = null): null|static|string;

    /**
     * Add all current query string values to the paginator.
     */
    public function withQueryString(): static;

    /**
     * Get the URL for the previous page, or null.
     */
    public function previousPageUrl(): ?string;

    /**
     * The URL for the next page, or null.
     */
    public function nextPageUrl(): ?string;

    /**
     * Get all of the items being paginated.
     */
    public function items(): array;

    /**
     * Get the "cursor" of the previous set of items.
     */
    public function previousCursor(): ?Cursor;

    /**
     * Get the "cursor" of the next set of items.
     */
    public function nextCursor(): ?Cursor;

    /**
     * Determine how many items are being shown per page.
     */
    public function perPage(): int;

    /**
     * Get the current cursor being paginated.
     */
    public function cursor(): ?Cursor;

    /**
     * Determine if there are enough items to split into multiple pages.
     */
    public function hasPages(): bool;

    /**
     * Get the base path for paginator generated URLs.
     */
    public function path(): ?string;

    /**
     * Determine if the list of items is empty or not.
     */
    public function isEmpty(): bool;

    /**
     * Determine if the list of items is not empty.
     */
    public function isNotEmpty(): bool;
}
