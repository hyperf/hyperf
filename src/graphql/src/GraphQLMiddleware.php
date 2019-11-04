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

namespace Hyperf\GraphQL;

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GraphQLMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! $this->isGraphQL($request)) {
            return $handler->handle($request);
        }

        $schema = $this->container->get(Schema::class);
        $input = $request->getParsedBody();
        $query = $input['query'];
        $variableValues = isset($input['variables']) ? $input['variables'] : null;

        $result = GraphQL::executeQuery($schema, $query, null, null, $variableValues);
        return $this->getResponse()->withBody(new SwooleStream(Json::encode($result)));
    }

    protected function getResponse(): ResponseInterface
    {
        return Context::get(ResponseInterface::class);
    }

    protected function isGraphQL(ServerRequestInterface $request): bool
    {
        return $request->getUri()->getPath() === '/graphql';
    }
}
