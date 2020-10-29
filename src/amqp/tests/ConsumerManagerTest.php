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
namespace HyperfTest\Amqp;

use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\ConsumerManager;
use Hyperf\Amqp\Message\ConsumerMessageInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;
use HyperfTest\Amqp\Stub\ContainerStub;
use HyperfTest\Amqp\Stub\DemoConsumer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ConsumerManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        ProcessManager::clear();
    }

    public function testConsumerAnnotation()
    {
        $container = ContainerStub::getContainer();

        AnnotationCollector::collectClass(DemoConsumer::class, Consumer::class, new Consumer([
            'exchange' => $exchange = uniqid(),
            'routingKey' => $routingKey = uniqid(),
            'queue' => $queue = uniqid(),
            'nums' => $nums = rand(1, 10),
            'maxConsumption' => $maxConsumption = rand(1, 10),
        ]));

        $manager = new ConsumerManager($container);
        $manager->run();

        $hasRegistered = false;
        /** @var AbstractProcess $item */
        foreach (ProcessManager::all() as $item) {
            if (method_exists($item, 'getConsumerMessage')) {
                $hasRegistered = true;
                /** @var ConsumerMessageInterface $message */
                $message = $item->getConsumerMessage();
                $this->assertTrue($item->isEnable(new \stdClass()));
                $this->assertSame($exchange, $message->getExchange());
                $this->assertSame($routingKey, $message->getRoutingKey());
                $this->assertSame($queue, $message->getQueue());
                $this->assertSame($nums, $item->nums);
                $this->assertSame($maxConsumption, $message->getMaxConsumption());
                break;
            }
        }

        $this->assertTrue($hasRegistered);
    }

    public function testConsumerAnnotationNotEnable()
    {
        $container = ContainerStub::getContainer();

        AnnotationCollector::collectClass(DemoConsumer::class, Consumer::class, new Consumer([
            'exchange' => $exchange = uniqid(),
            'routingKey' => $routingKey = uniqid(),
            'queue' => $queue = uniqid(),
            'nums' => $nums = rand(1, 10),
            'enable' => false,
        ]));

        $manager = new ConsumerManager($container);
        $manager->run();

        $hasRegistered = false;
        /** @var AbstractProcess $item */
        foreach (ProcessManager::all() as $item) {
            if (method_exists($item, 'getConsumerMessage')) {
                $hasRegistered = true;
                $this->assertFalse($item->isEnable(new \stdClass()));
                break;
            }
        }

        $this->assertTrue($hasRegistered);
    }
}
