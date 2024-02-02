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

/**
 * @property null|Socket $socket
 */
class UdpSocketAspect extends AbstractAspect
{
    public array $classes = [
        UdpSocket::class . '::getSocket',
    ];

    /**
     * @var Socket[]
     */
    public static array $coSockets = [];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! Coroutine::inCoroutine()) {
            return $proceedingJoinPoint->process();
        }

        $instance = $proceedingJoinPoint->getInstance();
        $hash = spl_object_hash($instance);

        if (isset(self::$coSockets[$hash]) && self::$coSockets[$hash] instanceof Socket) {
            return self::$coSockets[$hash];
        }

        $nonCoSocket = (fn () => $this->socket)->call($instance); // Save the socket of non-coroutine.
        (fn () => $this->socket = null)->call($instance); // Unset the socket of non-coroutine.
        self::$coSockets[$hash] = $proceedingJoinPoint->process(); // ReCreate the socket in coroutine.
        (fn () => $this->socket = $nonCoSocket)->call($instance); // Restore the socket of non-coroutine.

        return self::$coSockets[$hash];
    }
}
