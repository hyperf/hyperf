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
namespace Hyperf\Nsq;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Nsq\Annotation\Consumer as ConsumerAnnotation;
use Hyperf\Nsq\Event\AfterConsume;
use Hyperf\Nsq\Event\AfterSubscribe;
use Hyperf\Nsq\Event\BeforeConsume;
use Hyperf\Nsq\Event\BeforeSubscribe;
use Hyperf\Nsq\Event\FailToConsume;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

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
         * @var string $class
         * @var ConsumerAnnotation $annotation
         */
        foreach ($classes as $class => $annotation) {
            $instance = make($class);
            if (! $instance instanceof AbstractConsumer) {
                continue;
            }
            $annotation->topic && $instance->setTopic($annotation->topic);
            $annotation->channel && $instance->setChannel($annotation->channel);
            $annotation->name && $instance->setName($annotation->name);
            $annotation->pool && $instance->setPool($annotation->pool);

            $nums = $annotation->nums;
            $process = $this->createProcess($instance);
            $process->nums = (int) $nums;
            $process->name = $instance->getName() . '-' . $instance->getTopic();
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
             * @var Nsq
             */
            private $subscriber;

            /**
             * @var null|EventDispatcherInterface
             */
            private $dispatcher;

            /**
             * @var ConfigInterface
             */
            private $config;

            public function __construct(ContainerInterface $container, AbstractConsumer $consumer)
            {
                parent::__construct($container);
                $this->consumer = $consumer;
                $this->config = $container->get(ConfigInterface::class);
                $this->subscriber = make(Nsq::class, [
                    'container' => $container,
                    'pool' => $consumer->getPool(),
                ]);

                if ($container->has(EventDispatcherInterface::class)) {
                    $this->dispatcher = $container->get(EventDispatcherInterface::class);
                }
            }

            public function getConsumer(): AbstractConsumer
            {
                return $this->consumer;
            }

            public function isEnable($server): bool
            {
                return $this->config->get(
                    sprintf('nsq.%s.enable', $this->consumer->getPool()),
                    true
                ) && $this->consumer->isEnable();
            }

            public function handle(): void
            {
                $this->dispatcher && $this->dispatcher->dispatch(new BeforeSubscribe($this->consumer));
                $this->subscriber->subscribe(
                    $this->consumer->getTopic(),
                    $this->consumer->getChannel(),
                    function ($data) {
                        $result = null;
                        try {
                            $this->dispatcher && $this->dispatcher->dispatch(new BeforeConsume($this->consumer, $data));
                            $result = $this->consumer->consume($data);
                            $this->dispatcher && $this->dispatcher->dispatch(new AfterConsume($this->consumer, $data, $result));
                        } catch (\Throwable $throwable) {
                            $result = Result::DROP;
                            $this->dispatcher && $this->dispatcher->dispatch(new FailToConsume($this->consumer, $data, $throwable));
                        }

                        return $result;
                    }
                );

                $this->dispatcher && $this->dispatcher->dispatch(new AfterSubscribe($this->consumer));
            }
        };
    }
}
