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
use Hyperf\Utils\Coroutine;
use Swoole\Coroutine\Client;

class UdpSocketAspect extends AbstractAspect
{
    public array $classes = [
        \Monolog\Handler\SyslogUdp\UdpSocket::class . '::send',
        \Monolog\Handler\SyslogUdp\UdpSocket::class . '::close',
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

            $socket = new Client(SWOOLE_SOCK_UDP);
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
