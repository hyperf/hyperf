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

    public function send($message = null): bool
    {
        if (! $this->getStreamId()) {
            $this->setStreamId($this->client->openStream($this->method, Parser::serializeMessage($message)));
            return $this->getStreamId() > 0;
        }
        throw new RuntimeException('You can only send once by a streaming call except connection closed and you retry.');
    }

    public function push($message): bool
    {
        if (! $this->getStreamId()) {
            $this->setStreamId($this->client->openStream($this->method));
        }
        return $this->client->write($this->getStreamId(), Parser::serializeMessage($message), false);
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
        return Parser::parseResponse($recv, $this->deserialize);
    }

    public function end(): bool
    {
        if (! $this->getStreamId()) {
            return false;
        }
        $ret = $this->client->write($this->getStreamId(), null, true);
        if ($ret) {
            $this->setStreamId(0);
        }
        return $ret;
    }
}
