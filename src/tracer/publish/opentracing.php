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
use Hyperf\Tracer\Adapter\JaegerTracerFactory;
use Hyperf\Tracer\Adapter\NoOpTracerFactory;
use Hyperf\Tracer\Adapter\Reporter\Kafka;
use Hyperf\Tracer\Adapter\ZipkinTracerFactory;
use Zipkin\Reporters\Http;
use Zipkin\Reporters\Noop;
use Zipkin\Samplers\BinarySampler;

use function Hyperf\Support\env;

return [
    // To disable hyperf/opentracing temporarily, set default driver to noop.
    'default' => env('TRACER_DRIVER', 'zipkin'),
    'enable' => [
        'coroutine' => env('TRACER_ENABLE_COROUTINE', false),
        'db' => env('TRACER_ENABLE_DB', false),
        'elasticserach' => env('TRACER_ENABLE_ELASTICSERACH', false),
        'exception' => env('TRACER_ENABLE_EXCEPTION', false),
        'grpc' => env('TRACER_ENABLE_GRPC', false),
        'guzzle' => env('TRACER_ENABLE_GUZZLE', false),
        'method' => env('TRACER_ENABLE_METHOD', false),
        'redis' => env('TRACER_ENABLE_REDIS', false),
        'rpc' => env('TRACER_ENABLE_RPC', false),
        'ignore_exceptions' => [],
    ],
    'tracer' => [
        'zipkin' => [
            'driver' => ZipkinTracerFactory::class,
            'app' => [
                'name' => env('APP_NAME', 'skeleton'),
                // Hyperf will detect the system info automatically as the value if ipv4, ipv6, port is null
                'ipv4' => '127.0.0.1',
                'ipv6' => null,
                'port' => 9501,
            ],
            'reporter' => env('ZIPKIN_REPORTER', 'http'), // kafka, http
            'reporters' => [
                // options for http reporter
                'http' => [
                    'class' => Http::class,
                    'constructor' => [
                        'options' => [
                            'endpoint_url' => env('ZIPKIN_ENDPOINT_URL', 'http://localhost:9411/api/v2/spans'),
                            'timeout' => env('ZIPKIN_TIMEOUT', 1),
                        ],
                    ],
                ],
                // options for kafka reporter
                'kafka' => [
                    'class' => Kafka::class,
                    'constructor' => [
                        'options' => [
                            'topic' => env('ZIPKIN_KAFKA_TOPIC', 'zipkin'),
                            'bootstrap_servers' => env('ZIPKIN_KAFKA_BOOTSTRAP_SERVERS', '127.0.0.1:9092'),
                            'acks' => (int) env('ZIPKIN_KAFKA_ACKS', -1),
                            'connect_timeout' => (int) env('ZIPKIN_KAFKA_CONNECT_TIMEOUT', 1),
                            'send_timeout' => (int) env('ZIPKIN_KAFKA_SEND_TIMEOUT', 1),
                        ],
                    ],
                ],
                'noop' => [
                    'class' => Noop::class,
                ],
            ],
            'sampler' => BinarySampler::createAsAlwaysSample(),
        ],
        'jaeger' => [
            'driver' => JaegerTracerFactory::class,
            'name' => env('APP_NAME', 'skeleton'),
            'options' => [
                /*
                 * You can uncomment the sampler lines to use custom strategy.
                 *
                 * For more available configurations,
                 * @see https://github.com/jonahgeorge/jaeger-client-php
                 */
                // 'sampler' => [
                //     'type' => \Jaeger\SAMPLER_TYPE_CONST,
                //     'param' => true,
                // ],,
                'local_agent' => [
                    'reporting_host' => env('JAEGER_REPORTING_HOST', 'localhost'),
                    'reporting_port' => env('JAEGER_REPORTING_PORT', 5775),
                ],
            ],
        ],
        'noop' => [
            'driver' => NoOpTracerFactory::class,
        ],
    ],
    'tags' => [
        'http_client' => [
            'http.url' => 'http.url',
            'http.method' => 'http.method',
            'http.status_code' => 'http.status_code',
        ],
        'redis' => [
            'arguments' => 'arguments',
            'result' => 'result',
        ],
        'db' => [
            'db.query' => 'db.query',
            'db.statement' => 'db.statement',
            'db.query_time' => 'db.query_time',
        ],
        'exception' => [
            'class' => 'exception.class',
            'code' => 'exception.code',
            'message' => 'exception.message',
            'stack_trace' => 'exception.stack_trace',
        ],
        'request' => [
            'path' => 'request.path',
            'uri' => 'request.uri',
            'method' => 'request.method',
            'header' => 'request.header',
            // 'body' => 'request.body',
        ],
        'coroutine' => [
            'id' => 'coroutine.id',
        ],
        'response' => [
            'status_code' => 'response.status_code',
            // 'body' => 'response.body',
        ],
        'rpc' => [
            'path' => 'rpc.path',
            'status' => 'rpc.status',
        ],
    ],
];
