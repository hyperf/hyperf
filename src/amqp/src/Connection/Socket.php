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

namespace Hyperf\Amqp\Connection;

use Closure;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Client;
use Swoole\Timer;

class Socket
{
    protected $channel;

    protected $timerId;

    public function __construct(Client $client, Closure $heatbeat)
    {
        $this->channel = new Channel(1);
        $this->channel->push($client);

        $this->timerId = Timer::tick(2000, function () use ($heatbeat) {
            try {
                $heatbeat();
            } catch (\Throwable $exception) {
                var_dump($exception->getMessage());
            }
        });
    }

    public function __destruct()
    {
        Timer::clear($this->timerId);
    }

    public function __call($name, $arguments)
    {
        $socket = $this->channel->pop();

        $result = $socket->{$name}(...$arguments);

        $this->channel->push($socket);

        return $result;
    }

    public function __get($name)
    {
        $socket = $this->channel->pop();

        $result = $socket->{$name};

        $this->channel->push($socket);

        return $result;
    }
}
