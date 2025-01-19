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

namespace Hyperf\Coroutine;

/**
 * @method bool isFull()
 * @method bool isEmpty()
 */
class WaitConcurrent extends Concurrent
{
    protected WaitGroup $waitGroup;

    public function __construct(protected int $limit)
    {
        parent::__construct($limit);
        $this->waitGroup = new WaitGroup();
    }

    public function create(callable $callable): void
    {
        $this->waitGroup->add();

        $callable = function () use ($callable) {
            try {
                $callable();
            } finally {
                $this->waitGroup->done();
            }
        };

        parent::create($callable);
    }

    public function wait(float $timeout = -1): bool
    {
        return $this->waitGroup->wait($timeout);
    }
}
