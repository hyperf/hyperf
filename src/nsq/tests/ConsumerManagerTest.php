<?php


namespace HyperfTest\Nsq;


use Hyperf\Amqp\Message\ConsumerMessageInterface;
use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Nsq\AbstractConsumer;
use Hyperf\Nsq\Annotation\Consumer;
use Hyperf\Nsq\ConsumerManager;
use Hyperf\Process\ProcessManager;
use HyperfTest\Nsq\Stub\ContainerStub;
use HyperfTest\Nsq\Stub\DemoConsumer;
use HyperfTest\Nsq\Stub\DisabledDemoConsumer;
use PHPUnit\Framework\TestCase;

class ConsumerManagerTest extends TestCase
{
    protected $topic;
    protected $channel;
    protected $name;
    protected $nums;
    protected $container;

    protected function tearDown()
    {
        parent::tearDown();
        ProcessManager::clear();
    }

    public function testConsumerAnnotation()
    {
        $container = ContainerStub::getContainer();
        AnnotationCollector::collectClass(DemoConsumer::class, Consumer::class, new Consumer([
            'topic' => $this->topic = uniqid(),
            'channel' => $this->channel = uniqid(),
            'name' => $this->name = uniqid(),
            'nums' => $this->nums = rand(1, 10),
        ]));

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'nsq' => [
                'default' => [
                    'enable' => true
                ]
            ]
        ]));
        $manager = new ConsumerManager($container);
        $manager->run();
        $hasRegisted = false;
        foreach (ProcessManager::all() as $item) {
            if (method_exists($item, 'getConsumer')) {
                $hasRegisted = true;
                /** @var AbstractConsumer $consumer */
                $consumer = $item->getConsumer();
                $this->assertTrue($item->isEnable());
                $this->assertSame($this->name, $consumer->getName());
                $this->assertSame($this->channel, $consumer->getChannel());
                $this->assertSame($this->topic, $consumer->getTopic());
                $this->assertSame($this->nums, $item->nums);
                break;
            }
        }
        $this->assertTrue($hasRegisted);
    }

    public function testConsumerAnnotationNotEnableByConfig()
    {
        $container = ContainerStub::getContainer();
        AnnotationCollector::collectClass(DemoConsumer::class, Consumer::class, new Consumer([
            'topic' => $this->topic = uniqid(),
            'channel' => $this->channel = uniqid(),
            'name' => $this->name = uniqid(),
            'nums' => $this->nums = rand(1, 10),
        ]));

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'nsq' => [
                'default' => [
                    'enable' => false
                ]
            ]
        ]));

        $manager = new ConsumerManager($container);
        $manager->run();

        foreach (ProcessManager::all() as $item) {
            if (method_exists($item, 'getConsumer')) {
                /** @var AbstractConsumer $consumer */
                $this->assertFalse($item->isEnable());
                break;
            }
        }
    }

    public function testConsumerAnnotationNotEnableByConsumer()
    {
        $container = ContainerStub::getContainer();
        AnnotationCollector::collectClass(DisabledDemoConsumer::class, Consumer::class, new Consumer([
            'topic' => $this->topic = uniqid(),
            'channel' => $this->channel = uniqid(),
            'name' => $this->name = uniqid(),
            'nums' => $this->nums = rand(1, 10),
        ]));

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'nsq' => [
                'default' => [
                    'enable' => true
                ]
            ]
        ]));
        $manager = new ConsumerManager($container);
        $manager->run();
        foreach (ProcessManager::all() as $item) {
            if (method_exists($item, 'getConsumer') && ($item->getConsumer() instanceof DisabledDemoConsumer)) {
                $this->assertFalse($item->isEnable());
                break;
            }
        }
    }
}