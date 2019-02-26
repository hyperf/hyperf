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

namespace Hyperf\Amqp;

use Hyperf\Process\Process;
use Hyperf\Process\ProcessRegister;
use Psr\Container\ContainerInterface;
use Doctrine\Instantiator\Instantiator;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Amqp\Message\ConsumerMessageInterface;
use Hyperf\Amqp\Annotation\Consumer as ConsumerAnnotation;

class ConsumerManager
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function run()
    {
        $classes = AnnotationCollector::getClassByAnnotation(ConsumerAnnotation::class);
        $instantiator = $this->container->get(Instantiator::class);
        /**
         * @var string
         * @var ConsumerAnnotation $annotation
         */
        foreach ($classes as $class => $annotation) {
            $instance = $instantiator->instantiate($class);
            if (! $instance instanceof ConsumerMessageInterface) {
                continue;
            }
            $annotation->exchange && $instance->setExchange($annotation->exchange);
            $annotation->routingKey && $instance->setRoutingKey($annotation->routingKey);
            $annotation->queue && $instance->setQueue($annotation->queue);
            property_exists($instance, 'container') && $instance->container = $this->container;
            $nums = $annotation->nums;
            $process = $this->createProcess($instance);
            $process->nums = (int) $nums;
            $process->name = 'Consumer-' . $instance->getQueue();
            ProcessRegister::register($process);
        }
    }

    private function createProcess(ConsumerMessageInterface $consumerMessage): Process
    {
        return new class($this->container, $consumerMessage) extends Process {
            /**
             * @var \Hyperf\Amqp\Consumer
             */
            private $consumer;

            /**
             * @var ConsumerMessageInterface
             */
            private $consumerMessage;

            public function __construct(ContainerInterface $container, ConsumerMessageInterface $consumerMessage)
            {
                parent::__construct($container);
                $this->consumer = $container->get(Consumer::class);
                $this->consumerMessage = $consumerMessage;
            }

            public function handle(): void
            {
                $this->consumer->consume($this->consumerMessage);
            }
        };
    }
}
