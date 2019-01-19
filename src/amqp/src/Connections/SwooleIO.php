<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Amqp\Connections;

use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Wire\AMQPWriter;
use PhpAmqpLib\Wire\IO\AbstractIO;
use Swoole;

class SwooleIO extends AbstractIO
{
    const READ_BUFFER_WAIT_INTERVAL = 100000;

    /** @var string */
    protected $host;

    /** @var int */
    protected $port;

    /** @var float */
    protected $connection_timeout;

    /** @var float */
    protected $read_write_timeout;

    /** @var resource */
    protected $context;

    /** @var bool */
    protected $keepalive;

    /** @var int */
    protected $heartbeat;

    /** @var float */
    protected $last_read;

    /** @var float */
    protected $last_write;

    /** @var array */
    protected $last_error;

    /**
     * @var bool
     */
    protected $tcp_nodelay = false;

    /**
     * @var bool
     */
    protected $ssl = false;

    /** @var int */
    private $initial_heartbeat;

    /** @var Swoole\Coroutine\Client */
    private $sock;

    private $buffer = '';

    /**
     * @param string $host
     * @param int    $port
     * @param float  $connection_timeout
     * @param float  $read_write_timeout
     * @param null   $context
     * @param bool   $keepalive
     * @param int    $heartbeat
     */
    public function __construct(
        $host,
        $port,
        $connection_timeout,
        $read_write_timeout,
        $context = null,
        $keepalive = false,
        $heartbeat = 0
    ) {
        if ($heartbeat !== 0 && ($read_write_timeout < ($heartbeat * 2))) {
            throw new \InvalidArgumentException('read_write_timeout must be at least 2x the heartbeat');
        }
        $this->host = $host;
        $this->port = $port;
        $this->connection_timeout = $connection_timeout;
        $this->read_write_timeout = $read_write_timeout;
        $this->context = $context;
        $this->keepalive = $keepalive;
        $this->heartbeat = $heartbeat;
        $this->initial_heartbeat = $heartbeat;
    }

    /**
     * Sets up the stream connection
     *
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     * @throws \Exception
     */
    public function connect()
    {
        $sock = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
        if (!$sock->connect($this->host, $this->port, $this->connection_timeout)) {
            throw new AMQPRuntimeException(
                sprintf(
                    'Error Connecting to server(%s): %s ',
                    $sock->errCode,
                    swoole_strerror($sock->errCode)
                ),
                $sock->errCode
            );
        }
        $this->sock = $sock;
    }

    /**
     * Reconnects the socket
     */
    public function reconnect()
    {
        $this->close();
        $this->connect();
    }

    /**
     * @param int $len
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     * @return mixed|string
     */
    public function read($len)
    {
        $this->check_heartbeat();
        $count = 0;
        do {
            if ($len <= strlen($this->buffer)) {
                $data = substr($this->buffer, 0, $len);
                $this->buffer = substr($this->buffer, $len);
                $this->last_read = microtime(true);

                return $data;
            }

            if (!$this->sock->connected) {
                throw new AMQPRuntimeException('Broken pipe or closed connection');
            }

            $read_buffer = $this->sock->recv($this->read_write_timeout ? $this->read_write_timeout : -1);
            if ($read_buffer === false) {
                throw new AMQPRuntimeException('Error receiving data, errno=' . $this->sock->errCode);
            }

            if ($read_buffer === '') {
                if (5 < $count++) {
                    throw new AMQPRuntimeException('The receiving data is empty, errno=' . $this->sock->errCode);
                }
                continue;
            }

            $this->buffer .= $read_buffer;
        } while (true);


        return false;
    }

    /**
     * @param string $data
     * @return mixed|void
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     * @throws \PhpAmqpLib\Exception\AMQPTimeoutException
     */
    public function write($data)
    {
        $buffer = $this->sock->send($data);

        if ($buffer === false) {
            throw new AMQPRuntimeException('Error sending data');
        }

        if ($buffer === 0 && !$this->sock->connected) {
            throw new AMQPRuntimeException('Broken pipe or closed connection');
        }

        $this->last_write = microtime(true);
    }

    /**
     * Heartbeat logic: check connection health here
     */
    public function check_heartbeat()
    {
        // ignore unless heartbeat interval is set
        if ($this->heartbeat !== 0 && $this->last_read && $this->last_write) {
            $t = microtime(true);
            $t_read = round($t - $this->last_read);
            $t_write = round($t - $this->last_write);

            // server has gone away
            if (($this->heartbeat * 2) < $t_read) {
                $this->reconnect();
            }

            // time for client to send a heartbeat
            if (($this->heartbeat / 2) < $t_write) {
                $this->write_heartbeat();
            }
        }
    }

    public function close()
    {
        if (isset($this->sock) && $this->sock instanceof Swoole\Coroutine\Client) {
            $this->sock->close();
        }
        $this->sock = null;
        $this->last_read = null;
        $this->last_write = null;
    }

    /**
     * @return resource
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

    /**
     * @param int $sec
     * @param int $usec
     * @return int|mixed
     */
    public function select($sec, $usec)
    {
        $this->check_heartbeat();

        return 1;
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
        $this->heartbeat = $this->initial_heartbeat;

        return $this;
    }

    /**
     * Sends a heartbeat message
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
}
