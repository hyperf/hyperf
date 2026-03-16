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

namespace Hyperf\ConfigEtcd;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\HandlerStackFactory;
use Psr\Container\ContainerInterface;

class KVFactory extends \Hyperf\Etcd\KVFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        if ($client = $config->get('config_center.drivers.etcd.client')) {
            $uri = $client['uri'] ?? 'http://127.0.0.1:2379';
            $version = $client['version'] ?? 'v3beta';
            $options = $client['options'] ?? [];
            $factory = $container->get(HandlerStackFactory::class);

            return $this->make($uri, $version, $options, $factory);
        }

        return parent::__invoke($container);
    }
}
