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

namespace Hyperf\RpcClient\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\RpcClient\ProxyFactory;
use Psr\Container\ContainerInterface;

class AddConsumerDefinitionListener implements ListenerInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * Automatic create proxy service definitions from services.consumers.
     *
     * @param BootApplication $event
     */
    public function process(object $event): void
    {
        $container = $this->container;
        if ($container instanceof \Hyperf\Contract\ContainerInterface) {
            $consumers = $container->get(ConfigInterface::class)->get('services.consumers', []);
            $serviceFactory = $container->get(ProxyFactory::class);
            foreach ($consumers as $consumer) {
                if (empty($consumer['name'])) {
                    continue;
                }
                $serviceClass = $consumer['service'] ?? $consumer['name'];
                if (! interface_exists($serviceClass)) {
                    continue;
                }

                $proxyClass = $serviceFactory->createProxy($serviceClass);

                $container->define(
                    $consumer['id'] ?? $serviceClass,
                    function (ContainerInterface $container) use ($consumer, $serviceClass, $proxyClass) {
                        return new $proxyClass(
                            $container,
                            $consumer['name'],
                            $consumer['protocol'] ?? 'jsonrpc-http',
                            [
                                'load_balancer' => $consumer['load_balancer'] ?? 'random',
                                'service_interface' => $serviceClass,
                            ]
                        );
                    }
                );
            }
        }
    }
}
