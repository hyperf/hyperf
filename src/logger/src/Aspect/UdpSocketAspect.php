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

namespace Hyperf\Logger\Aspect;

use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Monolog\Handler\SyslogUdp\UdpSocket;
use Socket;
use WeakMap;

/**
 * @property null|Socket $socket
 */
class UdpSocketAspect extends AbstractAspect
{
    public array $classes = [
        UdpSocket::class . '::getSocket',
    ];

    public static WeakMap $coSockets;

    public function __construct()
    {
        self::$coSockets = new WeakMap();
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! Coroutine::inCoroutine()) {
            return $proceedingJoinPoint->process();
        }

        $instance = $proceedingJoinPoint->getInstance();

        if (isset(self::$coSockets[$instance]) && self::$coSockets[$instance] instanceof Socket) {
            return self::$coSockets[$instance];
        }

        return self::$coSockets[$instance] = (function () use ($proceedingJoinPoint) {
            $nonCoSocket = $this->socket; // Save the socket of non-coroutine.
            $this->socket = null; // Unset the socket of non-coroutine.
            $coSocket = $proceedingJoinPoint->process(); // ReCreate the socket in coroutine.
            $this->socket = $nonCoSocket; // Restore the socket of non-coroutine.
            return $coSocket;
        })->call($instance);
    }
}
