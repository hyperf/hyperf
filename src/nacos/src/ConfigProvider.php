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
use Hyperf\Nacos\Contract\LoggerInterface;

class ConfigProvider
{
    public function __invoke(): array
    {

        return [
            'listeners' => [
                Service\Listener\MainWorkerStartListener::class,
                Service\Listener\OnShutdownListener::class,
                Config\Listener\MainWorkerStartListener::class,
                Config\Listener\OnPipeMessageListener::class,
            ],
            'processes' => [
                Service\Process\InstanceBeatProcess::class,
                Config\Process\FetchConfigProcess::class,
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
