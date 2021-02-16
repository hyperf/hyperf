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
namespace Hyperf\Rpc\Contract;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface HttpMessageBuilderInterface
{
    public function buildRequest(string $request, array $context = []): ServerRequestInterface;

    public function buildErrorResponse(ServerRequestInterface $request, int $code = 500, Throwable $error = null): ResponseInterface;

    /**
     * @param mixed $response
     */
    public function buildResponse(ServerRequestInterface $request, $response): ResponseInterface;

    public function persistToContext(ResponseInterface $response): ResponseInterface;
}
