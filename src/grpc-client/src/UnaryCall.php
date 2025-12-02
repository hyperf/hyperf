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

use Google\Protobuf\Internal\Message;
use Hyperf\Grpc\Parser;
use stdClass;
use Swoole\Http2\Response as Http2Response;

class UnaryCall
{
    protected array $parsed;

    public function __construct(
        protected BaseClient $client,
        protected int $streamId,
        protected mixed $deserialize
    ) {
    }

    public function wait(): array
    {
        if (! $this->parsed) {
            $response = $this->client->recv($this->streamId);
            $this->parsed = $this->parse($response, $this->deserialize);
        }

        return $this->parsed;
    }

    /**
     * @param null|Http2Response $response
     * @param mixed $deserialize
     * @return array{0:null|Message,1:stdClass}
     */
    private function parse($response, $deserialize): array
    {
        $status = new stdClass();
        $status->code = 0;
        $status->details = 'OK';
        $status->rawResponse = $response;

        if (! $response) {
            $status->code = Parser::GRPC_ERROR_NO_RESPONSE;
            $status->details = 'No response';

            return [null, $status];
        }

        if (self::isInvalidStatus($response->statusCode)) {
            $status->details = $response->headers['grpc-message'] ?? 'Http status Error';
            $status->code = $response->headers['grpc-status'] ?? ($response->errCode ?: $response->statusCode);

            return [null, $status];
        }

        $grpcStatus = (int) ($response->headers['grpc-status'] ?? 0);

        if ($grpcStatus !== 0) {
            $status->code = $grpcStatus;
            $status->details = $response->headers['grpc-message'] ?? 'Unknown error';

            return [null, $status];
        }

        $message = Parser::deserializeMessage($deserialize, $response->data ?? '');

        return [$message, $status];
    }

    private static function isInvalidStatus(int $code): bool
    {
        return $code !== 0 && $code !== 200 && $code !== 400;
    }
}
