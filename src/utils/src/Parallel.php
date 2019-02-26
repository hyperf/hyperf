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

use Swoole\Coroutine\Channel;

class Parallel
{
    /**
     * @var callable[]
     */
    private $callbacks = [];

    public function add(callable $callable)
    {
        $this->callbacks[] = $callable;
    }

    public function wait(): array
    {
        $map = [];
        $channel = new Channel(count($this->callbacks));
        foreach ($this->callbacks as $key => $callback) {
            Coroutine::create(function () use ($callback, $key, $channel) {
                $channel->push([
                    'key' => $key,
                    'value' => $callback(),
                ]);
            });
        }
        while (! $channel->isEmpty()) {
            $result = $channel->pop();
            $map[$result['key']] = $result['value'];
        }
        return $map;
    }
}
