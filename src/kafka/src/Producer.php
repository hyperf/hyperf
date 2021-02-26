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
namespace Hyperf\Kafka;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Engine\Channel;
use Hyperf\Kafka\Transport\SwooleSocket;
use longlang\phpkafka\Broker;
use longlang\phpkafka\Producer\ProduceMessage;
use longlang\phpkafka\Producer\Producer as LongLangProducer;
use longlang\phpkafka\Producer\ProducerConfig;
use Swoole\Coroutine;

class Producer
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ?Channel
     */
    protected $chan;

    /**
     * @var LongLangProducer
     */
    protected $producer;

    /**
     * @var array
     */
    protected $topicsMeta;

    public function __construct(ConfigInterface $config, string $name = 'default')
    {
        $this->config = $config;
        $this->name = $name;
    }

    public function send(string $topic, ?string $value, ?string $key = null, array $headers = [], ?int $partitionIndex = null): void
    {
        $this->loop();
        $ack = new Channel();
        $this->chan->push(function () use ($topic, $key, $value, $headers, $partitionIndex, $ack) {
            try {
                if (! isset($this->topicsMeta[$topic])) {
                    $this->producer->send($topic, $value, $key, $headers);
                    $ack->close();
                    return;
                }

                if (! is_int($partitionIndex)) {
                    $index = $this->getIndex($key, $value, count($this->topicsMeta[$topic]));
                    $partitionIndex = array_keys($this->topicsMeta[$topic])[$index];
                }

                $this->producer->send(
                    $topic,
                    $value,
                    $key,
                    $headers,
                    $partitionIndex,
                    $this->topicsMeta[$topic][$partitionIndex]
                );
                $ack->close();
            } catch (\Throwable $e) {
                $ack->push($e);
                throw $e;
            }
        });
        if ($e = $ack->pop()) {
            throw $e;
        }
    }

    /**
     * @param ProduceMessage[] $messages
     */
    public function sendBatch(array $messages): void
    {
        $this->loop();
        $ack = new Channel();
        $this->chan->push(function () use ($messages, $ack) {
            try {
                $messagesByBroker = $this->slitByBroker($messages);
                foreach ($messagesByBroker as $brokerId => $messages) {
                    $this->producer->sendBatch($messages, $brokerId);
                }
                $ack->close();
            } catch (\Throwable $e) {
                $ack->push($e);
                throw $e;
            }
        });
        if ($e = $ack->pop()) {
            throw $e;
        }
    }

    public function close(): void
    {
        if ($this->chan) {
            $this->chan->close();
        }
    }

    public function getConfig(): ProducerConfig
    {
        return $this->producer->getConfig();
    }

    public function getBroker(): Broker
    {
        return $this->producer->getBroker();
    }

    protected function loop()
    {
        if ($this->chan != null) {
            return;
        }
        $this->chan = new Channel(1);
        Coroutine::create(function () {
            while (true) {
                $this->producer = $this->makeProducer();
                $this->topicsMeta = $this->fetchMeta();
                while (true) {
                    $closure = $this->chan->pop();
                    if (! $closure) {
                        break 2;
                    }
                    try {
                        $closure->call($this);
                    } catch (\Throwable $e) {
                        $this->producer->close();
                        break;
                    }
                }
            }
            /* @phpstan-ignore-next-line */
            $this->chan = null;
        });
    }

    private function makeProducer(): LongLangProducer
    {
        $config = $this->config->get('kafka.' . $this->name);
        $producerConfig = new ProducerConfig();
        $producerConfig->setConnectTimeout($config['connect_timeout']);
        $producerConfig->setSendTimeout($config['send_timeout']);
        $producerConfig->setRecvTimeout($config['recv_timeout']);
        $producerConfig->setClientId($config['client_id']);
        $producerConfig->setMaxWriteAttempts($config['max_write_attempts']);
        $producerConfig->setSocket(SwooleSocket::class);
        $producerConfig->setBootstrapServer($config['bootstrap_server']);
        $producerConfig->setUpdateBrokers($config['update_brokers']);
        $producerConfig->setBrokers($config['brokers']);
        $producerConfig->setAcks($config['acks']);
        $producerConfig->setProducerId($config['producer_id']);
        $producerConfig->setProducerEpoch($config['producer_epoch']);
        $producerConfig->setPartitionLeaderEpoch($config['partition_leader_epoch']);
        $producerConfig->setAutoCreateTopic($config['auto_create_topic']);
        return new LongLangProducer($producerConfig);
    }

    private function getIndex($key, $value, $max)
    {
        if ($key === null) {
            return crc32($value) % $max;
        }
        return crc32($key) % $max;
    }

    /**
     * @param ProduceMessage[] $messages
     */
    private function slitByBroker(array $messages): array
    {
        $messageByBroker = [];
        foreach ($messages as $message) {
            $messageByBroker[$this->getMessageBrokerId($message)][] = $message;
        }
        return $messageByBroker;
    }

    private function getMessageBrokerId(ProduceMessage $message): int
    {
        return $this->topicsMeta[$message->getTopic()][$message->getPartitionIndex()];
    }

    private function fetchMeta(): array
    {
        $metaCache = [];
        $topicMeta = $this->producer->getBroker()->getTopicsMeta();
        foreach ($topicMeta as $meta) {
            foreach ($meta->getPartitions() as $partition) {
                $metaCache[$meta->getName()][$partition->getPartitionIndex()] = $partition->getLeaderId();
            }
        }
        return $metaCache;
    }
}
