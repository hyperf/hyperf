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

use Hyperf\Grpc\StatusCode;
use Hyperf\GrpcClient\Exception\GrpcClientException;
use Swoole\Http2\Response as SwooleResponse;

class Parser extends \Hyperf\Grpc\Parser
{
    public const GRPC_ERROR_NO_RESPONSE = -1;

    public static function parseResponse(?SwooleResponse $response, mixed $deserialize): Response
    {
        if (! $response || empty($response->data)) {
            throw new GrpcClientException('No Response', self::GRPC_ERROR_NO_RESPONSE);
        }
        if ($response->statusCode !== 200) {
            throw new GrpcClientException('Http Code ' . $response->statusCode, StatusCode::HTTP_GRPC_STATUS_MAPPING[$response->statusCode] ?? StatusCode::UNKNOWN);
        }
        $code = (int) ($response->headers['grpc-status'] ?? 0);
        if ($code !== 0) {
            throw new GrpcClientException($response->headers['grpc-message'] ?? '', $code);
        }
        $data = $response->data;
        $reply = static::deserializeMessage($deserialize, $data);
        return new Response($reply, $response);
    }
}
