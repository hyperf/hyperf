<?php

namespace Hyperf\GrpcClient;


use Google\Protobuf\Internal\Message;
use Hyperf\GrpcServer\Utils\Parser;

class BaseStub extends VirtualClient
{
    /**
     * Call a remote method that takes a single argument and has a
     * single output.
     *
     * @param string $method The name of the method to call
     * @param Message $argument The argument to the method
     * @param callable $deserialize A function that deserializes the response
     * @param array $metadata A metadata map to send to the server
     *                              (optional)
     * @param array $options An array of options (optional)
     *
     * @return []
     */
    protected function _simpleRequest(
        string $method,
        Message $argument,
        $deserialize,
        array $metadata = [],
        array $options = []
    ) {
        $request = new \swoole_http2_request;
        $request->method = 'POST';
        $request->path = $method;
        $request->data = Parser::serializeMessage($argument);

        $streamId = $this->send($request);

        return Parser::parseToResultArray(
            $this->recv($streamId),
            $deserialize
        );
    }

    /**
     * Call a remote method that takes a stream of arguments and has a single
     * output.
     *
     * @param string $method The name of the method to call
     * @param callable $deserialize A function that deserializes the response
     * @param array $metadata A metadata map to send to the server
     *                              (optional)
     * @param array $options An array of options (optional)
     *
     * @return ClientStreamingCall The active call object
     */
    protected function _clientStreamRequest(
        $method,
        $deserialize,
        array $metadata = [],
        array $options = []
    ) {
        $call = new ClientStreamingCall();
        $call->setClient($this);
        $call->setMethod($method);
        $call->setDeserialize($deserialize);

        return $call;
    }

    /**
     * Call a remote method that takes a single argument and returns a stream
     * of responses.
     *
     * @param string $method The name of the method to call
     * @param mixed $argument The argument to the method
     * @param callable $deserialize A function that deserializes the responses
     * @param array $metadata A metadata map to send to the server
     *                              (optional)
     * @param array $options An array of options (optional)
     *
     * @return ServerStreamingCall The active call object
     */
    protected function _serverStreamRequest(
        $method,
        $argument,
        $deserialize,
        array $metadata = [],
        array $options = []
    ) {
        $call = new ServerStreamingCall();
        $call->setClient($this);
        $call->setMethod($method);
        $call->setDeserialize($deserialize);

        return $call;
    }

    /**
     * Call a remote method with messages streaming in both directions.
     *
     * @param string $method The name of the method to call
     * @param callable $deserialize A function that deserializes the responses
     * @param array $metadata A metadata map to send to the server
     *                              (optional)
     * @param array $options An array of options (optional)
     * @return bool|BidiStreamingCall
     */
    protected function _bidiRequest(
        $method,
        $deserialize,
        array $metadata = [],
        array $options = []
    ) {

        $call = new BidiStreamingCall();
        $call->setClient($this);
        $call->setMethod($method);
        $call->setDeserialize($deserialize);

        return $call;
    }
}