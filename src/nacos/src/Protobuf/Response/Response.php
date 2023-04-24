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
namespace Hyperf\Nacos\Protobuf\Response;

use Hyperf\Codec\Json;
use Hyperf\Collection\Arr;
use Hyperf\Contract\JsonDeSerializable;
use Hyperf\Grpc\Parser;
use Hyperf\Nacos\Protobuf\Payload;
use Stringable;

class Response implements JsonDeSerializable, Stringable
{
    public function __construct(
        public int $resultCode,
        public int $errorCode,
        public bool $success,
        public ?string $message = null,
        public ?string $requestId = null,
    ) {
    }

    public function __toString(): string
    {
        return Json::encode($this);
    }

    public static function jsonDeSerialize(mixed $data): static
    {
        /** @var Payload $payload */
        $payload = Parser::deserializeMessage([Payload::class, 'decode'], $data);

        $json = Json::decode($payload->getBody()->getValue());
        $class = Mapping::$mappings[$payload->getMetadata()->getType()] ?? null;
        if (! $class) {
            return new static(...self::namedParameters($json));
        }

        /* @phpstan-ignore-next-line */
        return new $class($json);
    }

    protected static function namedParameters(array $data): array
    {
        return Arr::only($data, ['resultCode', 'errorCode', 'success', 'message', 'requestId']);
    }
}
