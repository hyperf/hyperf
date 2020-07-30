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
namespace Hyperf\Nacos;

use Hyperf\Framework\Logger\StdoutLogger;
use Hyperf\Nacos\Config\FetchConfigProcess;
use Hyperf\Nacos\Config\OnPipeMessageListener;
use Hyperf\Nacos\Contract\LoggerInterface;
use Hyperf\Nacos\Listener\MainWorkerStartListener;
use Hyperf\Nacos\Listener\OnShutdownListener;
use Hyperf\Nacos\Process\InstanceBeatProcess;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                MainWorkerStartListener::class,
                OnShutdownListener::class,
                OnPipeMessageListener::class,
            ],
            'processes' => [
                InstanceBeatProcess::class,
                FetchConfigProcess::class,
            ],
            'dependencies' => [
                LoggerInterface::class => StdoutLogger::class,
            ],
            'annotations' => [],
            'publish' => [
                [
                    'id' => 'nacos',
                    'description' => 'The config for nacos.',
                    'source' => __DIR__ . '/../publish/nacos.php',
                    'destination' => BASE_PATH . '/config/autoload/nacos.php',
                ],
            ],
        ];
    }
}
