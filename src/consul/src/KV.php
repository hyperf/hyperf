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
            'body' => json_encode($value),
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
