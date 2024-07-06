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

namespace Hyperf\JsonRpc;

use Psr\Http\Message\ServerRequestInterface;

class HttpCoreMiddleware extends CoreMiddleware
{
    protected function handleNotFound(ServerRequestInterface $request): mixed
    {
        // @TODO Allow more health check conditions.
        if ($request->getHeaderLine('user-agent') === 'Consul Health Check') {
            // The request that from health checker, return 200 directly.
            return $this->response()->withStatus(200);
        }
        return parent::handleNotFound($request);
    }
}
