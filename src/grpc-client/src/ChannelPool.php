<?php

namespace Hyperf\GrpcClient;


use Swoole\Coroutine\Channel;

class ChannelPool extends \SplQueue
{

    private static $instance;

    public static function getInstance(): self
    {
        return static::$instance ?? (static::$instance = new ChannelPool);
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