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

use Psr\Http\Message\RequestInterface;

/**
 * 首次请求前自动调用 /{v3|v3beta|v3alpha}/auth/authenticate 获取 token，
 * 后续请求在 Authorization 头中附带该 token；若遇到 401 或 “invalid auth token”，自动刷新重试一次。
 */
class EtcdAuthMiddleware
{
    public function __construct(
        private readonly EtcdTokenProvider $provider,
        private readonly string $cacheKey = 'default'
    ) {
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            // 鉴权接口本身跳过
            $path = $request->getUri()->getPath();
            if (str_ends_with($path, '/auth/authenticate')) {
                return $handler($request, $options);
            }

            // 未开启用户名密码则直通
            if (! $this->provider->enabled()) {
                return $handler($request, $options);
            }

            // 仅支持v3, 给业务请求加上 Authorization: {token}
            $token = $this->provider->getToken($this->cacheKey, false);
            if ($token) {
                $request = $request->withHeader('Authorization', $token);
            }

            // 首次请求
            return $handler($request, $options)->then(function ($response) use ($request, $handler, $options) {
                $status = (int) $response->getStatusCode();

                // 401/403 认为 token 失效，刷新一次并重放
                $retried = $options['__etcd_auth_retried'] ?? false;
                if (($status === 401 || $status === 403) && ! $retried) {
                    $this->provider->invalidate($this->cacheKey);
                    $newToken = $this->provider->getToken($this->cacheKey, true);

                    $newReq = $request->withHeader('Authorization', $newToken);
                    $newOpts = $options;
                    $newOpts['__etcd_auth_retried'] = true;

                    return $handler($newReq, $newOpts);
                }

                return $response;
            });
        };
    }
}
