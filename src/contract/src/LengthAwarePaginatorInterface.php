<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Contract;

interface LengthAwarePaginatorInterface extends PaginatorInterface
{
    /**
     * Create a range of pagination URLs.
     */
    public function getUrlRange(int $start, int $end): array;

    /**
     * Determine the total number of items in the data store.
     */
    public function total(): int;

    /**
     * Get the page number of the last available page.
     */
    public function lastPage(): int;
}
