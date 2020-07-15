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
namespace Hyperf\GrpcClient;

use BadMethodCallException;
use Hyperf\Grpc\StatusCode;
use Hyperf\GrpcClient\Exception\GrpcClientException;
use Hyperf\Utils\ChannelPool;
use Hyperf\Utils\Coroutine;
use InvalidArgumentException;
use RuntimeException;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Http2\Client as SwooleHttp2Client;

class GrpcClient
{
    const GRPC_DEFAULT_TIMEOUT = 3.0;

    /**
     * @var ChannelPool
     */
    private $channelPool;

    /**
     * @var string
     */
    private $host;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var int
     */
    private $port;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var bool
     */
    private $sendYield = false;

    /**
     * @var bool
     */
    private $ssl = false;

    /**
     * The main coroutine id of the client.
     *
     * @var int
     */
    private $mainCoroutineId = 0;

    /**
     * @var null|SwooleHttp2Client
     */
    private $httpClient;

    /**
     * @var int
     */
    private $recvCoroutineId = 0;

    /**
     * @var int
     */
    private $sendCoroutineId = 0;

    /**
     * The hashMap of channels [streamId => response channel].
     * @var Channel[]
     */
    private $recvChannelMap = [];

    /**
     * @var int
     */
    private $waitStatus = Status::WAIT_PENDDING;

    /**
     * @var null|Channel
     */
    private $waitYield;

    /**
     * The channel to proxy send data from all of the coroutine.
     *
     * @var Channel
     */
    private $sendChannel;

    /**
     * The channel to get the current send stream id (as ret val).
     *
     * @var Channel
     */
    private $sendResultChannel;

    public function __construct(ChannelPool $channelPool)
    {
        $this->channelPool = $channelPool;
    }

    public function set(string $hostname, array $options = [])
    {
        $parts = parse_url($hostname);
        if (! $parts || ! isset($parts['host']) || ! $parts['port']) {
            throw new InvalidArgumentException("The hostname {$hostname} is illegal!");
        }
        $this->host = $parts['host'];
        $this->port = (int) $parts['port'];

        $defaultOptions = [
            'timeout' => self::GRPC_DEFAULT_TIMEOUT,
            'send_yield' => false,
            'ssl' => false,
            'ssl_host_name' => '',
            'credentials' => null,
        ];
        $this->options = $options + $defaultOptions;
        $this->timeout = &$this->options['timeout'];
        $this->sendYield = &$this->options['send_yield'];
        $this->ssl = (bool) $this->options['ssl'] || (bool) $this->options['ssl_host_name'];
    }

    public function start(): bool
    {
        if ($this->recvCoroutineId !== 0 || $this->sendCoroutineId !== 0) {
            throw new RuntimeException('Cannot restart the client.');
        }
        $this->mainCoroutineId = Coroutine::id();
        if ($this->mainCoroutineId <= 0) {
            throw new BadMethodCallException('You have to start it in an alone coroutine.');
        }
        if (! $this->getHttpClient()->connect()) {
            return false;
        }

        $this->runReceiveCoroutine();
        $this->runSendCoroutine();

        return true;
    }

    public function close($yield = false): bool
    {
        return $this->wait(Status::WAIT_CLOSE_FORCE, $yield);
    }

    public function closeRecv()
    {
        if ($this->waitStatus) {
            $shouldKill = true;
        } else {
            $shouldKill = ! $this->getHttpClient()->connect();
        }
        if ($shouldKill) {
            // Set `connected` of http client to `false`
            $this->getHttpClient()->close();
        }

        // Clear the receive channel map
        if (! empty($this->recvChannelMap)) {
            foreach ($this->recvChannelMap as $channel) {
                // If this channel has pending pop, we should push 'false' to negate the pop.
                // Otherwise we should release it directly.
                while ($channel->stats()['consumer_num'] !== 0) {
                    $channel->push(false);
                }
                $this->channelPool->release($channel);
            }
            $this->recvChannelMap = [];
        }
        return $shouldKill;
    }

    public function isConnected(): bool
    {
        return $this->httpClient->connected;
    }

    public function isStreamExist(int $streamId): bool
    {
        return isset($this->recvChannelMap[$streamId]);
    }

    public function isRunning(): bool
    {
        return $this->recvCoroutineId > 0 && ($this->sendYield === false ?: $this->sendCoroutineId > 0);
    }

    public function getHttpClient(): SwooleHttp2Client
    {
        if (! $this->httpClient instanceof SwooleHttp2Client) {
            $this->httpClient = $this->buildHttp2Client();
        }
        return $this->httpClient;
    }

    /**
     * Open a stream and return the id.
     */
    public function openStream(string $path, string $data = null): int
    {
        $request = new Request($path);
        $data && $request->data = $data;

        $request->pipeline = true;

        return $this->send($request);
    }

    public function send(Request $request): int
    {
        if (! $this->isConnected()) {
            return 0;
        }
        if ($this->sendYield === true) {
            $this->sendChannel->push($request);
            $streamId = $this->sendResultChannel->pop();
        } else {
            $streamId = $this->getHttpClient()->send($request);
        }
        if ($streamId === false) {
            throw new GrpcClientException('Failed to send the request to server', StatusCode::INTERNAL);
        }
        if ($streamId > 0) {
            $this->recvChannelMap[$streamId] = $this->channelPool->get();
        }

        return $streamId;
    }

    public function write(int $streamId, $data, bool $end = false)
    {
        return $this->httpClient->write($streamId, $data, $end);
    }

    public function recv(int $streamId, float $timeout = null)
    {
        if (! $this->isConnected() || $streamId <= 0 || ! $this->isStreamExist($streamId)) {
            return false;
        }
        $channel = $this->recvChannelMap[$streamId] ?? null;
        if ($channel instanceof Channel) {
            $response = $channel->pop($timeout === null ? $this->timeout : $timeout);
            // Pop timeout
            if ($response === false && $channel->errCode === -1) {
                unset($this->recvChannelMap[$streamId]);
            }

            return $response;
        }

        return false;
    }

    public function getErrCode(): int
    {
        return $this->httpClient ? $this->httpClient->errCode : 0;
    }

    /**
     * @param bool|float $yield
     */
    private function wait(int $type, $yield = true): bool
    {
        if (! $this->isConnected()) {
            return false;
        }
        $this->waitStatus = $type;
        if ($this->waitStatus === Status::WAIT_CLOSE) {
            return $this->yield($yield);
        }
        $closeId = $this->sendCloseRequest();
        $result = $closeId && ($this->sendYield ? $this->sendChannel->push(0) : true);
        if ($result === true) {
            $this->yield($yield);
        }
        return $result;
    }

    private function sendCloseRequest(): int
    {
        $closeRequest = new Request(Status::CLOSE_KEYWORD);

        return $this->send($closeRequest);
    }

    /**
     * @param bool|float $yield
     */
    private function yield($yield = true)
    {
        $yield = $yield === true ? -1 : $yield;
        if ($yield) {
            $this->waitYield = $this->channelPool->get();
            return $this->waitYield->pop($yield);
        }
    }

    private function runReceiveCoroutine()
    {
        // Receive wait
        Coroutine::create(function () {
            $this->recvCoroutineId = Coroutine::id();
            // Start the receive loop
            while (true) {
                $response = $this->getHttpClient()->recv();
                if ($response !== false) {
                    $streamId = $response->streamId;
                    if (! $this->isStreamExist($streamId)) {
                        continue;
                    }
                    // Force close.
                    if ($this->waitStatus === Status::WAIT_CLOSE_FORCE) {
                        if ($this->closeRecv()) {
                            break;
                        }
                    }
                    $channel = $this->recvChannelMap[$streamId];
                    $channel->push($response);
                    if (! $response->pipeline) {
                        unset($this->recvChannelMap[$streamId]);
                        $this->channelPool->push($channel);
                    }
                    // If wait status is equal to WAIT_CLOSE, and no coroutine is waiting, then break the recv loop.
                    if ($this->waitStatus === Status::WAIT_CLOSE && empty($this->recvChannelMap)) {
                        break;
                    }
                } else {
                    // If no response, then close all the connection.
                    if ($this->closeRecv()) {
                        break;
                    }
                }
            }

            // The receive coroutine is closed, notity the status to main coroutine.
            if ($this->waitYield instanceof Channel) {
                $this->waitYield->push(true);
                $this->channelPool->release($this->waitYield);
                $this->waitYield = null;
            }

            // Reset the properties.
            $this->recvCoroutineId = 0;
            $this->mainCoroutineId = 0;
            $this->waitStatus = Status::WAIT_PENDDING;
            $this->waitYield = null;
        });
    }

    private function runSendCoroutine()
    {
        if (! $this->sendYield) {
            return;
        }
        Coroutine::create(function () {
            $this->sendCoroutineId = Coroutine::id();
            $this->sendChannel = $this->channelPool->get();
            $this->sendResultChannel = $this->channelPool->get();
            while (true) {
                $data = $this->sendChannel->pop();
                if ($data === 0) {
                    break;
                }
                if ($data instanceof Request) {
                    $result = $this->getHttpClient()->send($data);
                } else {
                    $result = $this->getHttpClient()->write(...$data);
                }
                $this->sendResultChannel->push($result);
            }
            $this->sendCoroutineId = 0;
        });
    }

    private function buildHttp2Client(): SwooleHttp2Client
    {
        $httpClient = new SwooleHttp2Client($this->host, $this->port, $this->ssl);
        $httpClient->set($this->options);
        return $httpClient;
    }
}
