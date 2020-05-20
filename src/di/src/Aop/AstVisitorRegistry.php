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
namespace Hyperf\Di\Aop;

/**
 * @mixin \SplPriorityQueue
 */
class AstVisitorRegistry
{
    /**
     * @var \SplPriorityQueue
     */
    protected static $queue;

    public static function __callStatic($name, $arguments)
    {
        $queue = static::getQueue();
        if (method_exists($queue, $name)) {
            return $queue->{$name}(...$arguments);
        }
        throw new \InvalidArgumentException('Invalid method for ' . __CLASS__);
    }

    public static function getQueue(): \SplPriorityQueue
    {
        if (! static::$queue instanceof \SplPriorityQueue) {
            static::$queue = new \SplPriorityQueue();
        }
        return static::$queue;
    }
}
