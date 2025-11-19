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
            'baseUri' => $baseUri,
            'is_auth' => $is_auth,
            'name' => $name,
            'password' => $password,
            'options' => $_options,
        ] = $this->getConfig($option);

        $stack = parent::create($option, $middlewares);

        if ($baseUri === '' || $is_auth === false) {
            return $stack;
        }

        $options = array_replace([
            'base_uri' => $baseUri,
            'handler' => parent::create($option, $middlewares),
        ], $_options);

        $client = make(Client::class, [
            'config' => $options,
        ]);

        $provider = make(EtcdTokenProvider::class, [
            $client, $is_auth, $name, $password,
        ]);

        $host = parse_url($baseUri, PHP_URL_HOST) ?: null;
        $cacheKey = $host ?: 'default';

        $stack->push(
            make(EtcdAuthMiddleware::class, [$provider, (string) $cacheKey]),
            'etcd.auth'
        );

        return $stack;
    }

    private function getConfig(&$option): array
    {
        $config = $option['_auth'] ?? [];
        unset($option['_auth']);

        return [
            'baseUri' => $config['baseUri'] ?? '',
            'is_auth' => $config['auth']['enable'] ?? false,
            'name' => $config['auth']['name'] ?? null,
            'password' => $config['auth']['password'] ?? null,
            'options' => $config['options'] ?? [],
        ];
    }
}
