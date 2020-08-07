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
namespace Hyperf\Resource\Response;

use Hyperf\Resource\Json\JsonResource;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;

class ResponseEmitter extends \Hyperf\HttpServer\ResponseEmitter
{
    public function emit(ResponseInterface $response, Response $swooleResponse, bool $withContent = true)
    {
        if ($response instanceof JsonResource) {
            $response = $response->toResponse();
        }

        return parent::emit($response, $swooleResponse, $withContent);
    }
}
