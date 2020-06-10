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

    /**
     * @param int $ms millisecond
     */
    public static function tick(int $ms, callable $callback, ...$params): int
    {
        $timerId = static::generateTimerId();
        Coroutine::create(function () use ($ms, $callback, $timerId, $params) {
            static::initInfoData($timerId, $ms);
            retry(INF, function () use ($ms, $callback, $timerId, $params) {
                while (true) {
                    static::handleRemove($timerId);
                    static::incrRound();
                    static::incrInfoData($timerId);

                    // handler worker exit
                    $workerExited = CoordinatorManager::until(Constants::WORKER_EXIT)->yield($ms / 1000);
                    if ($workerExited || ! static::exists($timerId)) {
                        break;
                    }

                    $callback($timerId, ...$params);
                }
            }, $ms);
        });

        return $timerId;
    }

    public static function after(int $ms, callable $callback, ...$params): int
    {
        $timerId = static::generateTimerId();
        Coroutine::create(function () use ($ms, $callback, $timerId, $params) {
            static::initInfoData($timerId, $ms);
            retry(INF, function () use ($ms, $callback, $timerId, $params) {
                static::handleRemove($timerId);
                static::incrRound();
                static::incrInfoData($timerId);

                $coordinator = CoordinatorManager::until(Constants::WORKER_EXIT);
                $workerExited = $coordinator->yield($ms / 1000);
                if ($workerExited || ! static::exists($timerId)) {
                    return;
                }

                /**
                 * Incompatible: Swoole\Timer::after() will not pass the timer id as the first parameter to the callback.
                 */
                $callback($timerId, ...$params);
            }, $ms);
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

    protected static function initInfoData(int $timeId, int $interval): void
    {
        if (! isset(static::$infos[$timeId])) {
            return;
        }
        static::$infos[$timeId]['interval'] = $interval;
    }

    protected static function incrInfoData(int $timeId): void
    {
        if (! isset(static::$infos[$timeId])) {
            return;
        }
        static::$infos[$timeId]['exec_msec'] += static::$infos[$timeId]['interval'];
        static::$infos[$timeId]['round'] += 1;
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
