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

namespace Hyperf\Tracer\Adapter\Reporter;

use Closure;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Engine\Channel;
use Hyperf\Engine\Coroutine;
use Hyperf\Tracer\Exception\ConnectionClosedException;
use longlang\phpkafka\Producer\Producer;
use longlang\phpkafka\Producer\ProducerConfig;
use Throwable;
use Zipkin\Reporters\Http\ClientFactory;

use function Hyperf\Support\msleep;

class KafkaClientFactory implements ClientFactory
{
    protected ?Channel $chan = null;

    protected ?Producer $producer = null;

    protected array $options = [];

    protected int $channelSize = 65535;

    public function build(array $options): callable
    {
        $this->options = $options;
        if (isset($options['channel_size'])) {
            $this->channelSize = (int) $options['channel_size'];
        }

        $this->loop();

        return function (string $payload) use ($options): void {
            $topic = $options['topic'] ?? 'zipkin';
            $key = $options['key'] ?? uniqid('', true);
            $headers = $options['headers'] ?? [];
            $partitionIndex = $options['partition_index'] ?? null;
            $chan = $this->chan;

            $chan->push(function () use ($topic, $key, $payload, $headers, $partitionIndex) {
                try {
                    $this->producer->send($topic, $payload, $key, $headers, $partitionIndex);
                } catch (Throwable $e) {
                    throw $e;
                }
            });

            if ($chan->isClosing()) {
                throw new ConnectionClosedException('Connection closed.');
            }
        };
    }

    public function close(): void
    {
        $chan = $this->chan;
        $producer = $this->producer;
        $this->chan = null;
        $this->producer = null;

        $chan?->close();
        $producer?->close();
    }

    protected function loop(): void
    {
        if ($this->chan != null) {
            return;
        }

        $this->chan = new Channel($this->channelSize);

        Coroutine::create(function () {
            while (true) {
                $this->producer = $this->makeProducer();
                while (true) {
                    /** @var null|Closure $closure */
                    $closure = $this->chan?->pop();
                    if (! $closure) {
                        break 2;
                    }
                    try {
                        $closure->call($this);
                    } catch (Throwable) {
                        try {
                            $this->producer->close();
                        } catch (Throwable) {
                        }
                        break;
                    } finally {
                        $closure = null;
                    }
                }
            }

            $this->close();
        });

        Coroutine::create(function () {
            if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield()) {
                while (! $this->chan->isEmpty()) {
                    msleep(100);
                }
                $this->close();
            }
        });
    }

    protected function makeProducer(): Producer
    {
        $options = array_replace([
            'bootstrap_servers' => '127.0.0.1:9092',
            'acks' => -1,
            'connect_timeout' => 1,
            'send_timeout' => 1,
        ], $this->options);
        $config = new ProducerConfig();

        $config->setBootstrapServer($options['bootstrap_servers']);
        $config->setUpdateBrokers(true);
        if (is_int($options['acks'])) {
            $config->setAcks($options['acks']);
        }
        if (is_float($options['connect_timeout'])) {
            $config->setConnectTimeout($options['connect_timeout']);
        }
        if (is_float($options['send_timeout'])) {
            $config->setSendTimeout($options['send_timeout']);
        }

        return new Producer($config);
    }
}
