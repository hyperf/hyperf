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
     * The channel for the recv coroutine waiting for next send complete.
     *
     * @var Channel
     */
    private $recvWaitChannel;

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
        $this->recvWaitChannel = $channelPool->get();
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
        if (! Coroutine::inCoroutine()) {
            throw new RuntimeException('Client must be started in coroutine');
        }
        if (! $this->getHttpClient()->connect()) {
            throw new GrpcClientException('Connect failed, error=' . $this->getHttpClient()->errMsg, $this->getHttpClient()->errCode);
        }

        $this->mainCoroutineId = Coroutine::id();

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
            $shouldKill = $this->isConnected();
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
        $this->releaseRecvWaitChannel();
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
     * @param mixed $data
     */
    public function openStream(
        string $path,
        $data = '',
        string $method = '',
        bool $usePipelineRead = false,
        array $metadata = []
    ): int {
        $method = $method ?: ($data ? 'POST' : 'GET');
        $request = new Request($method);
        $request->path = $path;
        if ($data) {
            $request->data = $data;
        }
        $request->headers = $request->headers + $metadata;
        $request->pipeline = true;
        if ($usePipelineRead) {
            // @phpstan-ignore-next-line
            if (SWOOLE_VERSION_ID < 40503) {
                throw new InvalidArgumentException('Require Swoole version >= 4.5.3');
            }
            $request->usePipelineRead = true;
        }

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
            $this->recvWaitChannel->push(true);
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
        if ($this->sendYield === true) {
            return $this->sendChannel->push([$streamId, $data, $end])
                && $this->sendResultChannel->pop();
        }
        return $this->getHttpClient()->write($streamId, $data, $end);
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
            if ($response === false && $channel->errCode === SWOOLE_CHANNEL_TIMEOUT) {
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
        $this->getHttpClient()->close();
        $this->releaseRecvWaitChannel();
        $result = $this->sendYield ? $this->sendChannel->push(0) : true;
        if ($result === true) {
            $this->yield($yield);
        }
        return $result;
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
            while ($this->recvWaitChannel->pop()) {
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
                    $this->recvWaitChannel->push(true);
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

    private function releaseRecvWaitChannel()
    {
        if (!empty($this->recvWaitChannel)) {
            while ($this->recvWaitChannel->stats()['consumer_num'] !== 0) {
                $this->recvWaitChannel->push(false);
            }
            $this->channelPool->release($this->recvWaitChannel);
            $this->recvWaitChannel = null;
        }
    }
}
