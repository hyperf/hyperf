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

use Hyperf\Contract\StdoutLoggerInterface;
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
            $annotation->queue && $instance->setQueue($annotation->queue);
            $annotation->name && $instance->setName($annotation->name);
            $annotation->pool && $instance->setPool($annotation->pool);

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

            /**
             * @var StdoutLoggerInterface
             */
            private $logger;

            public function __construct(ContainerInterface $container, AbstractConsumer $consumer)
            {
                parent::__construct($container);
                $this->consumer = $consumer;

                $pool = $this->consumer->getPool();
                $this->subscriber = $this->container->get(DriverFactory::class)->get($pool);
                $this->logger = $container->get(StdoutLoggerInterface::class);
            }

            public function handle(): void
            {
                while (true) {
                    $this->subscriber->subscribe(
                        $this->consumer->getSubject(),
                        $this->consumer->getQueue(),
                        function ($data) {
                            $this->consumer->consume($data);
                        }
                    );

                    $this->logger->warning(sprintf(
                        'NatsConsumer[%s] subscribe timeout. Try subscribe again 1 millisecond later.',
                        $this->consumer->getName()
                    ));

                    usleep(1000);
                }
            }
        };
    }
}
