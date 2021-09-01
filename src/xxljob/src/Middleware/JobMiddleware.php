<?php

declare(strict_types=1);

namespace Hyperf\XxlJob\Middleware;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\Utils\Context;
use Hyperf\XxlJob\Application;
use Hyperf\XxlJob\Logger\XxlJobLogger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JobMiddleware implements MiddlewareInterface
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container, Application $app)
    {
        $this->app = $app;
        $this->container = $container;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeaders()['xxl-job-access-token'][0] ?? '';
        if ($token != $this->app->getConfig()->getAccessToken()) {
            $response = $this->container->get(HttpResponse::class);
            $json = json_encode([
                'code' => 401,
                'msg' => 'token fail',
            ], JSON_UNESCAPED_UNICODE);
            return $response->withStatus(401)
                ->withAddedHeader('content-type', 'application/json; charset=utf-8')
                ->withBody(new SwooleStream($json));
        }
        $body = $this->container->get(ServerRequestInterface::class)->getParsedBody();

        Context::set(XxlJobLogger::MARK_JOB_LOG_ID, $body['logId'] ?? null);

        return $handler->handle($request);
    }
}
