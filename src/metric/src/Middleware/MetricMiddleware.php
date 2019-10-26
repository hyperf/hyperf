<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Metric\Middleware;

use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Metric\Timer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MetricMiddleware implements MiddlewareInterface
{
    /**
     * @var MetricFactoryInterface
     */
    private $factory;

    public function __construct(MetricFactoryInterface $factory)
    {
        $this->factory = $factory;
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
            'request_status' => '500', //default to 500 incase uncaught exception occur
            'request_path' => $request->getRequestTarget(),
            'request_method' => $request->getMethod(),
        ];
        $timer = new Timer('http_requests', $labels);
        $response = $handler->handle($request);
        $labels['request_status'] = (string) $response->getStatusCode();
        $timer->end($labels);
        return $response;
    }
}
