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

namespace Hyperf\Consul;

use Psr\Container\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;

class ClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Client(function (array $options) {
            return GuzzleClientFactory::createClient($options);
        }, $container->get(StdoutLoggerInterface::class));
    }
}
