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

namespace Hyperf\RpcMultiplex\Contract;

use Psr\Http\Message\ServerRequestInterface;
use Swow\Psr7\Message\ResponsePlusInterface;
use Swow\Psr7\Message\ServerRequestPlusInterface;

interface HttpMessageBuilderInterface
{
    public function buildRequest(array $data, array $config = []): ServerRequestPlusInterface;

    public function buildResponse(ServerRequestInterface $request, array $data): ResponsePlusInterface;

    public function persistToContext(ResponsePlusInterface $response): ResponsePlusInterface;
}
