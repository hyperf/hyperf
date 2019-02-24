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

class KV extends Client implements KVInterface
{
    public function get($key, array $options = []): ConsulResponse
    {
        $params = [
            'query' => $this->resolveOptions($options, ['dc', 'recurse', 'keys', 'separator', 'raw', 'stale', 'consistent', 'default']),
        ];

        return $this->request('GET', '/v1/kv/' . $key, $params);
    }

    public function put($key, $value, array $options = []): ConsulResponse
    {
        $params = [
            'body' => (string) $value,
            'query' => $this->resolveOptions($options, ['dc', 'flags', 'cas', 'acquire', 'release']),
        ];

        return $this->request('PUT', '/v1/kv/' . $key, $params);
    }

    public function delete($key, array $options = []): ConsulResponse
    {
        $params = [
            'query' => $this->resolveOptions($options, ['dc', 'recurse']),
        ];

        return $this->request('DELETE', '/v1/kv/' . $key, $params);
    }
}
