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
namespace Hyperf\GrpcServer\Exception;

use Google\Protobuf\Any;
use Google\Protobuf\Internal\Message;
use Google\Rpc\Status;
use Hyperf\Grpc\StatusCode;
use Throwable;

class GrpcStatusException extends GrpcException
{
    /**
     * Represent protobuf field <code>int32 code = 1
     * extract from Status Message.
     */
    protected int $statusCode = StatusCode::OK;

    /**
     * Represent protobuf field <code>string message = 2
     * extract from Status Message.
     */
    protected string $statusMessage = '';

    /**
     * Represent protobuf field <code>repeated .google.protobuf.Any details = 3
     * extract from Status Message.
     * @var Message[] array
     */
    protected array $statusDetails = [];

    public function __construct($message = '', $code = 0, Throwable $previous = null, protected ?Status $status = null)
    {
        parent::__construct($message, $code, $previous);

        if (is_object($this->status)) {
            $this->withStatus($this->status);
        }
    }

    public function withStatus(Status $status): static
    {
        $this->status = $status;
        $this->extractFromStatus($status);

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getStatusMessage(): string
    {
        return $this->statusMessage;
    }

    /**
     * @return Message[]
     */
    public function getStatusDetails(): array
    {
        return $this->statusDetails;
    }

    private function extractFromStatus()
    {
        if ($this->status->getCode() == StatusCode::OK) {
            return;
        }

        $this->statusCode = $this->status->getCode();
        $this->statusMessage = $this->status->getMessage();
        $this->statusDetails = array_map(
            static fn (Any $detail) => $detail->unpack(),
            iterator_to_array($this->status->getDetails()->getIterator())
        );
    }
}
