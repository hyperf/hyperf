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
namespace Hyperf\Amqp\Connection;

use PhpAmqpLib\Connection\AbstractConnection;

class AMQPSwooleConnection extends AbstractConnection
{
    public function __construct(
        string $host,
        int $port,
        string $user,
        string $password,
        string $vhost = '/',
        bool $insist = false,
        string $loginMethod = 'AMQPLAIN',
        $loginResponse = null,
        string $locale = 'en_US',
        float $connectionTimeout = 3.0,
        float $readWriteTimeout = 3.0,
        $context = null,
        bool $keepalive = false,
        int $heartbeat = 0
    ) {
        if ($keepalive) {
            $io = new KeepaliveIO($host, $port, $connectionTimeout, $readWriteTimeout, $context, $keepalive, $heartbeat);
        } else {
            $io = new SwooleIO($host, $port, $connectionTimeout, $readWriteTimeout, $context, $keepalive, $heartbeat);
        }

        parent::__construct(
            $user,
            $password,
            $vhost,
            $insist,
            $loginMethod,
            $loginResponse,
            $locale,
            $io,
            $heartbeat,
            (int) $connectionTimeout
        );
    }

    public function getIO()
    {
        return $this->io;
    }
}
