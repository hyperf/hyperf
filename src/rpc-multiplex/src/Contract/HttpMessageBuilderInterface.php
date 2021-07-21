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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpMessageBuilderInterface
{
    public function buildRequest(array $data): ServerRequestInterface;

    public function buildResponse(ServerRequestInterface $request, array $data): ResponseInterface;

    public function persistToContext(ResponseInterface $response): ResponseInterface;
}
