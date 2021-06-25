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
namespace Hyperf\ServiceGovernanceNacos;

use Hyperf\ConfigNacos\Process\InstanceBeatProcess;
use Hyperf\ServiceGovernanceNacos\Listener\MainWorkerStartListener;
use Hyperf\ServiceGovernanceNacos\Listener\OnShutdownListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'listeners' => [
                MainWorkerStartListener::class,
                OnShutdownListener::class,
            ],
            'processes' => [
                InstanceBeatProcess::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
        ];
    }
}
