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

use Hyperf\Coroutine\Exception\ParallelExecutionException;
use Hyperf\Engine\Channel;
use Throwable;

use function sprintf;

class Parallel
{
    /**
     * @var callable[]
     */
    protected array $callbacks = [];

    protected ?Channel $concurrentChannel = null;

    protected array $results = [];

    /**
     * @var Throwable[]
     */
    protected array $throwables = [];

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
        $wg = new WaitGroup();
        $wg->add(count($this->callbacks));
        foreach ($this->callbacks as $key => $callback) {
            $this->concurrentChannel && $this->concurrentChannel->push(true);
            $this->results[$key] = null;
            Coroutine::create(function () use ($callback, $key, $wg) {
                try {
                    $this->results[$key] = $callback();
                } catch (Throwable $throwable) {
                    $this->throwables[$key] = $throwable;
                    unset($this->results[$key]);
                } finally {
                    $this->concurrentChannel && $this->concurrentChannel->pop();
                    $wg->done();
                }
            });
        }
        $wg->wait();
        if ($throw && ($throwableCount = count($this->throwables)) > 0) {
            $message = 'Detecting ' . $throwableCount . ' throwable occurred during parallel execution:' . PHP_EOL . $this->formatThrowables($this->throwables);
            $executionException = new ParallelExecutionException($message);
            $executionException->setResults($this->results);
            $executionException->setThrowables($this->throwables);
            unset($this->results, $this->throwables);
            throw $executionException;
        }
        return $this->results;
    }

    public function count(): int
    {
        return count($this->callbacks);
    }

    public function clear(): void
    {
        $this->callbacks = [];
        $this->results = [];
        $this->throwables = [];
    }

    /**
     * Format throwables into a nice list.
     *
     * @param Throwable[] $throwables
     */
    private function formatThrowables(array $throwables): string
    {
        $output = '';
        foreach ($throwables as $key => $value) {
            $output .= sprintf('(%s) %s: %s' . PHP_EOL . '%s' . PHP_EOL, $key, get_class($value), $value->getMessage(), $value->getTraceAsString());
        }
        return $output;
    }
}
