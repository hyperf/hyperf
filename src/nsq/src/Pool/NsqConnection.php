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
namespace Hyperf\Nsq\Pool;

use Hyperf\Nsq\MessageBuilder;
use Hyperf\Nsq\Subscriber;
use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Pool\KeepaliveConnection;
use Hyperf\Pool\Pool;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\Socket;

class NsqConnection extends KeepaliveConnection
{
    /**
     * @var array
     */
    protected $config = [
        'host' => 'localhost',
        'port' => 4150,
    ];

    /**
     * @var MessageBuilder
     */
    protected $builder;

    public function __construct(ContainerInterface $container, Pool $pool, array $config)
    {
        $this->config = array_merge($this->config, $config);
        $this->builder = $container->get(MessageBuilder::class);
        if ($pool instanceof NsqPool) {
            $this->name = 'nsq.connection.' . $pool->getName();
        }
        parent::__construct($container, $pool);
    }

    protected function getActiveConnection()
    {
        $socket = new Socket(AF_INET, SOCK_STREAM, 0);
        $host = $this->config['host'];
        $port = $this->config['port'];

        if (! $socket->connect($host, $port)) {
            throw new ConnectionException('Nsq connect failed.');
        }

        if ($socket->send($this->builder->buildMagic()) === false) {
            throw new ConnectionException('Nsq connect failed.');
        }

        $socket->sendAll($this->builder->buildIdentify());

        $reader = new Subscriber($socket);
        $reader->recv();

        if (! $reader->isOk()) {
            $result = $reader->getJsonPayload();
            if (Arr::get($result, 'auth_required') === true) {
                $socket->sendAll($this->builder->buildAuth($this->config['auth']));

                $reader = new Subscriber($socket);
                $reader->recv();
            }
        }

        return $socket;
    }

    /**
     * @param Socket $connection
     */
    protected function sendClose($connection): void
    {
        try {
            $connection->send($this->builder->buildCls());
        } catch (\Throwable $throwable) {
            // Do nothing
        }
    }
}
