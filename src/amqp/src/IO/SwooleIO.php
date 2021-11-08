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
namespace Hyperf\Amqp\IO;

use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Wire\AMQPWriter;
use PhpAmqpLib\Wire\IO\AbstractIO;
use Swoole\Coroutine\Client;
use const SWOOLE_SOCK_TCP;

class SwooleIO extends AbstractIO
{
    public const READ_BUFFER_WAIT_INTERVAL = 100000;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var int
     */
    protected $connectionTimeout;

    /**
     * @var int
     */
    protected $heartbeat;

    /**
     * @var null|Client
     */
    private $sock;

    /**
     * @var string
     */
    private $buffer = '';

    /**
     * @throws \InvalidArgumentException when readWriteTimeout argument does not 2x the heartbeat
     */
    public function __construct(
        string $host,
        int $port,
        int $connectionTimeout
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->connectionTimeout = $connectionTimeout;
    }

    /**
     * Sets up the stream connection.
     *
     * @throws AMQPRuntimeException
     */
    public function connect()
    {
        $this->sock = $this->makeClient();
    }

    public function read($len)
    {
        while (true) {
            if ($len <= strlen($this->buffer)) {
                $data = substr($this->buffer, 0, $len);
                $this->buffer = substr($this->buffer, $len);

                return $data;
            }

            if (! $this->sock->isConnected()) {
                throw new AMQPConnectionClosedException('Broken pipe or closed connection. ' . $this->sock->errMsg);
            }

            $buffer = $this->sock->recv(-1);

            if ($buffer === '') {
                throw new AMQPConnectionClosedException('Connection is closed. The reason is ' . $this->sock->errMsg);
            }

            $this->buffer .= $buffer;
        }
    }

    public function write($data)
    {
        $buffer = $this->sock->send($data);

        if ($buffer === false) {
            throw new AMQPConnectionClosedException('Error sending data');
        }
    }

    public function check_heartbeat()
    {
    }

    public function close()
    {
        $this->sock && $this->sock->close();
    }

    public function select($sec, $usec)
    {
        return 1;
    }

    public function disableHeartbeat()
    {
        return $this;
    }

    public function reenableHeartbeat()
    {
        return $this;
    }

    protected function makeClient()
    {
        $sock = new Client(SWOOLE_SOCK_TCP);
        if (! $sock->connect($this->host, $this->port, $this->connectionTimeout)) {
            throw new AMQPRuntimeException(
                sprintf('Error Connecting to server: %s ', $sock->errMsg),
                $sock->errCode
            );
        }
        return $sock;
    }

    protected function write_heartbeat()
    {
        $pkt = new AMQPWriter();
        $pkt->write_octet(8);
        $pkt->write_short(0);
        $pkt->write_long(0);
        $pkt->write_octet(0xCE);
        $this->write($pkt->getvalue());
    }

    protected function do_select($sec, $usec)
    {
        return 1;
    }
}
