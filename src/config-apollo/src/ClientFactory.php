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
namespace Hyperf\ConfigApollo;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;
use Psr\Container\ContainerInterface;

class ClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        /** @var \Hyperf\ConfigApollo\Option $option */
        $option = make(Option::class);
        $option->setServer($config->get('apollo.server', 'http://127.0.0.1:8080'))
            ->setAppid($config->get('apollo.appid', ''))
            ->setCluster($config->get('apollo.cluster', ''))
            ->setClientIp($config->get('apollo.client_ip', current(swoole_get_local_ip())))
            ->setPullTimeout($config->get('apollo.pull_timeout', 10))
            ->setIntervalTimeout($config->get('apollo.interval_timeout', 60));
        $namespaces = $config->get('apollo.namespaces', []);
        $callbacks = [];
        foreach ($namespaces as $namespace => $callable) {
            // If does not exist a user-defined callback, then delegate to the dafault callback.
            if (! is_numeric($namespace) && is_callable($callable)) {
                $callbacks[$namespace] = $callable;
            }
        }
        $httpClientFactory = function (array $options = []) use ($container) {
            return $container->get(GuzzleClientFactory::class)->create($options);
        };
        return make(Client::class, compact('option', 'callbacks', 'httpClientFactory', 'config'));
    }
}
