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
namespace Hyperf\SwooleTracker\Middleware;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Network;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SwooleTracker\Stats;
use Throwable;

class HttpServerMiddleware implements MiddlewareInterface
{
    protected ?string $name = null;

    public function __construct(ConfigInterface $config)
    {
        $this->name = $config->get('app_name', 'hyperf-skeleton');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (class_exists(Stats::class)) {
            $path = $request->getUri()->getPath();
            $ip = Network::ip();
            $traceId = $request->getHeaderLine('x-swoole-traceid') ?: '';
            $spanId = $request->getHeaderLine('x-swoole-spanid') ?: '';

            $tick = Stats::beforeExecRpc($path, $this->name, $ip, $traceId, $spanId);
            try {
                $response = $handler->handle($request);
                Stats::afterExecRpc($tick, true, $response->getStatusCode());
            } catch (Throwable $exception) {
                Stats::afterExecRpc($tick, false, $exception->getCode());
                throw $exception;
            }
        } else {
            $response = $handler->handle($request);
        }

        return $response;
    }
}
