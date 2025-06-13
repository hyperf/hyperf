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

namespace Hyperf\ConfigNacos;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Nacos\Config;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class NacosClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $options = $config->get('config_center.drivers.nacos.client', []) ?: $config->get('nacos', []);

        if (empty($options)) {
            throw new InvalidArgumentException("The config 'config_center.drivers.nacos.client' is missing.");
        }

        if (! empty($options['uri'])) {
            $baseUri = $options['uri'];
        } else {
            $baseUri = sprintf('http://%s:%d', $options['host'] ?? '127.0.0.1', $options['port'] ?? 8848);
        }

        return new NacosClient(new Config([
            'base_uri' => $baseUri,
            'username' => $options['username'] ?? null,
            'password' => $options['password'] ?? null,
            'access_key' => $options['access_key'] ?? null,
            'access_secret' => $options['access_secret'] ?? null,
            'guzzle_config' => $options['guzzle']['config'] ?? null,
            'host' => $options['host'] ?? null,
            'port' => $options['port'] ?? null,
            'grpc' => $options['grpc'] ?? [],
        ]));
    }
}
