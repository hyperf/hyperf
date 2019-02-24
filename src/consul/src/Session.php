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

namespace Hyperf\Consul;

class Session extends Client implements SessionInterface
{
    public function create($body = null, array $options = []): ConsulResponse
    {
        $params = [
            'body' => $body,
            'query' => $this->resolveOptions($options, ['dc']),
        ];

        return $this->request('PUT', '/v1/session/create', $params);
    }

    public function destroy($sessionId, array $options = []): ConsulResponse
    {
        $params = [
            'query' => $this->resolveOptions($options, ['dc']),
        ];

        return $this->request('PUT', '/v1/session/destroy/' . $sessionId, $params);
    }

    public function info($sessionId, array $options = []): ConsulResponse
    {
        $params = [
            'query' => $this->resolveOptions($options, ['dc']),
        ];

        return $this->request('GET', '/v1/session/info/' . $sessionId, $params);
    }

    public function node($node, array $options = []): ConsulResponse
    {
        $params = [
            'query' => $this->resolveOptions($options, ['dc']),
        ];

        return $this->request('GET', '/v1/session/node/' . $node, $params);
    }

    public function all(array $options = []): ConsulResponse
    {
        $params = [
            'query' => $this->resolveOptions($options, ['dc']),
        ];

        return $this->request('GET', '/v1/session/list', $params);
    }

    public function renew($sessionId, array $options = []): ConsulResponse
    {
        $params = [
            'query' => $this->resolveOptions($options, ['dc']),
        ];

        return $this->request('PUT', '/v1/session/renew/' . $sessionId, $params);
    }
}
