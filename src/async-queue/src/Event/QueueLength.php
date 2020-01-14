<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\AsyncQueue\Event;

class QueueLength
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var int
     */
    public $length;

    public function __construct(string $key, int $length)
    {
        $this->key = $key;
        $this->length = $length;
    }
}
