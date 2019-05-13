<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Tracer;

use GuzzleHttp\Client;
use Zipkin\Tracing;
use Zipkin\TracingBuilder;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Tracing::class => \Hyperf\Tracer\Tracing::class,
                TracingBuilder::class => TracingBuilderFactory::class,
                SwitchManager::class => SwitchManagerFactory::class,
                Client::class => Client::class,
            ],
            'commands' => [
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
        ];
    }
}
