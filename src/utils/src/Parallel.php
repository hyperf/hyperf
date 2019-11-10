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

namespace Hyperf\Utils;

use Hyperf\Utils\Exception\ParallelExecutionException;
use Swoole\Coroutine\Channel;

class Parallel
{
    /**
     * @var callable[]
     */
    private $callbacks = [];

    /**
     * @var null|Channel
     */
    private $concurrentChannel;

    /**
     * @param int $concurrent if $concurrent is equal to 0, that means unlimit
     */
    public function __construct(int $concurrent = 0)
    {
        if ($concurrent > 0) {
            $this->concurrentChannel = new Channel($concurrent);
        }
    }

    public function add(callable $callable, $key = null)
    {
        if (is_string($key)) {
            $this->callbacks[$key] = $callable;
        } else {
            $this->callbacks[] = $callable;
        }
    }

    public function wait(bool $throw = true): array
    {
        $result = [];
        $wg = new WaitGroup();
        $wg->add(count($this->callbacks));
        $throwables = [];
        foreach ($this->callbacks as $key => $callback) {
            $this->concurrentChannel && $this->concurrentChannel->push(true);
            Coroutine::create(function () use ($callback, $key, $wg, &$result, &$throwables) {
                try {
                    $result[$key] = call($callback);
                } catch (\Throwable $throwable) {
                    $throwables[$key] = $throwable;
                } finally {
                    $this->concurrentChannel && $this->concurrentChannel->pop();
                    $wg->done();
                }
            });
        }
        $wg->wait();
        if ($throw && count($throwables) > 0) {
            $msg = 'At least one throwable occurred during parallel execution:' . PHP_EOL . $this->formatThrowables($throwables);
            $pee = new ParallelExecutionException($msg);
            $pee->setResults($result);
            $pee->setThrowables($throwables);
            throw $pee;
        }
        return $result;
    }

    public function clear(): void
    {
        $this->callbacks = [];
    }

    /**
     * Format throwables into a nice list.
     */
    private function formatThrowables(array $e): string
    {
        $out = '';
        foreach ($e as $key => $value) {
            $out .= \sprintf('(%s) %s: %s' . PHP_EOL, $key, get_class($value), $value->getMessage());
        }
        return $out;
    }
}
