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

namespace Hyperf\Etcd;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Etcd\Exception\ClientNotFindException;
use Hyperf\Guzzle\HandlerStackFactory;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class KVFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class)->get('etcd');
        $uri = $config['uri'] ?? 'http://127.0.0.1:2379';
        $version = $config['version'] ?? 'v3';
        $auth = $config['auth'] ?? [];
        $options = $config['options'] ?? [];
        $factory = $container->get(HandlerStackFactory::class);

        return $this->make($uri, $version, $auth, $options, $factory);
    }

    protected function make(string $uri, string $version, array $auth, array $options, HandlerStackFactory $factory)
    {
        $params = [
            'uri' => $uri,
            'version' => $version,
            'auth' => $auth,
            'options' => $options,
            'factory' => $factory,
        ];

        switch ($version) {
            case 'v3':
            case 'v3alpha':
            case 'v3beta':
                return make(V3\KV::class, $params);
        }

        throw new ClientNotFindException("KV of {$version} is not find.");
    }
}
