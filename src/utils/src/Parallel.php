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

namespace Hyperf\Utils;

class Parallel
{
    /**
     * @var callable[]
     */
    private $callbacks = [];

    public function add(callable $callable, ?string $key = null)
    {
        if ($key) {
            $this->callbacks[$key] = $callable;
        } else {
            $this->callbacks[] = $callable;
        }
    }

    public function wait(): array
    {
        $waitGroup = new WaitGroup();
        $result = [];
        foreach ($this->callbacks as $key => $callback) {
            $waitGroup->add();
            Coroutine::create(function () use ($callback, $key, $waitGroup, &$result) {
                $result[$key] = $callback();
                $waitGroup->done();
            });
        }
        $waitGroup->wait();
        return $result;
    }
}
