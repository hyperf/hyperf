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

namespace Hyperf\ReactiveX;

use Exception;
use Hyperf\Engine\Channel;
use Hyperf\ReactiveX\Observable\ChannelObservable;
use Hyperf\ReactiveX\Observable\CoroutineObservable;
use Hyperf\ReactiveX\Observable\EventObservable;
use Hyperf\ReactiveX\Observable\HttpRouteObservable;
use Rx\Observable as RxObservable;
use Rx\SchedulerInterface;
use stdClass;

class Observable
{
    public static function __callStatic($method, $arguments)
    {
        return call_user_func([RxObservable::class, $method], ...$arguments);
    }

    public static function fromEvent(string $eventName, ?SchedulerInterface $scheduler = null): EventObservable
    {
        return new EventObservable($eventName, $scheduler);
    }

    /**
     * @throws Exception
     */
    public static function fromChannel(Channel $channel, ?SchedulerInterface $scheduler = null): ChannelObservable
    {
        return new ChannelObservable($channel, $scheduler);
    }

    /**
     * @param array<string>|string $httpMethod
     * @param null|callable|string $callback
     * @throws Exception
     */
    public static function fromHttpRoute(array|string $httpMethod, string $uri, $callback = null, ?SchedulerInterface $scheduler = null, string $serverName = 'http'): HttpRouteObservable
    {
        return new HttpRouteObservable($httpMethod, $uri, $callback, $scheduler, $serverName);
    }

    /**
     * @param array<callable>|callable $callables
     * @throws Exception
     */
    public static function fromCoroutine(array|callable $callables, ?SchedulerInterface $scheduler = null): CoroutineObservable
    {
        if (is_callable($callables)) {
            $callables = [$callables];
        }
        return new CoroutineObservable($callables, $scheduler);
    }

    public static function unwrap(RxObservable $observable): array
    {
        $chan = new Channel(1);
        $observable->subscribe(
            function ($x) use ($chan) {
                $send = new stdClass();
                $send->data = $x;
                $chan->push($send);
            },
            function ($x) {
                throw $x;
            },
            function () use ($chan) {
                $chan->close();
            }
        );
        $results = [];
        $receive = $chan->pop();
        while ($receive !== false) {
            $results[] = $receive->data;
            $receive = $chan->pop();
        }
        return $results;
    }

    public static function unwrapOne(RxObservable $observable)
    {
        $chan = new Channel(1);
        $id = $observable->subscribe(
            function ($x) use ($chan) {
                $send = new stdClass();
                $send->data = $x;
                $chan->push($send, 1);
            },
            function ($x) {
                throw $x;
            },
            function () use ($chan) {
                $chan->close();
            }
        );
        $receive = $chan->pop();
        $id->dispose();
        if ($receive !== false) {
            return $receive->data;
        }
        throw new Exception('Found no element from observable');
    }
}
