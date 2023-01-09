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
namespace Hyperf\Utils\Exception;

use RuntimeException;
use Throwable;

class MultipleItemsFoundException extends RuntimeException
{
    /**
     * The number of items found.
     *
     * @var int
     */
    public $count;

    /**
     * Create a new exception instance.
     *
     * @param int $count
     * @param int $code
     * @param null|Throwable $previous
     */
    public function __construct($count, $code = 0, $previous = null)
    {
        $this->count = $count;

        parent::__construct("{$count} items were found.", $code, $previous);
    }

    /**
     * Get the number of items found.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }
}
