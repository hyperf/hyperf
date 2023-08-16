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
namespace HyperfTest\Kafka;

use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Kafka\AbstractConsumer;
use Hyperf\Kafka\Annotation\Consumer;
use Hyperf\Kafka\ConsumerManager;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;
use HyperfTest\Kafka\Stub\ContainerStub;
use HyperfTest\Kafka\Stub\DemoConsumer;
use longlang\phpkafka\Client\SwooleClient;
use longlang\phpkafka\Consumer\ConsumeMessage;
use longlang\phpkafka\Consumer\ConsumerConfig;
use longlang\phpkafka\Socket\SwooleSocket;
use Mockery;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
class ConsumerManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        ProcessManager::clear();
    }

    public function testConsumerAnnotation()
    {
        $container = ContainerStub::getContainer();

        $topic = Arr::random([uniqid(), [uniqid(), uniqid()]]);
        AnnotationCollector::collectClass(DemoConsumer::class, Consumer::class, new Consumer(
            topic: $topic,
            groupId: $groupId = uniqid(),
            memberId: $memberId = uniqid(),
            autoCommit: true,
            nums: $nums = rand(1, 10),
            enable: true,
        ));
        $manager = new ConsumerManager($container);
        $manager->run();
        $hasRegistered = false;
        /** @var AbstractProcess $item */
        foreach (ProcessManager::all() as $item) {
            if (method_exists($item, 'getConsumerConfig')) {
                $hasRegistered = true;
                $config = $container->get(ConfigInterface::class)->get('kafka.default');

                /** @var ConsumerConfig $consumer */
                $consumer = $item->getConsumerConfig();

                $this->assertSame(true, $consumer->getAutoCommit());
                $this->assertSame($config['rack_id'], $consumer->getRackId());
                $this->assertSame($config['replica_id'], $consumer->getReplicaId());
                $this->assertSame((array) $topic, $consumer->getTopic());
                $this->assertSame((float) $config['rebalance_timeout'], $consumer->getRebalanceTimeout());
                $this->assertSame((float) $config['send_timeout'], $consumer->getSendTimeout());
                $this->assertSame($groupId, $consumer->getGroupId());
                $this->assertTrue(strpos($consumer->getGroupInstanceId(), $groupId) !== false);
                $this->assertSame($memberId, $consumer->getMemberId());
                $this->assertSame((float) $config['interval'], $consumer->getInterval());
                $this->assertTrue(in_array($config['bootstrap_servers'], $consumer->getBootstrapServers()));
                $this->assertSame(SwooleSocket::class, $consumer->getSocket());
                $this->assertSame(SwooleClient::class, $consumer->getClient());
                $this->assertSame($config['max_write_attempts'], $consumer->getMaxWriteAttempts());
                $this->assertTrue(strpos($consumer->getClientId(), $config['client_id']) !== false);
                $this->assertSame((float) $config['recv_timeout'], $consumer->getRecvTimeout());
                $this->assertSame((float) $config['connect_timeout'], $consumer->getConnectTimeout());
                $this->assertSame((float) $config['session_timeout'], $consumer->getSessionTimeout());
                $this->assertSame($config['group_retry'], $consumer->getGroupRetry());
                $this->assertSame((float) $config['group_retry_sleep'], $consumer->getGroupRetrySleep());
                $this->assertSame((float) $config['group_heartbeat'], $consumer->getGroupHeartbeat());
                $this->assertSame($config['offset_retry'], $consumer->getOffsetRetry());
                $this->assertSame($config['auto_create_topic'], $consumer->getAutoCreateTopic());
                $this->assertSame($config['partition_assignment_strategy'], $consumer->getPartitionAssignmentStrategy());
                $this->assertSame($nums, $item->nums);
                break;
            }
        }
        $this->assertTrue($hasRegistered);
    }

    public function testConsumerAnnotationNotEnable()
    {
        $container = ContainerStub::getContainer();

        AnnotationCollector::collectClass(DemoConsumer::class, Consumer::class, new Consumer(
            topic: $topic = uniqid(),
            groupId: $groupId = uniqid(),
            memberId: $memberId = uniqid(),
            autoCommit: true,
            nums: $nums = rand(1, 10),
            enable: false,
        ));

        $manager = new ConsumerManager($container);
        $manager->run();

        $hasRegistered = false;
        /** @var AbstractProcess $item */
        foreach (ProcessManager::all() as $item) {
            $this->assertFalse($item->isEnable(new stdClass()));
            break;
        }

        $this->assertFalse($hasRegistered);
    }

    public function testConsumeReturnNull()
    {
        $class = new class() extends AbstractConsumer {
            public function consume(ConsumeMessage $message)
            {
            }
        };

        $result = $class->consume(Mockery::mock(ConsumeMessage::class));
        $this->assertSame(null, $result);
    }
}
