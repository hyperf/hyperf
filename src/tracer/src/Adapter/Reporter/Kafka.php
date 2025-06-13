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

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;
use Zipkin\Recording\Span;
use Zipkin\Reporter;
use Zipkin\Reporters\JsonV2Serializer;
use Zipkin\Reporters\SpanSerializer;

use function count;
use function json_last_error;
use function sprintf;

class Kafka implements Reporter
{
    private LoggerInterface $logger;

    private SpanSerializer $serializer;

    public function __construct(
        private array $options,
        private KafkaClientFactory $clientFactory,
        ?LoggerInterface $logger = null,
        ?SpanSerializer $serializer = null
    ) {
        $this->serializer = $serializer ?? new JsonV2Serializer();
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param array|Span[] $spans
     */
    public function report(array $spans): void
    {
        if (count($spans) === 0) {
            return;
        }

        $payload = $this->serializer->serialize($spans);

        if (! $payload) {
            $this->logger->error(
                sprintf('failed to encode spans with code %d', json_last_error())
            );
            return;
        }

        $client = $this->clientFactory->build($this->options);

        try {
            $client($payload);
        } catch (Throwable $e) {
            $this->logger->error(sprintf('failed to report spans: %s', $e->getMessage()));
        }
    }
}
