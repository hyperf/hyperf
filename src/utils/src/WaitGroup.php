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

use Swoole\Coroutine\Channel as SwooleChannel;

class WaitGroup
{
    /**
     * @var int
     */
    private $counter = 0;

    /**
     * @var SwooleChannel
     */
    private $channel;

    public function __construct()
    {
        $this->channel = new SwooleChannel();
    }

    public function add(int $incr = 1): void
    {
        $this->counter += $incr;
    }

    public function done(): void
    {
        $this->channel->push(true);
    }

    public function wait(): void
    {
        while ($this->counter--) {
            $this->channel->pop();
        }
    }
}
