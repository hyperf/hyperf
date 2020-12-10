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
namespace Hyperf\Nacos\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnShutdown;
use Hyperf\Nacos\Api\NacosInstance;
use Hyperf\Nacos\Contract\LoggerInterface;
use Hyperf\Nacos\Instance;
use Hyperf\Server\Event\CoroutineServerStop;
use Psr\Container\ContainerInterface;

class OnShutdownListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var bool
     */
    private $processed = false;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            OnShutdown::class,
            CoroutineServerStop::class,
        ];
    }

    public function process(object $event)
    {
        if ($this->processed) {
            return;
        }
        $this->processed = true;

        $config = $this->container->get(ConfigInterface::class);
        if (! $config->get('nacos.enable', true)) {
            return;
        }
        if (! $config->get('nacos.remove_node_when_server_shutdown', false)) {
            return;
        }

        $logger = $this->container->get(LoggerInterface::class);

        $instance = $this->container->get(Instance::class);
        /** @var NacosInstance $nacosInstance */
        $nacosInstance = make(NacosInstance::class);
        if ($nacosInstance->delete($instance)) {
            $logger && $logger->info('nacos instance delete success.');
        } else {
            $logger && $logger->erro('nacos instance delete fail when shutdown.');
        }
    }
}
