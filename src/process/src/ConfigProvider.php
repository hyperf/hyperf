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
namespace Hyperf\Process;

use Hyperf\Process\Listener\BootProcessListener;
use Hyperf\Process\Listener\LogAfterProcessStoppedListener;
use Hyperf\Process\Listener\LogBeforeProcessStartListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                BootProcessListener::class,
                LogAfterProcessStoppedListener::class,
                LogBeforeProcessStartListener::class,
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
