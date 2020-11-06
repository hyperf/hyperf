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
use Hyperf\Di\Container;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\RpcClient\ProxyFactory;
use Psr\Container\ContainerInterface;

class AddConsumerDefinitionListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
    public function process(object $event)
    {
        /** @var Container $container */
        $container = $this->container;
        if ($container instanceof Container) {
            $consumers = $container->get(ConfigInterface::class)->get('services.consumers', []);
            $serviceFactory = $container->get(ProxyFactory::class);
            $definitions = $container->getDefinitionSource();
            foreach ($consumers as $consumer) {
                if (empty($consumer['name'])) {
                    continue;
                }
                $serviceClass = $consumer['service'] ?? $consumer['name'];
                if (! interface_exists($serviceClass)) {
                    continue;
                }

                $proxyClass = $serviceFactory->createProxy($serviceClass);

                $definitions->addDefinition(
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
