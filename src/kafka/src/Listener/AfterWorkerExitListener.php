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
namespace Hyperf\Kafka\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnWorkerExit;
use Hyperf\Kafka\Producer;
use Hyperf\Kafka\ProducerManager;
use Psr\Container\ContainerInterface;

class AfterWorkerExitListener implements ListenerInterface
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
        return [OnWorkerExit::class];
    }

    public function process(object $event)
    {
        if ($this->container->has(Producer::class)) {
            $this->container->get(Producer::class)->close();
        }
        if ($this->container->has(ProducerManager::class)) {
            $this->container->get(ProducerManager::class)->closeAll();
        }
    }
}
