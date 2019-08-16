<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

use Zipkin\Samplers\BinarySampler;

return [
    'enable' => [
        'guzzle' => env('TRACER_ENABLE_GUZZLE', false),
        'redis' => env('TRACER_ENABLE_REDIS', false),
        'db' => env('TRACER_ENABLE_DB', false),
    ],
    'zipkin' => [
        'app' => [
            'name' => env('APP_NAME', 'skeleton'),
            // Hyperf will detect the system info automatically as the value if ipv4, ipv6, port is null
            'ipv4' => '127.0.0.1',
            'ipv6' => null,
            'port' => 9501,
        ],
        'options' => [
            'endpoint_url' => env('ZIPKIN_ENDPOINT_URL', 'http://localhost:9411/api/v2/spans'),
            'timeout' => env('ZIPKIN_TIMEOUT', 1),
        ],
        'sampler' => BinarySampler::createAsAlwaysSample(),
    ],
];
