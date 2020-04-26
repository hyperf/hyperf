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
namespace Hyperf\Nsq;

use GuzzleHttp\Client;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Nsq\Annotation\Consumer as ConsumerAnnotation;
use Hyperf\Nsq\Batch;
use Hyperf\Nsq\Event\AfterConsume;
use Hyperf\Nsq\Event\AfterSubscribe;
use Hyperf\Nsq\Event\BeforeConsume;
use Hyperf\Nsq\Event\BeforeSubscribe;
use Hyperf\Nsq\Event\FailToConsume;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;
class ConsumerManager
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Hyperf\Nsq\Batch
     */
    private $batch;
    public function __construct(ContainerInterface $container, ConfigInterface $config,CacheInterface $cache,Batch $batch)
    {
        $this->container = $container;
        $this->config = $config;
        $this->cache = $cache;
        $this->batch = $batch;
    }

    public function run()
    {
        $classes = AnnotationCollector::getClassByAnnotation(ConsumerAnnotation::class);
        $nsq=$this->config->get('nsq');
        if (!empty($nsq['nsqlookup']) && !$nsq['nsqlookup']['debug']) {
            $nsqlookup=$nsq['nsqlookup'];
            $this->cache->set('nsqIpList', '');
            $nsqIpList = $this->batch->getNsqIpList($nsqlookup);
            $nsqConfig = $nsqIpList;
            $nsqConfig['nsqlookup'] = $nsqlookup;
            $this->config->set('nsq', $nsqConfig);
            /**
             * @var string
             * @var ConsumerAnnotation $annotation
             */
            foreach ($classes as $class => $annotation) {
                $instance = make($class);
                if (! $instance instanceof AbstractConsumer) {
                    continue;
                }
                if($annotation->topic){$instance->setTopic($annotation->topic);}else{$instance->setTopic($nsqConfig['nsqlookup']['topic']);}

                if($annotation->channel){$instance->setChannel($annotation->channel);}else{$instance->setChannel($nsqConfig['nsqlookup']['channel']);}

                if($annotation->name){$instance->setName($annotation->name);}else{$instance->setName($nsqConfig['nsqlookup']['name']);}
                foreach ($nsqIpList as $k => $v) {
                    $instance->setPool($k);
                    $process = $this->createProcess($instance);
                    if($annotation->nums){$process->nums=$annotation->nums;}else{ $process->nums = $nsqConfig['nsqlookup']['nums'];}
                    $process->name = $instance->getName() . $k . '-' . $instance->getTopic();
                    ProcessManager::register($process);
                }
            }
        }else{
            /**
             * @var string
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

            public function isEnable(): bool
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
