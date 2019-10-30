<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Nats;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Nats\Annotation\Consumer as ConsumerAnnotation;
use Hyperf\Nats\Driver\DriverFactory;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;
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
        /**
         * @var string
         * @var ConsumerAnnotation $annotation
         */
        foreach ($classes as $class => $annotation) {
            $instance = make($class);
            if (! $instance instanceof AbstractConsumer) {
                continue;
            }
            $annotation->subject && $instance->setSubject($annotation->subject);
            $annotation->name && $instance->setName($annotation->name);
            $annotation->pool && $instance->setName($annotation->pool);

            $nums = $annotation->nums;
            $process = $this->createProcess($instance);
            $process->nums = (int) $nums;
            $process->name = $instance->getName() . '-' . $instance->getSubject();
            ProcessManager::register($process);
        }
    }

    private function createProcess(AbstractConsumer $consumer): AbstractProcess
    {
        return new class($this->container, $consumer) extends AbstractProcess {
            /**
             * @var AbstractConsumer
             */
            private $consumer;

            /**
             * @var Driver\DriverInterface
             */
            private $subscriber;

            public function __construct(ContainerInterface $container, AbstractConsumer $consumer)
            {
                parent::__construct($container);
                $this->consumer = $consumer;

                $pool = $this->consumer->getPool();
                $this->subscriber = $this->container->get(DriverFactory::class)->get($pool);
            }

            public function handle(): void
            {
                $this->subscriber->subscribe($this->consumer->getSubject(), function ($data) {
                    $this->consumer->handle($data);
                });

                $this->subscriber->wait();
            }
        };
    }
}
