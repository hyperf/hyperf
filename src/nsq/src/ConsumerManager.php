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
use Hyperf\Coroutine\Waiter;
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
use Throwable;

use function Hyperf\Support\make;

class ConsumerManager
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function run()
    {
        $classes = AnnotationCollector::getClassesByAnnotation(ConsumerAnnotation::class);
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
            private Nsq $subscriber;

            private ?EventDispatcherInterface $dispatcher = null;

            private ConfigInterface $config;

            private Waiter $waiter;

            public function __construct(ContainerInterface $container, private AbstractConsumer $consumer)
            {
                parent::__construct($container);
                $this->config = $container->get(ConfigInterface::class);
                $this->subscriber = make(Nsq::class, [
                    'container' => $container,
                    'pool' => $consumer->getPool(),
                ]);
                $this->waiter = new Waiter(-1);

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
                $this->dispatcher?->dispatch(new BeforeSubscribe($this->consumer));
                $this->subscriber->subscribe(
                    $this->consumer->getTopic(),
                    $this->consumer->getChannel(),
                    function ($data) {
                        return $this->waiter->wait(function () use ($data) {
                            $result = null;
                            try {
                                $this->dispatcher?->dispatch(new BeforeConsume($this->consumer, $data));
                                $result = $this->consumer->consume($data);
                                $this->dispatcher?->dispatch(new AfterConsume($this->consumer, $data, $result));
                            } catch (Throwable $throwable) {
                                $result = Result::DROP;
                                $this->dispatcher?->dispatch(new FailToConsume($this->consumer, $data, $throwable));
                            }

                            return $result;
                        });
                    }
                );

                $this->dispatcher?->dispatch(new AfterSubscribe($this->consumer));
            }
        };
    }
}
