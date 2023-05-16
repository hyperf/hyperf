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
namespace Hyperf\Nats;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Nats\Annotation\Consumer as ConsumerAnnotation;
use Hyperf\Nats\Driver\DriverFactory;
use Hyperf\Nats\Event\AfterConsume;
use Hyperf\Nats\Event\AfterSubscribe;
use Hyperf\Nats\Event\BeforeConsume;
use Hyperf\Nats\Event\BeforeSubscribe;
use Hyperf\Nats\Event\FailToConsume;
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
        /** @var array<string, ConsumerAnnotation> $classes */
        $classes = AnnotationCollector::getClassesByAnnotation(ConsumerAnnotation::class);

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
            $process->nums = $nums;
            $process->name = $instance->getName() . '-' . $instance->getSubject();
            ProcessManager::register($process);
        }
    }

    private function createProcess(AbstractConsumer $consumer): AbstractProcess
    {
        return new class($this->container, $consumer) extends AbstractProcess {
            private Driver\DriverInterface $subscriber;

            private ?EventDispatcherInterface $dispatcher = null;

            public function __construct(ContainerInterface $container, private AbstractConsumer $consumer)
            {
                parent::__construct($container);
                $pool = $this->consumer->getPool();
                $this->subscriber = $this->container->get(DriverFactory::class)->get($pool);
                if ($container->has(EventDispatcherInterface::class)) {
                    $this->dispatcher = $container->get(EventDispatcherInterface::class);
                }
            }

            public function handle(): void
            {
                while (true) {
                    $this->dispatcher?->dispatch(new BeforeSubscribe($this->consumer));
                    $this->subscriber->subscribe(
                        $this->consumer->getSubject(),
                        $this->consumer->getQueue(),
                        function ($data) {
                            try {
                                $this->dispatcher?->dispatch(new BeforeConsume($this->consumer, $data));
                                $this->consumer->consume($data);
                                $this->dispatcher?->dispatch(new AfterConsume($this->consumer, $data));
                            } catch (Throwable $throwable) {
                                $this->dispatcher?->dispatch(new FailToConsume($this->consumer, $data, $throwable));
                            }
                        }
                    );

                    $this->dispatcher?->dispatch(new AfterSubscribe($this->consumer));
                    usleep(1000);
                }
            }
        };
    }
}
