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

namespace Hyperf\GraphQL;

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use Hyperf\Codec\Json;
use Hyperf\Context\ResponseContext;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GraphQLMiddleware implements MiddlewareInterface
{
    public function __construct(protected Schema $schema)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! $this->isGraphQL($request)) {
            return $handler->handle($request);
        }

        $input = $request->getParsedBody();
        $query = $input['query'];
        $variableValues = $input['variables'] ?? null;

        $result = GraphQL::executeQuery($this->schema, $query, null, null, $variableValues);
        return ResponseContext::get()->setBody(new SwooleStream(Json::encode($result)));
    }

    protected function isGraphQL(ServerRequestInterface $request): bool
    {
        return $request->getUri()->getPath() === '/graphql';
    }
}
