<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Nsq\Pool;

use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Pool\KeepaliveConnection;
use Hyperf\Pool\Pool;
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

    public function __construct(ContainerInterface $container, Pool $pool, array $config)
    {
        $this->config = array_merge($this->config, $config);
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

        if ($socket->send('  V2') === false) {
            throw new ConnectionException('Nsq connect failed.');
        }

        return $socket;
    }
}
