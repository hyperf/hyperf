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

use longlang\phpkafka\Producer\Producer;
use longlang\phpkafka\Producer\ProducerConfig;

class KafkaClientFactory
{
    private ?Producer $producer = null;

    public function build(array $options): callable
    {
        $this->producer ??= $this->createProducer($options);

        return function (string $payload) use ($options): void {
            $this->producer->send(
                $options['topic'] ?? 'zipkin',
                $payload,
                uniqid('', true)
            );
        };
    }

    private function createProducer(array $options): Producer
    {
        $options = array_replace([
            'bootstrap_servers' => '127.0.0.1:9092',
            'acks' => -1,
            'connect_timeout' => 1,
            'send_timeout' => 1,
        ], $options);
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
