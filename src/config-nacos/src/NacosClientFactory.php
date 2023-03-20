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
use Hyperf\Nacos\Application;
use Hyperf\Nacos\Config;
use Psr\Container\ContainerInterface;

class NacosClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class)->get('config_center.drivers.nacos.client', []);
        if (empty($config)) {
            return $container->get(Application::class);
        }

        if (! empty($config['uri'])) {
            $baseUri = $config['uri'];
        } else {
            $baseUri = sprintf('http://%s:%d', $config['host'] ?? '127.0.0.1', $config['port'] ?? 8848);
        }

        return new Application(new Config([
            'base_uri' => $baseUri,
            'username' => $config['username'] ?? null,
            'password' => $config['password'] ?? null,
            'access_key' => $config['access_key'] ?? null,
            'access_secret' => $config['access_secret'] ?? null,
            'guzzle_config' => $config['guzzle']['config'] ?? null,
            'host' => $config['host'] ?? null,
            'port' => $config['port'] ?? null,
            'grpc' => $config['grpc'] ?? [],
        ]));
    }
}
