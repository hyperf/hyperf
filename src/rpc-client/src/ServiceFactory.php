<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\RpcClient;

use ProxyManager\Configuration;
use ProxyManager\Factory\RemoteObjectFactory;
use Psr\Container\ContainerInterface;

class ServiceFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var RemoteObjectFactory[]
     */
    private $proxyFactoryCache;

    /**
     * ServiceFactory constructor.
     * @param ContainerInterface $container
     * @param RemoteObjectFactory $proxyFactory
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function createService(string $serviceName, string $protocol = 'jsonrpc-http')
    {
        return $this->getProxyFactory($protocol)->createProxy($serviceName);
    }

    private function getProxyFactory(string $protocol)
    {
        if (! isset($this->proxyFactoryCache[$protocol])) {
            $this->proxyFactoryCache[$protocol] = new RemoteObjectFactory(
                new ServiceAdapter($this->container, $protocol),
                make(Configuration::class)
            );
        }
        return $this->proxyFactoryCache[$protocol];
    }
}
