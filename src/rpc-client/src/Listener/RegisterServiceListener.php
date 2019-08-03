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

namespace Hyperf\RpcClient\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\RpcClient\ServiceFactory;
use Hyperf\Utils\Arr;
use ProxyManager\Factory\RemoteObjectFactory;
use Psr\Container\ContainerInterface;

class RegisterServiceListener implements ListenerInterface
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
     * Automatic create proxy service from services.consumers.
     *
     * @param BootApplication $event
     */
    public function process(object $event)
    {
        /** @var Container $container */
        $container = $this->container;
        if ($container instanceof Container && class_exists(RemoteObjectFactory::class)) {
            $consumers = $container->get(ConfigInterface::class)->get('services.consumers', []);
            $serviceFactory = $container->get(ServiceFactory::class);
            $definitions = $container->getDefinitionSource();
            foreach ($consumers as $consumer) {
                if (empty($consumer['name'])) {
                    continue;
                }
                if (! class_exists($consumer['name']) && ! interface_exists($consumer['name'])) {
                    continue;
                }
                $name = $consumer['id'] ?? $consumer['name'];
                $definitions->addDefinition($name, function () use ($serviceFactory, $consumer) {
                    return $serviceFactory->createService(
                        $consumer['name'],
                        $consumer['protocol'] ?? 'jsonrpc-http',
                        Arr::only($consumer, ['load_balancer'])
                    );
                });
            }
        }
    }
}
