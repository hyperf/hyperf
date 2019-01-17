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

namespace Hyperf\Framework\Constants;

class SwooleEvent
{

    /**
     * Swoole onStart event.
     */
    const ON_START = 'start';

    /**
     * Swoole onWorkerStart event.
     */
    const ON_WORKER_START = 'workerStart';

    /**
     * Swoole onRequest event.
     */
    const ON_REQUEST = 'request';

    /**
     * Before server start, it's not a swoole event.
     */
    const ON_BEFORE_START = 'beforeStart';

    public static function isSwooleEvent($event): bool
    {
        if (in_array($event, [
            self::ON_BEFORE_START,
        ])) {
            return false;
        }
        return true;
    }
}
