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
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\HandlerStackFactory;

use function Hyperf\Support\make;

class EtcdHandlerStackFactory extends HandlerStackFactory
{
    public function create(array $option = [], array $middlewares = []): HandlerStack
    {
        [
            'uri' => $uri,
            'version' => $version,
            'name' => $name,
            'password' => $password,
            'options' => $_options,
        ] = $this->getConfig($option);

        $stack = parent::create($option, $middlewares);

        if ($uri === '' || ! $name || ! $password) {
            // 未配置等场景，不注入中间件，保证对其他 HTTP 客户端零影响
            return $stack;
        }

        $options = array_replace([
            'base_uri' => $uri,
            'handler' => parent::create($option, $middlewares),
        ], $_options);

        $client = make(Client::class, [
            'config' => $options,
        ]);

        $provider = make(EtcdTokenProvider::class, [
            $client, $version, $name, $password,
        ]);

        $host = parse_url($uri, PHP_URL_HOST) ?: null;
        $cacheKey = $host ?: 'default';

        $stack->push(
            make(EtcdAuthMiddleware::class, [$provider, (string) $cacheKey]),
            'etcd.' . $version . '.auth'
        );

        return $stack;
    }

    private function getConfig(&$option): array
    {
        $config = $option['config'] ?? [];
        $auth = $config['auth'] ?? [];
        unset($option['config']);

        return [
            'uri' => $config['uri'] ?? '',
            'version' => $config['version'] ?? 'v3',
            'name' => $auth['name'] ?? null,
            'password' => $auth['password'] ?? null,
            'options' => $config['options'] ?? [],
        ];
    }
}
