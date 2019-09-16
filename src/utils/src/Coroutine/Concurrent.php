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

namespace Hyperf\Utils\Coroutine;

use Swoole\Coroutine\Channel;

class Concurrent
{
    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var float
     */
    protected $timeout;

    public function __construct($size, $timeout = 10.0)
    {
        $this->channel = new Channel($size);
        $this->timeout = $timeout;
    }

    public function call(callable $callable): void
    {
        while (true) {
            if ($this->channel->push(true, $this->timeout)) {
                break;
            }
        }

        go(function () use ($callable) {
            $callable();
            $this->channel->pop($this->timeout);
        });
    }
}
