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

    public function wait(): array
    {
        $result = [];
        $done = new Channel(count($this->callbacks));
        foreach ($this->callbacks as $key => $callback) {
            $this->concurrentChannel && $this->concurrentChannel->push(true);
            Coroutine::create(function () use ($callback, $key, $done, &$result) {
                try {
                    $result[$key] = call($callback);
                } catch (\Throwable $t) {
                    $done->push($t);
                    return;
                } finally {
                    $this->concurrentChannel && $this->concurrentChannel->pop();
                }
                $done->push(true);
            });
        }
        for ($i = 0; $i < count($this->callbacks); ++$i) {
            $ok = $done->pop();
            if ($ok !== true) {
                throw $ok;
            }
        }
        return $result;
    }

    public function clear(): void
    {
        $this->callbacks = [];
    }
}
