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

use Hyperf\Collection\Arr;
use Hyperf\Engine\Contract\Socket\SocketFactoryInterface;
use Hyperf\Engine\Socket;
use Hyperf\Nsq\MessageBuilder;
use Hyperf\Nsq\Subscriber;
use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Pool\KeepaliveConnection;
use Hyperf\Pool\Pool;
use Psr\Container\ContainerInterface;
use Throwable;

class NsqConnection extends KeepaliveConnection
{
    protected array $config = [
        'host' => 'localhost',
        'port' => 4150,
    ];

    protected MessageBuilder $builder;

    protected SocketFactoryInterface $factory;

    public function __construct(ContainerInterface $container, Pool $pool, array $config)
    {
        $this->config = array_merge($this->config, $config);
        $this->builder = $container->get(MessageBuilder::class);
        $this->factory = $container->get(SocketFactoryInterface::class);
        if ($pool instanceof NsqPool) {
            $this->name = 'nsq.connection.' . $pool->getName();
        }
        parent::__construct($container, $pool);
    }

    protected function getActiveConnection()
    {
        $host = $this->config['host'];
        $port = $this->config['port'];

        $socket = $this->factory->make(new Socket\SocketOption($host, $port));

        if ($socket->sendAll($this->builder->buildMagic()) === false) {
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
        } catch (Throwable $throwable) {
            // Do nothing
        }
    }
}
