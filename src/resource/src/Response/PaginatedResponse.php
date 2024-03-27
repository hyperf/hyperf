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

use Hyperf\Codec\Json;
use Hyperf\Collection\Arr;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;

class PaginatedResponse extends Response
{
    public function toResponse(): ResponseInterface
    {
        return $this->response()
            ->setStatus($this->calculateStatus())
            ->addHeader('content-type', 'application/json; charset=utf-8')
            ->setBody(new SwooleStream(Json::encode($this->wrap(
                $this->resource->resolve(),
                array_merge_recursive(
                    $this->paginationInformation(),
                    $this->resource->with(),
                    $this->resource->additional
                )
            ))));
    }

    /**
     * Add the pagination information to the response.
     */
    protected function paginationInformation(): array
    {
        $paginated = $this->resource->resource->toArray();

        return [
            'links' => $this->paginationLinks($paginated),
            'meta' => $this->meta($paginated),
        ];
    }

    /**
     * Get the pagination links for the response.
     */
    protected function paginationLinks(array $paginated): array
    {
        return [
            'first' => $paginated['first_page_url'] ?? null,
            'last' => $paginated['last_page_url'] ?? null,
            'prev' => $paginated['prev_page_url'] ?? null,
            'next' => $paginated['next_page_url'] ?? null,
        ];
    }

    /**
     * Gather the metadata for the response.
     */
    protected function meta(array $paginated): array
    {
        return Arr::except($paginated, [
            'data',
            'first_page_url',
            'last_page_url',
            'prev_page_url',
            'next_page_url',
        ]);
    }
}
