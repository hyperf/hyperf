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

use Hyperf\Engine\Channel;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
use InvalidArgumentException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Wire\AMQPWriter;
use PhpAmqpLib\Wire\IO\AbstractIO;
use Psr\Log\LoggerInterface;
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
    protected $readWriteTimeout;

    /**
     * @var int
     */
    protected $heartbeat;

    /**
     * @var Channel
     */
    protected $pushChannel;

    /**
     * @var Channel
     */
    protected $readChannel;

    /**
     * @var Channel
     */
    protected $brokenChannel;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $enableHeartbeat = false;

    /**
     * @var null|Client
     */
    private $sock;

    private $buffer = '';

    /**
     * @throws \InvalidArgumentException when readWriteTimeout argument does not 2x the heartbeat
     */
    public function __construct(
        string $host,
        int $port,
        int $connectionTimeout,
        int $readWriteTimeout,
        int $heartbeat = 0
    ) {
        if ($heartbeat !== 0 && ($readWriteTimeout < ($heartbeat * 2))) {
            throw new InvalidArgumentException('Argument readWriteTimeout must be at least 2x the heartbeat.');
        }
        $this->host = $host;
        $this->port = $port;
        $this->connectionTimeout = $connectionTimeout;
        $this->readWriteTimeout = $readWriteTimeout;
        $this->heartbeat = $heartbeat;

        $this->readChannel = $this->makeChannel();
        $this->brokenChannel = new Channel(1);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function heartbeat()
    {
        if (! $this->enableHeartbeat) {
            $this->enableHeartbeat = true;
            Coroutine::create(function () {
                while (true) {
                    $heartbeat = 5;
                    if ($this->heartbeat > 0) {
                        $heartbeat = $this->heartbeat;
                    }

                    if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield($heartbeat)) {
                        break;
                    }

                    if ($this->brokenChannel->isClosing()) {
                        break;
                    }

                    try {
                        // PING
                        if ($chan = $this->pushChannel and $chan->isEmpty()) {
                            $this->write_heartbeat();
                        }
                    } catch (\Throwable $exception) {
                        $this->logger && $this->logger->error((string) $exception);
                    }
                }
            });
        }
    }

    /**
     * Sets up the stream connection.
     *
     * @throws AMQPRuntimeException
     * @throws \Exception
     */
    public function connect()
    {
        $sock = new Client(SWOOLE_SOCK_TCP);
        if (! $sock->connect($this->host, $this->port, $this->connectionTimeout)) {
            throw new AMQPRuntimeException(
                sprintf('Error Connecting to server: %s ', $sock->errMsg),
                $sock->errCode
            );
        }
        $this->sock = $sock;
    }

    /**
     * Reconnects the socket.
     */
    public function reconnect()
    {
        $this->close();
        $this->loop();
    }

    /**
     * @param int $len
     * @throws AMQPRuntimeException
     * @return mixed|string
     */
    public function read($len)
    {
        $this->loop();

        do {
            if ($len <= strlen($this->buffer)) {
                $data = substr($this->buffer, 0, $len);
                $this->buffer = substr($this->buffer, $len);
                return $data;
            }

            $this->select($this->readWriteTimeout, null);
        } while (true);
    }

    /**
     * @param string $data
     * @throws AMQPRuntimeException
     * @throws \PhpAmqpLib\Exception\AMQPTimeoutException
     * @return mixed|void
     */
    public function write($data)
    {
        $this->loop();

        $this->pushChannel->push($data);
    }

    /**
     * Heartbeat logic: check connection health here.
     */
    public function check_heartbeat()
    {
    }

    public function close()
    {
        $this->logger && $this->logger->warning('Connection closed, wait to restart in next time.');
        $this->pushChannel->close();
        $this->sock->close();
    }

    /**
     * @return null|Client|resource
     */
    public function get_socket()
    {
        return $this->sock;
    }

    /**
     * @return resource
     */
    public function getSocket()
    {
        return $this->get_socket();
    }

    public function select($sec, $usec)
    {
        return $this->do_select($sec, $usec);
    }

    /**
     * @return $this
     */
    public function disableHeartbeat()
    {
        $this->heartbeat = 0;

        return $this;
    }

    /**
     * @return $this
     */
    public function reenableHeartbeat()
    {
        return $this;
    }

    public function isBroken(): bool
    {
        $this->brokenChannel->pop(-1);
        $this->brokenChannel->close();
        return true;
    }

    protected function loop(): void
    {
        $this->heartbeat();

        if ($this->pushChannel !== null && ! $this->pushChannel->isClosing()) {
            return;
        }
        $this->pushChannel = $this->makeChannel();
        $this->connect();

        Coroutine::create(function () {
            $reason = '';
            try {
                $chan = $this->pushChannel;
                $client = $this->sock;
                while (true) {
                    $data = $client->recv(-1);
                    if (! $client->isConnected()) {
                        $reason = 'client disconnected. ' . $client->errMsg;
                        break;
                    }
                    if ($chan->isClosing()) {
                        $reason = 'channel closed.';
                        break;
                    }

                    if ($data === false || $data === '') {
                        $reason = 'client broken. ' . $client->errMsg;
                        break;
                    }

                    $this->buffer .= $data;
                    $readChannel = $this->readChannel;
                    $this->readChannel = $this->makeChannel();
                    $readChannel->close();
                }
            } finally {
                $this->logger && $this->logger->warning('Recv loop broken, wait to restart in next time. The reason is ' . $reason);
                $this->brokenChannel->push(true);
                $chan->close();
                $client->close();
            }
        });

        Coroutine::create(function () {
            $reason = '';
            try {
                $chan = $this->pushChannel;
                $client = $this->sock;
                while (true) {
                    $data = $chan->pop();
                    if ($chan->isClosing()) {
                        $reason = 'channel closed.';
                        break;
                    }
                    if (! $client->isConnected()) {
                        $reason = 'client disconnected.' . $client->errMsg;
                        break;
                    }

                    if (empty($data)) {
                        continue;
                    }

                    $res = $client->send($data);
                    if ($res === false) {
                        $this->logger && $this->logger->warning('Send data failed. The reason is ' . $client->errMsg);
                    }
                }
            } finally {
                $this->logger && $this->logger->warning('Send loop broken, wait to restart in next time. The reason is ' . $reason);
                $this->brokenChannel->push(true);
                $chan->close();
                $client->close();
            }
        });
    }

    /**
     * Sends a heartbeat message.
     */
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
        if (strlen($this->buffer) > 0) {
            return 1;
        }

        $readChannel = $this->readChannel;

        $seconds = intval($sec) + intval($usec) / 1000;

        $readChannel->pop($seconds);
        if ($readChannel->isClosing()) {
            return 1;
        }
        return 0;
    }

    protected function makeChannel(): Channel
    {
        return new Channel(65535);
    }
}
