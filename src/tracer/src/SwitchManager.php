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

namespace Hyperf\Tracer;

use Hyperf\Context\Context;
use OpenTracing\Span;
use Throwable;

class SwitchManager
{
    /**
     * @var array
     */
    private $config
        = [
            'guzzle' => false,
            'redis' => false,
            'db' => false,
            // beta feature, please don't enable 'method' in production environment
            'method' => false,
            'error' => false,
            'ignore_exceptions' => [],
        ];

    /**
     * Apply the configuration to SwitchManager.
     */
    public function apply(array $config): void
    {
        $this->config = array_replace($this->config, $config);
    }

    /**
     * Determine if the tracer is enabled ?
     */
    public function isEnable(string $identifier): bool
    {
        if (! isset($this->config[$identifier])) {
            return false;
        }

        return $this->config[$identifier] && Context::get('tracer.root') instanceof Span;
    }

    public function isIgnoreException(string|Throwable $exception): bool
    {
        $ignoreExceptions = $this->config['ignore_exceptions'] ?? [];
        foreach ($ignoreExceptions as $ignoreException) {
            if (is_a($exception, $ignoreException, true)) {
                return true;
            }
        }
        return false;
    }
}
