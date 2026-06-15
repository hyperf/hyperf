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

namespace Hyperf\Di\Aop;

use Hyperf\Stdlib\SplPriorityQueue;
use InvalidArgumentException;

/**
 * @mixin SplPriorityQueue
 */
class AstVisitorRegistry
{
    protected static ?SplPriorityQueue $queue = null;

    protected static array $values = [];

    public static function __callStatic($name, $arguments)
    {
        $queue = static::getQueue();
        if (method_exists($queue, $name)) {
            return $queue->{$name}(...$arguments);
        }
        throw new InvalidArgumentException('Invalid method for ' . __CLASS__);
    }

    /**
     * 经测试 enterNode 和 leaveNode 符合洋葱模型，权重高的 enterNode 先执行，但是 leaveNode 后执行
     * According to tests, both enterNode and leaveNode conform to the onion model: enterNode with higher priority executes first, while leaveNode executes later.
     * @param string $value
     * @param int $priority
     */
    public static function insert($value, $priority = 0)
    {
        static::$values[] = $value;
        return static::getQueue()->insert($value, $priority);
    }

    public static function exists($value): bool
    {
        return in_array($value, static::$values);
    }

    public static function getQueue(): SplPriorityQueue
    {
        if (! static::$queue instanceof SplPriorityQueue) {
            static::$queue = new SplPriorityQueue();
        }
        return static::$queue;
    }
}
