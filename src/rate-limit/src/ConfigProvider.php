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

namespace Hyperf\RateLimit;

use Hyperf\RateLimit\Aspect\RateLimitAnnotationAspect;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'aspects' => [
                RateLimitAnnotationAspect::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for rate-limit.',
                    'source' => __DIR__ . '/../publish/rate_limit.php',
                    'destination' => BASE_PATH . '/config/autoload/rate_limit.php',
                ],
            ],
        ];
    }
}
