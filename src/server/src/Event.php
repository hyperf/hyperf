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
    const ON_START = 'start';

    /**
     * Swoole onWorkerStart event.
     */
    const ON_WORKER_START = 'workerStart';

    /**
     * Swoole onWorkerStop event.
     */
    const ON_WORKER_STOP = 'workerStop';

    /**
     * Swoole onWorkerExit event.
     */
    const ON_WORKER_EXIT = 'workerExit';

    /**
     * Swoole onWorkerError event.
     */
    const ON_WORKER_ERROR = 'workerError';

    /**
     * Swoole onPipeMessage event.
     */
    const ON_PIPE_MESSAGE = 'pipeMessage';

    /**
     * Swoole onRequest event.
     */
    const ON_REQUEST = 'request';

    /**
     * Swoole onReceive event.
     */
    const ON_RECEIVE = 'receive';

    /**
     * Swoole onConnect event.
     */
    const ON_CONNECT = 'connect';

    /**
     * Swoole onHandShake event.
     */
    const ON_HAND_SHAKE = 'handshake';

    /**
     * Swoole onOpen event.
     */
    const ON_OPEN = 'open';

    /**
     * Swoole onMessage event.
     */
    const ON_MESSAGE = 'message';

    /**
     * Swoole onClose event.
     */
    const ON_CLOSE = 'close';

    /**
     * Swoole onTask event.
     */
    const ON_TASK = 'task';

    /**
     * Swoole onFinish event.
     */
    const ON_FINISH = 'finish';

    /**
     * Swoole onShutdown event.
     */
    const ON_SHUTDOWN = 'shutdown';

    /**
     * Swoole onPacket event.
     */
    const ON_PACKET = 'packet';

    /**
     * Swoole onManagerStart event.
     */
    const ON_MANAGER_START = 'managerStart';

    /**
     * Swoole onManagerStop event.
     */
    const ON_MANAGER_STOP = 'managerStop';

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
