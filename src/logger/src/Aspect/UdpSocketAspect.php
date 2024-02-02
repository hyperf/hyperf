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

use Hyperf\Context\Context;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Monolog\Handler\SyslogUdp\UdpSocket;
use Socket;

use function Hyperf\Coroutine\defer;

/**
 * @property int $port
 */
class UdpSocketAspect extends AbstractAspect
{
    public array $classes = [
        UdpSocket::class . '::getSocket',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! Coroutine::inCoroutine()) {
            return $proceedingJoinPoint->process();
        }

        $instance = $proceedingJoinPoint->getInstance();

        return Context::getOrSet(
            spl_object_hash($instance),
            function () use ($instance) {
                $port = (fn () => $this->port)->call($instance);
                [$domain, $protocol] = $port === 0 ? [AF_UNIX, IPPROTO_IP] : [AF_INET, SOL_UDP];
                $socket = socket_create($domain, SOCK_DGRAM, $protocol);
                defer(function () use ($socket) {
                    if ($socket instanceof Socket) {
                        socket_close($socket);
                    }
                });
                return $socket;
            }
        );
    }
}
