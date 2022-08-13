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
use Hyperf\Grpc\Parser;
use Hyperf\Grpc\StatusCode;
use Hyperf\GrpcClient\Exception\GrpcClientException;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\ChannelPool;
use InvalidArgumentException;
use Swoole\Http2\Response;

/**
 * @method int send(Request $request)
 * @method mixed recv(int $streamId, float $timeout = null)
 * @method bool close($yield = false)
 */
class BaseClient
{
    protected ?GrpcClient $grpcClient;

    protected array $options;

    protected string $hostname;

    protected bool $initialized = false;

    protected string $basePath = '';

    public function __construct(string $hostname, array $options = [])
    {
        $this->hostname = $hostname;
        $this->options = $options;
    }

    public function __destruct()
    {
        if ($this->grpcClient) {
            $this->grpcClient->close(false);
        }
    }

    public function __get($name)
    {
        if (! $this->initialized) {
            $this->init();
        }
        return $this->_getGrpcClient()->{$name};
    }

    public function __call($name, $arguments)
    {
        if (! $this->initialized) {
            $this->init();
        }
        return $this->_getGrpcClient()->{$name}(...$arguments);
    }

    public function _getGrpcClient(): GrpcClient
    {
        if (! $this->initialized) {
            $this->init();
        }
        return $this->grpcClient;
    }

    public function request(string $path, Message $argument, string $class, array $headers = []): array
    {
        $streamId = retry($this->options['retry_attempts'] ?? 3, function () use ($path, $argument, $headers) {
            $streamId = $this->send($this->buildRequest($path, $argument, $headers));
            if ($streamId <= 0) {
                $this->init();
                // The client should not be used after this exception
                throw new GrpcClientException('Failed to send the request to server', StatusCode::INTERNAL);
            }
            return $streamId;
        }, $this->options['retry_interval'] ?? 100);
        // return [$message, $status, $response];
        return Parser::parseResponse($this->recv($streamId), [$class, 'decode']);
    }

    public function get(string $path, Message $argument, string $class, array $headers = []): array
    {
        return $this->request($path, $argument, $class, ['method' => 'GET'] + $headers);
    }

    public function post(string $path, Message $argument, string $class, array $headers = []): array
    {
        return $this->request($path, $argument, $class, ['method' => 'POST'] + $headers);
    }

    public function url(string $path): string
    {
        return $this->hostname . $this->basePath . $path;
    }

    /**
     * Call a remote method that takes a single argument and has a
     * single output.
     *
     * @param string $path The name of the method to call
     * @param Message $argument The argument to the method
     * @param callable $deserialize A function that deserializes the response
     * @throws GrpcClientException
     * @return array|\Google\Protobuf\Internal\Message[]|Response[]
     */
    protected function _simpleRequest(
        string $path,
        Message $argument,
        $deserialize,
        array $metadata = [],
        array $options = []
    ) {
        return $this->request($path, $argument, $deserialize[0], ($options['headers'] ?? []) + $metadata);
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

    protected function init()
    {
        if (! empty($this->options['client'])) {
            if (! ($this->options['client'] instanceof GrpcClient)) {
                throw new InvalidArgumentException('Parameter client have to instanceof Hyperf\GrpcClient\GrpcClient');
            }
            $this->grpcClient = $this->options['client'];
        } else {
            $this->grpcClient = new GrpcClient(ApplicationContext::getContainer()->get(ChannelPool::class));
            $this->grpcClient->set($this->hostname, $this->options);
        }
        if (! $this->start()) {
            $message = sprintf(
                'Grpc client start failed with error code %d when connect to %s',
                $this->grpcClient->getErrCode(),
                $this->hostname
            );
            throw new GrpcClientException($message, StatusCode::INTERNAL);
        }

        $this->initialized = true;
    }

    protected function buildRequest(string $path, Message $argument, array $headers): Request
    {
        $path = $this->basePath . $path;
        return new Request($path, $argument, $headers);
    }

    private function start()
    {
        $client = $this->grpcClient;
        return $client->isRunning() || $client->start();
    }
}
