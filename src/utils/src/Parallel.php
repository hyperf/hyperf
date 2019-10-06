<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Utils;

class Parallel
{
    /**
     * @var callable[]
     */
    private $callbacks = [];

    public function add(callable $callable, $key = null)
    {
        if (is_string($key)) {
            $this->callbacks[$key] = $callable;
        } else {
            $this->callbacks[] = $callable;
        }
    }

    public function wait(): array
    {
        $result = [];
        $wg = new WaitGroup();
        $wg->add(count($this->callbacks));
        foreach ($this->callbacks as $key => $callback) {
            Coroutine::create(function () use ($callback, $key, $wg, &$result) {
                $result[$key] = call($callback);
                $wg->done();
            });
        }
        $wg->wait();
        return $result;
    }
}
