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
use Hyperf\Engine\Socket;
use Hyperf\Utils\Coroutine;
use Monolog\Handler\SyslogUdp\UdpSocket;

class UdpSocketAspect extends AbstractAspect
{
    public array $classes = [
        UdpSocket::class . '::send',
        UdpSocket::class . '::close',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (Coroutine::inCoroutine()) {
            if ($proceedingJoinPoint->methodName == 'close') {
                return;
            }

            /** @var string $chunk */
            $chunk = $proceedingJoinPoint->arguments['keys']['chunk'] ?? '';
            [$ip, $port] = (fn () => [$this->ip, $this->port])->call($proceedingJoinPoint->getInstance());

            $key = sprintf('%s_%s_%s_%s', $proceedingJoinPoint->className, 'Socket', $ip, $port);
            $socket = Context::getOrSet($key, fn () => tap(new Socket(AF_INET, SOCK_DGRAM, SOL_UDP), function (Socket $socket) use ($ip, $port) {
                $socket->connect($ip, $port, 0.5);
                defer(fn () => $socket->isClosed() || $socket->close());
            }));

            $socket->send($chunk);

            return;
        }

        return $proceedingJoinPoint->process();
    }
}
