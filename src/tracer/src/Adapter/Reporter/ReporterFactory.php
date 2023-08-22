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

use Hyperf\Tracer\Adapter\HttpClientFactory;
use RuntimeException;
use Zipkin\Reporter;
use Zipkin\Reporters\Http;
use Zipkin\Reporters\Noop;

class ReporterFactory
{
    public function __construct(private HttpClientFactory $clientFactory)
    {
    }

    public function create(array $options): Reporter
    {
        return match ($options['reporter'] ?? 'http') {
            'kafka' => new Kafka(options: $options),
            'http' => new Http($options, $this->clientFactory),
            'noop' => new Noop(),
            default => throw new RuntimeException('Unsupported reporter.'),
        };
    }
}
