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

namespace Hyperf\ServiceGovernanceConsul;

use Hyperf\ServiceGovernanceConsul\Listener\RegisterDriverListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ConsulAgent::class => ConsulAgentFactory::class,
            ],
            'listeners' => [
                RegisterDriverListener::class,
            ],
        ];
    }
}
