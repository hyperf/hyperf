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

class BaseCall
{
    /**
     * @var ?Client
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

    public function setClient($client): self
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
}
