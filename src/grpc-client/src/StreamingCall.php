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

use Hyperf\Grpc\Parser;
use Hyperf\Grpc\StatusCode;
use Hyperf\GrpcClient\Exception\GrpcClientException;
use RuntimeException;

class StreamingCall
{
    /**
     * @var GrpcClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $method = '';

    /**
     * @var mixed
     */
    protected $deserialize;

    /**
     * @var int
     */
    protected $streamId = 0;

    /**
     * @var array
     */
    protected $metadata;

    public function setClient(GrpcClient $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function setDeserialize($deserialize): self
    {
        $this->deserialize = $deserialize;
        return $this;
    }

    public function getStreamId(): int
    {
        return $this->streamId;
    }

    public function setStreamId(int $streamId): self
    {
        $this->streamId = $streamId;
        return $this;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function send($message = null): void
    {
        if ($this->getStreamId() <= 0) {
            $streamId = $this->client->openStream(
                $this->method,
                Parser::serializeMessage($message),
                '',
                true,
                $this->metadata
            );
            if ($streamId <= 0) {
                throw $this->newException();
            }
            $this->setStreamId($streamId);
            return;
        }
        throw new RuntimeException('You can only send a streaming call once unless retrying after the connection is closed.');
    }

    public function push($message): void
    {
        if (! $this->getStreamId()) {
            $this->setStreamId($this->client->openStream(
                $this->method,
                null,
                '',
                true,
                $this->metadata
            ));
        }
        $success = $this->client->write($this->getStreamId(), Parser::serializeMessage($message), false);
        if (! $success) {
            throw $this->newException();
        }
    }

    public function recv(float $timeout = -1.0)
    {
        if ($this->getStreamId() <= 0) {
            $recv = false;
        } else {
            $recv = $this->client->recv($this->getStreamId(), $timeout);
            if (! $this->client->isStreamExist($this->getStreamId())) {
                // stream lost, we need re-push
                $this->streamId = 0;
            }
        }
        // disconnected or timed out
        if ($recv === false) {
            throw $this->newException();
        }
        // server ended the stream
        if ($recv->pipeline === false) {
            $this->streamId = 0;
            return [null, 0, $recv];
        }

        return Parser::parseResponse($recv, $this->deserialize);
    }

    public function end(): void
    {
        if (! $this->getStreamId()) {
            throw $this->newException();
        }
        // we cannot reset the streamId here, otherwise the client streaming will break.
        $success = $this->client->write($this->getStreamId(), null, true);
        if (! $success) {
            throw $this->newException();
        }
    }

    private function newException(): GrpcClientException
    {
        return new GrpcClientException('the remote server may have been disconnected or timed out', StatusCode::INTERNAL);
    }
}
