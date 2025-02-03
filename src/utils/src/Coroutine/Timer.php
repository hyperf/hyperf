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
namespace Hyperf\Utils\Coroutine;

use ArrayIterator;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
use Iterator;

class Timer
{

    /**
     * @var int
     */
    protected static $lastId = 0;

    /**
     * @var array
     */
    protected static $infos = [];

    /**
     * @var array
     */
    protected static $shouldRemoveIds = [];

    /**
     * @var int
     */
    protected static $round = 0;

    public static function tick(int $millisecond, callable $callback, ...$params): int
    {
        $timerId = static::generateTimerId();
        Coroutine::create(function () use ($millisecond, $callback, $timerId, $params) {
            static::initInfoData($timerId, $millisecond);
            retry(INF, function () use ($millisecond, $callback, $timerId, $params) {
                while (true) {
                    static::handleData($timerId);

                    $workerExited = CoordinatorManager::until(Constants::WORKER_EXIT)->yield($millisecond / 1000);
                    if ($workerExited || ! static::exists($timerId)) {
                        break;
                    }

                    $callback($timerId, ...$params);
                }
            }, $millisecond);
        });

        return $timerId;
    }

    public static function after(int $millisecond, callable $callback, ...$params): int
    {
        $timerId = static::generateTimerId();
        Coroutine::create(function () use ($millisecond, $callback, $timerId, $params) {
            static::initInfoData($timerId, $millisecond);
            retry(INF, function () use ($millisecond, $callback, $timerId, $params) {
                static::handleData($timerId);

                $coordinator = CoordinatorManager::until(Constants::WORKER_EXIT);
                $workerExited = $coordinator->yield($millisecond / 1000);
                if ($workerExited || ! static::exists($timerId)) {
                    return;
                }

                /**
                 * Incompatible: Swoole\Timer::after() will not pass the timer id as the first parameter to the callback.
                 */
                $callback($timerId, ...$params);
            }, $millisecond);
        });
        return $timerId;
    }

    public static function info(int $timerId): ?array
    {
        return static::$infos[$timerId] ?? null;
    }

    public static function stats(): array
    {
        $count = count(static::$infos);
        return [
            'initialized' => $count > 0,
            'num' => $count,
            'round' => static::$round
        ];
    }

    public static function list(): Iterator
    {
        return new ArrayIterator(array_keys(static::$infos));
    }

    public static function exists(int $timerId)
    {
        return isset(static::$infos[$timerId]);
    }

    public static function clear(int $timerId): bool
    {
        static::$infos[$timerId]['removed'] = true;
        static::$shouldRemoveIds[$timerId] = 1;
        return true;
    }

    public static function clearAll(): bool
    {
        foreach (static::$infos as $timerId => &$info) {
            if (isset($info['removed']) && ! $info['removed']) {
                $info['removed'] = true;
                static::$shouldRemoveIds[$timerId] = 1;
            }
        }

        return true;
    }

    protected function handleData(int $timerId): void
    {
        static::handleRemove($timerId);
        static::incrRound();
        static::incrInfoData($timerId);
    }

    protected static function initInfoData(int $timerId, int $interval): void
    {
        if (! isset(static::$infos[$timerId])) {
            return;
        }
        static::$infos[$timerId]['interval'] = $interval;
    }

    protected static function incrInfoData(int $timerId): void
    {
        if (! isset(static::$infos[$timerId])) {
            return;
        }
        static::$infos[$timerId]['exec_msec'] += static::$infos[$timerId]['interval'];
        static::$infos[$timerId]['round'] += 1;
    }

    protected static function incrRound(): void
    {
        static::$round++;
    }

    protected static function handleRemove(int $timerId): void
    {
        if (isset(static::$shouldRemoveIds[$timerId])) {
            unset(static::$infos[$timerId]);
            unset(static::$shouldRemoveIds[$timerId]);
        }
    }

    protected static function generateTimerId(): int
    {
        ++static::$lastId;
        static::$infos[static::$lastId] = [
            'exec_msec' => 0,
            'interval' => 0,
            'round' => -1,
            'removed' => false,
        ];
        return static::$lastId;
    }
}
