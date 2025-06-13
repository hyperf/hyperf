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

namespace Hyperf\Collection;

use RuntimeException;
use Throwable;

class MultipleItemsFoundException extends RuntimeException
{
    /**
     * The number of items found.
     */
    public int $count;

    /**
     * Create a new exception instance.
     *
     * @param null|Throwable $previous
     */
    public function __construct(int $count, int $code = 0, $previous = null)
    {
        $this->count = $count;

        parent::__construct("{$count} items were found.", $code, $previous);
    }

    /**
     * Get the number of items found.
     */
    public function getCount(): int
    {
        return $this->count;
    }
}
