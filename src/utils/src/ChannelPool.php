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

use Hyperf\Engine\Channel;
use SplQueue;

class ChannelPool extends SplQueue
{
    private static ?ChannelPool $instance = null;

    public static function getInstance(): self
    {
        return static::$instance ??= new self();
    }

    public function get(): Channel
    {
        return $this->isEmpty() ? new Channel(1) : $this->pop();
    }

    public function release(Channel $channel)
    {
        $channel->errCode = 0;
        $this->push($channel);
    }
}
