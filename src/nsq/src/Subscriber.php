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
namespace Hyperf\Nsq;

use Hyperf\Codec\Json;
use Hyperf\Engine\Contract\SocketInterface;

class Subscriber
{
    public const TYPE_RESPONSE = 0;

    public const TYPE_ERROR = 1;

    public const TYPE_MESSAGE = 2;

    protected int $size = 0;

    protected string $type = '';

    protected string $payload = '';

    public function __construct(protected SocketInterface $socket)
    {
    }

    public function recv()
    {
        $data = $this->socket->recvAll(8);
        $this->size = (int) sprintf('%u', unpack('N', substr($data, 0, 4))[1]);
        $this->type = sprintf('%u', unpack('N', substr($data, 4, 4))[1]);
        $length = $this->size - 4;
        $data = '';
        while ($len = $length - strlen($data)) {
            if ($len <= 0) {
                break;
            }
            $data .= $this->socket->recvAll($len);
        }
        $this->payload = Packer::unpackString($data);
        return $this;
    }

    public function getMessage(): Message
    {
        return new Message($this->getPayload());
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function getJsonPayload(): array
    {
        return Json::decode($this->getPayload());
    }

    public function isMessage(): bool
    {
        return (int) $this->type === self::TYPE_MESSAGE;
    }

    public function isHeartbeat(): bool
    {
        return $this->isMatchResponse('_heartbeat_');
    }

    public function isOk(): bool
    {
        return $this->isMatchResponse('OK');
    }

    private function isMatchResponse($response): bool
    {
        return (int) $this->type === self::TYPE_RESPONSE && $response === $this->getPayload();
    }
}
