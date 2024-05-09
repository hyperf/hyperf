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

namespace Hyperf\Grpc;

use Google\Protobuf\GPBEmpty;
use Google\Protobuf\Internal\Message;
use Google\Rpc\Status;
use Swoole\Http\Response;
use Swoole\Http2\Response as Http2Response;

class Parser
{
    public const GRPC_ERROR_NO_RESPONSE = -1;

    public static function pack(string $data): string
    {
        return pack('CN', 0, strlen($data)) . $data;
    }

    public static function unpack(string $data): string
    {
        // it's the way to verify the package length
        // 1 + 4 + data
        // $len = unpack('N', substr($data, 1, 4))[1];
        // assert(strlen($data) - 5 === $len);
        return substr($data, 5);
    }

    public static function serializeMessage($data)
    {
        return self::pack(self::serializeUnpackedMessage($data));
    }

    public static function deserializeMessage($deserialize, string $value)
    {
        if (empty($value)) {
            return null;
        }

        return self::deserializeUnpackedMessage($deserialize, self::unpack($value));
    }

    /**
     * @param null|Http2Response $response
     * @param mixed $deserialize
     * @return \Grpc\StringifyAble[]|Http2Response[]|Message[]
     */
    public static function parseResponse($response, $deserialize): array
    {
        if (! $response) {
            return ['No response', self::GRPC_ERROR_NO_RESPONSE, $response];
        }
        if (self::isInvalidStatus($response->statusCode)) {
            $message = $response->headers['grpc-message'] ?? 'Http status Error';
            $code = $response->headers['grpc-status'] ?? ($response->errCode ?: $response->statusCode);
            return [$message, (int) $code, $response];
        }
        $grpcStatus = (int) ($response->headers['grpc-status'] ?? 0);
        if ($grpcStatus !== 0) {
            return [$response->headers['grpc-message'] ?? 'Unknown error', $grpcStatus, $response];
        }
        $data = $response->data ?? '';
        $reply = self::deserializeMessage($deserialize, $data);
        $status = (int) ($response->headers['grpc-status'] ?? 0);
        return [$reply, $status, $response];
    }

    /**
     * @param Response $response
     */
    public static function statusFromResponse($response): ?Status
    {
        $detailsEncoded = $response->headers['grpc-status-details-bin'] ?? '';

        if (! $detailsEncoded || ! $detailsBin = base64_decode($detailsEncoded, true)) {
            return null;
        }
        return self::deserializeUnpackedMessage([Status::class, ''], $detailsBin);
    }

    public static function statusToDetailsBin(Status $status): string
    {
        return base64_encode(self::serializeUnpackedMessage($status));
    }

    private static function deserializeUnpackedMessage($deserialize, string $unpacked)
    {
        if (is_array($deserialize)) {
            [$className, $deserializeFunc] = $deserialize;
            /** @var Message $object */
            $object = new $className();
            if ($deserializeFunc && method_exists($object, $deserializeFunc)) {
                $object->{$deserializeFunc}($unpacked);
            } else {
                // @noinspection PhpUndefinedMethodInspection
                $object->mergeFromString($unpacked);
            }
            return $object;
        }
        return call_user_func($deserialize, $unpacked);
    }

    private static function serializeUnpackedMessage($data): string
    {
        if ($data === null) {
            $data = new GPBEmpty();
        }
        if (method_exists($data, 'encode')) {
            $data = $data->encode();
        } elseif (method_exists($data, 'serializeToString')) {
            $data = $data->serializeToString();
        } elseif (method_exists($data, 'serialize')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $data = $data->serialize();
        }

        return (string) $data;
    }

    private static function isInvalidStatus(int $code): bool
    {
        return $code !== 0 && $code !== 200 && $code !== 400;
    }
}
