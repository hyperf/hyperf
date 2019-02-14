<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpServer;

use Hyperf\Utils\Context;
use Hyperf\Utils\Contracts\Jsonable;
use Hyperf\Utils\Contracts\Arrayable;
use Swoft\Http\Message\Stream\SwooleStream;
use Hyperf\HttpServer\Exception\HttpException;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class Response implements ResponseInterface
{
    /**
     * Format data to JSON and return data with Content-Type:application/json header.
     *
     * @param array|Arrayable|Jsonable $data
     */
    public function json($data): PsrResponseInterface
    {
        $data = $this->toJson($data);
        return $this->getResponse()
            ->withAddedHeader('Content-Type', 'application/json')
            ->withBody(new SwooleStream($data));
    }

    /**
     * Format data to a string and return data with Content-Type:text/plain header.
     * @param mixed $data
     */
    public function raw($data): PsrResponseInterface
    {
        return $this->getResponse()
            ->withAddedHeader('Content-Type', 'text/plain')
            ->withBody(new SwooleStream((string) $data));
    }

    /**
     * @param array|Arrayable|Jsonable $data
     * @throws HttpException when the data encoding error
     */
    private function toJson($data): string
    {
        if (is_array($data)) {
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        if ($data instanceof Jsonable) {
            return (string) $data;
        }

        if ($data instanceof Arrayable) {
            return json_encode($data->toArray(), JSON_UNESCAPED_UNICODE);
        }

        throw new HttpException('Error encoding response data to JSON.');
    }

    /**
     * Get the response object from context.
     */
    private function getResponse(): PsrResponseInterface
    {
        return Context::get(PsrResponseInterface::class);
    }
}
