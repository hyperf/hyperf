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
namespace Hyperf\Contract;

use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

interface OnRequestInterface
{
    public function onRequest(SwooleRequest $request, SwooleResponse $response): void;
}
