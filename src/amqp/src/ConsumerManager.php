<?php

namespace Hyperf\Amqp;


use Hyperf\Amqp\Message\ConsumerInterface;
use Hyperf\Amqp\Message\ConsumerMessageInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Amqp\Annotation\Consumer as ConsumerAnnotation;
use Hyperf\Process\ProcessRegister;
use Hyperf\Process\Process;
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
        $consumers = $processes = [];
        $classes = AnnotationCollector::getClassByAnnotation(ConsumerAnnotation::class);
        foreach ($classes as $class => $property) {
            $instance = new $class();
            if (! $instance instanceof ConsumerMessageInterface) {
                continue;
            }
            $property['exchange'] && $instance->setExchange($property['exchange']);
            $property['routingKey'] && $instance->setRoutingKey($property['routingKey']);
            $property['queue'] && $instance->setQueue($property['queue']);
            $property['nums'] && ProcessRegister::register($this->createProcess($instance), $property['nums']);
        }

    }

    private function createProcess(ConsumerMessageInterface $consumer, int $nums): Process
    {
        $instance = new class extends Process
        {

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
        return (new $instance($this->container, $consumer))->setNums($nums);
    }

}