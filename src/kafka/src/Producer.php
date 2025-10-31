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
use InvalidArgumentException;
use longlang\phpkafka\Broker;
use longlang\phpkafka\Producer\ProduceMessage;
use longlang\phpkafka\Producer\Producer as LongLangProducer;
use longlang\phpkafka\Producer\ProducerConfig;
use longlang\phpkafka\Socket\SwooleSocket;
use Throwable;

class Producer
{
    public const SINGLE = 1;

    public const BATCH = 2;

    protected ?Channel $chan = null;

    protected ?LongLangProducer $producer = null;

    protected int $channelSize = 65535;

    public function __construct(protected ConfigInterface $config, protected string $name = 'default', protected int $timeout = 10)
    {
    }

    public function send(string $topic, ?string $value, ?string $key = null, array $headers = [], ?int $partitionIndex = null): void
    {
        try {
            $this->sendAsync($topic, $value, $key, $headers, $partitionIndex)->wait();
        } catch (TimeoutException $e) {
            $this->close();
            throw $e;
        }
    }

    public function sendAsync(string $topic, ?string $value, ?string $key = null, array $headers = [], ?int $partitionIndex = null): Promise
    {
        $this->loop();
        $promise = new Promise($this->timeout);
        $chan = $this->chan;
        $chan?->push([self::SINGLE, [$topic, $value, $key, $headers, $partitionIndex], $promise]);
        if ($chan?->isClosing()) {
            throw new ConnectionClosedException('Connection closed.');
        }
        return $promise;
    }

    public function sendBatch(array $messages): void
    {
        try {
            $this->sendBatchAsync($messages)->wait();
        } catch (TimeoutException $e) {
            $this->close();
            throw $e;
        }
    }

    /**
     * @param ProduceMessage[] $messages
     */
    public function sendBatchAsync(array $messages): Promise
    {
        $this->loop();
        $promise = new Promise($this->timeout);
        $chan = $this->chan;
        $chan?->push([self::BATCH, [$messages], $promise]);
        if ($chan?->isClosing()) {
            throw new ConnectionClosedException('Connection closed.');
        }
        return $promise;
    }

    public function close(): void
    {
        $this->chan?->close();
        $this->chan = null;
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
        $this->chan = new Channel($this->channelSize);
        Coroutine::create(function () {
            while (true) {
                $this->producer = $this->makeProducer();
                while (true) {
                    /** @var array{int, array, Promise}|bool $data */
                    $data = $this->chan?->pop();
                    if (! $data) {
                        break 2;
                    }
                    [$type, $args, $promise] = $data;
                    try {
                        match ($type) {
                            self::SINGLE => $this->producer->send(...$args),
                            self::BATCH => $this->producer->sendBatch(...$args),
                            default => throw new InvalidArgumentException('Unknown producer type: ' . var_export($type, true)),
                        };
                        $promise->close();
                    } catch (Throwable $e) {
                        $this->producer->close();
                        $promise->push($e);

                        if ($callback = $this->getConfig()->getExceptionCallback()) {
                            $callback($e);
                        }

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
        isset($config['produce_retry_sleep']) && $producerConfig->setProduceRetrySleep($config['produce_retry_sleep']);
        isset($config['produce_retry']) && $producerConfig->setProduceRetry($config['produce_retry']);
        $producerConfig->setAutoCreateTopic($config['auto_create_topic']);
        ! empty($config['sasl']) && $producerConfig->setSasl($config['sasl']);
        ! empty($config['ssl']) && $producerConfig->setSsl($config['ssl']);
        is_callable($config['exception_callback'] ?? null) && $producerConfig->setExceptionCallback($config['exception_callback']);
        return new LongLangProducer($producerConfig);
    }
}
