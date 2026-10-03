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

use Google\Protobuf\Internal\Message;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Coroutine\Channel\Pool as ChannelPool;
use Hyperf\Coroutine\Locker;
use Hyperf\Grpc\StatusCode;
use Hyperf\GrpcClient\Exception\GrpcClientException;
use InvalidArgumentException;
use Throwable;

use function Hyperf\Support\retry;

/**
 * @method int send(Request $request)
 * @method mixed recv(int $streamId, float $timeout = null)
 * @method bool close($yield = false)
 */
class BaseClient
{
    private bool $initialized = false;

    /**
     * @var null|array<array-key,GrpcClient>
     */
    private ?array $grpcClients = null;

    private int $clientCount = 1;

    public function __construct(private string $hostname, private array $options = [])
    {
        $this->clientCount = max(1, (int) ($this->options['client_count'] ?? 0));
    }

    public function __destruct()
    {
        if (! $this->initialized) {
            return;
        }

        $lastException = null;
        foreach ($this->grpcClients as $client) {
            try {
                $client->close(false);
            } catch (Throwable $exception) {
                $lastException = $exception;
            }
        }

        if ($lastException) {
            throw $lastException;
        }
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->_getGrpcClient()->{$name}(...$arguments);
    }

    public function _getGrpcClient(): GrpcClient
    {
        // Lazy initialization: defer client setup until first use to optimize resource usage.
        if (! $this->initialized) {
            $this->init();
        }

        // Ensure the client connection is started before use.
        return $this->start();
    }

    /**
     * Call a remote method that takes a single argument and has a
     * single output.
     *
     * @param string $method The name of the method to call
     * @param Message $argument The argument to the method
     * @param callable $deserialize A function that deserializes the response
     * @return UnaryCall
     * @throws GrpcClientException
     */
    protected function _simpleRequest(
        string $method,
        Message $argument,
        $deserialize,
        array $metadata = [],
        array $options = []
    ) {
        $options['headers'] = ($options['headers'] ?? []) + $metadata;
        $streamId = retry($this->options['retry_attempts'] ?? 3, function () use ($method, $argument, $options) {
            $streamId = $this->send($this->buildRequest($method, $argument, $options));
            if ($streamId <= 0) {
                $this->init();
                // The client should not be used after this exception
                throw new GrpcClientException('Failed to send the request to server', StatusCode::INTERNAL);
            }
            return $streamId;
        }, $this->options['retry_interval'] ?? 100);

        return new UnaryCall($this, $streamId, $deserialize);
    }

    /**
     * Call a remote method that takes a stream of arguments and has a single
     * output.
     *
     * @param string $method The name of the method to call
     * @param callable $deserialize A function that deserializes the response
     *
     * @return ClientStreamingCall The active call object
     */
    protected function _clientStreamRequest(
        string $method,
        $deserialize,
        array $metadata = [],
        array $options = []
    ): ClientStreamingCall {
        $call = new ClientStreamingCall();
        $call->setClient($this->_getGrpcClient())
            ->setMethod($method)
            ->setDeserialize($deserialize)
            ->setMetadata($metadata);

        return $call;
    }

    /**
     * Call a remote method that takes a single argument and returns a stream
     * of responses.
     *
     * @param string $method The name of the method to call
     * @param callable $deserialize A function that deserializes the responses
     * @param array $metadata A metadata map to send to the server
     *                        (optional)
     * @param array $options An array of options (optional)
     *
     * @return ServerStreamingCall The active call object
     */
    protected function _serverStreamRequest(
        $method,
        $deserialize,
        array $metadata = [],
        array $options = []
    ) {
        $call = new ServerStreamingCall();
        $call->setClient($this->_getGrpcClient())
            ->setMethod($method)
            ->setDeserialize($deserialize)
            ->setMetadata($metadata);

        return $call;
    }

    /**
     * Call a remote method with messages streaming in both directions.
     *
     * @param string $method The name of the method to call
     * @param callable $deserialize A function that deserializes the responses
     */
    protected function _bidiRequest(
        string $method,
        $deserialize,
        array $metadata = [],
        array $options = []
    ): BidiStreamingCall {
        $call = new BidiStreamingCall();
        $call->setClient($this->_getGrpcClient())
            ->setMethod($method)
            ->setDeserialize($deserialize)
            ->setMetadata($metadata);
        return $call;
    }

    private function start(): GrpcClient
    {
        $key = Context::getOrSet(self::class . '::id', fn () => array_rand($this->grpcClients));
        $client = $this->grpcClients[$key];

        // If the client is already running, return it directly.
        if ($client->isRunning()) {
            return $client;
        }

        $lockKey = sprintf('%s:start:%d', spl_object_hash($this), $key);

        if (Locker::lock($lockKey)) {
            try {
                $client->start(); // May throw exception
            } catch (Throwable $e) {
                $message = sprintf(
                    'Grpc client start failed with error code %d when connect to %s',
                    $client->getErrCode(),
                    $this->hostname
                );
                throw new GrpcClientException($message, StatusCode::INTERNAL, $e);
            } finally {
                Locker::unlock($lockKey);
            }
        }

        return $client;
    }

    private function init()
    {
        $lockKey = sprintf('%s:init', spl_object_hash($this));

        if (Locker::lock($lockKey)) {
            try {
                if ($this->initialized) {
                    return;
                }

                $channelPool = ApplicationContext::getContainer()->get(ChannelPool::class);
                if (! empty($this->options['client'])) { // Use the specified client.
                    if (! $this->options['client'] instanceof GrpcClient) {
                        throw new InvalidArgumentException('Parameter client have to instanceof Hyperf\GrpcClient\GrpcClient');
                    }
                    $this->grpcClients[] = $this->options['client'];
                } else { // Use multiple clients.
                    for ($i = 0; $i < $this->clientCount; ++$i) {
                        $grpcClient = new GrpcClient($channelPool);
                        $grpcClient->set($this->hostname, $this->options);
                        $this->grpcClients[] = $grpcClient;
                    }
                }

                $this->initialized = true;
            } finally {
                Locker::unlock($lockKey);
            }
        }
    }

    private function buildRequest(string $method, Message $argument, array $options): Request
    {
        $headers = $options['headers'] ?? [];
        return new Request($method, $argument, $headers);
    }
}
