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
use Hyperf\Support\Network;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class ClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        /** @var \Hyperf\ConfigApollo\Option $option */
        $option = make(Option::class);
        $option->setServer($config->get('config_center.drivers.apollo.server', 'http://127.0.0.1:8080'))
            ->setAppid($config->get('config_center.drivers.apollo.appid', ''))
            ->setCluster($config->get('config_center.drivers.apollo.cluster', ''))
            ->setClientIp($config->get('config_center.drivers.apollo.client_ip', Network::ip()))
            ->setPullTimeout($config->get('config_center.drivers.apollo.pull_timeout', 10))
            ->setIntervalTimeout($config->get('config_center.drivers.apollo.interval_timeout', 60))
            ->setSecret($config->get('config_center.drivers.apollo.secret', ''));
        $httpClientFactory = function (array $options = []) use ($container) {
            return $container->get(GuzzleClientFactory::class)->create($options);
        };
        return make(Client::class, compact('option', 'httpClientFactory'));
    }
}
