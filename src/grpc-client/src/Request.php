<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\GrpcClient;

use Swoole\Http2\Request as BaseRequest;

class Request extends BaseRequest
{
    const CONTENT_TYPE = 'application/grpc';

    public function __construct()
    {
        $this->headers['content-type'] = self::CONTENT_TYPE;
    }
}