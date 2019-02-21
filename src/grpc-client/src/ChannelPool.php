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

namespace Hyperf\GrpcClient;

use Swoole\Coroutine\Channel;

class ChannelPool extends \SplQueue
{
    private static $instance;

    public static function getInstance(): self
    {
        return static::$instance ?? (static::$instance = new ChannelPool());
    }

    public function get(): Channel
    {
        return $this->isEmpty() ? new Channel(0) : $this->pop();
    }

    public function put(Channel $channel)
    {
        $channel->errCode = 0;
        $this->push($channel);
    }
}
