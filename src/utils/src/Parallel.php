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
        if (is_null($key)) {
            $this->callbacks[] = $callable;
        } else {
            $this->callbacks[$key] = $callable;
        }
    }

    public function wait(bool $throw = true): array
    {
        $result = $throwables = [];
        $wg = new WaitGroup();
        $wg->add(count($this->callbacks));
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
        if ($throw && ($throwableCount = count($throwables)) > 0) {
            $message = 'Detecting ' . $throwableCount . ' throwable occurred during parallel execution:' . PHP_EOL . $this->formatThrowables($throwables);
            $executionException = new ParallelExecutionException($message);
            $executionException->setResults($result);
            $executionException->setThrowables($throwables);
            throw $executionException;
        }
        return $result;
    }

    public function clear(): void
    {
        $this->callbacks = [];
    }

    /**
     * Format throwables into a nice list.
     *
     * @param \Throwable[] $throwables
     */
    private function formatThrowables(array $throwables): string
    {
        $output = '';
        foreach ($throwables as $key => $value) {
            $output .= \sprintf('(%s) %s: %s' . PHP_EOL . '%s' . PHP_EOL, $key, get_class($value), $value->getMessage(), $value->getTraceAsString());
        }
        return $output;
    }
}
