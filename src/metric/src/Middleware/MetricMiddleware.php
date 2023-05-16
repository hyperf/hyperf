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
namespace Hyperf\Metric\Middleware;

use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Metric\CoroutineServerStats;
use Hyperf\Metric\Timer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class MetricMiddleware implements MiddlewareInterface
{
    public function __construct(protected CoroutineServerStats $stats)
    {
    }

    /**
     * Process an incoming server request.
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $labels = [
            'request_status' => '500', // default to 500 in case uncaught exception occur
            'request_path' => $this->getPath($request),
            'request_method' => $request->getMethod(),
        ];
        $timer = new Timer('http_requests', $labels);

        ++$this->stats->accept_count;
        ++$this->stats->request_count;
        ++$this->stats->connection_num;

        try {
            $response = $handler->handle($request);
            $labels['request_status'] = (string) $response->getStatusCode();
        } catch (Throwable $exception) {
            if ($exception instanceof HttpException) {
                $labels['request_status'] = (string) $exception->getStatusCode();
            }
            throw $exception;
        } finally {
            $timer->end($labels);
            ++$this->stats->close_count;
            ++$this->stats->response_count;
            --$this->stats->connection_num;
        }

        return $response;
    }

    protected function getPath(ServerRequestInterface $request): string
    {
        $dispatched = $request->getAttribute(Dispatched::class);
        if (! $dispatched) {
            return $request->getUri()->getPath();
        }
        if (! $dispatched->handler) {
            return 'not_found';
        }
        return $dispatched->handler->route;
    }
}
