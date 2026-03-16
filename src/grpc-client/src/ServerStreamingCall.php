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

/**
 * Represents an active call that sends a single message and then gets a
 * stream of responses.
 */
class ServerStreamingCall extends StreamingCall
{
    public function push($message): void
    {
        throw new GrpcClientException('ServerStreamingCall can not push data from client', StatusCode::INTERNAL);
    }
}
