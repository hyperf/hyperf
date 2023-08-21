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

    /**
     * logger is only meant to be used for development purposes. Enabling
     * an actual logger in production could cause a massive amount of data
     * that will flood the logs on failure.
     */
    private LoggerInterface $logger;

    private SpanSerializer $serializer;

    public function __construct(
        private array $options = [],
        LoggerInterface $logger = null,
        SpanSerializer $serializer = null
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->serializer = $serializer ?? new JsonV2Serializer();

        $config = new ProducerConfig();
        $config->setBootstrapServer($options['broker_list'] ?? '127.0.0.1:9092');
        $config->setUpdateBrokers(true);
        $config->setAcks(-1);
        $this->producer = new Producer($config);
    }

    /**
     * @param array|Span[] $spans
     */
    public function report(array $spans): void
    {
        if (empty($spans)) {
            return;
        }

        $this->serializer->serialize($spans);

        try {
            $this->producer->send(
                $this->options['topic_name'] ?? 'zipkin',
                $this->serializer->serialize($spans)
            );
        } catch (Throwable $e) {
            $this->logger->error(sprintf('failed to report spans: %s', $e->getMessage()));
        }
    }
}
