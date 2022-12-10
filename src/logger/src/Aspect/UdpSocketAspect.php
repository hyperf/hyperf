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
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Monolog\Handler\SyslogUdp\UdpSocket;

class UdpSocketAspect extends AbstractAspect
{
    public array $classes = [
        UdpSocket::class . '::getSocket',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        [$ip, $port] = (fn () => [$this->ip, $this->port])->call($proceedingJoinPoint->getInstance());
        (fn () => $this->socket = null)->call($proceedingJoinPoint->getInstance());
        $key = sprintf('%s_%s_%s_%s', $proceedingJoinPoint->className, 'Socket', $ip, $port);

        return Context::getOrSet($key, fn () => $proceedingJoinPoint->process());
    }
}
