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

namespace Hyperf\Server;

class Event
{
    /**
     * Swoole onStart event.
     */
    public const ON_START = 'start';

    /**
     * Swoole onWorkerStart event.
     */
    public const ON_WORKER_START = 'workerStart';

    /**
     * Swoole onWorkerStop event.
     */
    public const ON_WORKER_STOP = 'workerStop';

    /**
     * Swoole onWorkerExit event.
     */
    public const ON_WORKER_EXIT = 'workerExit';

    /**
     * Swoole onWorkerError event.
     */
    public const ON_WORKER_ERROR = 'workerError';

    /**
     * Swoole onPipeMessage event.
     */
    public const ON_PIPE_MESSAGE = 'pipeMessage';

    /**
     * Swoole onRequest event.
     */
    public const ON_REQUEST = 'request';

    /**
     * Swoole onReceive event.
     */
    public const ON_RECEIVE = 'receive';

    /**
     * Swoole onConnect event.
     */
    public const ON_CONNECT = 'connect';

    /**
     * Swoole onHandShake event.
     */
    public const ON_HAND_SHAKE = 'handshake';

    /**
     * Swoole onOpen event.
     */
    public const ON_OPEN = 'open';

    /**
     * Swoole onMessage event.
     */
    public const ON_MESSAGE = 'message';

    /**
     * Swoole onClose event.
     */
    public const ON_CLOSE = 'close';

    /**
     * Swoole onTask event.
     */
    public const ON_TASK = 'task';

    /**
     * Swoole onFinish event.
     */
    public const ON_FINISH = 'finish';

    /**
     * Swoole onShutdown event.
     */
    public const ON_SHUTDOWN = 'shutdown';

    /**
     * Swoole onPacket event.
     */
    public const ON_PACKET = 'packet';

    /**
     * Swoole onManagerStart event.
     */
    public const ON_MANAGER_START = 'managerStart';

    /**
     * Swoole onManagerStop event.
     */
    public const ON_MANAGER_STOP = 'managerStop';

    /**
     * Before server start, it's not a swoole event.
     */
    public const ON_BEFORE_START = 'beforeStart';

    public static function isSwooleEvent($event): bool
    {
        if ($event == self::ON_BEFORE_START) {
            return false;
        }
        return true;
    }
}
