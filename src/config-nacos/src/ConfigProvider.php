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
namespace Hyperf\ConfigNacos;

use Hyperf\ConfigNacos\Listener\MainWorkerStartListener;
use Hyperf\ConfigNacos\Listener\OnShutdownListener;
use Hyperf\ConfigNacos\Process\InstanceBeatProcess;
use Hyperf\ConfigNacos\Service\IPReader;
use Hyperf\ConfigNacos\Service\IPReaderInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                MainWorkerStartListener::class,
                OnShutdownListener::class,
            ],
            'processes' => [
                InstanceBeatProcess::class,
            ],
            'dependencies' => [
                IPReaderInterface::class => IPReader::class,
            ],
        ];
    }
}
