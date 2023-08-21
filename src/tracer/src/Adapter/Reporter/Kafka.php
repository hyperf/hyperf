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
    private const DEFAULT_OPTIONS = [
        'broker_list' => '',
        'topic_name' => 'zipkin',
    ];

    private Producer $producer;

    private array $options;

    private LoggerInterface $logger;

    private SpanSerializer $serializer;

    public function __construct(
        Producer $producer = null,
        array $options = [],
        LoggerInterface $logger = null,
        SpanSerializer $serializer = null
    ) {
        $this->options = array_replace(self::DEFAULT_OPTIONS, $options);
        $this->serializer = $serializer ?? new JsonV2Serializer();
        $this->logger = $logger ?? new NullLogger();

        if (! $producer) {
            $config = new ProducerConfig();
            $config->setBootstrapServer($options['broker_list'] ?? '127.0.0.1:9092');
            $config->setUpdateBrokers(true);
            $config->setAcks(-1);
            $producer = new Producer($config);
        }

        $this->producer = $producer;
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
                $this->serializer->serialize($spans),
                uniqid('', true)
            );
        } catch (Throwable $e) {
            $this->logger->error(sprintf('failed to report spans: %s', $e->getMessage()));
        }
    }
}
