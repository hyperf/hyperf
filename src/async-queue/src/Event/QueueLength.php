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
namespace Hyperf\AsyncQueue\Event;

use Hyperf\AsyncQueue\Driver\DriverInterface;

class QueueLength
{
    /**
     * @var DriverInterface
     */
    public $driver;

    /**
     * @var string
     */
    public $key;

    /**
     * @var int
     */
    public $length;

    public function __construct(DriverInterface $driver, string $key, int $length)
    {
        $this->driver = $driver;
        $this->key = $key;
        $this->length = $length;
    }
}
