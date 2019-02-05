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

use Doctrine\Instantiator\Instantiator;
use Hyperf\Amqp\Annotation\Consumer as ConsumerAnnotation;
use Hyperf\Amqp\Message\ConsumerMessageInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Process\Process;
use Hyperf\Process\ProcessRegister;
use Psr\Container\ContainerInterface;

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
        foreach ($classes as $class => $property) {
            $instance = $instantiator->instantiate($class);
            if (! $instance instanceof ConsumerMessageInterface) {
                continue;
            }
            $property['exchange'] && $instance->setExchange($property['exchange']);
            $property['routingKey'] && $instance->setRoutingKey($property['routingKey']);
            $property['queue'] && $instance->setQueue($property['queue']);
            property_exists($instance, 'container') && $instance->container = $this->container;
            $nums = $property['nums'] ?? 1;
            $process = $this->createProcess($instance);
            $process->nums = (int) $nums;
            $process->name = 'Consumer-' . $property['queue'];
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
