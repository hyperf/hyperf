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
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Engine\Channel;
use Hyperf\Kafka\Exception\ConnectionClosedException;
use Hyperf\Kafka\Exception\TimeoutException;
use longlang\phpkafka\Broker;
use longlang\phpkafka\Producer\ProduceMessage;
use longlang\phpkafka\Producer\Producer as LongLangProducer;
use longlang\phpkafka\Producer\ProducerConfig;
use longlang\phpkafka\Socket\SwooleSocket;
use Throwable;

class Producer
{
    protected ?Channel $chan = null;

    protected ?LongLangProducer $producer = null;

    public function __construct(protected ConfigInterface $config, protected string $name = 'default', protected int $timeout = 10)
    {
    }

    public function send(string $topic, ?string $value, ?string $key = null, array $headers = [], ?int $partitionIndex = null): void
    {
        $this->loop();
        $ack = new Channel(1);
        $chan = $this->chan;
        $chan->push(function () use ($topic, $key, $value, $headers, $partitionIndex, $ack) {
            try {
                $this->producer->send($topic, $value, $key, $headers, $partitionIndex);
                $ack->close();
            } catch (Throwable $e) {
                $ack->push($e);
                throw $e;
            }
        });
        if ($chan->isClosing()) {
            throw new ConnectionClosedException('Connection closed.');
        }
        if ($e = $ack->pop($this->timeout)) {
            throw $e;
        }
        if ($ack->isTimeout()) {
            throw new TimeoutException('Kafka send timeout.');
        }
    }

    /**
     * @param ProduceMessage[] $messages
     */
    public function sendBatch(array $messages): void
    {
        $this->loop();
        $ack = new Channel(1);
        $chan = $this->chan;
        $chan->push(function () use ($messages, $ack) {
            try {
                $this->producer->sendBatch($messages);
                $ack->close();
            } catch (Throwable $e) {
                $ack->push($e);
                throw $e;
            }
        });
        if ($chan->isClosing()) {
            throw new ConnectionClosedException('Connection closed.');
        }
        if ($e = $ack->pop()) {
            throw $e;
        }
        if ($ack->isTimeout()) {
            throw new TimeoutException('Kafka send timeout.');
        }
    }

    public function close(): void
    {
        $this->chan?->close();
        $this->producer?->close();
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
                while (true) {
                    $closure = $this->chan?->pop();
                    if (! $closure) {
                        break 2;
                    }
                    try {
                        $closure->call($this);
                    } catch (\Throwable) {
                        $this->producer->close();
                        break;
                    }
                }
            }

            $this->chan?->close();
            $this->chan = null;
            $this->producer->close();
        });

        Coroutine::create(function () {
            if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield()) {
                $this->chan?->close();
            }
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
        $producerConfig->setSocket($config['socket'] ?? SwooleSocket::class);
        $producerConfig->setBootstrapServers($config['bootstrap_servers']);
        $producerConfig->setAcks($config['acks']);
        $producerConfig->setProducerId($config['producer_id']);
        $producerConfig->setProducerEpoch($config['producer_epoch']);
        $producerConfig->setPartitionLeaderEpoch($config['partition_leader_epoch']);
        $producerConfig->setAutoCreateTopic($config['auto_create_topic']);
        ! empty($config['sasl']) && $producerConfig->setSasl($config['sasl']);
        ! empty($config['ssl']) && $producerConfig->setSsl($config['ssl']);
        return new LongLangProducer($producerConfig);
    }
}
