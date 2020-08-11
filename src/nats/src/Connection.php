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
namespace Hyperf\Nats;

use RandomLib\Factory;
use RandomLib\Generator;

/**
 * Connection Class.
 *
 * Handles the connection to a NATS server or cluster of servers.
 */
class Connection
{
    /**
     * Show DEBUG info?
     *
     * @var bool if debug is enabled
     */
    private $debug = false;

    /**
     * Number of PINGs.
     *
     * @var int number of pings
     */
    private $pings = 0;

    /**
     * Chunk size in bytes to use when reading an stream of data.
     *
     * @var int size of chunk
     */
    private $chunkSize = 1500;

    /**
     * Number of messages published.
     *
     * @var int number of messages
     */
    private $pubs = 0;

    /**
     * Number of reconnects to the server.
     *
     * @var int Number of reconnects
     */
    private $reconnects = 0;

    /**
     * List of available subscriptions.
     *
     * @var array list of subscriptions
     */
    private $subscriptions = [];

    /**
     * Connection options object.
     *
     * @var null|ConnectionOptions
     */
    private $options;

    /**
     * Connection timeout.
     *
     * @var float
     */
    private $timeout;

    /**
     * Stream File Pointer.
     *
     * @var mixed Socket file pointer
     */
    private $streamSocket;

    /**
     * Generator object.
     *
     * @var Generator|Php71RandomGenerator
     */
    private $randomGenerator;

    /**
     * Server information.
     *
     * @var mixed
     */
    private $serverInfo;

    /**
     * Constructor.
     *
     * @param ConnectionOptions $options connection options object
     */
    public function __construct(ConnectionOptions $options = null)
    {
        $this->pings = 0;
        $this->pubs = 0;
        $this->subscriptions = [];
        $this->options = $options;
        if (version_compare(phpversion(), '7.0', '>') === true) {
            $this->randomGenerator = new Php71RandomGenerator();
        } else {
            $randomFactory = new Factory();
            $this->randomGenerator = $randomFactory->getLowStrengthGenerator();
        }

        if ($options === null) {
            $this->options = new ConnectionOptions();
        }
    }

    /**
     * Enable or disable debug mode.
     *
     * @param bool $debug if debug is enabled
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * Return the number of pings.
     *
     * @return int Number of pings
     */
    public function pingsCount()
    {
        return $this->pings;
    }

    /**
     * Return the number of messages published.
     *
     * @return int number of messages published
     */
    public function pubsCount()
    {
        return $this->pubs;
    }

    /**
     * Return the number of reconnects to the server.
     *
     * @return int number of reconnects
     */
    public function reconnectsCount()
    {
        return $this->reconnects;
    }

    /**
     * Return the number of subscriptions available.
     *
     * @return int number of subscription
     */
    public function subscriptionsCount()
    {
        return count($this->subscriptions);
    }

    /**
     * Return subscriptions list.
     *
     * @return array list of subscription ids
     */
    public function getSubscriptions()
    {
        return array_keys($this->subscriptions);
    }

    /**
     * Sets the chunck size in bytes to be processed when reading.
     *
     * @param int $chunkSize set byte chunk len to read when reading from wire
     */
    public function setChunkSize($chunkSize)
    {
        $this->chunkSize = $chunkSize;
    }

    /**
     * Set Stream Timeout.
     *
     * @param float $seconds before timeout on stream
     *
     * @return bool
     */
    public function setStreamTimeout($seconds)
    {
        if ($this->isConnected() === true) {
            if (is_numeric($seconds) === true) {
                try {
                    $timeout = (float) number_format($seconds, 3);
                    $seconds = floor($timeout);
                    $microseconds = (($timeout - $seconds) * 1000);
                    return stream_set_timeout($this->streamSocket, $seconds, $microseconds);
                } catch (\Exception $e) {
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * Returns an stream socket for this connection.
     *
     * @return resource
     */
    public function getStreamSocket()
    {
        return $this->streamSocket;
    }

    /**
     * Checks if the client is connected to a server.
     *
     * @return bool
     */
    public function isConnected()
    {
        return isset($this->streamSocket);
    }

    /**
     * Returns current connected server ID.
     *
     * @return string server ID
     */
    public function connectedServerID()
    {
        return $this->serverInfo->getServerID();
    }

    /**
     * Connect to server.
     *
     * @param float $timeout number of seconds until the connect() system call should timeout
     *
     * @throws \Throwable exception raised if connection fails
     */
    public function connect($timeout = null)
    {
        if ($timeout === null) {
            $timeout = intval(ini_get('default_socket_timeout'));
        }

        $this->timeout = $timeout;
        $this->streamSocket = $this->getStream($this->options->getAddress(), $timeout);
        $this->setStreamTimeout($timeout);

        $msg = 'CONNECT ' . $this->options;
        $this->send($msg);
        $connectResponse = $this->receive();

        if ($this->isErrorResponse($connectResponse) === true) {
            throw Exception::forFailedConnection($connectResponse);
        }
        $this->processServerInfo($connectResponse);

        $this->ping();
        $pingResponse = $this->receive();

        if ($this->isErrorResponse($pingResponse) === true) {
            throw Exception::forFailedPing($pingResponse);
        }
    }

    /**
     * Sends PING message.
     */
    public function ping()
    {
        $msg = 'PING';
        $this->send($msg);
        ++$this->pings;
    }

    /**
     * Request does a request and executes a callback with the response.
     *
     * @param string $subject message topic
     * @param string $payload message data
     * @param \Closure $callback closure to be executed as callback
     */
    public function request($subject, $payload, \Closure $callback)
    {
        $inbox = uniqid('_INBOX.');
        $sid = $this->subscribe(
            $inbox,
            $callback
        );
        $this->unsubscribe($sid, 1);
        $this->publish($subject, $payload, $inbox);
        $this->wait(1);
    }

    /**
     * Subscribes to an specific event given a subject.
     *
     * @param string $subject message topic
     * @param \Closure $callback closure to be executed as callback
     *
     * @return string
     */
    public function subscribe($subject, \Closure $callback)
    {
        $sid = $this->randomGenerator->generateString(16);
        $msg = 'SUB ' . $subject . ' ' . $sid;
        $this->send($msg);
        $this->subscriptions[$sid] = $callback;
        return $sid;
    }

    /**
     * Subscribes to an specific event given a subject and a queue.
     *
     * @param string $subject message topic
     * @param string $queue queue name
     * @param \Closure $callback closure to be executed as callback
     *
     * @return string
     */
    public function queueSubscribe($subject, $queue, \Closure $callback)
    {
        $sid = $this->randomGenerator->generateString(16);
        $msg = 'SUB ' . $subject . ' ' . $queue . ' ' . $sid;
        $this->send($msg);
        $this->subscriptions[$sid] = $callback;
        return $sid;
    }

    /**
     * Unsubscribe from a event given a subject.
     *
     * @param string $sid subscription ID
     * @param int $quantity quantity of messages
     */
    public function unsubscribe($sid, $quantity = null)
    {
        $msg = 'UNSUB ' . $sid;
        if ($quantity !== null) {
            $msg = $msg . ' ' . $quantity;
        }

        $this->send($msg);
        if ($quantity === null) {
            unset($this->subscriptions[$sid]);
        }
    }

    /**
     * Publish publishes the data argument to the given subject.
     *
     * @param string $subject message topic
     * @param string $payload message data
     * @param string $inbox message inbox
     *
     * @throws Exception if subscription not found
     */
    public function publish($subject, $payload = null, $inbox = null)
    {
        $msg = 'PUB ' . $subject;
        if ($inbox !== null) {
            $msg = $msg . ' ' . $inbox;
        }

        $msg = $msg . ' ' . strlen($payload);
        $this->send($msg . "\r\n" . $payload);
        ++$this->pubs;
    }

    /**
     * Waits for messages.
     *
     * @param int $quantity number of messages to wait for
     *
     * @return null|Connection $connection Connection object
     */
    public function wait($quantity = 0)
    {
        $count = 0;
        $info = stream_get_meta_data($this->streamSocket);
        while (is_resource($this->streamSocket) === true && feof($this->streamSocket) === false && empty($info['timed_out']) === true) {
            $line = $this->receive();

            if ($line === false) {
                return null;
            }

            if (strpos($line, 'PING') === 0) {
                $this->handlePING();
            }

            if (strpos($line, 'MSG') === 0) {
                ++$count;
                $this->handleMSG($line);
                if (($quantity !== 0) && ($count >= $quantity)) {
                    return $this;
                }
            }

            $info = stream_get_meta_data($this->streamSocket);
        }

        $this->close();

        return $this;
    }

    /**
     * Reconnects to the server.
     */
    public function reconnect()
    {
        ++$this->reconnects;
        $this->close();
        $this->connect($this->timeout);
    }

    /**
     * Close will close the connection to the server.
     */
    public function close()
    {
        if ($this->streamSocket === null) {
            return;
        }

        fclose($this->streamSocket);
        $this->streamSocket = null;
    }

    /**
     * Indicates whether $response is an error response.
     *
     * @param string $response the Nats Server response
     *
     * @return bool
     */
    private function isErrorResponse($response)
    {
        return substr($response, 0, 4) === '-ERR';
    }

    /**
     * Returns an stream socket to the desired server.
     *
     * @param string $address server url string
     * @param float $timeout number of seconds until the connect() system call should timeout
     *
     * @throws \Exception exception raised if connection fails
     * @return resource
     */
    private function getStream($address, $timeout)
    {
        $errno = null;
        $errstr = null;

        $fp = stream_socket_client($address, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);

        if ($fp === false) {
            throw Exception::forStreamSocketClientError($errstr, $errno);
        }

        $timeout = (float) number_format($timeout, 3);
        $seconds = floor($timeout);
        $microseconds = (($timeout - $seconds) * 1000);
        stream_set_timeout($fp, $seconds, $microseconds);

        return $fp;
    }

    /**
     * Process information returned by the server after connection.
     *
     * @param string $connectionResponse INFO message
     */
    private function processServerInfo($connectionResponse)
    {
        $this->serverInfo = new ServerInfo($connectionResponse);
    }

    /**
     * Sends data thought the stream.
     *
     * @param string $payload message data
     *
     * @throws \Exception raises if fails sending data
     */
    private function send($payload)
    {
        $msg = $payload . "\r\n";
        $len = strlen($msg);
        while (true) {
            $written = @fwrite($this->streamSocket, $msg);
            if ($written === false) {
                throw new \Exception('Error sending data');
            }

            if ($written === 0) {
                throw new \Exception('Broken pipe or closed connection');
            }

            $len = ($len - $written);
            if ($len > 0) {
                $msg = substr($msg, (0 - $len));
            } else {
                break;
            }
        }

        if ($this->debug === true) {
            printf('>>>> %s', $msg);
        }
    }

    /**
     * Receives a message thought the stream.
     *
     * @param int $len number of bytes to receive
     *
     * @return bool|string
     */
    private function receive(int $len = 0)
    {
        if ($len > 0) {
            $chunkSize = $this->chunkSize;
            $line = null;
            $receivedBytes = 0;
            while ($receivedBytes < $len) {
                $bytesLeft = ($len - $receivedBytes);
                if ($bytesLeft < $this->chunkSize) {
                    $chunkSize = $bytesLeft;
                }

                $readChunk = fread($this->streamSocket, $chunkSize);
                $receivedBytes += strlen($readChunk);
                $line .= $readChunk;
            }
        } else {
            $line = fgets($this->streamSocket);
        }

        if ($this->debug === true) {
            printf('<<<< %s\r\n', $line);
        }

        return $line;
    }

    /**
     * Handles PING command.
     */
    private function handlePING()
    {
        $this->send('PONG');
    }

    /**
     * Handles MSG command.
     *
     * @param string $line message command from Nats
     *
     * @throws Exception if subscription not found
     * @codeCoverageIgnore
     */
    private function handleMSG($line)
    {
        $parts = explode(' ', $line);
        $subject = null;
        $length = trim($parts[3]);
        $sid = $parts[2];

        if (count($parts) === 5) {
            $length = trim($parts[4]);
            $subject = $parts[3];
        } elseif (count($parts) === 4) {
            $length = trim($parts[3]);
            $subject = $parts[1];
        }

        $payload = $this->receive((int) $length);
        $msg = new Message($subject, $payload, $sid, $this);

        if (isset($this->subscriptions[$sid]) === false) {
            throw Exception::forSubscriptionNotFound($sid);
        }

        $func = $this->subscriptions[$sid];
        if (is_callable($func) === true) {
            $func($msg);
        } else {
            throw Exception::forSubscriptionCallbackInvalid($sid);
        }
    }
}
