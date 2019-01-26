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

namespace Hyperf\Contract;

use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

interface ServerOnRequestInterface
{
    public function initCoreMiddleware(string $serverName): void;

    public function onRequest(SwooleRequest $request, SwooleResponse $response): void;
}
