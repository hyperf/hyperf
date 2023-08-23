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
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;
use Zipkin\Recording\Span;
use Zipkin\Reporter;
use Zipkin\Reporters\JsonV2Serializer;
use Zipkin\Reporters\SpanSerializer;

use function sprintf;

class Kafka implements Reporter
{
    private Producer $producer;

    private string $topic;

    private LoggerInterface $logger;

    private SpanSerializer $serializer;

    public function __construct(
        array $options = [],
        Producer $producer = null,
        LoggerInterface $logger = null,
        SpanSerializer $serializer = null
    ) {
        $this->topic = $options['topic'] ?? 'zipkin';
        $this->serializer = $serializer ?? new JsonV2Serializer();
        $this->logger = $logger ?? new NullLogger();
        $this->producer = $producer ?? $this->createProducer($options);
    }

    /**
     * @param array|Span[] $spans
     */
    public function report(array $spans): void
    {
        if (empty($spans)) {
            return;
        }

        try {
            $this->producer->send(
                $this->topic,
                $this->serializer->serialize($spans),
                uniqid('', true)
            );
        } catch (Throwable $e) {
            $this->logger->error(sprintf('failed to report spans: %s', $e->getMessage()));
        }
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
