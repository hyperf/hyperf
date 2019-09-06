<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\GrpcClient;

use Google\Protobuf\Internal\Message;
use Hyperf\Grpc\Parser;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\ChannelPool;
use InvalidArgumentException;

/**
 * @method int send(Request $request)
 * @method mixed recv(int $streamId, float $timeout = null)
 * @method bool close($yield = false)
 */
class BaseClient
{
    /**
     * @var GrpcClient
     */
    private $grpcClient;

    public function __construct(string $hostname, array $options = [])
    {
        if (! empty($options['client'])) {
            if (! ($options['client'] instanceof GrpcClient)) {
                throw new InvalidArgumentException('Parameter client have to instanceof Hyperf\GrpcClient\GrpcClient');
            }
            $this->grpcClient = $options['client'];
        } else {
            $this->grpcClient = new GrpcClient(ApplicationContext::getContainer()->get(ChannelPool::class));
            $this->grpcClient->set($hostname, $options);
        }
        $this->start();
    }

    public function __get($name)
    {
        return $this->getGrpcClient()->{$name};
    }

    public function __call($name, $arguments)
    {
        return $this->getGrpcClient()->{$name}(...$arguments);
    }

    public function start()
    {
        $client = $this->getGrpcClient();
        return $client->isRunning() || $client->start();
    }

    public function getGrpcClient(): GrpcClient
    {
        return $this->grpcClient;
    }

    /**
     * Call a remote method that takes a single argument and has a
     * single output.
     *
     * @param string $method The name of the method to call
     * @param Message $argument The argument to the method
     * @param callable $deserialize A function that deserializes the response
     * @param array $metadata A metadata map to send to the server
     *                        (optional)
     * @param array $options An array of options (optional)
     * @return []
     */
    protected function simpleRequest(
        string $method,
        Message $argument,
        $deserialize
    ) {
        $streamId = $this->send($this->buildRequest($method, $argument));
        return Parser::parseResponse($this->recv($streamId), $deserialize);
    }

    /**
     * Call a remote method that takes a stream of arguments and has a single
     * output.
     *
     * @param string $method The name of the method to call
     * @param callable $deserialize A function that deserializes the response
     * @param array $metadata A metadata map to send to the server
     *                        (optional)
     * @param array $options An array of options (optional)
     *
     * @return ClientStreamingCall The active call object
     */
    protected function clientStreamRequest(
        string $method,
        $deserialize
    ): ClientStreamingCall {
        $call = new ClientStreamingCall();
        $call->setClient($this->grpcClient)
            ->setMethod($method)
            ->setDeserialize($deserialize);

        return $call;
    }

    /**
     * Call a remote method with messages streaming in both directions.
     *
     * @param string $method The name of the method to call
     * @param callable $deserialize A function that deserializes the responses
     * @param array $metadata A metadata map to send to the server
     *                        (optional)
     * @param array $options An array of options (optional)
     * @return BidiStreamingCall|bool
     */
    protected function _bidiRequest(
        string $method,
        $deserialize
    ): BidiStreamingCall {
        $call = new BidiStreamingCall();
        $call->setClient($this->grpcClient)
            ->setMethod($method)
            ->setDeserialize($deserialize);

        return $call;
    }

    protected function buildRequest(string $method, Message $argument): \Swoole\Http2\Request
    {
        return new Request($method, $argument);
    }
}
