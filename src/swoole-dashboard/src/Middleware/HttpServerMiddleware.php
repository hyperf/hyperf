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

namespace Hyperf\SwooleDashboard\Middleware;

use Hyperf\Contract\ConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use StatsCenter;

class HttpServerMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    protected $name;

    public function __construct(ConfigInterface $config)
    {
        $this->name = $config->get('app_name', 'hyperf-skeleton');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $ip = swoole_get_local_ip();

        $tick = StatsCenter::beforeExecRpc($path, $this->name, $ip);
        try {
            $response = $handler->handle($request);
            StatsCenter::afterExecRpc($tick, true, $response->getStatusCode());
        } catch (\Throwable $exception) {
            StatsCenter::afterExecRpc($tick, false, $exception->getCode());
            throw $exception;
        }

        return $response;
    }
}
