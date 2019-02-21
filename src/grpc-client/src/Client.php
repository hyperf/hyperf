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

namespace Hyperf\GrpcClient;

use Swoole\Coroutine\Channel;

class Client
{
    const CLOSE_KEYWORD = '>>>SWOOLE|CLOSE<<<';

    const WAIT_FOR_ALL = 1;

    const WAIT_CLOSE = 2;

    const WAIT_CLOSE_FORCE = 3;

    const GRPC_DEFAULT_TIMEOUT = 3.0;

    /**
     * The queue to get channel object.
     * @var ChannelPool
     */
    private static $channelPool;

    /**
     * The stats of all clients.
     * @var array
     */
    private static $numStats = [
        'constructed_num' => 0,
        'destructed_num' => 0,
    ];

    private static $debug = false;

    /**
     * To record the cid of start coroutine.
     * @var int
     */
    private $mainCid;

    // ====== send =====

    /**
     * If use send yield mode.
     * @var bool
     */
    private $sendYield = false;

    /**
     * The channel to proxy send data from all of the coroutine.
     * @var Channel
     */
    private $sendChannel;

    /**
     * The channel to get the current send stream id (as ret val).
     * @var Channel
     */
    private $sendRetChannel;

    /**
     * To record the cid of send loop coroutine.
     * @var int
     */
    private $sendCid = 0;

    // ===== recv ======

    /**
     * The hashMap of channels [streamId => response].
     * @var Channel[]
     */
    private $recvChannelMap = [];

    /**
     * To record the cid of recv loop coroutine.
     * @var int
     */
    private $recvCid = 0;

    /**
     * The sign of if this Client is waiting to close.
     * @var int
     */
    private $waitStatus = 0;

    /**
     * @var Channel
     */
    private $waitYield;

    private $host;

    private $port;

    private $ssl;

    private $opts;

    /**
     * @var \Swoole\Coroutine\Http2\Client
     */
    private $client;

    private $timeout;

    public function __construct(string $hostname, array $opts = [])
    {
        self::$channelPool = ChannelPool::getInstance();
        // parse url
        $parts = parse_url($hostname);
        if (! $parts || ! isset($parts['host']) || ! $parts['port']) {
            throw new \InvalidArgumentException("The hostname {$hostname} is illegal!");
        }
        $this->host = $parts['host'];
        $this->port = $parts['port'];

        $default_opts = [
            'timeout' => self::GRPC_DEFAULT_TIMEOUT,
            'send_yield' => false,
            'ssl' => false,
            'ssl_host_name' => '',
        ];
        $this->opts = $opts + $default_opts;
        $this->timeout = &$this->opts['timeout'];
        $this->sendYield = &$this->opts['send_yield'];
        $this->ssl = &$this->opts['ssl'];
        $this->ssl = (bool) $this->ssl || (bool) $this->opts['ssl_host_name'];

        $this->constructClient();
    }

    public function __destruct()
    {
        ++self::$numStats['destructed_num'];
    }

    public function __get($name)
    {
        return $this->client->{$name} ?? null;
    }

    public static function numStats(): array
    {
        return self::$numStats;
    }

    public static function debug(bool $enable = true): void
    {
        self::$debug = $enable;
    }

    public function start(): bool
    {
        if ($this->recvCid !== 0 || $this->sendCid !== 0) {
            trigger_error('This client has started yet and it\'s running.', E_USER_WARNING);
            return false;
        }
        if ($this->mainCid = \Swoole\Coroutine::getuid() <= 0) {
            throw new \BadMethodCallException('You must start it in an alone coroutine.');
        }
        if (! $this->client->connect()) {
            return false;
        }
        // receive wait
        \Swoole\Coroutine::create(function () {
            $this->recvCid = \Swoole\Coroutine::getuid();
            // start recv loop
            while (true) {
                $response = $this->client->recv(-1);
                if (self::$debug) {
                    var_dump($response);
                    if ($response === false && $this->client->errCode !== 0) {
                        var_dump($this->client->errCode);
                        var_dump($this->client->errMsg);
                    }
                }

                if ($response !== false) {
                    // force close
                    if (
                        $this->waitStatus === self::WAIT_CLOSE_FORCE // &&
                        // ($message = $response->headers['grpc-message'] ?? null) &&
                        // strpos($message, self::CLOSE_KEYWORD) !== false
                    ) {
                        // close request has not recv channel pop wait
                        self::$channelPool->put($this->recvChannelMap[$response->streamId]);
                        unset($this->recvChannelMap[$response->streamId]);
                        goto _close;
                    }

                    // normal response
                    if ($the_channel = $this->recvChannelMap[$response->streamId] ?? null) {
                        // we can find the waiting coroutine and return response to it
                        $the_channel->push($response);

                        if (! $response->pipeline) {
                            // revert channel
                            unset($this->recvChannelMap[$response->streamId]);
                            self::$channelPool->put($the_channel);
                        }

                        // push finished, check if close wait and no coroutine is waiting, if Y, stop recv loop
                        if ($this->waitStatus === self::WAIT_CLOSE && empty($this->recvChannelMap)) {
                            break;
                        }
                    }// else discard it
                } else {
                    // false response
                    if (! $this->client->connected) {
                        _close:

                        // if you want to close it or retry connect failed, stop recv loop
                        if ($this->waitStatus) {
                            $need_kill = true;
                        } else {
                            if (version_compare(SWOOLE_VERSION, '4.2.3', '<=')) {
                                // deflater cache bug so we need to new one
                                $this->constructClient();
                            }
                            $need_kill = ! $this->client->connect();
                        }

                        // ↑↓ We must `retry-connect` before we push `false` response
                        // ↑↓ Then the pop channel coroutine can knows that if this client is available

                        // clear all, we will auto reconnect, but it need user retry again by himself
                        if (! empty($this->recvChannelMap)) {
                            foreach ($this->recvChannelMap as $the_channel) {
                                $the_channel->push(false);
                                self::$channelPool->put($the_channel);
                            }
                            $this->recvChannelMap = [];
                        }

                        if ($need_kill) {
                            break;
                        }
                    }
                }
            }

            // close success and notify close yield coroutine
            if ($this->waitYield) {
                $this->waitYield->push(true);
                self::$channelPool->put($this->waitYield);
                $this->waitYield = null;
            }

            $this->recvCid = 0;
            // clear all
            $this->mainCid = 0;
            $this->waitStatus = 0;
            $this->waitYield = null;
        });

        if ($this->sendYield) {
            // send wait
            \Swoole\Coroutine::create(function () {
                $this->sendCid = \Swoole\Coroutine::getuid();
                $this->sendChannel = new Channel(0);
                $this->sendRetChannel = new Channel(0);
                while (true) {
                    $sendData = $this->sendChannel->pop(-1);
                    if ($sendData === 0) {
                        break;
                    }
                    if ($sendData instanceof \swoole_http2_request) {
                        $ret = $this->client->send($sendData);
                    } else {
                        $ret = $this->client->write(...$sendData);
                    }
                    $this->sendRetChannel->push($ret);
                }

                $this->sendCid = 0;
            });
        }

        return true;
    }

    /**
     * @param null|mixed $key
     * @return array|int
     */
    public function stats($key = null)
    {
        return $this->client->stats($key);
    }

    public function isConnected(): bool
    {
        return $this->client->connected;
    }

    public function isRunning(): bool
    {
        return $this->recvCid > 0 && (! $this->sendYield ?: $this->sendCid > 0);
    }

    public function isStreamExist(int $streamId)
    {
        return isset($this->recvChannelMap[$streamId]);
    }

    public function getClient(): \Swoole\Coroutine\Http2\Client
    {
        return $this->client;
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function setTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * Open a stream and return it's id.
     *
     * @param string $path
     * @param string $data
     * @param string $method
     * @return int
     */
    public function openStream(string $path, $data = null, string $method = 'POST'): int
    {
        $request = new \swoole_http2_request();
        $request->method = $method;
        $request->path = $path;
        if ($data) {
            $request->data = $data;
        }
        $request->pipeline = true;

        return $this->send($request);
    }

    public function send(\swoole_http2_request $request): int
    {
        if (! $this->isConnected()) {
            return 0;
        }
        if ($this->sendYield) {
            $this->sendChannel->push($request);
            $streamId = $this->sendRetChannel->pop();
        } else {
            $streamId = $this->client->send($request);
        }
        if ($streamId > 0) {
            $this->recvChannelMap[$streamId] = self::$channelPool->get();
        }

        return $streamId;
    }

    public function write(int $streamId, $data, bool $end = false): bool
    {
        if (! $this->isConnected()) {
            return false;
        }
        if ($this->sendYield) {
            return $this->sendChannel->push([$streamId, $data, $end]) && $this->sendRetChannel->pop();
        }
        return $this->client->write($streamId, $data, $end);
    }

    public function recv(int $streamId, float $timeout = null)
    {
        if (! $this->isConnected() || $streamId <= 0) {
            return false;
        }
        $channel = $this->recvChannelMap[$streamId] ?? null;
        if ($channel) {
            $response = $channel->pop($timeout === null ? $this->timeout : $timeout);
            // timeout
            if ($response === false && $channel->errCode === -1) {
                unset($this->recvChannelMap[$streamId]);
            }

            return $response;
        }

        // the channel is not exist or you recv too late
        return false;
    }

    public function waitForAll(): bool
    {
        return $this->wait(self::WAIT_FOR_ALL, true);
    }

    /**
     * Close the client.
     *
     * @param int $type If CLOSE_FORCE, discard all of the requests which are pending, else waiting for all responses back
     * @param bool|float if yield(true = -1) or yield timeout num
     * @param mixed $yield
     * @return bool
     */
    public function close($yield = false): bool
    {
        return $this->wait(self::WAIT_CLOSE_FORCE, $yield);
    }

    public function closeWait($yield = self::GRPC_DEFAULT_TIMEOUT): bool
    {
        return $this->wait(self::WAIT_CLOSE, \Swoole\Coroutine::getuid() > 0 ? $yield : false) || $this->close();
    }

    public function closeAfter(float $time): bool
    {
        \Swoole\Coroutine::sleep($time);
        return $this->close();
    }

    private function constructClient()
    {
        // create client and init settings
        $this->client = new \Swoole\Coroutine\Http2\Client($this->host, $this->port, $this->ssl);
        $this->client->set($this->opts);
        ++self::$numStats['constructed_num'];
    }

    private function wait(int $type, $yield = true): bool
    {
        if (! $this->isConnected()) {
            return false;
        }
        $this->waitStatus = $type;
        if ($this->waitStatus === self::WAIT_CLOSE) {
            $ret = true;
            goto _yield;
        }
        $closeRequest = new \swoole_http2_request();
        $closeRequest->method = 'GET';
        $closeRequest->path = self::CLOSE_KEYWORD;

        $ret = ($close_id = $this->send($closeRequest)) && ($this->sendYield ? $this->sendChannel->push(0) : true);
        if ($ret) {
            _yield:
            $yield = $yield === true ? -1 : $yield;
            if ($yield) {
                $this->waitYield = self::$channelPool->get();
                return $this->waitYield->pop($yield);
            }
        }
        return $ret;
    }
}
