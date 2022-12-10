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

            $socket = new Socket(AF_INET, SOCK_DGRAM, SOL_UDP);
            $socket->connect($ip, $port, 0.5);

            defer(function () use ($socket) {
                $socket->close();
            });

            $socket->send($chunk);

            return;
        }

        return $proceedingJoinPoint->process();
    }
}
