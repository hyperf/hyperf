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
use Hyperf\Coroutine\Channel\Pool as ChannelPool;
use Hyperf\Grpc\Parser;
use Hyperf\Grpc\StatusCode;
use Hyperf\GrpcClient\Exception\GrpcClientException;
use InvalidArgumentException;
use Swoole\Http2\Response;

use function Hyperf\Support\retry;

/**
 * @method int send(Request $request)
 * @method mixed recv(int $streamId, float $timeout = null)
 * @method bool close($yield = false)
 */
class BaseClient
{
    private ?GrpcClient $grpcClient = null;

    private bool $initialized = false;

    /**
     * @var null|array<array-key,GrpcClient>
     */
    private ?array $grpcClients = null;

    private int $clientCount = 1;

    public function __construct(private string $hostname, private array $options = [])
    {
        if (
            isset($this->options['client_count'])
            && is_int($this->options['client_count'])
            && $this->options['client_count'] > 1
        ) {
            $this->clientCount = $this->options['client_count'];
        }
    }

    public function __destruct()
    {
        $this->grpcClient?->close(false);

        if ($this->grpcClients !== null) {
            foreach ($this->grpcClients as $client) {
                $client?->close(false);
            }
        }
    }

    public function __call($name, $arguments)
    {
        return $this->_getGrpcClient()->{$name}(...$arguments);
    }

    public function _getGrpcClient(): GrpcClient
    {
        // Initialize the client if not yet initialized.
        if (! $this->initialized) {
            $this->init();
        }
        // If multiple clients are used, randomly select one.
        if ($this->grpcClients !== null) {
            $this->grpcClient = $this->grpcClients[array_rand($this->grpcClients)];
        }
        // Start the client if not yet started.
        $this->start();
        // Return the client.
        return $this->grpcClient;
    }

    /**
     * Call a remote method that takes a single argument and has a
     * single output.
     *
     * @param string $method The name of the method to call
     * @param Message $argument The argument to the method
     * @param callable $deserialize A function that deserializes the response
     * @return array|Message[]|Response[]
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
        return Parser::parseResponse($this->recv($streamId), $deserialize);
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

    private function start()
    {
        $client = $this->grpcClient;
        if (! ($client->isRunning() || $client->start())) {
            $message = sprintf(
                'Grpc client start failed with error code %d when connect to %s',
                $client->getErrCode(),
                $this->hostname
            );
            throw new GrpcClientException($message, StatusCode::INTERNAL);
        }
        return true;
    }

    private function init()
    {
        $channelPool = ApplicationContext::getContainer()->get(ChannelPool::class);
        if (! empty($this->options['client'])) { // Use the specified client.
            if (! $this->options['client'] instanceof GrpcClient) {
                throw new InvalidArgumentException('Parameter client have to instanceof Hyperf\GrpcClient\GrpcClient');
            }
            $this->grpcClient = $this->options['client'];
        } elseif ($this->clientCount > 1) { // Use multiple clients.
            $count = $this->clientCount;
            for ($i = 0; $i < $count; ++$i) {
                $grpcClient = (new GrpcClient($channelPool))
                    ->set($this->hostname, $this->options);
                $this->grpcClients[] = $grpcClient;
            }
        } else { // Use single client.
            $this->grpcClient = (new GrpcClient($channelPool))
                ->set($this->hostname, $this->options);
        }

        $this->initialized = true;
    }

    private function buildRequest(string $method, Message $argument, array $options): Request
    {
        $headers = $options['headers'] ?? [];
        return new Request($method, $argument, $headers);
    }
}
