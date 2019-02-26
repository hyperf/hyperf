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

namespace Hyperf\ConfigApollo;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;

class ClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $option = new Option();
        $option->setServer($config->get('config-center.apollo.server', 'http://127.0.0.1:8080'))
            ->setAppid($config->get('config-center.apollo.appid', ''))
            ->setCluster($config->get('config-center.apollo.cluster', ''));
        $namespaces = $config->get('config-center.apollo.namespaces', []);
        $callbacks = [];
        foreach ($namespaces as $namespace => $callable) {
            // If does not exist a user-defined callback, then delegate to the dafault callback.
            if (! is_numeric($namespace) && is_callable($callbacks)) {
                $callbacks[$namespace] = $callable;
            }
        }
        $httpClientFactory = function (array $options = []) use ($container) {
            return $container->get(GuzzleClientFactory::class)->create($options);
        };
        return new Client($option, $callbacks, $httpClientFactory, $config);
    }
}
