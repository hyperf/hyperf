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
namespace Hyperf\Consul;

class Catalog extends Client implements CatalogInterface
{
    public function register($node): ConsulResponse
    {
        $params = [
            'body' => json_encode($node),
        ];

        return $this->request('PUT', '/v1/catalog/register', $params);
    }

    public function deregister($node): ConsulResponse
    {
        $params = [
            'body' => json_encode($node),
        ];

        return $this->request('PUT', '/v1/catalog/deregister', $params);
    }

    public function datacenters(): ConsulResponse
    {
        return $this->request('GET', '/v1/catalog/datacenters');
    }

    public function nodes(array $options = []): ConsulResponse
    {
        $params = [
            'query' => $this->resolveOptions($options, ['dc']),
        ];

        return $this->request('GET', '/v1/catalog/nodes', $params);
    }

    public function node($node, array $options = []): ConsulResponse
    {
        $params = [
            'query' => $this->resolveOptions($options, ['dc']),
        ];

        return $this->request('GET', '/v1/catalog/node/' . $node, $params);
    }

    public function services(array $options = []): ConsulResponse
    {
        $params = [
            'query' => $this->resolveOptions($options, ['dc']),
        ];

        return $this->request('GET', '/v1/catalog/services', $params);
    }

    public function service($service, array $options = []): ConsulResponse
    {
        $params = [
            'query' => $this->resolveOptions($options, ['dc', 'tag']),
        ];

        return $this->request('GET', '/v1/catalog/service/' . $service, $params);
    }
}
