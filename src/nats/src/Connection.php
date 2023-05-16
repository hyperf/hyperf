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

use Closure;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Coroutine;
use Throwable;

/**
 * Connection Class.
 *
 * Handles the connection to a NATS server or cluster of servers.
 */
class Connection
{
    /**
     * Show DEBUG info?
     */
    private bool $debug = false;

    /**
     * Number of PINGs.
     */
    private int $pings = 0;

    /**
     * Chunk size in bytes to use when reading a stream of data.
     */
    private int $chunkSize = 1500;

    /**
     * Number of messages published.
     */
    private int $pubs = 0;

    /**
     * Number of reconnects to the server.
     */
    private int $reconnects = 0;

    /**
     * List of available subscriptions.
     */
    private array $subscriptions = [];

    /**
     * Connection timeout.
     */
    private ?float $timeout = null;

    /**
     * Stream File Pointer.
     */
    private mixed $streamSocket;

    /**
     * Generator object.
     */
    private RandomGenerator $randomGenerator;

    /**
     * Server information.
     */
    private ServerInfo $serverInfo;

    /**
     * Constructor.
     *
     * @param ConnectionOptions $options connection options object
     */
    public function __construct(private ?ConnectionOptions $options = null)
    {
        $this->pings = 0;
        $this->pubs = 0;
        $this->subscriptions = [];
        $this->randomGenerator = new RandomGenerator();

        if ($options === null) {
            $this->options = new ConnectionOptions();
        }
    }

    /**
     * Enable or disable debug mode.
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * Return the number of pings.
     */
    public function pingsCount(): int
    {
        return $this->pings;
    }

    /**
     * Return the number of messages published.
     */
    public function pubsCount(): int
    {
        return $this->pubs;
    }

    /**
     * Return the number of reconnects to the server.
     */
    public function reconnectsCount(): int
    {
        return $this->reconnects;
    }

    /**
     * Return the number of subscriptions available.
     */
    public function subscriptionsCount(): int
    {
        return count($this->subscriptions);
    }

    /**
     * Return subscriptions list.
     */
    public function getSubscriptions(): array
    {
        return array_keys($this->subscriptions);
    }

    /**
     * Sets the chunk size in bytes to be processed when reading.
     */
    public function setChunkSize(int $chunkSize): void
    {
        $this->chunkSize = $chunkSize;
    }

    /**
     * Set Stream Timeout.
     */
    public function setStreamTimeout(float $seconds): bool
    {
        if ($this->isConnected() === true) {
            try {
                $timeout = (float) number_format($seconds, 3);
                $seconds = floor($timeout);
                $microseconds = (($timeout - $seconds) * 1000);
                return stream_set_timeout($this->streamSocket, $seconds, $microseconds);
            } catch (\Exception) {
                return false;
            }
        }

        return false;
    }

    /**
     * Returns a stream socket for this connection.
     */
    public function getStreamSocket()
    {
        return $this->streamSocket;
    }

    /**
     * Checks if the client is connected to a server.
     */
    public function isConnected(): bool
    {
        return isset($this->streamSocket);
    }

    /**
     * Returns current connected server ID.
     */
    public function connectedServerID(): string
    {
        return $this->serverInfo->getServerID();
    }

    /**
     * Connect to server.
     *
     * @param float $timeout number of seconds until the connect() system call should timeout
     *
     * @throws Throwable exception raised if connection fails
     */
    public function connect(?float $timeout = null)
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
    public function ping(): void
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
     * @param Closure $callback closure to be executed as callback
     */
    public function request(string $subject, string $payload, Closure $callback): void
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
     * Subscribes to a specific event given a subject.
     *
     * @param string $subject message topic
     * @param Closure $callback closure to be executed as callback
     */
    public function subscribe(string $subject, Closure $callback): string
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
     * @param Closure $callback closure to be executed as callback
     */
    public function queueSubscribe(string $subject, string $queue, Closure $callback): string
    {
        $sid = $this->randomGenerator->generateString(16);
        $msg = 'SUB ' . $subject . ' ' . $queue . ' ' . $sid;
        $this->send($msg);
        $this->subscriptions[$sid] = $callback;
        return $sid;
    }

    /**
     * Unsubscribe from an event given a subject.
     *
     * @param string $sid subscription ID
     * @param int $quantity quantity of messages
     */
    public function unsubscribe(string $sid, int $quantity = null): void
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
    public function publish(string $subject, string $payload, ?string $inbox = null): void
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
     * @return null|static $connection Connection object
     */
    public function wait(int $quantity = 0): ?static
    {
        $count = 0;
        $info = stream_get_meta_data($this->streamSocket);
        while (is_resource($this->streamSocket) === true && feof($this->streamSocket) === false && empty($info['timed_out']) === true) {
            $line = $this->receive();

            if ($line === false) {
                return null;
            }

            if (str_starts_with($line, 'PING')) {
                $this->handlePING();
            }

            if (str_starts_with($line, 'MSG')) {
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
    public function reconnect(): void
    {
        ++$this->reconnects;
        $this->close();
        $this->connect($this->timeout);
    }

    /**
     * Close will close the connection to the server.
     */
    public function close(): void
    {
        if ($this->streamSocket === null) {
            return;
        }

        fclose($this->streamSocket);
        $this->streamSocket = null;
    }

    public function heartbeat(): void
    {
        if ($this->timeout > 0) {
            Coroutine::create(function () {
                while (true) {
                    $exited = CoordinatorManager::until(Constants::WORKER_EXIT)->yield($this->timeout / 2);
                    if ($exited) {
                        break;
                    }

                    if (is_null($this->streamSocket)) {
                        break;
                    }

                    $this->ping();
                }
            });
        }
    }

    /**
     * Indicates whether $response is an error response.
     *
     * @param string $response the Nats Server response
     */
    private function isErrorResponse(string $response): bool
    {
        return str_starts_with($response, '-ERR');
    }

    /**
     * Returns a stream socket to the desired server.
     *
     * @param string $address server url string
     * @param float $timeout number of seconds until the connect() system call should timeout
     *
     * @return resource
     * @throws \Exception exception raised if connection fails
     */
    private function getStream(string $address, float $timeout)
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
                $msg = substr($msg, 0 - $len);
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
