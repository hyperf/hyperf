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

use Swoole\Coroutine\Channel as SwooleChannel;

class WaitGroup
{
    /**
     * @var SwooleChannel
     */
    private $channel;

    /**
     * @var int
     */
    public $counter = 0;

    public function __construct()
    {
        $this->channel = new SwooleChannel();
    }

    public function add(): void
    {
        ++$this->counter;
    }

    public function done(): void
    {
        $this->channel->push(true);
    }

    public function wait(): void
    {
        for ($i = 0; $i < $this->counter; ++$i) {
            $this->channel->pop();
        }
    }
}
