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

namespace Hyperf\Etcd\V3\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class EtcdTokenProvider
{
    /**
     * 简单进程内缓存：["cacheKey" => ["token" => string, "exp" => int]]
     * etcd token 不返回过期时间；这里用一个保守的 5 分钟软过期，遇到 401/403 会强制刷新。
     */
    private array $tokens = [];

    public function __construct(
        private Client $http,
        private bool $is_auth = false,
        private ?string $name = null,
        private ?string $password = null
    ) {
    }

    public function enabled(): bool
    {
        return (bool) $this->is_auth && ! empty($this->name) && ! empty($this->password);
    }

    /**
     * @throws GuzzleException|RuntimeException
     */
    public function getToken(string $cacheKey = 'default', bool $forceRefresh = false): ?string
    {
        if (! $this->enabled()) {
            return null;
        }

        $now = time();
        if (! $forceRefresh && isset($this->tokens[$cacheKey]) && ($this->tokens[$cacheKey]['exp'] ?? 0) > ($now + 10)) {
            return $this->tokens[$cacheKey]['token'];
        }

        $response = $this->http->post('auth/authenticate', [
            'json' => [
                'name' => $this->name,
                'password' => $this->password,
            ],
        ]);
        $data = json_decode((string) $response->getBody(), true) ?: [];
        $token = $data['token'] ?? null;
        if (! is_string($token) || $token === '') {
            throw new RuntimeException('etcd authenticate token failed.');
        }

        $this->tokens[$cacheKey] = [
            'token' => $token,
            'exp' => $now + 300, // 5 分钟保守过期
        ];

        return $token;
    }

    public function invalidate(string $cacheKey = 'default'): void
    {
        unset($this->tokens[$cacheKey]);
    }
}
