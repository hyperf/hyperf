<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE.md
 */

namespace Hyperf\Tracer;

use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;

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
            // beta feature, please donot enable 'method' in production environment
            'method' => false,
        ];

    /**
     * Apply the configuration to SwitchManager.
     */
    public function apply(array $config): void
    {
        $this->config = array_replace($this->config, $config);
    }

    /**
     * Determire if the tracer is enable ?
     */
    public function isEnable(string $identifier): bool
    {
        if (! isset($this->config[$identifier])) {
            return false;
        }

        return $this->config[$identifier] && Context::get(ServerRequestInterface::class) instanceof ServerRequestInterface;
    }
}
