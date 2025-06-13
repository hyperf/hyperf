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

use Hyperf\Engine\Socket;
use InvalidArgumentException;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Wire\AMQPWriter;
use PhpAmqpLib\Wire\IO\AbstractIO;
use Swow;

class SwowIO extends AbstractIO
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
    protected $heartbeat;

    private ?Socket $sock = null;

    /**
     * @throws InvalidArgumentException when readWriteTimeout argument does not 2x the heartbeat
     */
    public function __construct(
        string $host,
        int $port,
        protected int $connectionTimeout,
        protected int $readWriteTimeout = 3,
        protected bool $openSSL = false
    ) {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Sets up the stream connection.
     *
     * @throws AMQPRuntimeException
     */
    public function connect(): void
    {
        $this->sock = $this->makeClient();
    }

    public function read($len): string
    {
        $data = $this->sock->recvAll($len, $this->readWriteTimeout);
        if ($data === false || strlen($data) !== $len) {
            throw new AMQPConnectionClosedException('Read data failed, The reason is ' . $this->sock->errMsg);
        }

        return $data;
    }

    public function write($data): void
    {
        $len = $this->sock->sendAll($data, $this->readWriteTimeout);

        /* @phpstan-ignore-next-line */
        if ($data === false || strlen($data) !== $len) {
            throw new AMQPConnectionClosedException('Send data failed, The reason is ' . $this->sock->errMsg);
        }
    }

    public function check_heartbeat()
    {
    }

    public function close(): void
    {
        $this->sock && $this->sock->close();
    }

    public function select(?int $sec, int $usec = 0): int
    {
        return 1;
    }

    public function disableHeartbeat(): AbstractIO
    {
        return $this;
    }

    public function reenableHeartbeat(): AbstractIO
    {
        return $this;
    }

    protected function makeClient(): Socket
    {
        $sock = new Socket(Socket::TYPE_TCP);

        if ($this->openSSL === true) {
            // TODO: Support SSL.
        }

        try {
            $sock->connect($this->host, $this->port, $this->connectionTimeout * 1000);
        } catch (Swow\SocketException $exception) {
            throw new AMQPRuntimeException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        return $sock;
    }

    protected function write_heartbeat(): void
    {
        $pkt = new AMQPWriter();
        $pkt->write_octet(8);
        $pkt->write_short(0);
        $pkt->write_long(0);
        $pkt->write_octet(0xCE);
        $this->write($pkt->getvalue());
    }

    protected function do_select($sec, $usec): int
    {
        return 1;
    }
}
