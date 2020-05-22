<?php

namespace Hyperf\HttpServer\Resource\Json;

use Hyperf\Utils\Arr;

class PaginatedResourceResponse extends ResourceResponse
{
    public function toResponse()
    {
        return $this->wrap(
            $this->resource->resolve(),
            array_merge_recursive(
                $this->paginationInformation(),
                $this->resource->with(),
                $this->resource->additional
            )
        );
    }

    /**
     * Add the pagination information to the response.
     *
     * @return array
     */
    protected function paginationInformation()
    {
        $paginated = $this->resource->resource->toArray();

        return [
            'links' => $this->paginationLinks($paginated),
            'meta' => $this->meta($paginated),
        ];
    }

    /**
     * Get the pagination links for the response.
     *
     * @param array $paginated
     * @return array
     */
    protected function paginationLinks($paginated)
    {
        return [
            'first' => $paginated['first_page_url'] ?? null,
            'last' => $paginated['last_page_url'] ?? null,
            'prev' => $paginated['prev_page_url'] ?? null,
            'next' => $paginated['next_page_url'] ?? null,
        ];
    }

    /**
     * Gather the meta data for the response.
     *
     * @param array $paginated
     * @return array
     */
    protected function meta($paginated)
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
