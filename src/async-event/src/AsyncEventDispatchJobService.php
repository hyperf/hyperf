<?php

declare(strict_types=1);
/**
 * This file is part of MangaToon server projects.
 */
namespace Hyperf\AsyncEvent;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;

class AsyncEventDispatchJobService
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    public function __construct(DriverFactory $driverFactory)
    {
        $this->driver = $driverFactory->get('async_event_queue');
    }

    /**
     * @param $object
     */
    public function push(object $object, int $delay = 0): bool
    {
        $this->driver->push(new AsyncEventDispatchJob($object), $delay);
        return true;
    }
}
